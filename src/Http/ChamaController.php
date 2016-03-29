<?php
namespace Chama\Http;
use Chama\DB\Chama;
use Chama\DB\Membership;
use Chama\DB\User;
use Chama\Http\BaseController;
use Chama\Utils\Constants;
use Chama\Utils\Input;

/**
 * ChamaController
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class ChamaController extends BaseController {
	private $userModel;
	private $chamaModel;
	private $memberships;
	public function __construct() {
		$this->userModel = new User();
		$this->chamaModel = new Chama();
		$this->memberships = new Membership();

	}
	public function single() {

		$chamaId = $this->getChamaId();

		//render response as an array and convert to json
		$this->renderResponse(array(
			'chama_details' => $this->chamaModel->find($chamaId),
			'subscription_details' => $this->chamaModel->getSubscriptionDetails($chamaId),
			"status" => "success",
			"description" => "",
		));

	}

	public function forUser() {

		$userId = $this->getCurrentUserId();
		$chamas = $this->memberships->findForUser($userId);
		$user_details = $this->userModel->find($userId);
		$user_details["role"] = "";

		echo json_encode(array(
			'user_chamas' => $chamas,
			"user_details" => $user_details,
			"status" => "success",
			"description" => "",
		));

	}

	public function create() {
		//@todo get the chama details from request
		$name = Input::get("name");
		$members = Input::get("members");
		$date = Input::get("date");
		$fine = Input::get("fine");
		$contribution = Input::get("contribution");
		$chairman = $this->getCurrentUserId();

		if ($name == null) {
			echo ("name");
			echo json_encode(array(
				'chama_details' => "",
				'subscription_details' => "",
				"status" => "error",
				"description" => "The `name` field is required ",
			));
		} elseif ($members == null) {

			echo json_encode(array(
				'chama_details' => "",
				'subscription_details' => "",
				"status" => "error",
				"description" => "The `members` field is required ",
			));
		} elseif ($date == null) {

			echo json_encode(array(
				'chama_details' => "",
				'subscription_details' => "",
				"status" => "error",
				"description" => "The `date` field is required ",
			));
		} elseif ($fine == null) {

			echo json_encode(array(
				'chama_details' => "",
				'subscription_details' => "",
				"status" => "error",
				"description" => "The `fine` field is required ",
			));
		} elseif ($contribution == null) {

			echo json_encode(array(
				'chama_details' => "",
				'subscription_details' => "",
				"status" => "error",
				"description" => "The `contribution` field is required ",
			));
		} elseif ($this->chamaModel->exists($name, $chairman)) {

//first check whether the user already a chama by that exact name
			echo json_encode(array(
				'chama_details' => "",
				'subscription_details' => "",
				"status" => "error",
				"description" => "You already have a chama by that name",
			));

		} else {
			$chamaId = $this->chamaModel->create($name, $members, $date, $fine, $contribution, $chairman);

			$user_details = $this->userModel->find($chairman);
			$user_details["role"] = "";
			//render response as an array and convert to json
			echo json_encode(array(
				"user_details" => $user_details,
				'chama_details' => $this->chamaModel->find($chamaId), //start using that chama
				'subscription_details' => $this->chamaModel->getSubscriptionDetails($chamaId),
				"status" => "success",
				"description" => "The Chama has been created successfully.",
			));
		}

	}

	public function renewSubscription() {
		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();
		$activationCode = Input::get("activation_Code");
		//
	}

	public function update() {
		//@todo get the chama details from request
		$name = Input::get("name");
		$members = Input::get("members");
		$date = Input::get("date");
		$fine = Input::get("fine");
		$contributions = Input::get("contributions");
		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();

		if ($name == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `name` field is required ",
			));
		} elseif ($members == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `members` field is required ",
			));
		} elseif ($date == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `date` field is required ",
			));
		} elseif ($fine == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `fine` field is required ",
			));
		} elseif ($contributions == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `contribution` field is required ",
			));
		}

		if (!$this->memberships->hasRole($currentUserId, $chamaId, Constants::CHAIRMAN)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Only the Chairman can update Chama information.",
			));
		}

		if ($this->chamaModel->update($chamaId, $name, $members, $date, $fine, $contributions)) {
			//render response as an array and convert to json
			$this->renderResponse(array(
				"status" => "success",
				"description" => "The Chama has been updated successfully.",
			));
		} else {

			$this->renderResponse(array(
				"status" => "error",
				"description" => "Unable to update the chama",
			));
		}

	}
	public function delete() {
		//@todo get the chama details from request
		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();

		if (!$this->memberships->hasRole($currentUserId, $chamaId, Constants::CHAIRMAN)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Only the Chairman can delete a Chama.",
				'subscription_details' => "",
			));

		}

		if ($this->chamaModel->delete($chamaId)) {
			//render response as an array and convert to json
			$this->renderResponse(array(
				"status" => "success",
				"description" => "The Chama has been deleted successfully.",
				'chama_details' => "",
				'subscription_details' => "",
				"role" => "",
			));
		} else {

			$this->renderResponse(array(
				"status" => "error",
				"description" => "Unable to delete the chama",
				'subscription_details' => "",
			));
		}

	}

}