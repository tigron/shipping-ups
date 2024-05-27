<?php
/**
 * Service class
 *
 * This class is a representation of UPS Service
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Service {

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
	 * Get info
	 *
	 * @access public
	 * @return array<string>
	 */
	public function get_info(): array {
		$info = [
			'Code' => (string)$this->code,
			'Description' => $this->name
		];
		return $info;
	}
}
