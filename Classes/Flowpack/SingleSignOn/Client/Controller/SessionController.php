<?php
namespace Flowpack\SingleSignOn\Client\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

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
	 * @param string $sessionId The global session id to invalidate
	 * @param string $serverIdentifier Optional server identifier
	 */
	public function destroyAction($sessionId, $serverIdentifier = NULL) {
		if ($this->request->getHttpRequest()->getMethod() !== 'DELETE') {
			$this->response->setStatus(405);
			$this->response->setHeader('Allow', 'DELETE');
			return;
		}


		$sessions = $this->sessionManager->getSessionsByTag('Flowpack_SingleSignOn_Client-' . $sessionId);
		if ($sessions !== array()) {
			$message = 'Destroyed by SSO client REST service';
			if ($serverIdentifier !== NULL) {
				$message .= ' from server "' . $serverIdentifier . '"';
			}
			foreach ($sessions as $session) {
				$session->destroy($message);
			}

			$this->view->assign('value', array(
				'success' => TRUE
			));
		} else {
			$this->response->setStatus(404);

			$this->view->assign('value', array('error' => 'SessionNotFound'));
		}
	}

}
?>