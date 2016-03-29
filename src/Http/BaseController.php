<?php
namespace Chama\Http;
use Chama\DB\Chama;
use Chama\DB\Membership;
use Chama\DB\User;

/**
 * BaseController
 * A base for all our Http controllers
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class BaseController {
	public function renderResponse(array $output) {
		$userModel = new User();
		$chamaModel = new Chama();
		$membershipModel = new Membership();

		//look for chama id in both POST and GET

		if (!isset($output["chama_details"])) {

			$output['chama_details'] = $chamaModel->find($this->getChamaId());
		}
		if (!isset($output["user_details"])) {
			$output['user_details'] = $userModel->find($this->getCurrentUserId());
		}
		if (!isset($output["subscription_details"])) {
			$output['subscription_details'] = $chamaModel->getSubscriptionDetails($this->getChamaId());
		}
		if (!isset($output["user_details"]["role"])) {
			$output["user_details"]["role"] = $membershipModel->findUserRole($this->getChamaId(), $this->getCurrentUserId());
		}

		echo json_encode($output);
		die;
	}

	public function show404() {
		echo json_encode(array(
			"status" => "error",
			"role" => "",
			"description" => "404. Page not found.",
		));
	}

	protected function getChamaId() {
		if (isset($_POST['chama_id'])) {
			$chamaId = $_POST['chama_id'];
		} elseif (isset($_GET['chama_id'])) {
			$chamaId = $_GET['chama_id'];
		} else {
			echo json_encode(array(
				"status" => "error",
				"role" => "",
				"description" => "A required parameter `chama_id` is missing.",
			));
			die;
		}
		return $chamaId;
	}

	protected function getCurrentUserId() {
		if (isset($_POST['user_id'])) {
			$userId = $_POST['user_id'];
		} elseif (isset($_GET['user_id'])) {
			$userId = $_GET['user_id'];
		} else {
			echo json_encode(array(
				"status" => "error",
				"role" => "",
				"description" => "A required parameter `user_id` is missing.",
			));
			die;
		}
		return $userId;
	}

}