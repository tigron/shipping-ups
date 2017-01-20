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
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
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
			$url = 'https://wwwcie.ups.com/ups.app/xml/' . $endpoint;
		} else {
			$url = 'https://onlinetools.ups.com/ups.app/xml/' . $endpoint;
		}

		$template = Template::get();
		$authentication_xml = $template->render('authenticate.twig');

		$dom = new \DomDocument();
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($authentication_xml);
		$dom->formatOutput = true;
		$authentication_xml = $dom->saveXml();

		$dom = new \DomDocument();
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($xml);
		$dom->formatOutput = true;
		$xml = $dom->saveXml();

		$xml = $authentication_xml . "\n\n" . $xml . "\n";

		$log  = '----------------------------------------------------------------' . "\n";
		$log .= 'New request to: ' . $url . "\n";
		$log .= 'Date: ' . date('Y-m-d H:i:s') . "\n";
		$log .= 'Sending XML: ' . "\n";
		$log .= $xml . "\n";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/x-www-form-urlencoded; charset=utf-8' ]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);

		//execute post
		$result = curl_exec($ch);
		if ($result === false) {
			throw new \Exception('Error ' . curl_errno($ch) . ': ' . curl_error($ch));
		}

		$dom = new \DomDocument();
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($result);
		$dom->formatOutput = true;
		$result = $dom->saveXml();

		$log .= 'Response: ' . "\n" . $result . "\n\n\n";

		if (Config::$logfile !== null) {
			file_put_contents(Config::$logfile, $log, FILE_APPEND);
		}

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
