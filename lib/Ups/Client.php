<?php
/**
 * UPS Client class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Tigron\Ups;

class Client {

	/**
	 * \Skeleton\Template\Template object
	 *
	 * @access protected
	 * @var $template
	 */
	protected $template;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {

		// Initialize a template object
		$this->template = new \Skeleton\Template\Template();

		// Set the template path
		$this->template->set_template_directory(dirname(__FILE__) . '/../../templates');

		// Assign variables
		$this->template->assign('license_number', \Tigron\Ups\Config::$license_number);
		$this->template->assign('user_id', \Tigron\Ups\Config::$user_id);
		$this->template->assign('password', \Tigron\Ups\Config::$password);
	}

	/**
	 * Assign
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 */
	public function assign($key, $value) {
		$this->template->assign($key, $value);
	}

	/**
	 * Call
	 *
	 * @access public
	 * @param string $endpoint
	 * @param string $xml
	 * @return string $xml
	 */
	public function call($endpoint, $xml) {
		//open connection
		$ch = curl_init();

		if (Config::$mode == 'test') {
			curl_setopt($ch,CURLOPT_URL, 'https://wwwcie.ups.com/ups.app/xml/' . $endpoint);
		} else {
			// do live call here
		}

		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		//execute post
		$result = curl_exec($ch);
		$result = $this->xml_to_array($result);
		if ($result['Response']['ResponseStatusCode'] == 0) {
			throw new \Exception('Error ' . $result['Response']['Error']['ErrorCode'] . ': ' . $result['Response']['Error']['ErrorDescription']);
		}
		return $result;
	}

	/**
	 * XML to Array conversion
	 *
	 * @access private
	 * @param string $xml
	 * @return array $array
	 */
	private function xml_to_array($xml) {
		$doc = new \DOMDocument();
		$doc->loadXML($xml);
		$root = $doc->documentElement;
		$output = $this->domnode_to_array($root);
		$output['@root'] = $root->tagName;
		return $output;
	}

	/**
	 * Domnode to array conversion
	 *
	 * @access private
	 * @param Node $node
	 * @return array $array
	 */
	private function domnode_to_array($node) {
		$output = array();
		switch ($node->nodeType) {

			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;

			case XML_ELEMENT_NODE:
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
					$child = $node->childNodes->item($i);
					$v = $this->domnode_to_array($child);
					if(isset($child->tagName)) {
						$t = $child->tagName;
						if(!isset($output[$t])) {
							$output[$t] = array();
						}
						$output[$t][] = $v;
					}
					elseif($v || $v === '0') {
						$output = (string) $v;
					}
				}
				if ($node->attributes->length && !is_array($output)) { //Has attributes but isn't an array
					$output = array('@content'=>$output); //Change output into an array.
				}
				if (is_array($output)) {
					if ($node->attributes->length) {
						$a = array();
						foreach($node->attributes as $attrName => $attrNode) {
							$a[$attrName] = (string) $attrNode->value;
						}
						$output['@attributes'] = $a;
					}
					foreach ($output as $t => $v) {
						if (is_array($v) && count($v)==1 && $t!='@attributes') {
							$output[$t] = $v[0];
						}
					}
				}
				break;
		}
		return $output;
	}
}