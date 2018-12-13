# shipping-ups
PHP library to allow shipping via UPS

## Howto

	/**
	 * Initialize the UPS package
	 */
	\Tigron\Ups\Config::$license_number = 'ups_license_number';
	\Tigron\Ups\Config::$account_number = 'account_number';
	\Tigron\Ups\Config::$user_id = 'user_id';
	\Tigron\Ups\Config::$password = 'password';
	\Tigron\Ups\Config::$logfile = 'path to log file';
	\Tigron\Ups\Config::$mode = 'test/prod';
	\Tigron\Ups\Config::$socket_timeout = 5; // Default = 5

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
	 * Define the ship from address
	 */
	$ship_from_address = new \Tigron\Ups\Address();
	$ship_from_address->line1 = 'street 1';
	$ship_from_address->line2 = 'additional line';
	$ship_from_address->line3 = 'additional line';
	$ship_from_address->zipcode = '12345';
	$ship_from_address->city = 'City';
	$ship_from_address->country = 'BE'; 	// ISO2 country code

	$ship_from = new \Tigron\Ups\Contact();
	$ship_from->company = 'Company name';
	$ship_from->firstname = 'Recipient firstname';
	$ship_from->lastname = 'Recipient lastname';
	$ship_from->phone = '+32.1234567';
	$ship_from->fax = '+32.1234567';
	$ship_from->email = 'info@example.com';
	$ship_from->vat = '000000000';
	$ship_from->address = $ship_from_address;

	/**
	 * Define the Recipient
	 */
	$address = new \Tigron\Ups\Address();
	$address->line1 = 'street 1';
	$address->line2 = 'additional line';
	$address->line3 = 'additional line';
	$address->zipcode = '12345';
	$address->city = 'City';
	$address->state = 'NY' // Required for US/Canada/Ireland
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
	 * Add notifications
	 */
	$notification = new \Tigron\Ups\Notification();
	$notification->code = 2;
	$notification->email_addresses = [ 'test@example.com' ];

	/**
	 * Optional: Send paperless invoice
	 */
	$internationalForms = new \Tigron\Ups\Internationalforms();
	$internationalForms->type = '01';
	$internationalForms->export_reason = 'SALE';
	$internationalForms->add_soldto($this->get_ups_customer($shipment));

	$product = new \Tigron\Ups\Product();
	$product->description = 'This is my test product';
	$product->quantity = 5;
	$product->measurement_code = 'PC';
	$product->origin_country = 'BE';
	$product->value = 100;

	$internationalForms->add_product($product);

	/**
	 * Ship
	 */
	$shipping = new \Tigron\Ups\Shipping();

	$shipping->set_shipper($shipper);
	$shipping->set_ship_from($ship_from);
	$shipping->set_ship_to($recipient);
	$shipping->set_sold_to($recipient);
	$shipping->add_package($package1);
	$shipping->add_package($package2);
	$shipping->set_service($service);
	$shipping->add_notification($notification);

	$result = $shipping->confirm();
	$accept_result = $shipping->accept($result['ShipmentDigest']);

	/**
	 * This is the label in GIF format
	 */
	$label = base64_decode($accept_result['ShipmentResults']['PackageResults']['LabelImage']['GraphicImage']);
