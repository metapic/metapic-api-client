<?php

namespace MetaPic;

use Exception;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
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
 * @method array|string getClicks(array $clickData = [])
 * @method array|string getClick(\int $userId, array $clickData = [])
*/
class ApiClient {
	private $baseUrl;
	private $clientId;
	private $secretKey;
	private $apiClient;

	/* @var Request $lastRequest */
	private $lastRequest;
	/* @var Response $lastResponse */
	private $lastResponse;

	public function __construct($baseUrl, $clientId = null, $secretKey = null) {
		$this->baseUrl = $baseUrl;
		$this->clientId = $clientId;
		$this->secretKey = $secretKey;
		$this->apiClient = new Client($this->baseUrl);
		$this->apiClient->setDefaultOption('timeout', 10);
		$this->apiClient->setDefaultOption('connect_timeout', 5);
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

	/**
	 * @return Client
	 */
	public function getApiClient() {
		return $this->apiClient;
	}

	/**
	 * @return string
	 */
	public function getBaseUrl() {
		return $this->baseUrl;
	}

	public function getResponseCode() {
		return (is_object($this->lastResponse)) ? $this->lastResponse->getStatusCode( ) : null;
	}

	/**
	 * @param string $baseUrl
	 */
	public function setBaseUrl( $baseUrl ) {
		$this->baseUrl = $baseUrl;
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
    public function deepLinkBlogPost($userId,$blogPost, $userToken = null) {
        $data = ["blogPost" => $blogPost];
	    if ($userToken != null)
		    $data["user_access_token"] = $userToken;

        $request = $this->setupRequest("post", "deepLinkBlogPost/".$userId, $data);
	    $request->addPostFields($data);
        $response = $this->sendRequest($request);
        return $response;
    }

	public function getIframeToken($userId) {
		$request = $this->setupRequest("get", "users/".$userId."/iframe-token");
		$response = $this->sendRequest($request);
		return $response;
	}

	public function getUserConfig($userId) {
		$request = $this->setupRequest("get", "users/".$userId."/user-config");
		$response = $this->sendRequest($request);
		return $response;
	}

	public function login($email, $password) {
		$request = $this->getApiClient()->post("users/login", null, [
			"email" => $email,
			"password" => $password
		]);
		$user = $this->sendRequest($request);
		return (isset($user["id"])) ? $user : null;
	}

	public function register($email, $password) {
		$request = $this->getApiClient()->post("users/register", null, [
			"email" => $email,
			"password" => $password
		]);
		$user = $this->sendRequest($request);
		return (isset($user["id"])) ? $user : null;
	}

	public function checkClient($clientKey) {
		$data = ["mtpc_client" => $clientKey];
		$request = $this->setupRequest("post", "clients/check", $data);
		$request->addPostFields($data);
		return $this->sendRequest($request);
	}

	public function getUserByEmail($email) {
		$users = $this->getResources("users", ["email" => $email]);
		return count($users) == 1 ? $users[0] : null;
	}

	public function activateUser($email) {
		$data = ["email" => $email];
		$request = $this->setupRequest("post", "users/activate", $data);
		$request->addPostFields($data);
		return $this->sendRequest($request);
	}

	public function generateIframeCode($token) {
		$data = ["access_token" => $token];
		$request = $this->setupRequest("post", "/generateIframeRandomCode", $data);
		$request->addPostFields($data);
		$response = $this->sendRequest($request);

	}

	public function getClientClicksByDate($userId = null, array $resourceData = []) {
		$url = "clicks/by-date";
		if (is_numeric($userId)) $url .= "/".$userId;
		$request = $this->setupRequest("get", $url, $resourceData);
		$request->getQuery()->merge($resourceData);
		return $this->sendRequest($request);
	}

	/**
	 * @param RequestInterface $request
	 *
	 * @return array|\Guzzle\Http\Message\Response
	 */
	protected function sendRequest(RequestInterface $request) {
		try {
			$response = $request->send();
			$this->lastRequest = $request;
		}
		catch (BadResponseException $e) {
			$response = $e->getResponse();
		}
		$this->lastResponse = $response;
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
	protected  function setupRequest($callMethod, $url, array $arguments = []) {
		$request = $this->apiClient->$callMethod("/" . $url);
		$arguments["client_id"] = $this->clientId;
		$timeStamp = date("Y-m-d H:i:s");
		$authKey = $this->getAuthToken($arguments, $this->secretKey, $timeStamp);
		$request->getQuery()
			->set("access_token", $authKey)
			->set("client_id", $this->clientId)
			->set("mtpc_timestamp", $timeStamp);
		return $request;
	}

	public function __call($method, $args) {
		$arr = preg_split('/(?=[A-Z])/', $method);
		$lastLetter = substr($arr[1], -1);
		$resourceName = strtolower($arr[1]);
		$methodName = strtolower($arr[0]) . "Resource";
		if ($lastLetter != "s") $resourceName .= "s";
		else $methodName .= "s";
		$argCount = count($args);

		if (!method_exists($this, $methodName)) throw new ApiException("Method not allowed");
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

	/**
	 * @param array $arguments
	 *
	 * @param       $secretKey
	 * @param       $timeStamp
	 *
	 * @return array
	 */
	protected function getAuthToken(array $arguments, $secretKey, $timeStamp) {
		$arguments["mtpc_timestamp"] = $timeStamp;
		ksort($arguments);
		$authData = implode('&', array_map(function ($v, $k) {
			return $k . '=' . $v;
		}, $arguments, array_keys($arguments)));
		$authKey = hash_hmac("sha256", $authData, $secretKey);
		return $authKey;
	}
}