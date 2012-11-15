<?php
namespace TYPO3\SingleSignOn\Client\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\SingleSignOn\Client\Domain\Model\SsoClient;

/**
 * Interface for a mapper service that maps a global account to a local account
 */
interface GlobalAccountMapperInterface {

	/**
	 * @param \TYPO3\SingleSignOn\Client\Domain\Model\SsoClient $ssoClient
	 * @param array $globalAccountData
	 * @return \TYPO3\Flow\Security\Account
	 */
	public function getAccount(SsoClient $ssoClient, array $globalAccountData);

}
?>