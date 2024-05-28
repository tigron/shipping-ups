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
	private function __construct() {
		$base_uri = 'https://onlinetools.ups.com/';
		if (Config::$mode == 'test') {
			$base_uri = 'https://wwwcie.ups.com/';
		}

		$this->guzzle = new \GuzzleHttp\Client([
			'base_uri' => $base_uri,
			'http_errors' => false,

			'handler' => $this->create_logging_handler_stack([
				'{method} {uri} HTTP/{version} {req_body} - {req_headers}',
				"RESPONSE: {code} - {res_body}\n"
			]),
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
	 * Create logging handler stack
	 *
	 * @access public
	 * @param array $message_formats
	 * @return HandlerStack $stack
	 */
	private function create_logging_handler_stack(array $message_formats): \GuzzleHttp\HandlerStack {
		$stack = \GuzzleHttp\HandlerStack::create();
		foreach ($message_formats as $message_format) {
			$stack->unshift(
				$this->get_logger($message_format)
			);
		}

		return $stack;
	}

	/**
	 * Get logger
	 *
	 * @access public
	 * @param string $message_format
	 * @return
	 */
	private function get_logger(string $message_format) {
		if (empty($this->logger)) {
			$this->logger = new \Monolog\Logger('tigron/shipping-ups');
			$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
			$handler = new \Monolog\Handler\StreamHandler(Config::$logfile);
			$handler->setFormatter($formatter);
			$this->logger->pushHandler($handler);
		}

		return \GuzzleHttp\Middleware::log(
			$this->logger,
			new \GuzzleHttp\MessageFormatter($message_format)
		);
	}

	/**
	 * Get a client
	 *
	 * @access public
	 * @return Client $client
	 */
	public static function get(): self {
		if (!isset(self::$client)) {
			$client = new self();
			self::$client = $client;
		}
		return self::$client;
	}
}
