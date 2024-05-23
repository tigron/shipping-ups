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
		$json = $template->render('call/rate.twig');

		$result = $this->call('rating', 'Rate', $json);
		return $result;
	}


}
