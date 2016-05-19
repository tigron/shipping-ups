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
		$template = Template::get();
		$template->assign('address', $this);
		return $template->render('object/address.twig');
	}
}
