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
	 * Render
	 *
	 * @access public
	 * @return string $xml
	 */
	public function render() {
		/**
		 * UPS Exceptions
		 */
		if ($this->country == 'AT') {
			if ($this->zipcode == 6691) {
				$this->zipcode = 87491;
				$this->country = 'DE';
			} elseif ($this->zipcode == 6991) {
				$this->zipcode = 87567;
				$this->country = 'DE';
			} elseif ($this->zipcode == 6992) {
				$this->zipcode = 87568;
				$this->country = 'DE';
			} elseif ($this->zipcode == 6993) {
				$this->zipcode = 87569;
				$this->country = 'DE';
			}
		}

		$this->line1 = Util::replace_unsupported_characters($this->line1);
		$this->line2 = Util::replace_unsupported_characters($this->line2);
		$this->line3 = Util::replace_unsupported_characters($this->line3);
		$this->city  = Util::replace_unsupported_characters($this->city);

		$template = Template::get();
		$template->assign('address', $this);
		return $template->render('object/address.twig');
	}
}
