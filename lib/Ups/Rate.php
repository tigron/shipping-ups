<?php
/**
 * Ship class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Rate {

	/**
	 * Shipper
	 *
	 * @var \Tigron\Ups\Contact $shipper
	 * @access private
	 */
	private $shipper = null;

	/**
	 * Ship_From
	 *
	 * @var \Tigron\Ups\Contact $ship_from
	 * @access private
	 */
	private $ship_from = null;

	/**
	 * Ship To
	 *
	 * @var \Tigron\Ups\Contact $ship_to
	 * @access private
	 */
	private $ship_to = null;

	/**
	 * Package
	 *
	 * @var array $packages
	 * @access private
	 */
	private $packages = [];

	/**
	 * Service
	 *
	 * @var \Tigron\Ups\Service $service
	 * @access private
	 */
	private $service = null;

	/**
	 * Set shipper
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $shipper
	 */
	public function set_shipper(\Tigron\Ups\Contact $shipper) {
		$this->shipper = $shipper;
	}

	/**
	 * Set ship_from
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $ship_from
	 */
	public function set_ship_from(\Tigron\Ups\Contact $ship_from) {
		$this->ship_from = $ship_from;
	}

	/**
	 * Set ship_to
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $recipient
	 */
	public function set_ship_to(\Tigron\Ups\Contact $recipient) {
		$this->ship_to = $recipient;
	}

	/**
	 * Add package
	 *
	 * @access public
	 * @param \Tigron\Ups\Package $package
	 */
	public function add_package(\Tigron\Ups\Package $package) {
		$this->packages[] = $package;
	}

	/**
	 * Set Service
	 *
	 * @access public
	 * @param \Tigron\Ups\Service $serice
	 */
	public function set_service(\Tigron\Ups\Service $service) {
		$this->service = $service;
	}

	/**
	 * Get info
	 *
	 * @access public
	 */
	public function get_info(): array {
		$info = [
			'Request' => [
				'TransactionReference' => [
					'CustomerContext' => 'Customer Comment',
				],
			],
			'Shipment' => [
				'Shipper' => $this->shipper->get_info(),
				'ShipFrom' => $this->shipper->get_info(),
				'ShipTo' => $this->ship_to->get_info(),
				'PaymentInformation' => [
					'ShipmentCharge' => [
						'Type' => '01',
						'BillShipper' => [
							'AccountNumber' => \Tigron\Ups\Config::$account_number,
						],
					],
				],
				'Service' => $this->service->get_info(),
				'Package' => [],
			],
		];
		if ($this->ship_from !== null) {
			$info['Shipment']['ShipFrom'] = $this->ship_from->get_info();
		}
		foreach ($this->packages as $package) {
			$info['Shipment']['Package'][] = $package->get_info();
		}

		return [ 'RateRequest' => $info ];
	}

	/**
	 * Rate a shipment
	 *
	 * @access public
	 * @return array $response
	 */
	public function rate() {
		$client = Client::get();
		return $client->request('POST', '/rating/' . Config::$api_version . '/rate', $this->get_info());
	}
}
