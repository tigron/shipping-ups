<?php
/**
 * Contact class
 *
 * This class is a representation of a contact
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Internationalforms {

	/**
	 * Products
	 *
	 * @access private
	 * @var array $products
	 */
	private $products;

	/**
	 * Type
	 *
	 * @var string $type
	 * @access public
	 *
	 *	01 - Invoice;
	 *	03 - CO; 0
	 *	04 - NAFTA CO;
	 *	05 - Partial Invoice;
	 *	06 - Packinglist,
	 *	07 - Customer Generated Forms;
	 *	08 - Air Freight Packing List;
	 *	09 - CN22 Form;
	 *	10 - UPS Premium Care Form,
	 *	11 - EEI.
	 */
	public $type = null;

	/**
	 * export_reason
	 *
	 * @access public
	 * @var string $export_reason
	 *
	 * Possible values:
	 * SALE, GIFT, SAMPLE, RETURN, REPAIR, INTERCOMPANYDATA
	 */
	public $export_reason = '';

	/**
	 * Soldto
	 *
	 * @access public
	 * @var Contact $contact
	 */
	public $soldto = null;

	/**
	 * Validate
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors) {
	}

	/**
	 * Add product
	 *
	 * @access public
	 * @param \Tigron\Ups\Product $product
	 */
	public function add_product(\Tigron\Ups\Product $product) {
		$this->products[] = $product;
	}

	/**
	 * add soldTo
	 *
	 * @access public
	 * @param \Tigron\Ups\Contact $contact
	 */
	public function add_soldto(\Tigron\Ups\Contact $contact) {
		$this->soldto = $contact;
	}

	/**
	 * Get products
	 *
	 * @access public
	 * @return array $products
	 */
	public function get_products() {
		return $this->products;
	}

	/**
	 * Get info
	 *
	 * @access public
	 * return array<string> $info
	 */
	public function get_info(): array {
		$info = [
			'FormType' => $this->type,
			'Product' => [],
			'CurrencyCode' => 'EUR',
			'InvoiceDate' => date('Ymd'),
			'ReasonForExport' => $this->export_reason,
			'Contacts' => [
				'SoldTo' => $this->soldto->get_info(),
			],
		];
		foreach ($this->products as $product) {
			$info['Product'][] = $product->get_info();
		}
		return $info;
	}
}
