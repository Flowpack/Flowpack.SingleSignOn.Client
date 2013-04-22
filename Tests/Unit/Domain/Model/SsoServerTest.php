<?php
namespace Flowpack\SingleSignOn\Client\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use \Mockery as m;

/**
 * Unit test for SsoServer
 */
class SsoServerTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function touchSessionCallsServerSessionRestService() {
		$ssoServer = new \Flowpack\SingleSignOn\Client\Domain\Model\SsoServer();
		$ssoServer->setServiceBaseUri('http://ssodemoserver/test/sso');

		$mockRequestEngine = m::mock('TYPO3\Flow\Http\Client\RequestEngineInterface');
		$this->inject($ssoServer, 'requestEngine', $mockRequestEngine);

		$mockRequestSigner = m::mock('Flowpack\SingleSignOn\Client\Security\RequestSigner');
		$this->inject($ssoServer, 'requestSigner', $mockRequestSigner);
		$mockRequestSigner->shouldReceive('signRequest')->andReturnUsing(function ($request) { return $request; });

		$mockSsoClient = m::mock('Flowpack\SingleSignOn\Client\Domain\Model\SsoClient', array(
			'getPublicKeyFingerprint' => 'ClientPublicKeyFingerprint'
		));

		$mockRequestEngine->shouldReceive('sendRequest')->with(m::on(function($request) use (&$lastRequest) {
			$lastRequest = $request;
			return TRUE;
		}))->once()->andReturn(m::mock('TYPO3\Flow\Http\Response', array(
			'getStatusCode' => 200
		)));

		$ssoServer->touchSession($mockSsoClient, 'test-session-id');

		$this->assertEquals('http://ssodemoserver/test/sso/session/test-session-id/touch', (string)$lastRequest->getUri());
		$this->assertEquals('POST', $lastRequest->getMethod());
	}

	/**
	 * @test
	 */
	public function redeemAccessTokenCallsServerAccessTokenRestService() {
		$ssoServer = new \Flowpack\SingleSignOn\Client\Domain\Model\SsoServer();
		$ssoServer->setServiceBaseUri('http://ssodemoserver/test/sso');

		$mockRequestEngine = m::mock('TYPO3\Flow\Http\Client\RequestEngineInterface');
		$this->inject($ssoServer, 'requestEngine', $mockRequestEngine);

		$mockRequestSigner = m::mock('Flowpack\SingleSignOn\Client\Security\RequestSigner');
		$this->inject($ssoServer, 'requestSigner', $mockRequestSigner);
		$mockRequestSigner->shouldReceive('signRequest')->andReturnUsing(function ($request) { return $request; });

		$mockSsoClient = m::mock('Flowpack\SingleSignOn\Client\Domain\Model\SsoClient', array(
			'getPublicKeyFingerprint' => 'ClientPublicKeyFingerprint'
		));

		$mockResponse = m::mock('TYPO3\Flow\Http\Response', array(
			'getStatusCode' => 201,
			'getContent' => '{}'
		));
		$mockResponse->shouldReceive('getHeader')->with('Content-Type')->andReturn('application/json');
		$mockRequestEngine->shouldReceive('sendRequest')->with(m::on(function($request) use (&$lastRequest) {
			$lastRequest = $request;
			return TRUE;
		}))->once()->andReturn($mockResponse);


		$ssoServer->redeemAccessToken($mockSsoClient, 'test-access-token');

		$this->assertEquals('http://ssodemoserver/test/sso/token/test-access-token/redeem', (string)$lastRequest->getUri());
		$this->assertEquals('POST', $lastRequest->getMethod());
	}

	/**
	 * @test
	 */
	public function destroySessionCallsServerSessionRestService() {
		$ssoServer = new \Flowpack\SingleSignOn\Client\Domain\Model\SsoServer();
		$ssoServer->setServiceBaseUri('http://ssodemoserver/test/sso');

		$mockRequestEngine = m::mock('TYPO3\Flow\Http\Client\RequestEngineInterface');
		$this->inject($ssoServer, 'requestEngine', $mockRequestEngine);

		$mockRequestSigner = m::mock('Flowpack\SingleSignOn\Client\Security\RequestSigner');
		$this->inject($ssoServer, 'requestSigner', $mockRequestSigner);
		$mockRequestSigner->shouldReceive('signRequest')->andReturnUsing(function ($request) { return $request; });

		$mockRequestEngine->shouldReceive('sendRequest')->with(m::on(function($request) use (&$lastRequest) {
			$lastRequest = $request;
			return TRUE;
		}))->once()->andReturn(m::mock('TYPO3\Flow\Http\Response', array(
			'getStatusCode' => 200
		)));

		$mockSsoClient = m::mock('Flowpack\SingleSignOn\Client\Domain\Model\SsoClient', array(
			'getPublicKeyFingerprint' => 'ClientPublicKeyFingerprint',
			'getServiceBaseUri' => 'http://ssodemoclient/sso'
		));

		$ssoServer->destroySession($mockSsoClient, 'test-session-id');

		$this->assertStringStartsWith('http://ssodemoserver/test/sso/session/test-session-id/destroy', (string)$lastRequest->getUri());
		$this->assertEquals('DELETE', $lastRequest->getMethod());
	}

	/**
	 * Check for Mockery expectations
	 */
	public function tearDown() {
		m::close();
	}

}
?>