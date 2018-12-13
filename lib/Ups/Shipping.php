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
	 * Set shipper
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $shipper
	 */
	public function set_shipper(\Tigron\Ups\Contact $shipper) {
		$this->shipper = $shipper;
	}

	/**
	 * Set recipient (deprecated)
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $recipient
	 */
	public function set_recipient(\Tigron\Ups\Contact $recipient) {
		$this->set_ship_to($recipient);
		$this->set_sold_to($recipient);
	}

	/**
	 * Set sold_to
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $recipient
	 */
	public function set_sold_to(\Tigron\Ups\Contact $recipient) {
		$this->sold_to = $recipient;
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
	 * Set package
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
	 * Set ship_from
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $ship_from
	 */
	public function set_ship_from(\Tigron\Ups\Contact $ship_from) {
		$this->ship_from = $ship_from;
	}

	/**
	 * Add notification
	 *
	 * @access public
	 * @param \Tigron\Ups\Notification $notification
	 */
	public function add_notification(\Tigron\Ups\Notification $notification) {
		$this->notifications[] = $notification;
	}

	/**
	 * Set internationalForms
	 *
	 * @access public
	 * @param \Tigron\Ups\InternationForms $internationforms
	 */
	public function set_internationalforms(\Tigron\Ups\Internationalforms $internationalforms) {
		$this->internationalforms = $internationalforms;
	}

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
	public function confirm() {

		if (!isset($this->shipper)) {
			throw new \Exception('Shipper is not set, use "set_shipper()" to define one');
		}

		if (!isset($this->ship_from)) {
			throw new \Exception('Ship_From is not set, use "set_ship_from()" to define one');
		}

		if (!isset($this->ship_to)) {
			throw new \Exception('Recipient ship_to is not set, use "set_ship_to()" to define one');
		}

		if (!isset($this->sold_to)) {
			throw new \Exception('Recipient sold_to is not set, use "set_sold_to()" to define one');
		}

		if (count($this->packages) == 0) {
			throw new \Exception('Add package first. Use "add_package()" to add one');
		}

		if (!isset($this->service)) {
			throw new \Exception('No service set, use "set_service()" to add one');
		}


		$template = Template::get();
		$template->assign('shipper', $this->shipper);
		$template->assign('ship_from', $this->ship_from);
		$template->assign('sold_to', $this->sold_to);
		$template->assign('ship_to', $this->ship_to);
		$template->assign('packages', $this->packages);
		$template->assign('service', $this->service);
		$template->assign('notifications', $this->notifications);
		$template->assign('internationalforms', $this->internationalforms);

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
