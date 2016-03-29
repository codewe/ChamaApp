<?php
namespace Chama\DB;
use Chama\DB\Contribution;
use Chama\DB\DbManager;
use Chama\DB\Expense;
use Chama\DB\Fine;
use Chama\DB\Membership;
use Chama\DB\Notice;
use Chama\DB\Subscription;
use Chama\Utils\Constants;
use Chama\Utils\Money;
use Chama\Utils\Sms;
use DateInterval;
use DateTime;

/**
 * Chama money_format('%i', $number)
 * Manages all the Chama functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class Chama {
	private $db;
	public function __construct() {
		$this->db = DbManager::connect();
	}
	public function find($chamaId) {
		$memberships = new Membership();
		$notices = new Notice();
		$expenses = new Expense();
		$fines = new Fine();
		$contributions = new Contribution();

		$this->db->where("chama_id", $chamaId);

		//find the basic details
		$basicData = $this->db->getOne("chama");
		//add chama status data
		$basicData["total_members"] = $memberships->countForChama($chamaId);
		$basicData["notices"] = $notices->countForChama($chamaId);
		$totalExpenses = $expenses->sumForChama($chamaId);
		$basicData["total_expenses"] = Money::format('%i', $totalExpenses);
		$totalFines = $fines->sumForChama($chamaId);
		$basicData["total_fines"] = Money::format('%i', $totalFines);
		$totalContributions = $contributions->sumForChama($chamaId);
		$basicData["total_contributions"] = Money::format('%i', $totalContributions);

		$total = $totalFines + $totalContributions - $totalExpenses;
		$basicData["total"] = Money::format('%i', $total);

		//return the data
		return $basicData;
	}

	public function create($name, $members, $date, $fine, $contributions, $chairman) {
		$subscriptions = new Subscription();
		$data = Array(
			"chama_name" => $name,
			"members" => $members,
			"contribution_date" => $date,
			"fine_charges" => $fine,
			"monthly_contribution" => $contributions,
			"chairman_id" => $chairman,
			"created_on" => date('Y-m-d H:i:s'),
		);
		//create
		$chamaId = $this->db->insert('chama', $data);

		//determine the end of the trial
		$now = new DateTime;

		//create subscription
		$subscriptions->create($chamaId,
			Constants::DEFAULT_ACTIVATION_CODE,
			$now->format("Y-m-d H:i:s"),
			$now->add(new DateInterval(Constants::TRIAL_PERIOD))->format("Y-m-d H:i:s"),
			true
		);

		//add current user as a member
		$memberships = new Membership();
		$memberships->assignChama($chairman, $chamaId, Constants::CHAIRMAN);

		//send the welcome message
		Sms::trialWelcome($chamaId, $chairman);

		//return the id
		return $chamaId;
	}

	public function delete($chamaId) {
		//1 delete the chama
		$this->db->where('chama_id', $chamaId);
		$this->db->delete('chama');

		$memberships = new Membership();
		//2. delete memberships
		$memberships->deleteAllForChama($chamaId);

		//3. delete notices
		$notices = new Notice();
		$notices->deleteAllForChama($chamaId);
		//4. chama subscriptions
		$subscriptions = new Subscription();
		$subscriptions->deleteForChama($chamaId);
		//5. chama contributions
		$contributions = new Contribution();
		$contributions->deleteAllForChama($chamaId);
		//6. member fines
		$fines = new Fine();
		$fines->deleteAllForChama($chamaId);
		//7. chama expenses
		$expenses = new Expense();
		$expenses->deleteAllForChama($chamaId);

	}
	public function update($chamaId, $name, $members, $date, $fine, $contributions) {
		$this->db->where('chama_id', $chamaId);
		$data = Array(
			"chama_name" => $name,
			"members" => $members,
			"contribution_date" => $date,
			"fine_charges" => $fine,
			"monthly_contribution" => $contributions,
		);
		return $this->db->update('chama', $data);
	}
	public function exists($chamaName, $chairmanId) {

		$this->db->where('chama_name', $chamaName);
		$this->db->where('chairman_id', $chairmanId);
		$chama = $this->db->get("chama");

		return false;

		return (count($chama) > 0) ? true : false;
	}
	public function getSubscriptionDetails($chamaId) {
		$subscriptionsModel = new Subscription();

		$subscription = $subscriptionsModel->findForChama($chamaId);

		$hasTrialExpired = $subscriptionsModel->hasTrialExpired($subscription['end_date']);

		$isTrial = $subscriptionsModel->isTrial($subscription['activation_code']);

		$timeLeft = $subscriptionsModel->remainingDays($subscription['end_date']);

		return array(
			'activation_code' => $subscription['activation_code'],
			'is_trial' => $isTrial,
			'has_trial_expired' => $hasTrialExpired,
			'start_date' => $subscription['start_date'],
			'end_date' => $subscription['end_date'],
			'time_left' => $timeLeft,
		);

	}
}