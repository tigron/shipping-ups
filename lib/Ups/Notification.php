<?php
/**
 * Notification class
 *
 * This class is a representation of an Notification
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Notification {

	/**
	 * code
	 *
	 * 2 - Return Notification or Label Creation Notification,
	 * 5 - QV In-transit Notification
	 * 6 - QV Ship Notification
	 * 7 - QV Exception Notification
	 * 8 - QV Delivery Notification
	 * 012 - Alternate Delivery Location Notification
	 * 013 - UAP Shipper Notification
	 *
	 * @var string $code
	 * @access public
	 */
	public $code = '';

	/**
	 * Email addresses
	 *
	 * @access public
	 * @var array $email_addresses
	 */
	public $email_addresses = [];

	/**
	 * Get info
	 *
	 * @access public
	 * @return array<string> $info
	 */
	public function get_info() {
		$info = [
			'NotificationCode' => (string)$this->code,
			'EMail' => [
				'EMailAddress' => $this->email_addresses,
			],
		];
		return $info;
	}
}
