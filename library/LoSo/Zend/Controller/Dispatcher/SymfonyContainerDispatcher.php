<?php
class LoSo_Zend_Controller_Dispatcher_SymfonyContainerDispatcher extends Zend_Controller_Dispatcher_Standard
{
    public function dispatch(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response)
    {
        $this->setResponse($response);

        /**
         * Get controller class
         */
        if (!$this->isDispatchable($request)) {
            $controller = $request->getControllerName();
            if (!$this->getParam('useDefaultControllerAlways') && !empty($controller)) {
                require_once 'Zend/Controller/Dispatcher/Exception.php';
                throw new Zend_Controller_Dispatcher_Exception('Invalid controller specified (' . $request->getControllerName() . ')');
            }

            $className = $this->getDefaultControllerClass($request);
        } else {
            $className = $this->getControllerClass($request);
            if (!$className) {
                $className = $this->getDefaultControllerClass($request);
            }
        }

        /**
         * Load the controller class file
         */
        $className = $this->loadClass($className);

        /**
         * Instantiate controller with request, response, and invocation
         * arguments; throw exception if it's not an action controller
         */
        if(Zend_Registry::isRegistered(LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap::getRegistryIndex())) {
            $container = Zend_Registry::get(LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap::getRegistryIndex());
            $controllerId = 'zend.controller.' . $className;
        }
        else {
            $container = null;
        }
        if(null !== $container && $container->hasService(lcfirst($controllerId))) {
            $container->setService('zend.controller.request', $request);
            $container->setService('zend.controller.response', $this->getResponse());
            $container->setService('zend.controller.params', $this->getParams());
            $controller = $container->getService($controllerId);
            if(!$controller instanceof LoSo_Zend_Controller_Action) {
                throw new LoSo_Exception('Controller from Symfony Container "' . $className . '" is not an instance of LoSo_Zend_Controller_Action');
            }
            $controller->init();
        }
        else {
            $controller = new $className($request, $this->getResponse(), $this->getParams());
            if($controller instanceof LoSo_Zend_Controller_Action) {
                $controller->init();
            }
        }
        if (!($controller instanceof Zend_Controller_Action_Interface) &&
            !($controller instanceof Zend_Controller_Action)) {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception(
                'Controller "' . $className . '" is not an instance of Zend_Controller_Action_Interface'
            );
        }

        /**
         * Retrieve the action name
         */
        $action = $this->getActionMethod($request);

        /**
         * Dispatch the method call
         */
        $request->setDispatched(true);

        // by default, buffer output
        $disableOb = $this->getParam('disableOutputBuffering');
        $obLevel   = ob_get_level();
        if (empty($disableOb)) {
            ob_start();
        }

        try {
            $controller->dispatch($action);
        } catch (Exception $e) {
            // Clean output buffer on error
            $curObLevel = ob_get_level();
            if ($curObLevel > $obLevel) {
                do {
                    ob_get_clean();
                    $curObLevel = ob_get_level();
                } while ($curObLevel > $obLevel);
            }
            throw $e;
        }

        if (empty($disableOb)) {
            $content = ob_get_clean();
            $response->appendBody($content);
        }

        // Destroy the page controller instance and reflection objects
        $controller = null;
    }
}
