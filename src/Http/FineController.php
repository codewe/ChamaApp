<?php
namespace Chama\Http;
use Chama\DB\Fine;
use Chama\DB\Membership;
use Chama\Http\BaseController;
use Chama\Utils\Input;

/**
 * FineController
 * Manages all the expense functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class FineController extends BaseController {
	private $expenseModel;
	private $memberships;
	public function __construct() {
		$this->finesModel = new Fine();
		$this->memberships = new Membership();

	}
	public function forChama() {

		$chamaId = $this->getChamaId();
		$userId = $this->getCurrentUserId();
		$this->renderResponse(array(
			"status" => "success",
			"description" => "",
			"fines" => $this->finesModel->forChama($chamaId),
		));

	}
	public function pay() {

		$chamaId = $this->getChamaId();
		$userId = $this->getCurrentUserId();
		$month = Input::get("month");
		$amount = Input::get("amount");
		$year = Input::get("year");
		$target_user = Input::get("target_user");
		$membershipId = $this->memberships->findMembershipId($target_user, $chamaId);

		if ($month == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `month` field is required ",
			));
		} elseif ($year == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `year` field is required ",
			));
		}

		$this->renderResponse(array(
			"status" => "success",
			"description" => "",
			"fines" => $this->finesModel->pay($membershipId, $amount, $month, $year, $userId),
		));

	}
	public function forUser() {

		$chamaId = $this->getChamaId();
		$month = Input::get("month");
		$year = Input::get("year");
		$userId = $this->getCurrentUserId();
		if ($month == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `month` field is required ",
			));
		} elseif ($year == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `year` field is required ",
			));
		} else {
			$this->renderResponse(array(
				"status" => "success",
				"description" => "",
				"fines" => $this->finesModel->forUser($userId, $chamaId, $year),
			));

		}

	}
}