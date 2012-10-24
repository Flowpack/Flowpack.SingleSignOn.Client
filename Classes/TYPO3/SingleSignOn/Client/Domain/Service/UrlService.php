<?php
namespace TYPO3\SingleSignOn\Client\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * URL service for building single sign-on URLs for the client
 *
 * TODO Move some functionality to domain service
 *
 * @Flow\Scope("singleton")
 */
class UrlService {

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
	protected $ssoServerEndpointUrl;

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
		if (isset($settings['ssoServerEndpointUrl'])) {
			$this->ssoServerEndpointUrl = $settings['ssoServerEndpointUrl'];
		}
		if (isset($settings['ssoServerPublicKeyUuid'])) {
			$this->ssoServerPublicKeyUuid = $settings['ssoServerPublicKeyUuid'];
		}
	}

	/**
	 * @param string $callbackUrl
	 * @return string
	 */
	public function buildLoginRedirectUrl($callbackUrl) {
		if ((string)$this->ssoServerEndpointUrl === '') {
			throw new \TYPO3\Flow\Configuration\Exception\InvalidConfigurationTypeException('Missing TYPO3.SingleSignOn.Client.ssoServerEndpointUrl setting', 1351075101);
		}
		if ((string)$this->ssoClientIdentifier === '') {
			throw new \TYPO3\Flow\Configuration\Exception\InvalidConfigurationTypeException('Missing TYPO3.SingleSignOn.Client.ssoClientIdentifier setting', 1351075078);
		}
		if ((string)$this->ssoClientKeyPairUuid === '') {
			throw new \TYPO3\Flow\Configuration\Exception\InvalidConfigurationTypeException('Missing TYPO3.SingleSignOn.Client.ssoClientKeyPairUuid setting', 1351075159);
		}

		$url = new \TYPO3\Flow\Http\Uri($this->ssoServerEndpointUrl);
		$arguments = array(
			'callbackUrl' => (string)$callbackUrl,
			'ssoClientIdentifier' => $this->ssoClientIdentifier
		);
		ksort($arguments);
		$url->setQuery(http_build_query($arguments));

		$signature = $this->rsaWalletService->sign((string)$url, $this->ssoClientKeyPairUuid);
		$arguments['signature'] = base64_encode($signature);
		$url->setQuery(http_build_query($arguments));

		return (string)$url;
	}

	/**
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