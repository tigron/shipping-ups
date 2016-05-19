<?php
/**
 * Ship class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Rate extends Client {

	/**
	 * Validate an Address to UPS AddressValidation API
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $shipper
	 * @param \Tigron\Ups\Contact $recipient
	 * @param array $packages
	 * @param \Tigron\Ups\Service $service
	 * @return array $response
	 */
	public function rate(\Tigron\Ups\Contact $shipper, \Tigron\Ups\Contact $recipient, $packages, \Tigron\Ups\Service $service, \Tigron\Ups\Contact $ship_from = null) {
		$this->assign('shipper', $shipper);
		if ($ship_from === null) {
			$this->assign('ship_from', $shipper);
		} else {
			$this->assign('ship_from', $ship_from);
		}
		$this->assign('recipient', $recipient);
		$this->assign('packages', $packages);
		$this->assign('service', $service);
		$xml = $this->template->render('rate.twig');

		$result = $this->call('Rate', $xml);
		return $result;
	}


}
