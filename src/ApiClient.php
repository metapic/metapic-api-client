<?php

namespace MetaPic;

/**
 * @method array|string getUsers(array $userData = [])
 * @method array|string getRevenueTiers(array $revenueData = [])
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
		$this->apiClient = new Client(['base_uri' => $this->baseUrl, 'timeout' => 10, 'connect_timeout' => 5]);
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
		$requestData = $this->setupRequestData($resourceData);
		$response = $this->sendRequest('get', $resourceName, $requestData);
		return $response;
	}

	private function getResource($resourceName, $resourceId, array $resourceData = []) {
		$requestData = $this->setupRequestData($resourceData);
		$response = $this->sendRequest('get', $resourceName . "/" . $resourceId, $requestData);
		return $response;
	}

	private function createResource($resourceName, array $resourceData = []) {
		$requestData = $this->setupRequestData($resourceData);
		$response = $this->sendRequest("post", $resourceName, $requestData);
		return $response;
	}

	private function updateResource($resourceName, $resourceId, array $resourceData = []) {
		$requestData = $this->setupRequestData($resourceData);
		$response = $this->sendRequest('put', $resourceName . "/" . $resourceId, $requestData);
		return $response;
	}

	private function deleteResource($resourceName, $resourceId, array $resourceData = []) {
		$requestData = $this->setupRequestData($resourceData);
		$response = $this->sendRequest('delete', $resourceName . "/" . $resourceId, $requestData);
		return $response;
	}

	public function getUserAccessToken($userId) {
		$response = $this->sendRequest("get", "users/".$userId."/access-token", $this->setupRequestData());
		return $response;
	}

	public function deepLinkBlogPost($userId, $blogPost, $userToken = null) {
        $data = ["blogPost" => $blogPost];
	    if ($userToken != null)
		    $data["user_access_token"] = $userToken;

		$requestData = $this->setupRequestData($data);
		$response = $this->sendRequest("post", "deepLinkBlogPost/".$userId, $requestData);
        return $response;
    }

	public function createDeepLinks($userId, array $links) {
		$data = ["userId" => $userId, "links" => json_encode($links)];

		$requestData = $this->setupRequestData($data);
		$response = $this->sendRequest("post", "deep-links", $requestData);
		return $response;
	}

	public function getIframeToken($userId) {
		$response = $this->sendRequest("get", "users/".$userId."/iframe-token", $this->setupRequestData());
		return $response;
	}

	public function getUserConfig($userId) {
		$response = $this->sendRequest("get", "users/".$userId."/user-config", $this->setupRequestData());
		return $response;
	}

	public function login($email, $password) {
		$user = $this->sendRequest("post", "users/login", [
			"email" => $email,
			"password" => $password
		]);
		return (isset($user["id"])) ? $user : null;
	}

	public function register($email, $password, $clientId = 591571223752267, $username = "") {//default to metapic SE
		$user = $this->sendRequest("post", "users/register", [
			"email" => $email,
			"password" => $password,
			"client_id" => $clientId,
			"username" => $username
		]);
		return (isset($user["id"])) ? $user : null;
	}

	public function checkClient($clientKey) {
		$requestData = $this->setupRequestData(["mtpc_client" => $clientKey]);
		return $this->sendRequest("post", "clients/check", $requestData);
	}

	public function getUserByEmail($email) {
		$users = $this->getResources("users", ["email" => $email]);
		return count($users) == 1 ? $users[0] : null;
	}

	public function activateUser($email) {
		$requestData = $this->setupRequestData(["email" => $email]);
		return $this->sendRequest("post", "users/activate", $requestData);
	}

	public function generateIframeCode($token) {
		$requestData = $this->setupRequestData(["access_token" => $token]);
		return $this->sendRequest("post", "/generateIframeRandomCode", $requestData);
	}

	public function getPopularTags($userId) {
		return $this->sendRequest("get", "tags/popular/".$userId, $this->setupRequestData());
	}

	public function getPaymentInvoice($id=null) {
		$url= "/client/".$this->clientId."/paymentsInvoice";
		if($id !== null) {
			$url.="/".$id;
		}
		return $this->sendRequest("get", $url,  $this->setupRequestData());
	}

	public function getPaymentInvoiceLastMonth() {
		return $this->sendRequest("get", "/client/".$this->clientId."/paymentsInvoice/lastMonth",  $this->setupRequestData());
	}
	public function getPaymentInvoiceThisMonth() {
		return $this->sendRequest("get", "/client/".$this->clientId."/paymentsInvoice/thisMonth",  $this->setupRequestData());
	}
	

	public function getClientClicksByDate($userId = null, array $resourceData = []) {
		$url = "clicks/by-date";
		if (is_numeric($userId)) $url .= "/".$userId;
		$requestData = $this->setupRequestData($resourceData);
		return $this->sendRequest("get", $url, $requestData);
	}
	public function getEarningBetween($userId = null, array $resourceData = []) {
		$url = "getEarningBetween";
		if (is_numeric($userId)) $url .= "/".$userId;
		$requestData = $this->setupRequestData($resourceData);
		return $this->sendRequest("get", $url, $requestData);
	}



	public function getHello(array $args = []) {
		$requestData = $this->setupRequestData($args);
		return $this->sendRequest("get", "/hello", $requestData);
	}


	/**
	 * @param $requestType
	 * @param $url
	 * @param $data
	 * @return array|\Guzzle\Http\Message\Response
	 */
	protected function sendRequest($requestType, $url, $data) {
		try {
			$type = strtoupper($requestType);
			$dataKey = (in_array($type, ["GET", "DELETE"])) ? "query" : "form_params";

	
			
			$response = $this->apiClient->request($type, $url, [$dataKey => $data]);
		}
		catch (ApiException $e) {
			return $e;
		}
		$this->lastResponse = $response;
		return json_decode($response->getBody(), true);
	}

	/**
	 * @param array $arguments
	 * @return array
	 */
	protected  function setupRequestData(array $arguments = []) {
		$arguments["client_id"] = $this->clientId;
		$timeStamp = date("Y-m-d H:i:s");
		$authKey = $this->getAuthToken($arguments, $this->secretKey, $timeStamp);
		$authData = ["access_token" => $authKey, "client_id" => $this->clientId, "mtpc_timestamp" => $timeStamp];
		return array_merge($arguments, $authData);
	}

	public function __call($method, $args) {
		$arr = preg_split('/(?=[A-Z])/', $method);
		$methodType = array_shift($arr);
		$resourceName = strtolower(implode("-", $arr));
		$lastLetter = substr($resourceName, -1);
		$methodName = strtolower($methodType) . "Resource";
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
