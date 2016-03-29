<?php
namespace Chama\Utils;
use Chama\DB\Chama;
use Chama\DB\User;
use Chama\Utils\AfricasTalkingGateway;
use Chama\Utils\AfricasTalkingGatewayException;
use Chama\Utils\Constants;

/**
 * Sms
 * Sms Sender
 *
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class Sms {
	public static $sendSms = false;

	public static function newMemberAdded($chamaId, $invitedId, $inviterId, $role) {
		$chamas = new Chama();
		$users = new User();

		$chama = $chamas->find($chamaId);
		$chamaName = $chama["chama_name"];

		$invitee = $users->find($invitedId);
		$inviteeName = $invitee["name"];
		$name_parts = explode(" ", trim($inviteeName));
		$inviteeFirstName = $name_parts[0];
		$inviteePhone = $invitee["phonenumber"];

		$inviter = $users->find($inviterId);
		$inviterName = $inviter["name"];
		$name_parts = explode(" ", trim($inviterName));
		$inviterFirstName = $name_parts[0];
		$inviterPhone = $invitee["phonenumber"];

		$message = "Dear " . $inviteeFirstName . ",\n" .
			$inviterFirstName . "<" . $inviterPhone . "> has added you as a {$role} to the chama '"
			. $chamaName . "' on ChamaApp. Get the app at http://bit.ly/chamaapp. Use your phonenumber and the password " . Constants::DEFAULT_PASSWORD. " to login. ";

		static::send($inviteePhone, $message);
	}

	public static function existingMemberAdded($chamaId, $invitedId, $inviterId, $role) {
		$chamas = new Chama();
		$users = new User();

		$chama = $chamas->find($chamaId);
		$chamaName = $chama["chama_name"];

		$invitee = $users->find($invitedId);
		$inviteeName = $invitee["name"];
		$name_parts = explode(" ", trim($inviteeName));
		$inviteeFirstName = $name_parts[0];
		$inviteePhone = $invitee["phonenumber"];

		$inviter = $users->find($inviterId);
		$inviterName = $inviter["name"];
		$name_parts = explode(" ", trim($inviterName));
		$inviterFirstName = $name_parts[0];
		$inviterPhone = $invitee["phonenumber"];

		$message = "Dear " . $inviteeFirstName . ",\n" .
			$inviterFirstName . "<" . $inviterPhone . "> has added you as a '{$role}' to the chama"
			. $chamaName . "on ChamaApp. ";

		static::send($inviteePhone, $message);

	}
	public static function trialWelcome($chamaId, $chairmanId) {
		$chamas = new Chama();
		$users = new User();

		$chama = $chamas->find($chamaId);
		$chamaName = $chama["chama_name"];

		$user = $users->find($chairmanId);
		$username = $user["name"];
		$name_parts = explode(" ", trim($username));
		$firstName = $name_parts[0];
		$phonenumber = $user["phonenumber"];

		$message = "Dear " . $firstName . ",\n" .
			"Welcome to ChamaApp. Your chama '{$chamaName}' has been setup successfully.
			Your trial runs for 60 days. To renew your subscription or acccess support call +254728270795.";

		static::send($phonenumber, $message);
	}

	/**
	 * Send the SMS asap.
	 */
	public static function send($phonenumber, $msg) {
		if (!static::$sendSms) {
			return;
		}

		$gateway = new AfricasTalkingGateway("user", "key");

		try {
			// Thats it, hit send and we'll take care of the rest.
			$results = $gateway->sendMessage($phonenumber, $msg);
//			$print_r=print_r($results);
	//		file_put_contents("sent_sms.log", $print_r, FILE_APPEND);
			if (strtolower($results[0]->status) == "success") {
				return true;
			} else {
				return false;
			}
		} catch (AfricasTalkingGatewayException $e) {
			$error = 'SMS::Sending to {$phonenumber} failed with the following error ' . $e->getMessage();
			file_put_contents("failed_sms.log", $error, FILE_APPEND);
			return false;

		}
		// DONE!!!
	}
}