<?php
namespace Flowpack\SingleSignOn\Client\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Flowpack\SingleSignOn\Client\Exception;


/**
 * @Flow\Scope("singleton")
 */
class SingleSignOnManager {

	/**
	 * @Flow\Inject
	 * @var \Flowpack\SingleSignOn\Client\Domain\Factory\SsoServerFactory
	 */
	protected $ssoServerFactory;

	/**
	 * @Flow\Inject
	 * @var \Flowpack\SingleSignOn\Client\Domain\Factory\SsoClientFactory
	 */
	protected $ssoClientFactory;

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
		$tokens = $this->securityContext->getAuthenticationTokensOfType('Flowpack\SingleSignOn\Client\Security\SingleSignOnToken');
		foreach ($tokens as $token) {
			$providerName = $token->getAuthenticationProviderName();
			$serverIdentifier = \TYPO3\Flow\Utility\Arrays::getValueByPath($allConfiguration, 'security.authentication.providers.' . $providerName . '.providerOptions.server');
			if ($serverIdentifier !== NULL) {
				$ssoClient = $this->ssoClientFactory->create();
				$ssoServer = $this->ssoServerFactory->create($serverIdentifier);
				$ssoServer->destroySession($ssoClient, $token->getGlobalSessionId());
			}
		}
	}

}
?>