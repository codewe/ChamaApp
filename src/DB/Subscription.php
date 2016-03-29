<?php
namespace Chama\DB;
use Chama\DB\DbManager;
use Chama\Utils\Constants;
use DateTime;

/**
 * Subscription
 * Manages all the Subscription functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class Subscription {
	private $db;
	public function __construct() {
		$this->db = DbManager::connect();
	}
	public function create($chamaId, $activationCode, $start_date, $end_date, $isFirstTime = false) {
		$data = Array(
			'activation_code' => $activationCode,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'chama_id' => $chamaId,
		);
		if ($isFirstTime) {
			return $this->db->insert('chama_subscriptions', $data);
		} else {
			$this->db->where('chama_id', $chamaId);
			return $this->db->update('chama_subscriptions', $data);
		}

	}
	public function isTrial($activation_code) {

		if ($activation_code === Constants::DEFAULT_ACTIVATION_CODE) {

			return true;
		}
		return false;

	}
	public function hasTrialExpired($end_date) {
		//just compare the end date and the current time

		$now = new DateTime("now");
		$end = new DateTime($end_date);

		return ($now >= $end) ? true : false;

	}
	public function remainingDays($end_date) {
		if ($this->hasTrialExpired($end_date)) {
			return "0 days";
		} else {
			$now = new DateTime;
			$end = new DateTime($end_date);
			$interval = $now->diff($end);
			$formattedInterval = $interval->format('%a days');
			return $formattedInterval;
		}

	}
	public function deleteForChama($chamaId) {
		$this->db->where("chama_id", $chamaId);
		return $this->db->delete('chama_subscriptions');
	}
	public function findForChama($chamaId) {
		$this->db->where("chama_id", $chamaId);
		$data = $this->db->getOne('chama_subscriptions');
		return $data;
	}
}