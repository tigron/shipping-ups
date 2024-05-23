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
	 * Render
	 *
	 * @access public
	 * @return string $xml
	 */
	public function render() {
		$template = Template::get();

		$this->company   = Util::replace_unsupported_characters($this->company);
		$this->firstname = Util::replace_unsupported_characters($this->firstname);
		$this->lastname  = Util::replace_unsupported_characters($this->lastname);

		$template->assign('contact', $this);
		return $template->render('object/contact/contact.twig');
	}

	/**
	 * Render shipper
	 *
	 * For some reason, UPS uses another XML schema for a shipper
	 * The data is identical but field names are different
	 *
	 * @access public
	 * @return string $xml
	 */
	public function render_shipper() {
		$template = Template::get();
		$template->assign('contact', $this);
		$template->assign('user_id', Config::$user_id);
		return $template->render('object/contact/shipper.twig');
	}

	/**
	 * Render soldto
	 *
	 * For some other reason, UPS uses yet another XML schema for a 'soldTo'
	 * The data is identical but field names are different
	 *
	 * @access public
	 * @return string $xml
	 */
	public function render_soldto() {
		$template = Template::get();
		$template->assign('contact', $this);
		return $template->render('object/contact/soldto.twig');
	}
}
