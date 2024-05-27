<?php
/**
 * Package_Type class
 *
 * This class is a representation of UPS Package_Type
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups\Package;

class Type {

	/**
	 * code
	 *
	 * @var string $code
	 * @access public
	 */
	public $code = '';

	/**
	 * name
	 *
	 * @var string $name
	 * @access public
	 */
	public $name = '';

	/**
	 * Get all
	 *
	 * @access public
	 * @return array $types
	 */
	public static function get_all() {
		$types = [
			'01' => 'UPS Letter',
			'02' => 'Customer Supplied Package',
			'03' => 'Tube',
			'04' => 'PAK',
			'21' => 'UPS Express Box',
			'24' => 'UPS 25KG Box',
			'25' => 'UPS 10KG Box',
			'30' => 'Pallet',
			'2a' => 'Small Express Box',
			'2b' => 'Medium Express Box',
			'2c' => 'Large Express Box',
			'56' => 'Flats',
			'57' => 'Parcels',
			'58' => 'BPM',
			'59' => 'First Class',
			'60' => 'Priority',
			'61' => 'Machinables',
			'62' => 'Irregulars',
			'63' => 'Parcel Post',
			'64' => 'BPM Parcel',
			'65' => 'Media Mail',
			'66' => 'BPM Flat',
			'67' => 'Standard Flat',
		];
		$objects = [];
		foreach ($types as $key => $type) {
			$object = new self();
			$object->code = $key;
			$object->name = $type;
			$objects[] = $object;
		}
		return $objects;
	}

	/**
	 * Get info
	 *
	 * @access public
	 */
	public function get_info() {
		$info = [
			'Code' => (string)$this->code,
			'Description' => $this->name
		];
		return $info;
	}

	/**
	 * Get by code
	 *
	 * @access public
	 * @param string $code
	 * @return \Tigron\Ups\Package\Type $type
	 */
	public static function get_by_code($code) {
		$types = self::get_all();
		foreach ($types as $type) {
			if ($type->code == $code) {
				return $type;
			}
		}
		throw new \Exception('Package type not found');
	}

}
