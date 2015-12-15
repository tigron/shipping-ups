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
		$this->assign('zipcode', $address->zipcode);
		$this->assign('city', $address->city);
		$this->assign('country', $address->country);
		$xml = $this->template->render('AddressValidationRequest.twig');
		$result = $this->call('AV', $xml);
		return $result;
	}

}
