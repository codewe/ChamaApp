<?php
namespace Chama\Http;
use Chama\DB\Expense;
use Chama\DB\Membership;
use Chama\Http\BaseController;
use Chama\Utils\Constants;
use Chama\Utils\Input;

/**
 * ExpenseController
 * Manages all the expense functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class ExpenseController extends BaseController {
	private $expenseModel;
	private $memberships;
	public function __construct() {
		$this->expenseModel = new Expense();
		$this->memberships = new Membership();

	}
	public function all() {

		$chamaId = $this->getChamaId();
		$page = Input::get("page", 1);
		$expenses = $this->expenseModel->forChama($chamaId, $page);
		$this->renderResponse(array(
			"status" => "success",
			"description" => "",
			"expenses" => $expenses,
		));
	}
	public function search() {

		$chamaId = $this->getChamaId();
		$month = Input::get("month");
		$year = Input::get("year");
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
				"expenses" => $this->expenseModel->search($chamaId, $month, $year),
			));

		}

	}
	public function single() {
		$expenseId = Input::get("id");
		if ($expenseId == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `id` of the expense is required ",
			));
		} elseif (!$data = $this->expenseModel->single($expenseId)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "No expense with that identifier was found.",
			));
		} else {
			$this->renderResponse(array(
				"status" => "success",
				"description" => "",
				"expense" => $data,
			));
		}

	}
	public function delete() {
		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();
		$expenseId = Input::get("id");

		if ($expenseId == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `id` of the expense is required ",
			));
		} elseif (!$this->memberships->hasRole($currentUserId, $chamaId, Constants::TREASURER)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Only treasurers can delete expenses.",
			));

		}

		if (!$this->expenseModel->delete($expenseId)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Unable to delete expense.",
			));

		} else {

			$this->renderResponse(array(
				"status" => "success",
				"description" => "Expense deleted successfully.",
			));
		}

	}
	public function create() {
		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();
		$amount = Input::get("amount");
		$description = Input::get("description");
		$date = Input::get("date");
		$dateparts = explode("-", $date);

		if (count($dateparts) != 3) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Enter date in the format yyyy-mm-dd ",
			));
		} elseif ($amount == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `amount` field is required ",
			));
		} elseif ($description == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `description` field is required ",
			));
		} elseif ($date == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `date` field is required ",
			));
		}

		if (!$this->memberships->hasRole($currentUserId, $chamaId, Constants::TREASURER)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Only treasurers can post expenses.",
			));

		}
		$day = $dateparts[2];
		$month = $dateparts[1];
		$year = $dateparts[0];

		if (!$this->expenseModel->create($chamaId, $amount, $description, $day, $month, $year, $currentUserId)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Unable to add expense.",
			));

		} else {

			$this->renderResponse(array(
				"status" => "success",
				"description" => "Expense posted successfully.",
			));
		}
	}
	public function update() {
		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();
		$expenseId = Input::get("id");
		$amount = Input::get("amount");
		$description = Input::get("description");
		$date = Input::get("date");

		if ($expenseId == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `id` of the expense is required ",
			));
		} elseif ($amount == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `amount` field is required ",
			));
		} elseif ($description == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `description` field is required ",
			));
		} elseif ($date == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `date` field is required ",
			));
		}

		if (!$this->memberships->hasRole($currentUserId, $chamaId, Constants::TREASURER)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Only treasurers can add expenses.",
			));

		}

		$noticeId = $this->expenseModel->update($expenseId, $amount, $description, $date);
		if ($noticeId) {
			$this->renderResponse(array(
				"status" => "success",
				"description" => "The expense has been updated successfully",
			));

		} else {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Unable to update the expense.",
			));
		}
	}
}