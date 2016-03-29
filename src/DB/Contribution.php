<?php
namespace Chama\DB;
use Chama\DB\Chama;
use Chama\DB\DbManager;
use Chama\DB\Fine;
use Chama\Utils\Money;

/**
 * Contribution
 * Manages all the contribution functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class Contribution {
	private $db;
	public function __construct() {
		$this->db = DbManager::connect();
	}
	public function forChama($chamaId, $page = 1) {
		$this->db->join("chama_members",
			"member_contributions.membership_id = chama_members.membership_id", "LEFT");
		$this->db->join("users", "chama_members.user_id = users.user_id", "INNER");
		$this->db->where("chama_members.chama_id", $chamaId);
		$data = $this->db->paginate("member_contributions", $page, "member_contributions.amount,member_contributions.month, member_contributions.year, member_contributions.contribution_date, member_contributions.id AS contribution_id, users.name,users.phonenumber,users.user_id");

		$out = array();

		foreach ($data as $contribution) {
			$contribution["decorated_amount"] = Money::format('%i', $contribution["amount"]);
			$contribution["month"] = date("F", mktime(0, 0, 0, $contribution["month"], 10));

			$out[] = $contribution;
		}
		return $out;
	}
	public function sumForChama($chamaId) {
		$this->db->join("chama_members",
			"member_contributions.membership_id = chama_members.membership_id", "LEFT");
		$this->db->where("chama_members.chama_id", $chamaId);
		$result = $this->db->getOne("member_contributions", "SUM(amount) as amount");

		return (is_null($result['amount'])) ? 0 : $result['amount'];

	}
	public function single($contributionId) {
		$this->db->where("id", $contributionId);
		$contribution = $this->db->getOne("member_contributions");
		$contribution["amount"] = Money::format('%i', $contribution["amount"]);
		return $contribution;

	}
	public function forUser($chamaId, $year, $targetUser) {
		$this->db->join("chama_members",
			"member_contributions.membership_id = chama_members.membership_id", "LEFT");
		$this->db->join("member_fines",
			"member_contributions.membership_id = member_fines.membership_id", "LEFT");
		$this->db->where("chama_members.chama_id", $chamaId);
		$this->db->where("chama_members.user_id", $targetUser);
		$this->db->where("member_contributions.year", $year, "=");
		$data = $this->db->paginate("member_contributions", 1, "amount,member_contributions.month as month, member_contributions.year as year,contribution_date, member_contributions.id AS contribution_id, fine_amount");

		$out = array();
		$foundMonths = array();
		foreach ($data as $contribution) {
			$contribution["decorated_amount"] = Money::format('%i', $contribution["amount"]);
			$contribution["fine_amount"] = Money::format('%i', $contribution["fine_amount"]);
			$contribution["month"] = date("F", mktime(0, 0, 0, $contribution["month"], 10));
			$out[] = $contribution;
			$foundMonths[] = $contribution["month"];
		}

//for months where user has not contributed, set a zero
		for ($i = 1; $i <= 12; $i++) {
			if (!in_array($i, $foundMonths)) {
				$newContr = array(
					"amount" => Money::format('%i', 0),
					"decorated_amount" => Money::format('%i', 0),
					"month" => date("F", mktime(0, 0, 0, $i, 10)),
					"year" => $year,
					"fine_amount" => Money::format('%i', 0),
					"contribution_date" => null,
					"contribution_id" => null,
				);
				$data[] = $newContr;
			}
		}

		return $out;
	}

	public function getUserContributionYears($chamaId, $targetUser) {
		$this->db->join("chama_members",
			"member_contributions.membership_id = chama_members.membership_id", "LEFT");
		$this->db->where("chama_members.chama_id", $chamaId);
		$this->db->where("chama_members.user_id", $targetUser);
		$data = $this->db->getValue("member_contributions", "year", null);
		return (!is_null($data)) ? array_unique($data) : [];
	}
	public function create($month, $year, $amount, $date, $memberId, $postedBy, $chamaId) {

		//check whether deadline has been met and whether the user had cleared their contribution for that month.
		$chamas = new Chama();
		$chama = $chamas->find($chamaId);
		$fines = new Fine();
		if ($date > $chama["contribution_date"]) {
			//they should be fined!!
			$fines->create($memberId, $month, $year, $chama["fine_charges"]);
		}

		$id = $this->db->insert('member_contributions', array(
			'membership_id' => $memberId,
			'month' => $month,
			'year' => $year,
			'amount' => $amount,
			'contribution_date' => $date,
			'posted_by' => $postedBy,
		));

		return $id;
	}
	public function delete($contributionsId) {
		$this->db->where('id', $contributionsId);
		return $this->db->delete('member_contributions');

	}
	public function deleteAllForChama($chamaId) {
		$this->db->where("chama_id", $chamaId);
		return $this->db->delete('member_contributions');
	}

}