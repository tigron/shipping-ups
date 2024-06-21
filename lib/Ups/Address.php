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
	 * Line 1
	 *
	 * @var string $line1
	 * @access public
	 */
	public $line1 = '';

	/**
	 * Line 2
	 *
	 * @var string $line1
	 * @access public
	 */
	public $line2 = '';

	/**
	 * Line 3
	 *
	 * @var string $line1
	 * @access public
	 */
	public $line3 = '';

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
	 * State
	 *
	 * @var string $state
	 * @access public
	 */
	public $state = '';

	/**
	 * Country (iso-2)
	 *
	 * @var string $country
	 * @access public
	 */
	public $country = '';

	/**
	 * Address rendering mode
	 *
	 * @var string $mode
	 * @access public
	 */
	public $mode = '';

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

		$zipcode = str_replace(' ', '', $this->zipcode);
		$zipcode = strtoupper($zipcode);
		$zipcode = intval($zipcode);
		include dirname(__FILE__) . '/../../assets/city_us.php';
		if (isset($us_zip_state[$zipcode])) {
			return $us_zip_state[$zipcode];
		} else {
			throw new \Exception('No state found for US city with zipcode ' . $zipcode);
		}
	}

	/**
	 * Get Canada_State
	 *
	 * @access public
	 * @return string $state
	 */
	public function get_canada_state() {
		if ($this->country != 'CA') {
			throw new \Exception('Cannot get state for country other than Canada');
		}

		$zipcode = str_replace(' ', '', $this->zipcode);
		$zipcode = strtoupper($zipcode);
		include dirname(__FILE__) . '/../../assets/city_canada.php';
		if (isset($canada_zip_state[$zipcode])) {
			return $canada_zip_state[$zipcode];
		} else {
			throw new \Exception('No state found for city with zipcode ' . $zipcode);
		}
	}

	/**
	 * Validate
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors) {
		$errors = [];

		if (!empty($this->line1)) {
			if (strlen( trim($this->line1) ) > 35) {
				$errors['line1'] = true;
			}
			if (strlen( trim($this->line2) ) > 35) {
				$errors['line2'] = true;
			}
			if (strlen( trim($this->line3) ) > 35) {
				$errors['line3'] = true;
			}
		}

		if (empty($this->city)) {
			$errors['city'] = true;
		}

		if (empty($this->zipcode)) {
			$errors['zipcode'] = true;
		}

		if (count($errors) > 0) {
			return false;
		}

		return true;
	}

	/**
	 * Get info
	 *
	 * @access public
	 * @return array<string> $info
	 */
	public function get_info(): array {
		$info = [];
		$info['AddressLine'] = substr($this->line1 . ' ' . $this->line2 . ' ' . $this->line3, 0, 35);
		$info['City'] = $this->city;
		$info['PostalCode'] = $this->zipcode;
		if ($this->country === 'US') {
			$info['StateProvinceCode'] = $this->get_us_state();
		}
		if ($this->country === 'CA') {
			$info['StateProvinceCode'] = $this->address->get_canada_state();
		}
		if ($this->country === 'IE') {
			$info['StateProvinceCode'] = substr($this->zipcode, 0, 3);
		}
		if (!empty($this->state_code)) {
			$info['StateProvinceCode'] = $this->state_code;
		}
		$info['CountryCode'] = $this->country;

		/**
		 * UPS Exceptions
		 */
		if ($this->country == 'ES') {
			$zipcodes = [ 52001, 52002, 52003, 52004, 52005, 52006, 52070, 52071, 52080 ]; // source: https://worldpostalcode.com/spain/melilla
			if (in_array($this->zipcode, $zipcodes)) {
				$info['CountryCode'] = 'XL'; // Melilla is part of Spain but located in northwest coast of Africa, sharing a border with Morocco
			}
		}
		return $info;
	}
}
