<?php
namespace Chama\Http;
use Chama\DB\Auth;
use Chama\DB\Membership;
use Chama\DB\User;
use Chama\Http\BaseController;
use Chama\Utils\Input;

/**
 * UserController
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class UserController extends BaseController {
	private $userModel;
	private $memberships;
	public function __construct() {
		$this->memberships = new Membership();
		$this->userModel = new User();
	}

	public function login() {
		$phonenumber = Input::get("phone");
		$password = Input::get("password");
		if ($phonenumber == null) {
			echo json_encode(array(
				"status" => "error",
				"description" => "The `phone` field is required ",
			));
			die;
		} elseif ($password == null) {
			echo json_encode(array(
				"status" => "error",
				"description" => "The `password` field is required ",
			));
			die;
		}
		$exists = $this->userModel->exists($phonenumber);
		$userId = Auth::login($phonenumber, $password);
		//if phonenumber not found, we have a problem
		if (!$exists) {
			echo json_encode(array(
				"status" => "error",
				"description" => "No user found for those credentials",
			));
			die;
		}
		if ($exists && !$userId) {
			echo json_encode(array(
				"status" => "error",
				"description" => "Invalid password. Check and try again.",
			));
			die;
		} else {
			echo json_encode(array(
				"status" => "success",
				"user_details" => $this->userModel->find($userId),
				"description" => "",
			));
			die;
		}
	}

	public function delete() {
		$userId = $this->getCurrentUserId();

		if ($userId = $this->userModel->delete($userId)) {
			echo json_encode(array(
				"status" => "success",
				"description" => "User deleted successfully",
			));
			die;
		} else {
			echo json_encode(array(
				"status" => "error",
				"description" => "Unable to delete user.",
			));
			die;
		}
	}

	public function update() {
		$name = Input::get("name");
		$phonenumber = Input::get("phone");
		$password = Input::get("password");
		$userId = $this->getCurrentUserId();
		if ($name == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `name` field is required ",
			));

		} elseif ($phonenumber == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `phone` field is required ",
			));
		} elseif ($password == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `password` field is required ",
			));
		} elseif ($userId = $this->userModel->update($userId, $name, $phonenumber, $password)) {
			$this->renderResponse(array(
				"status" => "success",
				"description" => "User updated successfully",
			));
		} else {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Unable to update successfully",
			));
		}
	}

	public function create() {

		$name = Input::get("name");
		$phonenumber = Input::get("phone");
		$password = Input::get("password");
		if ($name == null) {
			echo json_encode(array(
				"status" => "error",
				"description" => "The `name` field is required ",
			));
		} elseif ($phonenumber == null) {
			echo json_encode(array(
				"status" => "error",
				"description" => "The `phone` field is required ",
			));
		} elseif ($password == null) {
			echo json_encode(array(
				"status" => "error",
				"description" => "The `password` field is required ",
			));
		} elseif ($this->userModel->exists($phonenumber)) {
			echo json_encode(array(
				"status" => "error",
				"description" => "A user with those credentials already exists.",
			));
		} elseif ($userId = $this->userModel->create($name, $phonenumber, $password)) {
			echo json_encode(array(
				"status" => "success",
				"description" => "User created successfully",
				"user_details" => $this->userModel->find($userId),
			));
		} else {
			echo json_encode(array(
				"status" => "error",
				"description" => "Unable to create the user",
			));
		}

	}

}