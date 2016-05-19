<?php
/**
 * AddressValidation class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class AddressValidation extends Client {

	/**
	 * Validate an Address to UPS AddressValidation API
	 *
	 * @access public
	 * @param \Tigron\Ups\Address $address
	 * @return array $response
	 */
	public function validate(Address $address) {
		$template = Template::get();
		$template('zipcode', $address->zipcode);
		$template('city', $address->city);
		$template('country', $address->country);
		$xml = $template->render('call/AddressValidationRequest.twig');
		$result = $this->call('AV', $xml);
		return $result;
	}

}
