<?php
namespace Chama\DB;
use Chama\DB\DbManager;
use Chama\Utils\Hash;
use forxer\Gravatar\Gravatar;

/**
 * User
 * Manages all the user functions
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class User {
	private $db;
	public function __construct() {
		$this->db = DbManager::connect();
	}
	public function find($userId) {
		$this->db->where("user_id", $userId);
		$user = $this->db->getOne("users");
		unset($user['password']);
		$user['image'] = Gravatar::image($user['phonenumber'] . 'email@example.com', 80, 'identicon');
		return $user;
	}
	public function delete($userId) {
		$this->db->where('user_id', $userId);
		return $this->db->delete('users');
	}
	public function update($userId, $name, $phonenumber, $password) {
		$this->db->where('user_id', $userId);
		return $this->db->update('users',
			array(
				'name' => $name,
				'phonenumber' => $phonenumber,
				'password' => Hash::encode($password),
			)
		);
	}
	public function create($name, $phonenumber, $password) {

		$id = $this->db->insert('users',
			Array(
				'name' => $name,
				'phonenumber' => $phonenumber,
				'password' => Hash::encode($password),
			)
		);

		return $id;
	}
	public function exists($phonenumber) {

		$this->db->where('phonenumber', $phonenumber);
		$user = $this->db->getOne("users");
		return (count($user) > 0) ? $user["user_id"] : false;
	}
}