<?php
/**
 * Config class
 * Configuration for Skeleton\File
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
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
	 * Test/production
	 *
	 * @access public
	 * @var string $mode
	 */
	public static $mode = 'test';

}