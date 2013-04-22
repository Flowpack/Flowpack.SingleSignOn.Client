<?php
namespace Flowpack\SingleSignOn\Client\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use \TYPO3\Flow\Http\Uri;

use \Flowpack\SingleSignOn\Client\Exception;
use \Flowpack\SingleSignOn\Client\Domain\Model\SsoClient;

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
	 * @Flow\Inject
	 * @var \Flowpack\SingleSignOn\Client\Security\RequestSigner
	 */
	protected $requestSigner;

	/**
	 * Build a URI to redirect to the server authentication endpoint
	 *
	 * @param \Flowpack\SingleSignOn\Client\Domain\Model\SsoClient $ssoClient The SSO client that wants to authenticate against the server
	 * @param string $callbackUri A URI where the server should redirect back after successful authentication on the server
	 * @return string The URI for the redirect
	 */
	public function buildAuthenticationEndpointUri(SsoClient $ssoClient, $callbackUri) {
		$uri = new Uri($this->endpointUri);
		$arguments = array(
			'callbackUri' => (string)$callbackUri,
			'ssoClientIdentifier' => $ssoClient->getServiceBaseUri()
		);
		ksort($arguments);
		$uri->setQuery(http_build_query($arguments));

		$signature = $this->rsaWalletService->sign((string)$uri, $ssoClient->getPublicKeyFingerprint());
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
	 * @param \Flowpack\SingleSignOn\Client\Domain\Model\SsoClient $ssoClient
	 * @param string $accessToken
	 * @return array
	 */
	public function redeemAccessToken(SsoClient $ssoClient, $accessToken) {
		$serviceUri = new Uri($this->serviceBaseUri . '/token/' . urlencode($accessToken) . '/redeem');
		$request = \TYPO3\Flow\Http\Request::create($serviceUri, 'POST');
		$request->setHeader('Accept', 'application/json');
		$request->setContent('');

		$signedRequest = $this->requestSigner->signRequest($request, $ssoClient->getPublicKeyFingerprint(), $ssoClient->getPublicKeyFingerprint());

		$response = $this->requestEngine->sendRequest($signedRequest);
		if ($response->getStatusCode() !== 201) {
			throw new Exception('Unexpected status code for redeem access token when calling "' . (string)$serviceUri . '": "' . $response->getStatus() . '"', 1352754575);
		}

		if ($response->getHeader('Content-Type') !== 'application/json') {
			throw new Exception('Unexpected content type for redeem access token when calling "' . (string)$serviceUri . '": "' . $response->getHeader('Content-Type') . '", expected "application/json"', 1352994795);
		}

		$authenticationData = json_decode($response->getContent(), TRUE);
		if ($authenticationData === NULL) {
			throw new Exception('Could not decode JSON response: Error ' . json_last_error(), 1352994936);
		}

		// TODO Validate content of authentication data

		return $authenticationData;
	}

	/**
	 * Touch a global session
	 *
	 * This method is thought to work asynchronously to not block when the
	 * server is responding slow.
	 *
	 * @param \Flowpack\SingleSignOn\Client\Domain\Model\SsoClient $ssoClient
	 * @param string $sessionId
	 * @return void
	 * @throws \Flowpack\SingleSignOn\Client\Exception\SessionNotFoundException
	 */
	public function touchSession(SsoClient $ssoClient, $sessionId) {
		$serviceUri = $this->serviceBaseUri . '/session/' . urlencode($sessionId) . '/touch';
		$request = \TYPO3\Flow\Http\Request::create(new Uri($serviceUri), 'POST');
		$request->setContent('');

		$signedRequest = $this->requestSigner->signRequest($request, $ssoClient->getPublicKeyFingerprint(), $ssoClient->getPublicKeyFingerprint());

		// TODO Handle timeout and other server errors (client should keep running!)
		$response = $this->requestEngine->sendRequest($signedRequest);
		if ($response->getStatusCode() === 404 && $response->getHeader('Content-Type') === 'application/json') {
			$data = json_decode($response->getContent(), TRUE);
			if (is_array($data) && isset($data['error']) && $data['error'] === 'SessionNotFound') {
				throw new \Flowpack\SingleSignOn\Client\Exception\SessionNotFoundException();
			}
		}
		if ($response->getStatusCode() !== 200) {
			throw new Exception('Unexpected status code for touch session when calling "' . (string)$serviceUri . '": "' . $response->getStatus() . '"', 1354030063);
		}
	}

	/**
	 * Destroy the given global session
	 *
	 * @param \Flowpack\SingleSignOn\Client\Domain\Model\SsoClient $ssoClient
	 * @param $sessionId
	 * @return void
	 */
	public function destroySession(SsoClient $ssoClient, $sessionId) {
		$serviceUri = new Uri($this->serviceBaseUri . '/session/' . urlencode($sessionId) . '/destroy');
		$serviceUri->setQuery(http_build_query(array('clientIdentifier' => $ssoClient->getServiceBaseUri())));
		$request = \TYPO3\Flow\Http\Request::create($serviceUri, 'DELETE');
		$request->setContent('');

		$signedRequest = $this->requestSigner->signRequest($request, $ssoClient->getPublicKeyFingerprint(), $ssoClient->getPublicKeyFingerprint());

		// TODO Send request asynchronously
		$response = $this->requestEngine->sendRequest($signedRequest);
		if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 404) {
			throw new Exception('Unexpected status code for destroy session when calling "' . (string)$serviceUri . '": "' . $response->getStatus() . '"', 1354132939);
		}
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