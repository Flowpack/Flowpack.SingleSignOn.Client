<?php
namespace TYPO3\SingleSignOn\Client\Aspect;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * An aspect which logs SSO relevant actions
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class LoggingAspect {

	/**
	 * @var \TYPO3\Flow\Log\SecurityLoggerInterface
	 * @Flow\Inject
	 */
	protected $securityLogger;

	/**
	 *
	 *
	 * @Flow\AfterReturning("method(TYPO3\SingleSignOn\Client\Security\RequestPattern\SignedRequestPattern->emitSignatureNotVerified())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint The current joinpoint
	 */
	public function logSignedRequestPatternFailures(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$request = $joinPoint->getMethodArgument('request');
		if ($request instanceof \TYPO3\Flow\Mvc\RequestInterface) {
			if ($request->getControllerObjectName() === 'TYPO3\SingleSignOn\Client\Controller\SessionController') {
				$this->securityLogger->log('Signature for call to Session service could not be verified', LOG_NOTICE, array(
					'identifier' => $joinPoint->getMethodArgument('identifier'),
					'publicKeyFingerprint' => $joinPoint->getMethodArgument('publicKeyFingerprint'),
					'signature' => base64_encode($joinPoint->getMethodArgument('signature')),
					'signData' => $joinPoint->getMethodArgument('signData'),
					'content' => $joinPoint->getMethodArgument('request')->getHttpRequest()->getContent(),
				));
			}
		}
	}

}

?>