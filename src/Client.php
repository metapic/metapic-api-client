<?php
/**
 * Created by PhpStorm.
 * User: marcusdalgren
 * Date: 2017-10-25
 * Time: 09:57
 */

namespace MetaPic;


class Client {
	private $curl = null;
	private $base_uri = "";
	private $timeout = 10;
	private $connect_timeout = 5;
	private $form_params = [];
	private $query = [];


	public function __construct(array $options = []) {
		$this->setOptions($options);
	}

	public function request($method, $uri = '', array $options = []) {
		$this->initRequest($method, $uri, $options);
		$result = curl_exec($this->curl);
		if ($result === false) {
			throw new ApiException("Curl error");
		}
		return new Response($result);
	}

	private function initRequest($method, $uri = '', array $options = []) {
		$method = strtoupper($method);
		$this->setOptions($options);
		$this->curl = curl_init();
		$uri = $this->base_uri.$uri;

		if (count($this->query) > 0) {
			$uri .= "?" . http_build_query($this->query);
		}
		if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($this->form_params));
		}
		curl_setopt($this->curl, CURLOPT_URL, $uri);
		curl_setopt($this->curl, CURLOPT_HEADER, false);
		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
	}

	private function setOptions(array $options) {
		foreach ($options as $key => $option) {
			if ($key == "base_uri") {
				$this->base_uri = rtrim($option, "/")."/";
			}
			else if (property_exists($this, $key)) {
				$this->$key = $option;
			}
		}
	}
}