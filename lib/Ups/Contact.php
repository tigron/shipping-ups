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
}
