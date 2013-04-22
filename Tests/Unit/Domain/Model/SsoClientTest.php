<?php
namespace Flowpack\SingleSignOn\Client\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client".*
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
		$ssoClient = new \Flowpack\SingleSignOn\Client\Domain\Model\SsoClient();
		$ssoClient->setPublicKeyFingerprint('key-pair-uuid');

		$mockRsaWalletService = m::mock('TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface');
		$this->inject($ssoClient, 'rsaWalletService', $mockRsaWalletService);

		$mockRsaWalletService->shouldReceive('decrypt')->with('access-token-cipher', 'key-pair-uuid')->andReturn('access-token');

		$result = $ssoClient->decryptCallbackAccessToken('access-token-cipher');
		$this->assertEquals('access-token', $result);
	}

}
?>