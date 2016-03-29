<?php
namespace Chama\Utils;
use Chama\Utils\Constants;

/**
 * Hash
 * Encryptor
 *
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class Hash {
	/**
	 * Encrypts the string and return the payload
	 */
	public static function encode($value) {
		return md5(Constants::PASSWORD_SALT . $value . Constants::PASSWORD_SALT);
	}
}