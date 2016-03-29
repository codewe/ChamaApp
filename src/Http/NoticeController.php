<?php
namespace Chama\Http;
use Chama\DB\Membership;
use Chama\DB\Notice;
use Chama\Http\BaseController;
use Chama\Utils\Constants;
use Chama\Utils\Input;

/**
 * NoticeController
 * Manages all the notice functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class NoticeController extends BaseController {
	private $repository;
	private $memberships;
	public function __construct() {
		$this->repository = new Notice();
		$this->memberships = new Membership();
	}
	public function all() {
		$chamaId = $this->getChamaId();
		$page = Input::get("page", 1); //default to page one

		$this->renderResponse(array(
			"status" => "success",
			"description" => "",
			"notices" => $this->repository->findForChama($chamaId, $page),
		));

	}
	public function single() {
		$noticeId = Input::get("id");
		if ($noticeId == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `id` of the notice is required ",
			));
		} elseif (!$data = $this->repository->single($noticeId)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "No notice with the specified identifier was found.",
			));
		} else {
			$this->renderResponse(array(
				"status" => "success",
				"description" => "",
				"notice" => $data,
			));
		}

	}
	public function create() {
		$chamaId = $this->getChamaId();
		$title = Input::get("title");
		$date = Input::get("date");
		$venue = Input::get("venue");
		$time = Input::get("time");
		$description = Input::get("description");
		$currentUserId = $this->getCurrentUserId();

		if ($title == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `title` field is required ",
			));
		} elseif ($date == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `date` field is required ",
			));
		} elseif ($venue == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `venue` field is required ",
			));
		} elseif ($time == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `time` field is required ",
			));
		} elseif ($description == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `description` field is required ",
			));
		} elseif (!$this->memberships->hasRole($currentUserId, $chamaId, Constants::SECRETARY)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Only secretaries can post notices.",
			));

		}

		$noticeId = $this->repository->create($chamaId, $title, $date, $venue, $time, $description, $currentUserId);
		if ($noticeId) {
			$this->renderResponse(array(
				"status" => "success",
				"description" => "The notice has been created successfully",
			));

		} else {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Unable to post the notice",
			));
		}
	}
	public function update() {
		$chamaId = $this->getChamaId();
		$noticeId = Input::get("id");
		$title = Input::get("title");
		$date = Input::get("date");
		$venue = Input::get("venue");
		$time = Input::get("time");
		$description = Input::get("description");
		$currentUserId = $this->getCurrentUserId();

		if ($title == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `title` field is required ",
			));
		} elseif ($date == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `date` field is required ",
			));
		} elseif ($venue == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `venue` field is required ",
			));
		} elseif ($time == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `time` field is required ",
			));
		} elseif ($description == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `description` field is required ",
			));
		} elseif (!$this->memberships->hasRole($currentUserId, $chamaId, Constants::SECRETARY)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Only secretaries can modify notices.",
			));

		}

		$noticeId = $this->repository->update($noticeId, $title, $date, $venue, $time, $description);
		if ($noticeId) {
			$this->renderResponse(array(
				"status" => "success",
				"description" => "The notice has been updated successfully",
			));

		} else {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Unable to update the notice",
			));
		}
	}
	public function delete() {
		$chamaId = $this->getChamaId();
		$noticeId = Input::get("id");
		$currentUserId = $this->getCurrentUserId();
		if ($noticeId == null) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "The `id` of the notice is required ",
			));
		} elseif (!$this->memberships->hasRole($currentUserId, $chamaId, Constants::SECRETARY)) {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Only secretaries can modify notices.",
			));

		}
		$this->repository->delete($noticeId);
		if ($noticeId) {
			$this->renderResponse(array(
				"status" => "success",
				"description" => "The notice has been deleted successfully",
			));

		} else {
			$this->renderResponse(array(
				"status" => "error",
				"description" => "Unable to delete the notice",
			));
		}
	}
}