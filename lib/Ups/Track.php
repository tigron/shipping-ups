<?php
/**
 * Ship class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Track {

	/**
	 * Tracking number
	 *
	 * @access public
	 * @var string $tacking_number
	 */
	public $tracking_number = null;

	/**
	 * Rate a shipment
	 *
	 * @access public
	 * @return array<string> $response
	 */
	public function track(): array {
		$client = Client::get();
		return $client->request('GET', '/track/v1/details/' . $this->tracking_number, []);
	}
}
