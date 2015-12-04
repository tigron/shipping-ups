<?php
/**
 * Address class
 *
 * This class is a representation of an Address
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Address {

	/**
	 * Street
	 *
	 * @var string $street
	 * @access public
	 */
	public $street = '';

	/**
	 * Housenumber
	 *
	 * @var string $housenumber
	 * @access public
	 */
	public $housenumber = '';

	/**
	 * Zipcode
	 *
	 * @var string $zipcode
	 * @access public
	 */
	public $zipcode = '';

	/**
	 * City
	 *
	 * @var string $city
	 * @access public
	 */
	public $city = '';

	/**
	 * Country (iso-2)
	 *
	 * @var string $country
	 * @access public
	 */
	public $country = '';

	/**
	 * Get US_State
	 *
	 * @access public
	 * @return string $state
	 */
	public function get_us_state() {
		if ($this->country != 'US') {
			throw new \Exception('Cannot get state for country other than US');
		}

		include dirname(__FILE__) . '/../../assets/city_us.php';
		foreach ($us_city as $city) {
			if ($city['zip'] == $this->zipcode) {
				return $city['state'];
			}
		}
		throw new \Exception('No state found for city with zipcode ' . $this->zipcode);
	}
}
