<?php
namespace TYPO3\SingleSignOn\Client\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\SingleSignOn\Server\Exception;

/**
 * Session management controller
 *
 * Acts as server-to-server REST service to manage client sessions for server push notifications.
 *
 * @Flow\Scope("singleton")
 */
class SessionController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Session\SessionManagerInterface
	 */
	protected $sessionManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Client\Domain\Factory\SsoClientFactory
	 */
	protected $ssoClientFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Client\Domain\Factory\SsoServerFactory
	 */
	protected $ssoServerFactory;

	/**
	 * @var string
	 */
	protected $defaultViewObjectName = 'TYPO3\Flow\Mvc\View\JsonView';

	/**
	 * @var array
	 */
	protected $supportedMediaTypes = array('application/json');

	/**
	 * DELETE /sso/session/xyz-123/destroy
	 *
	 * @param string $sessionId The session id of the SSO client (not the global SSO session id)
	 */
	public function destroyAction($sessionId) {
		if ($this->request->getHttpRequest()->getMethod() !== 'DELETE') {
			$this->response->setStatus(405);
			$this->response->setHeader('Allow', 'DELETE');
			return;
		}

		$session = $this->sessionManager->getSession($sessionId);
		if ($session !== NULL) {
			// TODO Add server identifier to message
			$session->destroy('Destroyed by session REST service from server ...');

			$this->view->assign('value', array(
				'success' => TRUE
			));
		} else {
			$this->response->setStatus(404);
		}
	}

}
?>