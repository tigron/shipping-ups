<?php
/**
 * Ship class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Track extends Client {

	/**
	 * Validate an Address to UPS AddressValidation API
	 *
	 * @access public
	 * @param string $tracking_number
	 * @return array $response
	 */
	public function track($tracking_number) {
		$template = Template::get();
		$template->assign('tracking_number', $tracking_number);
		$json = $template->render('call/track.twig');
		$result = $this->call('track', 'details/' . $tracking_number, '{}', 'GET', [],'v1');
		return $result;
	}

}
