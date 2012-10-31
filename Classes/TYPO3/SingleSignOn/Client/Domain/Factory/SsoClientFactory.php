<?php
namespace TYPO3\SingleSignOn\Client\Domain\Factory;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\SingleSignOn\Client\Exception;

/**
 * A SSO client factory
 *
 * @Flow\Scope("singleton")
 */
class SsoClientFactory {

	/**
	 * @var string
	 */
	protected $clientIdentifier;

	/**
	 * @var string
	 */
	protected $clientKeyPairUuid;

	/**
	 * Prepare settings
	 *
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		if (isset($settings['client']['identifier'])) {
			$this->clientIdentifier = $settings['client']['identifier'];
		}
		if (isset($settings['client']['keyPairUuid'])) {
			$this->clientKeyPairUuid = $settings['client']['keyPairUuid'];
		}
	}

	/**
	 * Build a SSO client instance from settings
	 *
	 * Note: Every SSO entry point and authentication provider uses the same SSO client.
	 *
	 * @return \TYPO3\SingleSignOn\Client\Domain\Model\SsoClient
	 */
	public function create() {
		$ssoClient = new \TYPO3\SingleSignOn\Client\Domain\Model\SsoClient();
		if ((string)$this->clientIdentifier === '') {
			throw new Exception('Missing TYPO3.SingleSignOn.Client.client.identifier setting', 1351075078);
		}
		$ssoClient->setIdentifier($this->clientIdentifier);
		if ((string)$this->clientKeyPairUuid === '') {
			throw new Exception('Missing TYPO3.SingleSignOn.Client.client.keyPairUuid setting', 1351075159);
		}
		$ssoClient->setKeyPairUuid($this->clientKeyPairUuid);
		return $ssoClient;
	}

}
?>