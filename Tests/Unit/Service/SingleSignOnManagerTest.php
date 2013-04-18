<?php
namespace Flowpack\SingleSignOn\Client\Tests\Unit\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client".*
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
		$manager = new \Flowpack\SingleSignOn\Client\Service\SingleSignOnManager();

		$mockSsoClientFactory = m::mock('Flowpack\SingleSignOn\Client\Domain\Factory\SsoClientFactory');
		$this->inject($manager, 'ssoClientFactory', $mockSsoClientFactory);
		$mockSsoServerFactory = m::mock('Flowpack\SingleSignOn\Client\Domain\Factory\SsoServerFactory');
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
			->with('Flowpack\SingleSignOn\Client\Security\SingleSignOnToken')
			->andReturn(array(
				m::mock('Flowpack\SingleSignOn\Client\Security\SingleSignOnToken', array(
					'getAuthenticationProviderName' => 'SsoTestProvider',
					'getGlobalSessionId' => 'test-session-id'
				)
			)));
		$mockSsoServer = m::mock('Flowpack\SingleSignOn\Client\Domain\Model\SsoServer');
		$mockSsoServerFactory->shouldReceive('create')->with('TestServer')->andReturn($mockSsoServer);

		$mockSsoClient = m::mock('Flowpack\SingleSignOn\Client\Domain\Model\SsoClient');
		$mockSsoClientFactory->shouldReceive('create')->andReturn($mockSsoClient);

		$mockSsoServer->shouldReceive('destroySession')->with($mockSsoClient, 'test-session-id')->once();

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