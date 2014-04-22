<?php

namespace MetaPic;

use Exception;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Guzzle\Http\Message\Request;
use Guzzle\Service\Client;

/**
 * @method array|string getUsers(array $userData = [])
 * @method array|string getUser(\int $userId)
 * @method array|string createUser(array $userData)
 * @method array|string updateUser(\int $userId, array $userData)
 * @method array|string deleteUser(\int $userId, array $userData = [])
 * @method array|string getImages(array $imageData = [])
 * @method array|string getImage(\int $imageId, array $imageData = [])
 * @method array|string createImage(array $imageData)
 * @method array|string updateImage(\int $imageId, array $imageData)
 * @method array|string deleteImage(\int $imageId, array $imageData = [])
 * @method array|string getTags(array $tagData = [])
 * @method array|string getTag(\int $tagId, array $tagData = [])
 * @method array|string createTag(array $tagData)
 * @method array|string updateTag(\int $tagId, array $tagData)
 * @method array|string deleteTag(\int $tagId, array $tagData = [])
 */
class ApiClient {
	private $baseUrl;
	private $clientId;
	private $secretKey;
	private $apiClient;
	private $lastRequest;

	public function __construct($baseUrl, $clientId, $secretKey) {
		$this->baseUrl = $baseUrl;
		$this->clientId = $clientId;
		$this->secretKey = $secretKey;

		$this->apiClient = new Client($this->baseUrl);
	}

	/**
	 * @return mixed
	 */
	public function getLastRequest() {
		return $this->lastRequest;
	}

	/**
	 * @return string
	 */
	public function getClientId() {
		return $this->clientId;
	}

	/**
	 * @param string $clientId
	 */
	public function setClientId($clientId) {
		$this->clientId = $clientId;
	}

	/**
	 * @return string
	 */
	public function getSecretKey() {
		return $this->secretKey;
	}

	/**
	 * @param string $secretKey
	 */
	public function setSecretKey($secretKey) {
		$this->secretKey = $secretKey;
	}

	private function getResources($resourceName, array $resourceData = []) {
		$request = $this->setupRequest("get", $resourceName, $resourceData);
		$request->getQuery()->merge($resourceData);
		$response = $this->sendRequest($request);
		return $response;
	}

	private function getResource($resourceName, $resourceId, array $resourceData = []) {
		$request = $this->setupRequest("get", $resourceName . "/" . $resourceId, $resourceData);
		$request->getQuery()->merge($resourceData);
		$response = $this->sendRequest($request);
		return $response;
	}

	private function createResource($resourceName, array $resourceData = []) {
		$request = $this->setupRequest("post", $resourceName, $resourceData);
		$request->addPostFields($resourceData);
		$response = $this->sendRequest($request);
		return $response;
	}

	private function updateResource($resourceName, $resourceId, array $resourceData = []) {
		$request = $this->setupRequest("put", $resourceName . "/" . $resourceId, $resourceData);
		$request->addPostFields($resourceData);
		$response = $this->sendRequest($request);
		return $response;
	}

	private function deleteResource($resourceName, $resourceId, array $resourceData = []) {
		$request = $this->setupRequest("delete", $resourceName . "/" . $resourceId, $resourceData);
		$request->getQuery()->merge($resourceData);
		$response = $this->sendRequest($request);
		return $response;
	}

	public function getUserAccessToken($userId) {
		$request = $this->setupRequest("get", "users/".$userId."/access-token");
		$response = $this->sendRequest($request);
		return $response;
	}

	/**
	 * @param Request $request
	 *
	 * @return array|\Guzzle\Http\Message\Response
	 */
	private function sendRequest(Request $request) {
		try {
			$response = $request->send();
			$this->lastRequest = $request;
		}
		catch (BadResponseException $e) {
			$response = $e->getResponse();
		}
		try {
			return $response->json();
		}
		catch (Exception $e) {
			return $response->getBody(true);
		}
	}

	/**
	 * @param string $callMethod
	 * @param string $url
	 * @param array $arguments
	 *
	 * @return Request|EntityEnclosingRequest
	 */
	private function setupRequest($callMethod, $url, array $arguments = []) {
		$request = $this->apiClient->$callMethod("/" . $url);
		$arguments["client_id"] = $this->clientId;
		$arguments["mtpc_timestamp"] = date("Y-m-d H:i:s");
		ksort($arguments);
		$authData = implode('&', array_map(function ($v, $k) {
			return $k . '=' . $v;
		}, $arguments, array_keys($arguments)));
		$authKey = hash_hmac("sha256", $authData, $this->secretKey);

		$request->getQuery()
			->set("access_token", $authKey)
			->set("client_id", $this->clientId)
			->set("mtpc_timestamp", $arguments["mtpc_timestamp"]);
		return $request;
	}

	public function __call($method, $args) {
		$arr = preg_split('/(?=[A-Z])/', $method);
		$lastLetter = substr($arr[1], -1);
		$resourceName = strtolower($arr[1]);
		$methodName = strtolower($arr[0]) . "Resource";
		if ($lastLetter == "s") $resourceName .= "s";
		else $methodName .= "s";
		$argCount = count($args);
		if ($argCount > 1) {
			return $this->$methodName($resourceName, $args[0], $args[1]);
		}
		else if ($argCount > 0) {
			return $this->$methodName($resourceName, $args[0]);
		}
		else {
			return $this->$methodName($resourceName);
		}
	}
}