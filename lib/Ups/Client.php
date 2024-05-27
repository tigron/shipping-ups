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
	 * Guzzle
	 *
	 * @var GuzzleHttp\Client
	 */
	private $guzzle;

	/**
	 * Headers
	 *
	 * @var array $headers
	 */
	private $headers = [
		'Content-Type' => 'application/json',
	];

	/**
	 * Only 1 client can exist
	 *
	 * @access private
	 * @var Client $client
	 */
	private static $client = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$base_uri = 'https://onlinetools.ups.com/';
		if (Config::$mode == 'test') {
			$base_uri = 'https://wwwcie.ups.com/';
		}

		$this->guzzle = new \GuzzleHttp\Client([
			'base_uri' => $base_uri,
			'http_errors' => false,
		]);
	}

	/**
	 * Execute a request
	 *
	 * @access public
	 * @param string $method
	 * @param string $endpoint
	 * @param array $payload
	 */
	public function request($method, $endpoint, $payload) {
		$this->authenticate();
		$this->headers['transId'] = microtime();
		$this->headers['transactionSrc'] = 'tigron/shipping-ups';
		$response = $this->guzzle->request($method, '/api/' . $endpoint, [
			'headers' => $this->headers,
			'json' => $payload
		]);

		/**
		 * If request fails, throw an Exception
		 */
		if ($response->getStatusCode() !== 200) {
			$body = json_decode($response->getBody(), true);
			$error = array_shift($body['response']['errors']);
			throw new \Exception('Error in request ' . $method . ' "' . $endpoint . '": ' . $error['message']);
		}

		$response = json_decode($response->getBody(), true);
		return $response;
	}

	/**
	 * Sets the Authorization header
	 *
	 * @access private
	 * @return void
	 */
	private function authenticate(): void {
		try {
			$token = $this->get_access_token();
		} catch (\Exception $e) {
			$token = $this->create_access_token();
		}

		$this->headers['Authorization'] = 'Bearer ' . $token;
	}

	/**
	 * Get the access token
	 *
	 * @access private
	 * @return string $access_token
	 */
	private function get_access_token(): string {
		$config = \Skeleton\Core\Config::Get();
		$access_token_file = Config::$access_token_file;
		if ($access_token_file === null) {
			$access_token_file = sys_get_temp_dir() . '/ups.token';
		}

		if (file_exists($access_token_file) === false) {
			throw new \Exception('No access token present');
		}

		$access_token = file_get_contents($access_token_file);
		$access_token = json_decode($access_token, true);

		/**
		 * If the access_token is invalid, we need to request a new one
		 */
		if ($access_token === false) {
			throw new \Exception('Invalid access token');
		}


		/**
		 * If the access_token is expired, we need to request a new one
		 */
		$now = (new \DateTime())->format('Uv');
		if ($now > $access_token['expires_at'] - 60) {
			throw new \Exception('Access token expired');
		}
		$token = $access_token['access_token'];
		return $token;
	}

	/**
	 * Create an access code
	 *
	 * @access private
	 * @return string $access_token
	 */
	private function create_access_token() {
		$response = $this->guzzle->request('POST', '/security/v1/oauth/token', [
			'auth' => [
				Config::$client_id,
				Config::$client_secret
			],
			'form_params' => [
				'grant_type' => 'client_credentials',
			],
		]);

		/**
		 * If request fails, throw an Exception
		 */
		if ($response->getStatusCode() !== 200) {
			$body = json_decode($response->getBody(), true);
			$error = array_shift($body['response']['errors']);
			throw new \Exception('Unable to request access token: ' . $error['message']);
		}

		/**
		 * If the request went ok, write the access token to a file
		 */
		$access_token = json_decode($response->getBody(), true);
		$access_token['expires_at'] = $access_token['issued_at'] + $access_token['expires_in'];

		$config = \Skeleton\Core\Config::Get();
		$access_token_file = Config::$access_token_file;
		if ($access_token_file === null) {
			$access_token_file = sys_get_temp_dir() . '/ups.token';
		}

		file_put_contents($access_token_file, json_encode($access_token));
		return $access_token['access_token'];
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
	public function call2(string $endpoint, string $request_option, string $payload, string $method = 'POST', array $parameters = [], string $version = null): array {

		// test syntax of JSON
		json_decode($payload);
		if (json_last_error() > 0) {
			if (Config::$logfile !== null) {
				file_put_contents(Config::$logfile, "JSON parsing error: " . json_last_error_msg() . " (" . json_last_error() . ")". "\n", FILE_APPEND);
				file_put_contents(Config::$logfile, $payload. "\n", FILE_APPEND);
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
			// if bearer token is expired or expires in less than 60 seconds we need to request a new one
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

	public static function get(): self {
		if (!isset(self::$client)) {
			$client = new self();
			self::$client = $client;
		}
		return self::$client;
	}

}
