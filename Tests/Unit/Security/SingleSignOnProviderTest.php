<?php
namespace TYPO3\SingleSignOn\Client\Tests\Unit\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use \Mockery as m;
use \TYPO3\Flow\Http\Uri;

/**
 *
 */
class SingleSignOnProviderTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function authenticateTagsSessionWithGlobalSessionIdOnSuccessfulAuthentication() {
		$mockAuthenticationToken = m::mock('TYPO3\SingleSignOn\Client\Security\SingleSignOnToken', array(
			'getAuthenticationStatus' => \TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_NEEDED,
			'getCredentials' => array('signature' => 'TestSignature', 'accessToken' => 'EncryptedAccessToken'),
			'setGlobalSessionId' => NULL,
			'setAccount' => NULL,
			'setAuthenticationStatus' => NULL
		));

		/** @var \TYPO3\SingleSignOn\Client\Security\SingleSignOnProvider $provider */
		$provider = $this->getAccessibleMock('TYPO3\SingleSignOn\Client\Security\SingleSignOnProvider', array('dummy'), array('SingleSignOn', array('server' => 'TestServer')));

		$mockSsoServer = m::mock('TYPO3\SingleSignOn\Client\Domain\Model\SsoServer', array(
			'verifyCallbackSignature' => TRUE,
			'redeemAccessToken' => array(
				'account' => array('accountIdentifier' => 'TestAccount'),
				'sessionId' => 'GlobalSessionId'
			)
		));
		$mockSsoServerFactory = m::mock('TYPO3\SingleSignOn\Client\Domain\Factory\SsoServerFactory');
		$mockSsoServerFactory->shouldReceive('create')->with('TestServer')->andReturn($mockSsoServer);
		$this->inject($provider, 'ssoServerFactory', $mockSsoServerFactory);

		$mockSsoClient = m::mock('TYPO3\SingleSignOn\Client\Domain\Model\SsoClient', array(
			'decryptCallbackAccessToken' => 'DecryptedAccessToken'
		));
		$mockSsoClientFactory = m::mock('TYPO3\SingleSignOn\Client\Domain\Factory\SsoClientFactory');
		$mockSsoClientFactory->shouldReceive('create')->andReturn($mockSsoClient);
		$this->inject($provider, 'ssoClientFactory', $mockSsoClientFactory);

		$mockSession = m::mock('TYPO3\Flow\Session\SessionInterface');
		$this->inject($provider, 'session', $mockSession);

		$mockAccount = m::mock('TYPO3\Flow\Security\Account');
		$mockGlobalAccountMapper = m::mock('TYPO3\SingleSignOn\Client\Service\GlobalAccountMapperInterface', array(
			'getAccount' => $mockAccount
		));
		$this->inject($provider, 'globalAccountMapper', $mockGlobalAccountMapper);

		$mockSession->shouldReceive('addTag')->with('TYPO3_SingleSignOn_Client-GlobalSessionId')->once();

		$provider->authenticate($mockAuthenticationToken);

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