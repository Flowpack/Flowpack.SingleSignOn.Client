<?php
namespace TYPO3\SingleSignOn\Client\Tests\Unit\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use \Mockery as m;

/**
 * Unit test for SingleSignOnManager
 */
class SingleSignOnManagerTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function logoutCallsDestroySessionForSsoServersFromTokens() {
		$manager = new \TYPO3\SingleSignOn\Client\Service\SingleSignOnManager();

		$mockSsoServerFactory = m::mock('TYPO3\SingleSignOn\Client\Domain\Factory\SsoServerFactory');
		$this->inject($manager, 'ssoServerFactory', $mockSsoServerFactory);
		$mockConfigurationManager = m::mock('TYPO3\Flow\Configuration\ConfigurationManager');
		$this->inject($manager, 'configurationManager', $mockConfigurationManager);
		$mockSecurityContext = m::mock('TYPO3\Flow\Security\Context');
		$this->inject($manager, 'securityContext', $mockSecurityContext);

		$mockConfigurationManager
			->shouldReceive('getConfiguration')
			->with(\TYPO3\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.Flow')
			->andReturn(array(
				'security' => array(
					'authentication' => array(
						'providers' => array(
							'SsoTestProvider' => array(
								'providerOptions' => array(
									'server' => 'TestServer'
								)
							)
						)
					)
				)
			));
		$mockSecurityContext
			->shouldReceive('getAuthenticationTokensOfType')
			->with('TYPO3\SingleSignOn\Client\Security\SingleSignOnToken')
			->andReturn(array(
				m::mock('TYPO3\SingleSignOn\Client\Security\SingleSignOnToken', array(
					'getAuthenticationProviderName' => 'SsoTestProvider',
					'getGlobalSessionId' => 'test-session-id'
				)
			)));
		$mockSsoServer = m::mock('TYPO3\SingleSignOn\Client\Domain\Model\SsoServer');
		$mockSsoServerFactory->shouldReceive('create')->with('TestServer')->andReturn($mockSsoServer);

		$mockSsoServer->shouldReceive('destroySession')->with('test-session-id')->once();

		$manager->logout();

			// PHPUnit does not notice any assertion with Mockery
		$this->assertTrue(TRUE);
	}

	/**
	 * Check for Mockery expectations
	 */
	public function tearDown() {
		m::close();
	}

}
?>