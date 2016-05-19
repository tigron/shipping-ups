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
	public function confirm(\Tigron\Ups\Contact $shipper, \Tigron\Ups\Contact $recipient, $packages, \Tigron\Ups\Service $service, \Tigron\Ups\Contact $ship_from = null, $notifications = []) {
		$template = Template::get();
		$template->assign('shipper', $shipper);
		if ($ship_from === null) {
			$template->assign('ship_from', $shipper);
		} else {
			$template->assign('ship_from', $ship_from);
		}
		$template->assign('recipient', $recipient);
		$template->assign('packages', $packages);
		$template->assign('service', $service);
		if (!is_array($notifications)) {
			$template->assign('notifications', [ $notifications ]);
		} else {
			$template->assign('notifications', $notifications);
		}
		$xml = $template->render('call/ShipConfirm.twig');

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
		$template = Template::get();
		$template->assign('digest', $digest);
		$xml = $template->render('call/ShipAccept.twig');
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
		$template = Template::get();
		$template->assign('tracking', $tracking);
		$xml = $template->render('call/LabelRecovery.twig');
		$result = $this->call('LabelRecovery', $xml);

		return $result;
	}
}
