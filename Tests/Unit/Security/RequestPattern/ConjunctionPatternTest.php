<?php
namespace TYPO3\SingleSignOn\Client\Tests\Unit\Security\RequestPattern;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use \Mockery as m;
use \TYPO3\Flow\Http\Uri;

/**
 *
 */
class ConjunctionPatternTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function matchRequestReturnsFalseOnFirstSubpatternFail() {
		$pattern = new \TYPO3\SingleSignOn\Client\Security\RequestPattern\ConjunctionPattern();

		$mockObjectManager = m::mock('TYPO3\Flow\Object\ObjectManagerInterface');
		$this->inject($pattern, 'objectManager', $mockObjectManager);
		$mockRequestPatternResolver = m::mock('TYPO3\Flow\Security\RequestPatternResolver');
		$this->inject($pattern, 'requestPatternResolver', $mockRequestPatternResolver);

		$mockFailingPattern = m::mock('TYPO3\Flow\Security\RequestPatternInterface', array('setPattern' => NULL));
		$mockFailingPattern->shouldReceive('matchRequest')->andReturn(FALSE);
		$mockIgnoredPattern = m::mock('TYPO3\Flow\Security\RequestPatternInterface', array('setPattern' => NULL));
		$mockIgnoredPattern->shouldReceive('matchRequest')->never();

		$mockRequestPatternResolver->shouldReceive('resolveRequestPatternClass')->with('TestPattern1')->andReturn('MyPackage\Security\TestPattern1');
		$mockObjectManager->shouldReceive('get')->with('MyPackage\Security\TestPattern1')->andReturn($mockFailingPattern);
		$mockRequestPatternResolver->shouldReceive('resolveRequestPatternClass')->with('TestPattern2')->andReturn('MyPackage\Security\TestPattern2');
		$mockObjectManager->shouldReceive('get')->with('MyPackage\Security\TestPattern2')->andReturn($mockIgnoredPattern);

		$pattern->setPattern(array(
			'patterns' => array(
				array(
					'patternType' => 'TestPattern1',
					'patternValue' => 'TestValue'
				),
				array(
					'patternType' => 'TestPattern2',
					'patternValue' => 'TestValue'
				)
			)
		));

		$mockActionRequest = m::mock('TYPO3\Flow\Mvc\ActionRequest');

		$result = $pattern->matchRequest($mockActionRequest);
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function matchRequestReturnsTrueIfAllSubpatternsMatch() {
		$pattern = new \TYPO3\SingleSignOn\Client\Security\RequestPattern\ConjunctionPattern();

		$mockObjectManager = m::mock('TYPO3\Flow\Object\ObjectManagerInterface');
		$this->inject($pattern, 'objectManager', $mockObjectManager);
		$mockRequestPatternResolver = m::mock('TYPO3\Flow\Security\RequestPatternResolver');
		$this->inject($pattern, 'requestPatternResolver', $mockRequestPatternResolver);

		$mockFailingPattern = m::mock('TYPO3\Flow\Security\RequestPatternInterface', array('setPattern' => NULL));
		$mockFailingPattern->shouldReceive('matchRequest')->andReturn(TRUE);
		$mockIgnoredPattern = m::mock('TYPO3\Flow\Security\RequestPatternInterface', array('setPattern' => NULL));
		$mockIgnoredPattern->shouldReceive('matchRequest')->andReturn(TRUE);

		$mockRequestPatternResolver->shouldReceive('resolveRequestPatternClass')->with('TestPattern1')->andReturn('MyPackage\Security\TestPattern1');
		$mockObjectManager->shouldReceive('get')->with('MyPackage\Security\TestPattern1')->andReturn($mockFailingPattern);
		$mockRequestPatternResolver->shouldReceive('resolveRequestPatternClass')->with('TestPattern2')->andReturn('MyPackage\Security\TestPattern2');
		$mockObjectManager->shouldReceive('get')->with('MyPackage\Security\TestPattern2')->andReturn($mockIgnoredPattern);

		$pattern->setPattern(array(
			'patterns' => array(
				array(
					'patternType' => 'TestPattern1',
					'patternValue' => 'TestValue'
				),
				array(
					'patternType' => 'TestPattern2',
					'patternValue' => 'TestValue'
				)
			)
		));

		$mockActionRequest = m::mock('TYPO3\Flow\Mvc\ActionRequest');

		$result = $pattern->matchRequest($mockActionRequest);
		$this->assertTrue($result);
	}

	/**
	 * @test
	 */
	public function matchRequestReturnsTrueWithEmptySubpatterns() {
		$pattern = new \TYPO3\SingleSignOn\Client\Security\RequestPattern\ConjunctionPattern();

		$pattern->setPattern(array(
			'patterns' => array()
		));

		$mockActionRequest = m::mock('TYPO3\Flow\Mvc\ActionRequest');

		$result = $pattern->matchRequest($mockActionRequest);
		$this->assertTrue($result);
	}

	/**
	 * Check for Mockery expectations
	 */
	public function tearDown() {
		m::close();
	}
}

?>