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
	 * @param string $request_option
	 * @param string $payload
	 * @param string $method
	 * @param array $parameters
	 * @param string $version
	 * @return array
	 */
	public function call(string $endpoint, string $request_option, string $payload, string $method = 'POST', array $parameters = [], string $version = null): array {

		// test syntax of JSON
		json_decode($payload);
		if (json_last_error() > 0) {
			if (Config::$logfile !== null) {
				file_put_contents(Config::$logfile, "JSON parsing error: " . json_last_error_msg() . " (" . json_last_error() . ")", FILE_APPEND);
				file_put_contents(Config::$logfile, $payload, FILE_APPEND);
			}
			throw new \Exception("JSON parsing error: " . json_last_error_msg() . " (" . json_last_error() . ")");
		}

		//open connection
		$ch = curl_init();

		if ($version === null) {
			$version = Config::$api_version;
		}
		if (Config::$mode == 'test') {
			$url = 'https://wwwcie.ups.com/api/' . $endpoint . '/' . $version . '/' . $request_option;
		} else {
			$url = 'https://onlinetools.ups.com/api/' . $endpoint . '/' . $version . '/' . $request_option;
		}

		if (count($parameters) > 0) {
			$url .= "?" . http_build_query($parameters);
		}

		$config = \Skeleton\Core\Config::Get();

		// checking if bearer toking is still valid or requesting one
		try {
			// searching for auth file containing a token
			if (file_exists($config->tmp_dir . '/ups.json') === false) {
				throw new \Exception('No auth file present');
			}
			$auth = file_get_contents($config->tmp_dir . '/ups.json');
			$auth = json_decode($auth, true);
			// if bearer token is expired or expires in less than 60 seconds we need to request a new on
			if ($auth['expires_at'] - 60 < time()) {
				throw new \Exception("New token needed");
			}
			$token = $auth['access_token'];
		} catch (\Exception $e) {
			// requesting a new token
			$payload = "grant_type=client_credentials";

			$authentication_url = 'https://onlinetools.ups.com/security/v1/oauth/token';
			if (Config::$mode == 'test') {
				$authentication_url = 'https://wwwcie.ups.com/security/v1/oauth/token';
			}
			curl_setopt_array($ch, [
				CURLOPT_HTTPHEADER => [
					"Content-Type: application/x-www-form-urlencoded",
					"x-merchant-id: string",
					"Authorization: Basic " . base64_encode(Config::$client_id . ':' . Config::$secrect_key),
				],
				CURLOPT_POSTFIELDS => $payload,
				CURLOPT_URL => $authentication_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CUSTOMREQUEST => "POST",
			]);

			$response = curl_exec($ch);
			$error = curl_error($ch);

			curl_close($ch);

			if ($error) {
				throw new \Excpetion("cURL Error #:" . $error);
			}
			$response = json_decode($response, true);
			$response['expires_at'] = (int)$response['issued_at'] + (int)$response['expires_in'];
			// saving token and expiration tstamp to file
			file_put_contents($config->tmp_dir . '/ups.json', json_encode($response));
			$token = $response['access_token'];
		}

		$template = Template::get();

		$headers = [
			"Content-Type: application/x-www-form-urlencoded",
			"Authorization: Bearer " . $token,
			"transId: string",
			"transactionSrc: testing",
		];

		$log  = '----------------------------------------------------------------' . "\n";
		$log .= 'New request to: ' . $url . "\n";
		$log .= 'Date: ' . date('Y-m-d H:i:s') . "\n";
		$log .= 'Headers: ' . print_r($headers, true);
		$log .= 'Payload: ' . "\n";
		$log .= $payload . "\n";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_TIMEOUT, Config::$socket_timeout);
		if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}

		//execute post
		$result = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$log .= "Return code: HTTP/" . $httpcode . "\n";

		if (curl_errno($ch) > 0) {
			throw new \Exception('Error ' . curl_errno($ch) . ': ' . curl_error($ch));
		}

		if (empty($result)) {
			$log .= 'Response: empty' . "\n\n\n";
			if (Config::$logfile !== null) {
				file_put_contents(Config::$logfile, $log, FILE_APPEND);
			}
			throw new \Exception('JSON returned by UPS is empty');
		}

		$res = json_decode($result, true);
		if ($res === null) {
			$log .= 'Response: ' . "\n" . $result . "\n\n\n";
			if (Config::$logfile !== null) {
				file_put_contents(Config::$logfile, $log, FILE_APPEND);
			}
			throw new \Exception('JSON returned by UPS is not syntaxically correct');
		}

		$log .= 'Response: ' . "\n" . print_r($res, true) . "\n\n\n";
		if (Config::$logfile !== null) {
			file_put_contents(Config::$logfile, $log, FILE_APPEND);
		}

		if (isset($res['response']['errors'])) {
			foreach ($res['response']['errors'] as $error) {
				throw new \Exception('Error ' . $error['code'] . ': ' . $error['message']);
			}
		}
		return $res;
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
