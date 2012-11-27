<?php
namespace TYPO3\SingleSignOn\Client\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use \Mockery as m;

/**
 * Unit test for SsoClient
 */
class SsoClientTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function decryptCallbackAccessTokenUsesRsaWalletServiceAndClientKeyPair() {
		$ssoClient = new \TYPO3\SingleSignOn\Client\Domain\Model\SsoClient();
		$ssoClient->setKeyPairUuid('key-pair-uuid');

		$mockRsaWalletService = m::mock('TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface');
		$this->inject($ssoClient, 'rsaWalletService', $mockRsaWalletService);

		$mockRsaWalletService->shouldReceive('decrypt')->with('access-token-cipher', 'key-pair-uuid')->andReturn('access-token');

		$result = $ssoClient->decryptCallbackAccessToken('access-token-cipher');
		$this->assertEquals('access-token', $result);
	}

}
?>