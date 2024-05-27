<?php
/**
 * Package class
 *
 * This class is a representation of UPS Package
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Package {

	/**
	 * Package Type
	 *
	 * @var \Tigron\Ups\Package\Type
	 * @access public
	 */
	public $type = null;

	/**
	 * name
	 *
	 * @var string $name
	 * @access public
	 */
	public $description = '';

	/**
	 * Weight (in kg)
	 *
	 * @var string $weight
	 * @access public
	 */
	public $weight = '';

	/**
	 * Large package
	 *
	 * @var bool $large
	 * @access public
	 */
	public $large = false;

	/**
	 * Additional handling
	 *
	 *	Additional Handling
	 *	An Additional Handling charge may be applied to the following:
	 *	Any article that is encased in an outside shipping container made of metal or wood.
	 *	Any item, such as a barrel, drum, pail or tire, that is not fully encased in a corrugated cardboard shipping container.
	 *	Any package with the longest side exceeding 60 inches or its second longest side exceeding 30 inches.
	 *	Any package with an actual weight greater than 70 pounds.
	 *
	 * @var bool $additional_handling
	 * @access public
	 */
	public $additional_handling = false;

	/**
	 * Get info
	 *
	 * @access public
	 * @return array<string> $info
	 */
	public function get_info(): array {
		$info = [
			'Packaging' => $this->type->get_info(),
			'Dimensions' => [
				'UnitOfMeasurement' => [
					'Code' => 'CM',
					'Description' => 'Centimeters',
				],
				'Length' => (string)5,
				'Width' => (string)5,
				'Height' => (string)5,
			],
			'PackageWeight' => [
				'UnitOfMeasurement' => [
					'Code' => 'KGS',
					'Description' => 'Kilograms',
				],
				'Weight' => (string)$this->weight,
			],
		];
		return $info;
	}

}
