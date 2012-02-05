<?php
/**
* OpenSRS Publishing Module for Hostbill
* Allows Hostbill to automate the management of goMobi accounts via XML-API
*
* @author Chris Talkington <chris@talkington.info>
* @version 1.0.2
*/
class opensrs_publishing extends HostingModule {
	protected $description = 'Resell publishing services (goMobi) from OpenSRS, fully automated solution with basic integration.';

	// options for the product configuration from Settings => Products & Services => Product => Connect with Module
	protected $options = array(
		'option1' => array (
			'name' => 'service',
			'value' => 'gomobi',
			'type' => 'select',
			'default' => array('gomobi')
		)
	);

	protected $lang = array(
		'english' => array(
			'opensrs_publishingservice' => 'Publishing Service',
			'OSRSPLoginTo' => 'Login to goMobi'
		)
	);

	protected $serverFields = array(
		'hostname' => false,
		'ip' => false,                   // 1
		'maxaccounts' => false,
		'status_url' => false,
		'username' => true,              // 2
		'password' => false,             // 3
		'hash' => true,
		'ssl' => true,                   // 4
		'nameservers' => false,
		'field1' => false,               // 5
		'field2' => false                // 6
	);

	protected $serverFieldsDescription = array(
		'username' => 'User ID',
		'hash' => 'Private Key',
		'ssl' => 'Test Mode'
	);

	// $details are a specific data for each account. REQUIRED names are 'username', 'password', 'domain'
	protected $details = array(
		'option1' => array (
			'name' => 'username',
			'value' => false,
			'type' => 'input',
			'default' =>false
		),
		'option2' => array (
			'name' => 'password',
			'value' => false,
			'type' => 'input',
			'default'=> false
		),
		'option3' => array (
			'name' => 'domain',
			'value' => false,
			'type' => 'input',
			'default' =>false
		)
	);

	// you can add your own command and create method to handle the action
	protected $commands = array('Create', 'Suspend', 'Unsuspend', 'Terminate','Expire','Unexpire');

	private $api_username;
	private $api_private_key;
	private $api_environment;
	private $api_environments = array(
		'live' => array('rr-n1-tor.opensrs.net',55443),
		'testing' => array('horizon.opensrs.net',55443)
	);
	private $api_service;

	private $api_allowed_services = array('gomobi');
	private	$api_allowed_actions = array(
		'gomobi' => array(
			'create','delete','disable','enable','let_expire','generate_redirection_code',
			'get_control_panel_url','get_service_info','update'
		)
	);

	private $debug_email = '';

	private $base_api_obj;

	// this is the method to load the Server Info configured at Apps Section.
	public function connect($connect) {
		$this->api_username = $connect['username'];
		$this->api_private_key = $connect['hash'];
		$this->api_environment = ($connect['secure']) ? 'testing' : 'live';

		$this->api_service = $this->options['option1']['value'];
	}

	public function Create() {
		$attributes = array(
			'end_user_auth_info' => array(
				'email_address' => $this->client_data['email'],
				'username' => $this->details['option1']['value'],
				'password' => $this->details['option2']['value']
			)
		);

		if($response = $this->api('create',$attributes)) {
			if ($response->response_code == 200) {
				return true;
			} else {
				$this->addError($response->response_text);
				return false;
			}
		} else {
			return false;
		}
	}

	public function Suspend() {
		if ($response = $this->api('disable')) {
			if ($response->response_code == 200) {
				return true;
			} else {
				$this->addError($response->response_text);
				return false;
			}
		} else {
			return false;
		}
	}

	public function Unsuspend() {
		if ($response = $this->api('enable')) {
			if ($response->response_code == 200) {
				return true;
			} else {
				$this->addError($response->response_text);
				return false;
			}
		} else {
			return false;
		}
	}

	public function Terminate() {
		if ($response = $this->api('delete')) {
			if ($response->response_code == 200) {
				return true;
			} else {
				$this->addError($response->response_text);
				return false;
			}
		} else {
			return false;
		}
	}

	public function Expire() {
		if ($response = $this->api('let_expire')) {
			if ($response->response_code == 200) {
				$this->addInfo($response->response_text);
				return true;
			} else {
				$this->addError($response->response_text);
				return false;
			}
		} else {
			return false;
		}
	}

	public function Unexpire() {
		if ($response = $this->api('enable')) {
			if ($response->response_code == 200) {
				$this->addInfo($response->response_text);
				return true;
			} else {
				$this->addError($response->response_text);
				return false;
			}
		} else {
			return false;
		}
	}

	/** Integration Features **/
	protected $clientCommands = array('LoginInfo','OSRSPLoginTo');

	public function testConnection() {
		return $this->is_connected();
	}

	public function getCustomTemplate($method) {
		if (in_array($method,array('LoginInfo','OSRSPLoginTo'))) {
			return MAINDIR.'includes'.DS.'modules'.DS.'Hosting'.DS.'opensrs_publishing'.DS.'template.tpl';
		}
		return '';
	}

	public function LoginInfo() {}

	public function getLoginInfo() {
		return array(
			'hostname' => sprintf('https://%s.domainadmin.com/login',$this->api_username),
			'username' => $this->details['option1']['value'],
			'password' => $this->details['option2']['value']
		);
	}

	public function OSRSPLoginTo() {}

	public function getOSRSPLoginTo() {
		return array(
			'hostname' => sprintf('https://%s.domainadmin.com',$this->api_username),
			'username' => $this->details['option1']['value'],
			'password' => $this->details['option2']['value']
		);
	}

	/** Integration Helpers **/

	private function is_connected() {
		if($this->connected == true) return true;

		$attributes = array(
			'sender' => 'OpenSRS SERVER',
			'version' => '2.32',
			'state' => 'ready'
		);

		$api_obj = $this->buildNewAPIObject('version','check',$attributes);

		if ($response = $this->post($api_obj->saveXML())) {
			if ($response->response_code == 401) {
				$this->addError($response->response_text);
				return false;
			} else {
				$this->connected = true;
				return true;
			}
		} else {
			$this->addError("Couldn't Connect To Host.");
			return false;
		}
	}

	public function api($action,$attributes = array()) {
		if (in_array($this->api_service,$this->api_allowed_services)) {
			if (in_array($action,$this->api_allowed_actions[$this->api_service])) {
				$attributes = $this->getAPIAttributesArray($attributes);
				$api_obj = $this->buildNewAPIObject('publishing',$action,$attributes);
				return $this->post($api_obj->saveXML());
			} else {
				$this->addError("Send Error: Unknown Action '$action'");
				return false;
			}
		} else {
			$this->addError("Send Error: Unknown Service '{$this->api_service}'");
			return false;
		}
	}

	private function post($payload) {
		$request = curl_init($this->getAPIEnvironment('url'));
		$headers = $this->getAPIHTTPHeaders($payload);

		curl_setopt_array($request,array(
			CURLOPT_PORT => $this->getAPIEnvironment('port'),
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => $payload,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => false
		));

		$response = curl_exec($request);
		curl_close($request);

		if ($response === false) {
			$this->addError(ucwords(curl_error($request)));
			return false;
		} else {
			$parsed = $this->parseAPIResponse($response);

			$this->debug_email(array($this->getAPIEnvironment(),$payload,$headers,$parsed),'post env/payload/headers/parsed response');

			return $parsed;
		}
	}

	/** XML API Helpers **/

	private function getAPIEnvironment($var = 'all') {
		$env = $this->api_environment;

		if ($var == 'host') return $this->api_environments[$env][0];
		if ($var == 'port') return $this->api_environments[$env][1];
		if ($var == 'url') return 'https://' . $this->api_environments[$env][0];

		return $this->api_environments[$env];
	}

	private function getAPIAttributesArray($attributes = array()) {
		$base_attributes = array(
			'service_type' => $this->api_service,
			'domain' => $this->details['option3']['value']
		);

		if (is_array($attributes)) $attributes = array_merge($base_attributes,$attributes);

		return $attributes;
	}

	private function getAPIHTTPHeaders($payload) {
		return array(
			'X-Username: ' . $this->api_username,
			'X-Signature: ' . md5(md5($payload . $this->api_private_key) . $this->api_private_key),
			'Content-Length: ' . strlen($payload),
			'Content-Type: ' . 'text/xml'
		);
	}

	/**
	* Builds XML Request as DOMDocument Object
	* @return DOMDocument
	*/
	private function buildNewAPIObject($object,$action,$attributes,$protocol = 'XCP') {
		$api = $this->getBaseAPIObject();

		$data = array(
			'protocol' => $protocol,
			'object' => strtoupper($object),
			'action' => strtoupper($action),
			'attributes' => $attributes
		);

		if (!empty($data)) {
			foreach ($api->getElementsByTagName('data_block') as $data_block) {
				if ($temp_child = $this->convertArrayToElement($data)) $data_block->appendChild($temp_child);
			}
		}

		return $api;
	}

	/**
	* Parses XML Response into Object
	*/
	private function parseAPIResponse($response) {
		$sxml = simplexml_load_string($response);
		$response = new stdClass();

		foreach ($sxml->body->data_block->dt_assoc->item as $item) {
			$k = (string) $item->attributes()->key;
			$v = (string) $item;
			$response->{$k} = $v;
		}

		$response->response_code = (int) $response->response_code;

		return $response;
	}

	/**
	* Gets Base DOMDocument Object
	* @return DOMDocument
	*/
	private function getBaseAPIObject() {
		$this->buildBaseAPIObject();
		return $this->base_api_obj;
	}

	/**
	* Builds Base DOMDocument Object
	*/
	private function buildBaseAPIObject() {
		$dom = new DOMImplementation();
		$dtd = $dom->createDocumentType('OPS_envelope','','ops.dtd');
		$doc = $dom->createDocument('','',$dtd);
		$doc->encoding = 'UTF-8';
		$doc->standalone = false;
		$doc->formatOutput = true;

		$envelope = $doc->createElement('OPS_envelope');

		$header = $doc->createElement('header');
		$header->appendChild($doc->createElement('version','0.9'));
		$envelope->appendChild($header);

		$body = $doc->createElement('body');

		$body->appendChild($doc->createElement('data_block'));

		$envelope->appendChild($body);

		$doc->appendChild($envelope);

		$this->base_api_obj = $doc;
	}

	private function convertArrayToElement($data) {
		if (!empty($data) && is_array($data)) {
			$temp_master_child = $this->base_api_obj->createElement('dt_assoc');

			foreach ($data as $k => $v) {
				if (!is_array($v)) {
					$temp_child = $this->base_api_obj->createElement('item',$v);
					$temp_child->setAttribute('key',$k);
				} else {
					$temp_child = $this->base_api_obj->createElement('item');
					$temp_child->setAttribute('key',$k);

					if ($temp_child_data = $this->convertArrayToElement($v)) $temp_child->appendChild($temp_child_data);
				}

				if (isset($temp_child)) $temp_master_child->appendChild($temp_child);
			}

			return $temp_master_child;
		}

		return false;
	}

	/**
	* Debug Helpers
	*/

	private function debug_email($data,$data_pretty = 'Not Set') {
		if ($this->api_environment == 'testing' && !empty($this->debug_email)) {
			@mail($this->debug_email,"opensrs_publishing debug: $data_pretty",print_r($data,true));
		}
	}
}