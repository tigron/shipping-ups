<?php
/**
 * Config class
 * Configuration for Tigron\Ups
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Tigron\Ups;

class Config {

	/**
	 * License number
	 *
	 * Get your API token at ups.com
	 *
	 * @access public
	 * @var string $license_number
	 */
	public static $license_number = null;

	/**
	 * Account number
	 *
	 * The account number
	 *
	 * @access public
	 * @var string $account_number
	 */
	public static $account_number = null;

	/**
	 * User id
	 *
	 * The username for ups.com
	 *
	 * @access public
	 * @var string $user_id
	 */
	public static $user_id = null;

	/**
	 * Password
	 *
	 * The password for ups.com
	 *
	 * @access public
	 * @var string $user_id
	 */
	public static $password = null;

	/**
	 * Client ID
	 *
	 * The client_id for ups.com
	 *
	 * @access public
	 * @var string $client_id
	 */
	public static $client_id = null;

	/**
	 * Secret Key
	 *
	 * The secrect_key for ups.com
	 *
	 * @access public
	 * @var string $secrect_key
	 */
	public static $secrect_key = null;

	/**
	 * API Version
	 *
	 * The api_version for ups.com
	 *
	 * @access public
	 * @var string $api_version
	 */
	public static $api_version = null;

	/**
	 * Test/production
	 *
	 * @access public
	 * @var string $mode
	 */
	public static $mode = 'test';

	/**
	 * Log file
	 *
	 * @access public
	 * @var string $logfile
	 */
	public static $logfile = null;

	/**
	 * Socket timeout
	 *
	 * @access public
	 * @var int $socket_timeout
	 */
	public static $socket_timeout = 5;

}
