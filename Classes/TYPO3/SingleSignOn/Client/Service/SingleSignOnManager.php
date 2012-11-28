<?php
namespace TYPO3\SingleSignOn\Client\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\SingleSignOn\Client\Exception;


/**
 * @Flow\Scope("singleton")
 */
class SingleSignOnManager {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Client\Domain\Factory\SsoServerFactory
	 */
	protected $ssoServerFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * Notify SSO servers about the logged out client
	 *
	 * All active authentication tokens of type SingleSignOnToken will be
	 * used to get the registered global session id and send a request
	 * to the session service on the SSO server.
	 *
	 * @return void
	 */
	public function logout() {
		$allConfiguration = $this->configurationManager->getConfiguration(\TYPO3\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.Flow');
		$tokens = $this->securityContext->getAuthenticationTokensOfType('TYPO3\SingleSignOn\Client\Security\SingleSignOnToken');
		foreach ($tokens as $token) {
			$providerName = $token->getAuthenticationProviderName();
			$serverIdentifier = \TYPO3\Flow\Utility\Arrays::getValueByPath($allConfiguration, 'security.authentication.providers.' . $providerName . '.providerOptions.server');
			if ($serverIdentifier !== NULL) {
				$ssoServer = $this->ssoServerFactory->create($serverIdentifier);
				$ssoServer->destroySession($token->getGlobalSessionId());
			}
		}
	}

}
?>