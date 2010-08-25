<?php
/**
 * Flash Messenger - implement session-based messages
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Controller_Action_Helper_FlashMessenger extends Zend_Controller_Action_Helper_FlashMessenger
{
    /**
     * preDispatch() - runs before action is dispatched.
     *
     * @return LoSo_Zend_Controller_Action_Helper_FlashMessenger Provides a fluent interface
     */
    public function preDispatch()
    {
        $controller = $this->getActionController();
        $controller->view->infoMessages = $this->setNamespace('default')->getMessages();
        $controller->view->successMessages = $this->setNamespace('success')->getMessages();
        $controller->view->errorMessages = $this->setNamespace('error')->getMessages();
        $controller->view->warnMessages = $this->setNamespace('warn')->getMessages();
    }

    /**
     * addMessage() - Add a message to flash message
     *
     * @param  string $message
     * @param  string $namespace
     * @return LoSo_Zend_Controller_Action_Helper_FlashMessenger Provides a fluent interface
     */
    public function addMessage($message, $namespace = null)
    {
        if (!empty($namespace)) {
            $this->setNamespace($namespace);
        }
        return parent::addMessage($message);
    }

    /**
     * Strategy pattern: proxy to addMessage()
     *
     * @param  string $message
     * @param  string $namespace
     * @return void
     */
    public function direct($message, $namespace = null)
    {
        return $this->addMessage($message, $namespace);
    }
}
