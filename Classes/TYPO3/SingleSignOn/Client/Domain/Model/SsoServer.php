<?php
namespace TYPO3\SingleSignOn\Client\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use \TYPO3\Flow\Http\Uri;
use \TYPO3\SingleSignOn\Client\Exception;

/**
 * SSO server
 *
 * Will be configured using settings.
 */
class SsoServer {

	/**
	 * The public key
	 * @var string
	 */
	protected $publicKey;

	/**
	 * The SSO server endpoint URI
	 * @var string
	 */
	protected $endpointUri;

	/**
	 * The SSO server service base URI
	 * @var string
	 */
	protected $serviceBaseUri;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface
	 */
	protected $rsaWalletService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Http\Client\CurlEngine
	 */
	protected $requestEngine;

	/**
	 * Build a URI to redirect to the server authentication endpoint
	 *
	 * @param \TYPO3\SingleSignOn\Client\Domain\Model\SsoClient $ssoClient The SSO client that wants to authenticate against the server
	 * @param string $callbackUri A URI where the server should redirect back after successful authentication on the server
	 * @return string The URI for the redirect
	 */
	public function buildAuthenticationEndpointUri(SsoClient $ssoClient, $callbackUri) {
		$uri = new Uri($this->endpointUri);
		$arguments = array(
			'callbackUri' => (string)$callbackUri,
			'ssoClientIdentifier' => $ssoClient->getIdentifier()
		);
		ksort($arguments);
		$uri->setQuery(http_build_query($arguments));

		$signature = $this->rsaWalletService->sign((string)$uri, $ssoClient->getKeyPairUuid());
		$arguments['signature'] = base64_encode($signature);
		$uri->setQuery(http_build_query($arguments));

		return (string)$uri;
	}

	/**
	 * Verify the signature of a callback redirect to the client
	 *
	 * @param string $accessTokenCipher
	 * @param string $signature
	 * @return boolean
	 */
	public function verifyCallbackSignature($accessTokenCipher, $signature) {
		return $this->rsaWalletService->verifySignature($accessTokenCipher, $signature, $this->publicKey);
	}

	/**
	 * @param \TYPO3\SingleSignOn\Client\Domain\Model\SsoClient $ssoClient
	 * @param string $accessToken
	 * @return array
	 */
	public function redeemAccessToken(\TYPO3\SingleSignOn\Client\Domain\Model\SsoClient $ssoClient, $accessToken) {
		$serviceUri = $this->serviceBaseUri . '/redeem/' . urlencode($accessToken);
		$request = \TYPO3\Flow\Http\Request::create(new Uri($serviceUri), 'DELETE');
		$request->setHeader('Accept', 'application/json');

		$response = $this->requestEngine->sendRequest($request);
		if ($response->getStatusCode() !== 200) {
			throw new Exception('Unexpected status code for redeem access token when calling "' . (string)$serviceUri . '": "' . $response->getStatus() . '"', 1352754575);
		}

		if ($response->getHeader('Content-Type') !== 'application/json') {
			throw new Exception('Unexpected content type for redeem access token when calling "' . (string)$serviceUri . '": "' . $response->getHeader('Content-Type') . '", expected "application/json"', 1352994795);
		}

		$authenticationData = json_decode($response, TRUE);
		if ($authenticationData === NULL) {
			throw new Exception('Could not decode JSON response: Error ' . json_last_error(), 1352994936);
		}

		// TODO Validate content of authentication data

		return $authenticationData;
	}

	/**
	 * Get the Sso server's public key
	 *
	 * @return string The Sso server's public key
	 */
	public function getPublicKey() {
		return $this->publicKey;
	}

	/**
	 * Sets this Sso server's public key
	 *
	 * @param string $publicKey The Sso server's public key
	 * @return void
	 */
	public function setPublicKey($publicKey) {
		$this->publicKey = $publicKey;
	}

	/**
	 * Get the Sso server's endpoint URI
	 *
	 * @return string The Sso server's endpoint URI
	 */
	public function getEndpointUri() {
		return $this->endpointUri;
	}

	/**
	 * Sets this Sso server's endpoint URI
	 *
	 * @param string $endpointUri The Sso server's endpoint URI
	 * @return void
	 */
	public function setEndpointUri($endpointUri) {
		$this->endpointUri = $endpointUri;
	}

	/**
	 * Get the Sso server's service base URI
	 *
	 * @return string The Sso server's service base URI
	 */
	public function getServiceBaseUri() {
		return $this->serviceBaseUri;
	}

	/**
	 * Sets this Sso server's service base URI
	 *
	 * @param string $serviceBaseUri The Sso server's service base URI
	 * @return void
	 */
	public function setServiceBaseUri($serviceBaseUri) {
		$this->serviceBaseUri = $serviceBaseUri;
	}

}
?>