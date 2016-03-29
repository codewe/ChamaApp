<?php
namespace Chama\DB;
use Chama\DB\DbManager;
use forxer\Gravatar\Gravatar;

/**
 * Membership
 * Manages all the user functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class Membership {
	private $db;
	public function __construct() {
		$this->db = DbManager::connect();
	}
	public function assignChama($userId, $chamaId, $role, $created_by = null) {

		$this->removeRole($chamaId, $userId, $role);

		$id = $this->db->insert('chama_members', array(
			'user_id' => $userId,
			'chama_id' => $chamaId,
			'role' => $role,
			'date_joined' => date("Y-m-d"),
			'created_by' => $created_by,
		));

		return $id;
	}
	public function isMemberOf($userId, $chamaId) {
		$this->db->where('user_id', $userId);
		$this->db->where('chama_id', $chamaId);

		$stats = $this->db->getOne("chama_members", "count(*) as cnt");
		$retVal = ((int) $stats['cnt'] > 0) ? true : false;
		return $retVal;
	}
	public function findMembershipId($userId, $chamaId) {
		$this->db->where('user_id', $userId);
		$this->db->where('chama_id', $chamaId);

		$stats = $this->db->getOne("chama_members", "membership_id as id");
		return $stats['id'];
	}
	public function delete($userId, $chamaId) {
		$this->db->where('user_id', $userId);
		$this->db->where('chama_id', $chamaId);
		return $this->db->delete('chama_members');

	}
	public function findForUser($userId, $page = 1) {

		$this->db->join("chama", "chama_members.chama_id = chama.chama_id", "LEFT");
		$this->db->where("chama_members.user_id", $userId);
		return $this->db->paginate("chama_members", $page, "chama.*");

	}
	public function findForChama($chamaId, $page = 1) {
		$this->db->join("chama", "chama_members.chama_id = chama.chama_id", "LEFT");
		$this->db->join("users", "chama_members.user_id=users.user_id", "LEFT");
		$this->db->where("chama_members.chama_id", $chamaId);
		$members = $this->db->paginate("chama_members", $page, "chama_members.role,chama_members.date_joined, users.name,users.phonenumber,users.user_id");

		//assign message
		$out = array();

		foreach ($members as $member) {
			$member['image'] = Gravatar::image($member['role'] . 'email@example.com', 80, 'identicon');
			$out[] = $member;
		}
		return $out;
	}
	public function countForChama($chamaId) {
		$this->db->join("chama", "chama_members.chama_id = chama.chama_id", "LEFT");
		$this->db->where("chama_members.chama_id", $chamaId);
		$result = $this->db->getOne("chama_members", "COUNT(*) as cnt");
		return $result['cnt'];
	}
	public function hasRole($userId, $chamaId, $role) {
		$this->db->where("user_id", $userId);
		$this->db->where("chama_id", $chamaId);
		$data = $this->db->getOne("chama_members");
		return ($data['role'] == $role) ? true : false;
	}
	public function changeRole($userId, $chamaId, $role) {
		$this->db->where("chama_id", $chamaId);
		$this->db->where("user_id", $userId);
		$data = Array(
			'role' => $role,
		);
		return $this->db->update('chama_members', $data);
	}
	public function removeRole($chamaId, $userId, $role) {
		$roles = array("secretary", "treasurer");
		if (in_array($role, $roles)) {
			$this->db->where("chama_id", $chamaId);
			$this->db->where("role", $role);
			$data = Array(
				'role' => "member",
			);
			return $this->db->update('chama_members', $data);
		} else {
			$this->db->where("chama_id", $chamaId);
			$this->db->where("user_id", $userId);
			$this->db->where("role", $role);
			return $this->db->delete('chama_members');
		}

	}
	public function findUserRole($chamaId, $userId) {
		$this->db->where("chama_id", $chamaId);
		$this->db->where("user_id", $userId);
		$roles = $this->db->getValue('chama_members', "role");
		return $roles;

	}
	public function deleteAllForChama($chamaId) {
		$this->db->where("chama_id", $chamaId);
		return $this->db->delete('chama_members');
	}
}