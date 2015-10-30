<?php
/**
 * Ship class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Shipping extends Client {

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
	public function confirm(\Tigron\Ups\Contact $shipper, \Tigron\Ups\Contact $recipient, $packages, \Tigron\Ups\Service $service) {
		$this->assign('shipper', $shipper);
		$this->assign('recipient', $recipient);
		$this->assign('packages', $packages);
		$this->assign('service', $service);
		$xml = $this->template->render('ShipConfirm.twig');
		$result = $this->call('ShipConfirm', $xml);
		return $result;
	}

	/**
	 * Accept shipping
	 *
	 * @access public
	 * @param string $digest
	 */
	public function accept($digest) {
		$this->assign('digest', $digest);
		$xml = $this->template->render('ShipAccept.twig');
		$result = $this->call('ShipAccept', $xml);
		return $result;
	}

	/**
	 * Get label
	 *
	 * @access public
	 * @param string $tracking
	 */
	public function get_label($tracking) {
		$this->assign('tracking', $tracking);
		$xml = $this->template->render('LabelRecovery.twig');
		echo $xml;
		$result = $this->call('LabelRecovery', $xml);

		return $result;
	}

}
