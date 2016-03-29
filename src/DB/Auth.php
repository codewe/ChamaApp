<?php
namespace Chama\DB;
use Chama\DB\DbManager;
use Chama\DB\Membership;
use Chama\DB\User;
use Chama\Utils\Hash;

/**
 * Auth
 * Manages DB connection. Only.
 *
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class Auth {

	public static function login($phone, $pass) {
		$db = DbManager::connect();
		$membershipModel = new Membership();

		$db->where('phonenumber', $phone);
		$db->where('password', Hash::encode($pass));

		$user = $db->getOne("users");

		if (!is_null($user['user_id'])) {
			return $user['user_id'];

		} else {
			return false;
		}

	}

	public static function register($phone, $pass) {
		$db = DbManager::connect();
		$userModel = new User();
		//create
		$userId = $userModel->create($name, $password);
		//
		return $userId;
	}

}