<?php
namespace TYPO3\SingleSignOn\Client\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\SingleSignOn\Client\Domain\Model\SsoClient;

/**
 * Maps global accounts to local accounts by mapping all safe properties (when existing)
 */
class SimpleGlobalAccountMapper implements GlobalAccountMapperInterface {

	/**
	 * @param \TYPO3\SingleSignOn\Client\Domain\Model\SsoClient $ssoClient
	 * @param array $globalAccountData
	 * @return \TYPO3\Flow\Security\Account
	 */
	public function getAccount(SsoClient $ssoClient, array $globalAccountData) {
		$account = new \TYPO3\Flow\Security\Account();
		$account->setAccountIdentifier($globalAccountData['accountIdentifier']);
		$account->setAuthenticationProviderName('SingleSignOn');

		$roleIdentifiers = array_map(function($role) {
			return $role['identifier'];
		}, $globalAccountData['roles']);
		$account->setRoles($roleIdentifiers);
		return $account;
	}

}
?>