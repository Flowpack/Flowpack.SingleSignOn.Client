<?php
namespace TYPO3\SingleSignOn\Client\Domain\Service;

/*                                                                            *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client"  *
 *                                                                            *
 *                                                                            */

use TYPO3\Flow\Annotations as Flow;

/**
 * URI service for building single sign-on URIs for the client
 *
 * @Flow\Scope("singleton")
 */
class UriService {

	/**
	 * @var string
	 */
	protected $ssoClientIdentifier;

	/**
	 * @var string
	 */
	protected $ssoClientKeyPairUuid;

	/**
	 * @var string
	 */
	protected $ssoServerEndpointUri;

	/**
	 * @var string
	 */
	protected $ssoServerPublicKeyUuid;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface
	 */
	protected $rsaWalletService;

	/**
	 * Prepare settings
	 *
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		if (isset($settings['ssoClientIdentifier'])) {
			$this->ssoClientIdentifier = $settings['ssoClientIdentifier'];
		}
		if (isset($settings['ssoClientKeyPairUuid'])) {
			$this->ssoClientKeyPairUuid = $settings['ssoClientKeyPairUuid'];
		}
		if (isset($settings['ssoServerEndpointUri'])) {
			$this->ssoServerEndpointUri = $settings['ssoServerEndpointUri'];
		}
		if (isset($settings['ssoServerPublicKeyUuid'])) {
			$this->ssoServerPublicKeyUuid = $settings['ssoServerPublicKeyUuid'];
		}
		if ((string)$this->ssoServerEndpointUri === '') {
			throw new \TYPO3\Flow\Configuration\Exception\InvalidConfigurationTypeException('Missing TYPO3.SingleSignOn.Client.ssoServerEndpointUri setting', 1351075101);
		}
		if ((string)$this->ssoClientIdentifier === '') {
			throw new \TYPO3\Flow\Configuration\Exception\InvalidConfigurationTypeException('Missing TYPO3.SingleSignOn.Client.ssoClientIdentifier setting', 1351075078);
		}
		if ((string)$this->ssoClientKeyPairUuid === '') {
			throw new \TYPO3\Flow\Configuration\Exception\InvalidConfigurationTypeException('Missing TYPO3.SingleSignOn.Client.ssoClientKeyPairUuid setting', 1351075159);
		}
	}

	/**
	 *
	 *
	 * @param string $callbackUri
	 * @return string
	 */
	public function buildLoginRedirectUri($callbackUri) {
		$uri = new \TYPO3\Flow\Http\Uri($this->ssoServerEndpointUri);
		$arguments = array(
			'callbackUri' => (string)$callbackUri,
			'ssoClientIdentifier' => $this->ssoClientIdentifier
		);
		ksort($arguments);
		$uri->setQuery(http_build_query($arguments));

		$signature = $this->rsaWalletService->sign((string)$uri, $this->ssoClientKeyPairUuid);
		$arguments['signature'] = base64_encode($signature);
		$uri->setQuery(http_build_query($arguments));

		return (string)$uri;
	}

	/**
	 *
	 *
	 * @param string $accessTokenCipher
	 * @param string $signature
	 * @return boolean
	 */
	public function verifyCallbackSignature($accessTokenCipher, $signature) {
		if ((string)$this->ssoServerPublicKeyUuid === '') {
			throw new \TYPO3\Flow\Configuration\Exception\InvalidConfigurationTypeException('Missing TYPO3.SingleSignOn.Client.ssoServerPublicKeyUuid setting', 1351097365);
		}

		return $this->rsaWalletService->verifySignature($accessTokenCipher, $signature, $this->ssoServerPublicKeyUuid);
	}

	/**
	 * @param string $accessTokenCipher
	 * @return string The decrypted access token
	 */
	public function decryptCallbackAccessToken($accessTokenCipher) {
		return $this->rsaWalletService->decrypt($accessTokenCipher, $this->ssoClientKeyPairUuid);
	}

}
?>