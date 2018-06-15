<?php
/**
 * Ship class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Tigron\Ups;

class Paperless {

	/**
	 * Get the SOAP Client
	 *
	 * @access private
	 * @return \Soapclient $soapclient
	 */
	private function get_client() {
		$mode = [
			'soap_version' => 'SOAP_1_1',  // use soap 1.1 client
			'trace' => 1
		];

		$client = new \SoapClient(dirname(__FILE__) . '/../../assets/PaperlessDocumentAPI.wsdl' , $mode);

		if (Config::$mode == 'test') {
			$url = 'https://wwwcie.ups.com/webservices/PaperlessDocumentAPI';
		} else {
			$url = 'https://filexfer.ups.com/webservices/PaperlessDocumentAPI';
		}

		//set endpoint url
		$client->__setLocation($url);

		//create soap header
		$usernameToken['Username'] = \Tigron\Ups\Config::$user_id;
		$usernameToken['Password'] = \Tigron\Ups\Config::$password;
		$serviceAccessLicense['AccessLicenseNumber'] = \Tigron\Ups\Config::$license_number;
		$upss['UsernameToken'] = $usernameToken;
		$upss['ServiceAccessToken'] = $serviceAccessLicense;

		$header = new \SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0','UPSSecurity',$upss);
	    $client->__setSoapHeaders($header);
	    return $client;
	}

	private function call($function_name, $arguments) {
		$client = $this->get_client();

		/**
		 * To get endpoint location, unset it and set it back
		 */
		$endpoint = $client->__setLocation();
		$client->__setLocation($endpoint);

		$error = null;
		$response = $client->$function_name($arguments);

		$log  = '----------------------------------------------------------------' . "\n";
		$log .= 'New request to: ' . $endpoint . "\n";
		$log .= 'Date: ' . date('Y-m-d H:i:s') . "\n";
		$log .= 'Sending XML: ' . "\n";

		$dom = new \DomDocument();
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($client->__getLastRequest());
		$dom->formatOutput = true;
		$xml = $dom->saveXml();

		$log .= $xml . "\n";
		$log .= 'Response: ' . "\n";

		$dom = new \DomDocument();
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($client->__getLastResponse());
		$dom->formatOutput = true;
		$xml = $dom->saveXml();

		$log .= $xml;

		if (Config::$logfile !== null) {
			file_put_contents(Config::$logfile, $log, FILE_APPEND);
		}
		if (isset($response)) {
			return $response;
		}
	}

	public function upload(\Skeleton\File\File $file) {
		if (!$file->is_pdf()) {
			throw new \Exception('File must be a PDF');
		}

		$data= [
			'Request' => [
				'TransactionReference' => [
					'CustomerContext' => 'test',
				]
			],
			'ShipperNumber' => \Tigron\Ups\Config::$account_number,
			'UserCreatedForm' => [
				'UserCreatedFormFileName' => $file->name,
				'UserCreatedFormFile' => $file->get_contents(),
				'UserCreatedFormFileFormat' => 'pdf',
				'UserCreatedFormDocumentType' => '002'
			]
		];

  	    $resp = $this->call('ProcessUploading',$data);
  	    return $resp->FormsHistoryDocumentID->DocumentID;
	}
}
