<?php
/**
 * Contact class
 *
 * This class is a representation of a contact
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Contact {

	/**
	 * Address
	 *
	 * @var \Tigron\Ups\Address $address
	 * @access public
	 */
	public $address = null;

	/**
	 * Company
	 *
	 * @var string $company
	 * @access public
	 */
	public $company = '';

	/**
	 * Firstname
	 *
	 * @access public
	 * @var string $firstname
	 */
	public $firstname = '';

	/**
	 * Lastname
	 *
	 * @access public
	 * @var string $lastname
	 */
	public $lastname = '';

	/**
	 * Phone
	 *
	 * @var string $phone
	 * @access public
	 */
	public $phone = '';

	/**
	 * Fax
	 *
	 * @var string $fax
	 * @access public
	 */
	public $fax = '';

	/**
	 * Email
	 *
	 * @var string $email
	 * @access public
	 */
	public $email = '';

	/**
	 * Vat
	 *
	 * @var string $vat
	 * @access public
	 */
	public $vat = '';

	/**
	 * Number
	 *
	 * @access public
	 * @var string $number
	 */
	public $number = '';

	/**
	 * Validate
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors) {
		$errors = [];
		if (trim($this->company) == '' AND ( trim($this->firstname) == '' AND trim($this->lastname) == '') ) {
			$errors['company'] = true;
			$errors['firstname'] = true;
			$errors['lastname'] = true;
		}

		if (trim($this->email) != '' AND strlen($this->email) > 50) {
			$errors['email'] = true;
		}


		if (!$this->address->validate($address_errors)) {
			$errors = array_merge($errors, $address_errors);
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
		if (empty($this->company)) {
			$info['Name'] = substr($this->firstname . ' ' . $this->lastname, 0, 35);
		} else {
			$info['Name'] = substr($this->company, 0, 35);
		}
		$info['AttentionName'] = substr($this->firstname . ' ' . $this->lastname, 0, 35);
		if (!empty($this->company)) {
			$info['CompanyDisplayableName'] = substr($this->company, 0, 35);
		}
		if (!empty($this->vat)) {
			$info['TaxIdentificationNumber'] = $this->vat;
		}
		if (!empty($this->phone)) {
			$info['Phone']['Number'] = $this->phone;
		}
		if (!empty($this->fax)) {
			$info['FaxNumber'] = $this->fax;
		}
		if (!empty($this->email)) {
			$info['EMailAddress'] = $this->email;
		}
		$info['Address'] = $this->address->get_info();

		$info['CompanyDisplayableName'] = Util::replace_unsupported_characters($info['CompanyDisplayableName']);
		$info['AttentionName'] = Util::replace_unsupported_characters($info['AttentionName']);

		return $info;
	}
}
