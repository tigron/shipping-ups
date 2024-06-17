<?php
/**
 * Ship class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Shipping {

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
	 * Sold To
	 *
	 * @var \Tigron\Ups\Contact $sold_to
	 * @access private
	 */
	private $sold_to = null;

	/**
	 * Package
	 *
	 * @var array $packages
	 * @access private
	 */
	private $packages = [];

	/**
	 * Notifications
	 *
	 * @var array $notifications
	 * @access private
	 */
	private $notifications = [];

	/**
	 * Service
	 *
	 * @var \Tigron\Ups\Service $service
	 * @access private
	 */
	private $service = null;

	/**
	 * Internationalforms
	 *
	 * @var \Tigron\Ups\Internationalforms $internationalforms
	 * @access private
	 */
	private $internationalforms = null;

	/**
	 * Any extra information about the shipment
	 *
	 * @var array
	 * @access private
	 */
	private $extra_information = [];

	/**
	 * Set shipper
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $shipper
	 */
	public function set_shipper(\Tigron\Ups\Contact $shipper): void {
		$this->shipper = $shipper;
	}

	/**
	 * Set recipient (deprecated)
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $recipient
	 */
	public function set_recipient(\Tigron\Ups\Contact $recipient): void {
		$this->set_ship_to($recipient);
		$this->set_sold_to($recipient);
	}

	/**
	 * Set ship_from
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $ship_from
	 */
	public function set_ship_from(\Tigron\Ups\Contact $ship_from): void {
		$this->ship_from = $ship_from;
	}

	/**
	 * Set sold_to
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $recipient
	 */
	public function set_sold_to(\Tigron\Ups\Contact $recipient): void {
		// <ErrorDescription>The Sold To party's country code must be the same as the Ship To party's country code with the exception of Canada and satellite countries.</ErrorDescription>
		// AX - Aland Islands  is consider a satellite country belonging to Finland, From the invoice details point of view the Sold to Country is Finland not Aland Islands.
		if ($recipient->address->country == 'AX') {
			$recipient->address->country = 'FI';
		}
		$this->sold_to = $recipient;
	}

	/**
	 * Set ship_to
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $recipient
	 */
	public function set_ship_to(\Tigron\Ups\Contact $recipient): void {
		$this->ship_to = $recipient;
	}

	/**
	 * Set package
	 *
	 * @access public
	 * @param \Tigron\Ups\Package $package
	 */
	public function add_package(\Tigron\Ups\Package $package): void {
		$this->packages[] = $package;
	}

	/**
	 * Set Service
	 *
	 * @access public
	 * @param \Tigron\Ups\Service $serice
	 */
	public function set_service(\Tigron\Ups\Service $service): void {
		$this->service = $service;
	}


	/**
	 * Add notification
	 *
	 * @access public
	 * @param \Tigron\Ups\Notification $notification
	 */
	public function add_notification(\Tigron\Ups\Notification $notification): void {
		$this->notifications[] = $notification;
	}

	/**
	 * Set internationalForms
	 *
	 * @access public
	 * @param \Tigron\Ups\InternationForms $internationforms
	 */
	public function set_internationalforms(\Tigron\Ups\Internationalforms $internationalforms): void {
		$this->internationalforms = $internationalforms;
	}

	/**
	 * Set extra information
	 *
	 * @access public
	 * @param array $extra_information
	 */
	public function set_extra_information(array $extra_information): void {
		$this->extra_information = $extra_information;
	}

	/**
	 * Get info
	 *
	 * @access public
	 * @return array<string> $info
	 */
	public function get_info(): array {
		$info = [
			'Request' => [
				'RequestOption' => 'nonvalidate',
				'TransactionReference' => [
					'CustomerContext' => 'Customer Comment',
				],
			],
			'LabelSpecification' => [
				'LabelPrintMethod' => [
					'Code' => 'GIF',
					'Description' => 'gif file',
				],
				'HTTPUserAgent' => 'Mozilla/4.5',
				'LabelImageFormat' => [
					'Code' => 'GIF',
					'Description' => 'gif',
				],
			],
			'Shipment' => [
				'Description' => $this->packages[0]->description,
				'Shipper' => $this->shipper->get_info(),
				'ShipTo' => $this->ship_to->get_info(),
				'ShipFrom' => $this->ship_from->get_info(),
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
			'ShipmentServiceOptions' => [],
		];
		foreach ($this->packages as $package) {
			$package_info = $package->get_info();
			$package_info['Packaging'] = $package_info['PackagingType'];
			unset($package_info['PackagingType']);
			$info['Shipment']['Package'][] = $package_info;
		}

		if (count($this->notifications) > 0) {
			$info['Shipment']['ShipmentServiceOptions']['Notification'] = [];
			foreach ($this->notifications as $notification) {
				$info['Shipment']['ShipmentServiceOptions']['Notification'][] = $notification->get_info();
			}
		}

		if ($this->internationalforms !== null) {
			$info['Shipment']['ShipmentServiceOptions']['InternationalForms'] = $this->internationalforms->get_info();
		}

		$info['Shipment']['Shipper']['ShipperNumber'] = Config::$account_number;
		$info = array_merge_recursive($info, $this->extra_information);
		return [ 'ShipmentRequest' => $info ];
	}

	/**
	 * Start shipment
	 *
	 * @access public
	 * @return array $response
	 */
	public function handle() {
		$client = Client::get();
		return $client->request('POST', '/shipments/' . Config::$api_version . '/ship', $this->get_info());
	}
}
