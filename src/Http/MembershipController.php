<?php
namespace Chama\Http;
use Chama\DB\Membership as MembershipModel;
use Chama\DB\User;
use Chama\Http\BaseController;
use Chama\Utils\Constants;
use Chama\Utils\Hash;
use Chama\Utils\Input;
use Chama\Utils\Sms;

/**
 * MembershipController
 * Manages all the user functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class MembershipController extends BaseController {
	private $userModel;
	private $membershipModel;
	public function __construct() {
		$this->userModel = new User();
		$this->membershipModel = new MembershipModel();

	}
	public function add() {
		$name = Input::get("name");
		$phonenumber = Input::get("phonenumber");
		$chamaId = $this->getChamaId();
		$role = strtolower(Input::get("role"));
		$dateJoined = Input::get("date_joined");
		$currentUserId = $this->getCurrentUserId();
		$isNew = false;
		if ($name == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `name` field is required ",
			));
		} elseif ($phonenumber == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `phonenumber` field is required ",
			));
		} elseif ($role == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `role` field is required ",
			));
		} elseif ($dateJoined == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `date_joined` field is required ",
			));
		}

		//first create the user with a default password. Only if they don't exist
		$memberId = $this->userModel->exists($phonenumber);
		if (!$memberId) {
			$isNew = true;

			//create user with a default password. They ought to change it later
			$password = Constants::DEFAULT_PASSWORD;
			$memberId = $this->userModel->create($name, $phonenumber, Hash::encode($password));
		
		}
		if ($this->membershipModel->isMemberOf($memberId, $chamaId)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The user is already a member of this chama.",
			));
		}
		//now lets assign the user to the chama
		if ($this->membershipModel->assignChama($memberId, $chamaId, $role, $currentUserId)) {
			if ($isNew) {
				//sms them to invite them
				Sms::newMemberAdded($chamaId, $memberId, $currentUserId, $role);
			} else {
				//sms them about the role change.
				Sms::existingMemberAdded($chamaId, $memberId, $currentUserId, $role);
				//
			}

			$this->renderResponse(array(
				"status" => "success",
				"description" => "The member has been added successfully.",
			));

		} else {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Unable to add the member to the Chama",
			));
		}

	}

	public function delete() {
		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();
		$targetUserId = Input::get("target_user");

		if ($targetUserId == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `target_user` field is required ",
			));
		} elseif ($this->membershipModel->delete($targetUserId, $chamaId)) {
			$this->renderResponse(array(
				"status" => "success",
				"description" => "The member has been deleted successfully.",
			));

		} else {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Unable to deleted the member from the Chama",
			));
		}
	}
	public function changeRole() {
		$chamaId = $this->getChamaId();
		$currentUserId = $this->getCurrentUserId();
		$targetUserId = Input::get("target_user");
		$role = strtolower(Input::get("role"));

		if ($targetUserId == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `target_user` field is required ",
			));
		} elseif ($role == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `role` field is required ",
			));
		}
		//a chama can only have one chairman
		elseif ($currentUserId == $targetUserId) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "You cannot change your role.",
			));

		} elseif (Constants::CHAIRMAN == strtolower($role)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Only one chairman allowed per Chama.",
			));

		} elseif (!$this->membershipModel->hasRole($currentUserId, $chamaId, Constants::CHAIRMAN)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Permission denied. Only Chairman's allowed to change roles.",
			));

		} else {
			$this->membershipModel->changeRole($targetUserId, $chamaId, $role);
			$this->renderResponse(array(
				"status" => "success",
				"description" => "User role changed successfully.",
			));
		}
	}
	public function all() {
		$chamaId = $this->getChamaId();
		$page = Input::get("page", 1);
		$members = $this->membershipModel->findForChama($chamaId, $page);
		$this->renderResponse(array(
			"status" => "success",
			"description" => "",
			"members" => $members,
		));
	}
}