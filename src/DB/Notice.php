<?php
namespace Chama\DB;
use Chama\DB\DbManager;

/**
 * Notice
 * Manages all the notice functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class Notice {
	private $db;
	public function __construct() {
		$this->db = DbManager::connect();
	}
	public function findForChama($chamaId, $page = 1) {

		$this->db->where('chama_id', $chamaId);
		//only load those whose date has not passed
		$this->db->where('date', date('Y-m-d'), ">=");
		$this->db->join("users", "users.user_id=chama_notices.posted_by", "LEFT");

		$data = $this->db->paginate("chama_notices", $page, "chama_notices.title, chama_notices.venue, chama_notices.date,chama_notices.time,chama_notices.description,chama_notices.id AS notice_id, users.name,users.phonenumber,users.user_id");

		return $data;

	}
	public function countForChama($chamaId) {
		$this->db->where('chama_id', $chamaId);

		$result = $this->db->getOne("chama_notices", "COUNT(*) as cnt");
		return $result['cnt'];
	}
	public function single($noticeId) {
		$this->db->where("id", $noticeId);
		$this->db->join("users", "users.user_id=chama_notices.posted_by", "LEFT");
		return $this->db->getOne("chama_notices", "chama_notices.title, chama_notices.venue, chama_notices.date,chama_notices.time,chama_notices.description,chama_notices.id AS notice_id, users.name,users.phonenumber,users.user_id");
	}
	public function create($chamaId, $title, $date, $venue, $time, $description, $posted_by) {
		$id = $this->db->insert('chama_notices', array(
			'chama_id' => $chamaId,
			'title' => $title,
			'date' => $date,
			'venue' => $venue,
			'time' => $time,
			'posted_by' => $posted_by,
			'description' => $description,
		));
		return $id;
	}
	public function findForDate($chamaId, $date, $page = 1) {
		$offset = $page * 10;
		$this->db->where('chama_id', $chamaId);
		$this->db->where('date', $date, ">=");

		return $this->db->paginate("chama_notices", $page);
	}
	public function deleteAllForChama($chamaId) {
		$this->db->where("chama_id", $chamaId);
		return $this->db->delete('chama_notices');
	}
	public function update($noticeId, $title, $date, $venue, $time, $description) {
		$this->db->where("id", $noticeId);
		$data = Array(
			'title' => $title,
			'date' => $date,
			'venue' => $venue,
			'time' => $time,
			'description' => $description,
		);
		return $this->db->update('chama_notices', $data);
	}
	public function delete($noticeId) {
		$this->db->where('id', $noticeId);
		return $this->db->delete('chama_notices');

	}
}