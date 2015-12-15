# shipping-ups
PHP library to allow shipping via UPS

## Howto

	/**
	 * Define the shipper
	 */
	$shipper_address = new \Tigron\Ups\Address();
	$shipper_address->line1 = 'street 1';
	$shipper_address->line2 = 'additional line';
	$shipper_address->line3 = 'additional line';
	$shipper_address->zipcode = '12345';
	$shipper_address->city = 'City';
	$shipper_address->country = 'BE'; 	// ISO2 country code

	$shipper = new \Tigron\Ups\Contact();
	$shipper->company = 'Company name';
	$shipper->firstname = 'Recipient firstname';
	$shipper->lastname = 'Recipient lastname';
	$shipper->phone = '+32.1234567';
	$shipper->fax = '+32.1234567';
	$shipper->email = 'info@example.com';
	$shipper->vat = '000000000';
	$shipper->address = $shipper_address;

	/**
	 * Define the Recipient
	 */
    $address = new \Tigron\Ups\Address();
	$address->line1 = 'street 1';
	$address->line2 = 'additional line';
	$address->line3 = 'additional line';
	$address->zipcode = '12345';
	$address->city = 'City';
	$address->country = 'BE'; 	// ISO2 country code

	$recipient = new \Tigron\Ups\Contact();
	$recipient->company = 'Company name';
	$recipient->firstname = 'Recipient firstname';
	$recipient->lastname = 'Recipient lastname';
	$recipient->phone = '+32.1234567';
	$recipient->fax = '+32.1234567';
	$recipient->email = 'info@example.com';
	$recipient->vat = '000000000';
	$recipient->address = $address;

	/**
	 * Define the package type
	 */
	$package_type = \Tigron\Ups\Package\Type::get_by_code('02');

	/**
	 * Create packages
	 */
	$package1 = new \Tigron\Ups\Package();
	$package1->type = $package_type;
	$package1->description = 'Order x';
	$package1->weight = $shipment->get_weight()/1000;

	$package2 = new \Tigron\Ups\Package();
	$package2->type = $package_type;
	$package2->description = 'Order y';
	$package2->weight = $shipment->get_weight()/1000;

	$packages = [ $package1, $package2 ];

	/**
	 * Select the UPS service
	 */
	$service = new \Tigron\Ups\Service();
	$service->name = 'UPS Saver';
	$service->code = 65;

	/**
	 * Ship
	 */
	$shipping = new \Tigron\Ups\Shipping();
	$result = $shipping->confirm($shipper, $recipient, [ $package ], $service);
	$accept_result = $shipping->accept($result['ShipmentDigest']);

	/**
	 * This is the label in GIF format
	 */
	$label = base64_decode($accept_result['ShipmentResults']['PackageResults']['LabelImage']['GraphicImage']);
