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
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface
	 */
	protected $rsaWalletService;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->ssoClientIdentifier = $settings['ssoClientIdentifier'];
		$this->ssoClientKeyPairUuid = $settings['ssoClientKeyPairUuid'];
		$this->ssoServerEndpointUrl = $settings['ssoServerEndpointUrl'];
	}

	/**
	 * @param string $callbackUrl
	 * @return string
	 */
	public function buildLoginRedirectUrl($callbackUrl) {
		$url = new \TYPO3\Flow\Http\Uri($this->ssoServerEndpointUrl);
		$arguments = array(
			'callbackUrl' => (string)$callbackUrl,
			'ssoClientIdentifier' => $this->ssoClientIdentifier
		);
		ksort($arguments);
		$url->setQuery(http_build_query($arguments));

		$signature = $this->rsaWalletService->sign((string)$url, $this->ssoClientKeyPairUuid);
		$arguments['signature'] = $signature;
		$url->setQuery(http_build_query($arguments));

		return (string)$url;
	}

}
?>