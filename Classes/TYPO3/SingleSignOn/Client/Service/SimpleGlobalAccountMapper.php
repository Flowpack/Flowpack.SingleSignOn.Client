<?php
namespace TYPO3\SingleSignOn\Client\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\SingleSignOn\Client\Domain\Model\SsoClient;
use TYPO3\SingleSignOn\Client\Exception;


/**
 * Maps global accounts to local accounts by mapping all properties and nested objects
 *
 * @Flow\Scope("singleton")
 */
class SimpleGlobalAccountMapper implements GlobalAccountMapperInterface {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Property\PropertyMapper
	 */
	protected $propertyMapper;

	/**
	 * @var array
	 */
	protected $typeMapping = array();

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		if (isset($settings['accountMapper']['typeMapping'])) {
			$this->typeMapping = $settings['accountMapper']['typeMapping'];
		}
	}

	/**
	 * @param \TYPO3\SingleSignOn\Client\Domain\Model\SsoClient $ssoClient
	 * @param array $globalAccountData
	 * @return \TYPO3\Flow\Security\Account
	 */
	public function getAccount(SsoClient $ssoClient, array $globalAccountData) {
		$account = new \TYPO3\Flow\Security\Account();
		$account->setAccountIdentifier($globalAccountData['accountIdentifier']);
		$account->setAuthenticationProviderName('SingleSignOn');
		$account->setRoles($globalAccountData['roles']);

		if (isset($globalAccountData['party'])) {
			$party = $this->mapParty($globalAccountData['party']);
			if ($party !== NULL) {
				$account->setParty($party);
			}
		}

		return $account;
	}

	/**
	 * Map the party from the given source data
	 *
	 * @param array $source
	 * @return \TYPO3\Party\Domain\Model\AbstractParty
	 */
	protected function mapParty(array $source) {
		if (!isset($source['__type'])) {
			throw new Exception('Cannot map party without explicit type (server should return "__type" in party account data):' . json_encode($source), 1354111717);
		}
		$partyType = $source['__type'];
		unset($source['__type']);
		if (isset($this->typeMapping[$partyType])) {
			$partyType = $this->typeMapping[$partyType];
		}
		$configuration = new TrustedPropertyMappingConfiguration();
		return $this->propertyMapper->convert($source, $partyType, $configuration);
	}

	/**
	 * @param array $typeMapping
	 */
	public function setTypeMapping($typeMapping) {
		$this->typeMapping = $typeMapping;
	}

}
?>