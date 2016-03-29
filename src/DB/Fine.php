<?php
namespace Chama\DB;
use Chama\DB\DbManager;
use Chama\Utils\Money;

/**
 * Fine
 * Manages all the fine functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class Fine {
	private $db;
	public function __construct() {
		$this->db = DbManager::connect();
	}
	public function forChama($chamaId, $page = 1) {
		$this->db->join("chama_members",
			"member_fines.membership_id = chama_members.membership_id", "LEFT");
		$this->db->join("users", "chama_members.user_id = users.user_id", "INNER");
		$this->db->where("chama_members.chama_id", $chamaId);
		$data = $this->db->paginate("member_fines", $page, "member_fines.month, member_fines.year, member_fines.fine_amount, member_fines.id AS fine_id, users.name,users.phonenumber,users.user_id");

		$out = array();

		foreach ($data as $fine) {
			$fine["decorated_amount"] = Money::format('%i', $fine["fine_amount"]);
			$out[] = $fine;
		}
		return $out;
	}

	public function sumForChama($chamaId) {
		$this->db->join("chama_members",
			"member_fines.membership_id = chama_members.membership_id", "LEFT");
		$this->db->where("chama_members.chama_id", $chamaId);
		$result = $this->db->getOne("member_fines", "SUM(fine_amount) as amount");

		return (is_null($result['amount'])) ? 0 : $result['amount'];

	}
	public function sumForUser($chamaId, $userId) {
		$this->db->join("chama_members", "member_fines.membership_id = chama_members.membership_id", "LEFT");
		$this->db->where("chama_members.user_id", $userId);
		$this->db->where("chama_members.chama_id", $chamaId);
		$result = $this->db->getOne("member_fines", "SUM(fine_amount) as amount");

		return (is_null($result['amount'])) ? 0 : $result['amount'];

	}

	public function forUser($userId, $chamaId, $year, $page = 1) {
		$this->db->join("chama_members", "member_fines.membership_id = chama_members.membership_id", "LEFT");
		$this->db->where("chama_members.user_id", $userId);
		$this->db->where("chama_members.chama_id", $chamaId);
		$this->db->where("year", $year);

		$this->db->where("member_fines.status", "unpaid");
		$data = $this->db->paginate("member_fines", $page);
		$out = array();

		foreach ($data as $fine) {
			$fine["decorated_amount"] = Money::format('%i', $fine["fine_amount"]);
			$out[] = $fine;
		}

		$out["total_pending_fines"] = Money::format('%i', $this->sumForUser($chamaId, $userId));

		return $out;
	}

	public function create($membershipId, $month, $year, $amount) {
		$id = $this->db->insert('member_fines', array(
			'membership_id' => $membershipId,
			'month' => $month,
			'year' => $description,
			'fine_amount' => $amount,
		));
		return $id;
	}
	public function hasFine($membershipId, $month, $year) {

		$this->db->where("membership_id", $membershipId);
		$this->db->where("month", $month);
		$this->db->where("year", $year);
		$this->db->where("status", "unpaid");
		$fineDetails = $this->db->getValue("member_fines", "count(*)");

		return ($count > 0) ? true : false;

	}
	public function hasClearedFine($membershipId, $month, $year) {
		//get amount required for the fine for that particular month and date
		$this->db->where("membership_id", $membershipId);
		$this->db->where("month", $month);
		$this->db->where("year", $year);
		$fineRequired = $this->db->getValue("member_fines", "fine_amount");

		//then check the total amount paid so far.
		$this->db->where("membership_id", $membershipId);
		$this->db->where("month", $month);
		$this->db->where("year", $year);
		$amountPaid = $this->db->getValue("member_fines", "COUNT(amount_paid)");

		//compare and decide if fine has been cleared
		return ($amountPaid >= $fineRequired) ? true : false;
	}
	public function markAsCleared($membershipId, $month, $year) {
		//get amount required for the fine for that particular month and date
		$this->db->where("membership_id", $membershipId);
		$this->db->where("month", $month);
		$this->db->where("year", $year);
		$data = Array(
			'status' => "paid",
		);
		return $this->db->update('member_fines', $data);
	}
	public function pay($membershipId, $amount, $month, $year, $posted_by) {
		//first pay the fine, only if there;s one
		if ($this->hasFine($membershipId, $month, $year)) {
			$id = $this->db->insert('paid_fines', array(
				'membership_id' => $membershipId,
				'amount' => $amount,
				'month' => $month,
				'year' => $year,
				'amount' => $amount,
				'posted_by' => $posted_by,
			));

			//then check whether the fine has been cleared
			if ($this->hasClearedFine($membershipId, $month, $year)) {
				$this->markAsCleared($membershipId, $month, $year);
			}
		}

		//return
		return true;
	}
	public function deleteAllForChama($chamaId) {
		//delete all pending fines
		$this->db->join("chama_members", "member_fines.membership_id = chama_members.membership_id", "LEFT");
		$this->db->where("chama_members.chama_id", $chamaId);
		$this->db->delete('member_fines');

		//delete paid fine information
		$this->db->join("chama_members", "paid_fines.membership_id = chama_members.membership_id", "LEFT");
		$this->db->where("chama_members.chama_id", $chamaId);
		return $this->db->delete('paid_fines');
	}
}