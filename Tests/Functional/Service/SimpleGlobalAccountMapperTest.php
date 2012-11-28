<?php
namespace TYPO3\SingleSignOn\Client\Tests\Functional\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use \Mockery as m;

/**
 * Functional test for SimpleGlobalAccountMapper
 */
class SimpleGlobalAccountMapperTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @test
	 */
	public function getAccountMapsPartyToExistingClass() {
		$ssoClient = new \TYPO3\SingleSignOn\Client\Domain\Model\SsoClient();
		$accountData = array(
			'accountIdentifier' => 'jdoe',
			'roles' => array('Administrator'),
			'party' => array(
				'__type' => 'TYPO3\Party\Domain\Model\Person',
				'name' => array(
					'firstName' => 'John',
					'lastName' => 'Doe'
				)
			)
		);

		/** @var \TYPO3\SingleSignOn\Client\Service\GlobalAccountMapperInterface $accountMapper */
		$accountMapper = $this->objectManager->get('TYPO3\SingleSignOn\Client\Service\SimpleGlobalAccountMapper');

		$account = $accountMapper->getAccount($ssoClient, $accountData);

		$this->assertInstanceOf('TYPO3\Party\Domain\Model\Person', $account->getParty());
	}

	/**
	 * @test
	 */
	public function getAccountUsesTypeMappingForParty() {
		$ssoClient = new \TYPO3\SingleSignOn\Client\Domain\Model\SsoClient();
		$accountData = array(
			'accountIdentifier' => 'jdoe',
			'roles' => array('Administrator'),
			'party' => array(
				'__type' => 'Legacy\Person',
				'name' => array(
					'firstName' => 'John',
					'lastName' => 'Doe'
				)
			)
		);

		/** @var \TYPO3\SingleSignOn\Client\Service\SimpleGlobalAccountMapper $accountMapper */
		$accountMapper = $this->objectManager->get('TYPO3\SingleSignOn\Client\Service\SimpleGlobalAccountMapper');
		$accountMapper->setTypeMapping(array(
			'Legacy\Person' => 'TYPO3\Party\Domain\Model\Person'
		));

		$account = $accountMapper->getAccount($ssoClient, $accountData);

		$this->assertInstanceOf('TYPO3\Party\Domain\Model\Person', $account->getParty());
	}

}
?>