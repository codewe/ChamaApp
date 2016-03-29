<?php
namespace Chama\Http;
use Chama\DB\Contribution;
use Chama\DB\Membership;
use Chama\Http\BaseController;
use Chama\Utils\Constants;
use Chama\Utils\Input;

/**
 * Contribution
 * Manages all the contribution functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class ContributionController extends BaseController {
	private $contributions;
	private $memberships;
	public function __construct() {
		$this->contributions = new Contribution();
		$this->memberships = new Membership();

	}
	public function all() {
		//@todo add ability to search by month and date

		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();
		$page = Input::get("page", 1);

		$contributions = $this->contributions->forChama($chamaId, $page);

		$this->renderResponse(array(
			"status" => "success",
			"description" => "",
			"contributions" => $contributions,
		));
	}

	public function byUser() {

		$chamaId = $this->getChamaId();
		$year = Input::get("year");

		$target_user = Input::get("target_user");
		if ($year == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `year` field is required ",
			));
		} elseif ($target_user == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `target_user` field is required ",
			));
		} else {
			$this->renderResponse(array(
				"status" => "success",
				"description" => "",
				"contributions" => $this->contributions->forUser($chamaId, $year, $target_user ) ,
			));

		}

	}
	public function userContributionYears() {

		$target_user = Input::get("target_user");
		$chamaId = $this->getChamaId();
		if ($target_user == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `target_user` field is required ",
			));
		} else {
			$this->renderResponse(array(
				"status" => "success",
				"description" => "",
				"years" => $this->contributions->getUserContributionYears($chamaId, $target_user),
			));

		}

	}
	public function single() {

		$contributionId = Input::get("id");
		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();

		if ($contributionId == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `id` of the contribution is required ",
			));
		} else {
			$contribution = $this->contributions->single($contributionId);

			$this->renderResponse(array(
				"status" => "success",
				"description" => "",
				"contribution" => $contribution,
			));
		}

	}
	public function create() {
		$month = Input::get("month");
		$year = Input::get("year");
		$amount = Input::get("amount");
		$date = Input::get("date");
		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();
		$targetUserId = Input::get("target_user");

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
		} elseif ($date == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `date` field is required ",
			));
		} elseif ($amount == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `amount` field is required ",
			));
		}

		$memberId = $this->memberships->findMembershipId($currentUserId, $chamaId);

		if (!$this->memberships->hasRole($currentUserId, $chamaId, Constants::TREASURER)) {
			$this->renderResponse(array(
				"status" => "failure",
				"description" => "Only treasurers can add contributions.",
			));

		}

		if (!$this->contributions->create($month, $year, $amount, $date, $memberId, $currentUserId)) {
			$this->renderResponse(array(
				"status" => "failure",
				"description" => "Unable to add contribution.",
			));

		} else {

			$this->renderResponse(array(
				"status" => "success",
				"description" => "Contribution added successfully.",
			));
		}
	}
	public function delete() {
		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();
		$contributionId = Input::get("id");

		if ($contributionId == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `id` of the contribution is required ",
			));
		}

		if (!$this->memberships->hasRole($currentUserId, $chamaId, Constants::TREASURER)) {
			$this->renderResponse(array(
				"status" => "failure",
				"description" => "Only treasurers can delete contributions.",
			));

		}

		if (!$this->contributions->delete($contributionId)) {
			$this->renderResponse(array(
				"status" => "failure",
				"description" => "Unable to delete contribution.",
			));

		} else {

			$this->renderResponse(array(
				"status" => "success",
				"description" => "Contribution deleted successfully.",
			));
		}

	}

}