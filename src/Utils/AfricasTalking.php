<?php

/**
 * SMS
 * @author    James Ngugi <ngugi823@gmail.com>
 * @copyright Copyright (c) James Ngugi
 * @license   All rights reserved.
 *
 */

namespace BlackJack\SMS\Gateways;

use BlackJack\SMS\Gateways\AfricasTalkingGateway;
use BlackJack\SMS\Gateways\AfricasTalkingGatewayException;
use BlackJack\SMS\Gateways\GatewayInterface;
use Exception;
use Log;

class AfricasTalking implements GatewayInterface {
	private $gateway;
	private $messageId;
	private $apiKey;
	private $shortcode;
	private $keyword;

	public function __construct() {
		$this->username = env('AFRICAS_TALKING_USERNAME');
		$this->apiKey = env('AFRICAS_TALKING_API_KEY');
		$this->shortcode = env('SHORTCODE');
		$this->keyword = env('KEYWORD');
		if (is_null($this->username) || is_null($this->apiKey)) {
			throw new Exception('The AFricas Talking Username and API Key are missing from .env file.');
			# code...
		}
		$this->gateway = new AfricasTalkingGateway($this->username, $this->apiKey);
	}

	public function sendSms($message, $recipient) {
		try {
			// Thats it, hit send and we'll take care of the rest.
			$results = $this->gateway->sendMessage($recipient, $message);
			if (strtolower($results[0]->status) == "success") {
				return true;
			} else {
				return false;
			}

/*
foreach ($results as $result) {
// status is either "Success" or "error message"
//echo " Number: " . $result->number;
//echo " Status: " . $result->status;
$messageArray['status'] = $result->status;
//echo " Cost: " . $result->cost . "\n";
$this->updateDeliveryReport($messageArray['messageId'], $messageArray['status'], NULL);
//we only need to loop once,
break;
}
 */
		} catch (AfricasTalkingGatewayException $e) {
			Log::alert('SMS::Sending SMS failed with the following error ' . $e->getMessage());

			return false;

		}
		// DONE!!!

	}

}
