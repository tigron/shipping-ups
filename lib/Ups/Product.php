<?php
/**
 * Product class
 *
 * This class is a representation of UPS Service
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Product {

	/**
	 * Description
	 *
	 * @var string $description
	 * @access public
	 */
	public $description = '';

	/**
	 * Quantity
	 *
	 * @var string $quantity
	 * @access public
	 */
	public $quantity = null;

	/**
	 * measurement_code
	 *
	 * @var string $measurement_code
	 * @access public
	 *
	 *	BA = Barrel
	 *	BE = Bundle
	 *	BG = Bag
	 *	BH = Bunch
	 *	BOX = Box
	 *	BT = Bolt
	 *	BU = Butt
	 *	CI = Canister
	 *	CM = Centimeter
	 *	CON = Container
	 *	CR = Crate
	 *	CS = Case
	 *	CT = Carton
	 *	CY = Cylinder
	 *	DOZ = Dozen
	 *	EA = Each
	 *	EN = Envelope
	 *	FT = Feet
	 *	KG = Kilogram
	 *	KGS = Kilograms
	 *	LB = Pound
	 *	LBS = Pounds
	 *	L = Liter
	 *	M = Meter
	 *	NMB = Number
	 *	PA = Packet
	 *	PAL = Pallet
	 *	PC = Piece
	 *	PCS = Pieces
	 *	PF = Proof Liters
	 *	PKG = Package
	 *	PR = Pair
	 *	PRS = Pairs
	 *	RL = Roll
	 *	SET = Set
	 *	SME = Square Meters
	 *	SYD = Square Yards
	 *	TU = Tube
	 *	YD = Yard
	 *	OTH = Other
	 */
	public $measurement_code = null;

	/**
	 * Measurement_description
	 *
	 * @access public
	 * @var array $measurement_description
	 */
	public $measurement_description = [
		'BA' => 'Barrel',
		'BE' => 'Bundle',
		'BG' => 'Bag',
		'BH' => 'Bunch',
		'BOX' => 'Box',
		'BT' => 'Bolt',
		'BU' => 'Butt',
		'CI' => 'Canister',
		'CM' => 'Centimeter',
		'CON' => 'Container',
		'CR' => 'Crate',
		'CS' => 'Case',
		'CT' => 'Carton',
		'CY' => 'Cylinder',
		'DOZ' => 'Dozen',
		'EA' => 'Each',
		'EN' => 'Envelope',
		'FT' => 'Feet',
		'KG' => 'Kilogram',
		'KGS' => 'Kilograms',
		'LB' => 'Pound',
		'LBS' => 'Pounds',
		'L' => 'Liter',
		'M' => 'Meter',
		'NMB' => 'Number',
		'PA' => 'Packet',
		'PAL' => 'Pallet',
		'PC' => 'Piece',
		'PCS' => 'Pieces',
		'PF' => 'Proof Liters',
		'PKG' => 'Package',
		'PR' => 'Pair',
		'PRS' => 'Pairs',
		'RL' => 'Roll',
		'SET' => 'Set',
		'SME' => 'Square Meters',
		'SYD' => 'Square Yards',
		'TU' => 'Tube',
		'YD' => 'Yard',
		'OTH' => 'Other'
	];

	/**
	 * Origin_Country
	 *
	 * @access public
	 * @var string
	 */
	public $origin_country = null;

	/**
	 * Value
	 *
	 * @var string $value
	 * @access public
	 */
	public $value = null;

	/**
	 * Get info
	 *
	 * @access public
	 */
	public function get_info() {
		$info = [
			'Description' => $this->get_description_parts(),
			'Unit' => [
				'Number' => (string)$this->quantity,
				'UnitOfMeasurement' => [
					'Code' => $this->measurement_code,
					'Description' => $this->measurement_description[$this->measurement_code],
				],
				'Value' => (string)$this->value,
			],
			'OriginCountryCode' => $this->origin_country,
		];
		return $info;
	}

	/**
	 * Get description
	 *
	 * @access public
	 * @return array $description_parts
	 */
	public function get_description_parts() {
		$description = wordwrap($this->description, 35, "\n", true);
		$lines = explode("\n", $description);
		return $lines;
	}


}
