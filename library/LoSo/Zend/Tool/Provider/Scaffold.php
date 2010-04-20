<?php
class LoSo_Zend_Tool_Provider_Scaffold extends Zend_Tool_Framework_Provider_Abstract implements Zend_Tool_Framework_Provider_Pretendable
{
    protected $entityName;

    public function __construct()
    {
        //$this->_prepare();
        require_once 'Zend/Loader/Autoloader.php';
        Zend_Loader_Autoloader::getInstance()->registerNamespace('LoSo');
    }

    public function controller($entityName, $forceOverwrite = false)
    {
        $this->entityName = $entityName;
        $this->_registry->getResponse()->appendContent('Scaffold controller for ' . $entityName);

        $controllerScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Controller($this->_getEntityName(), $this->_getModule(), $this->_getAppNamespace());
        $controller = $this->_parse($controllerScaffold->scaffold());
        $this->_write($controller, './application/controllers/' . $entityName . 'Controller.php', $forceOverwrite);
    }

    public function views($entityName, $forceOverwrite = false)
    {
        $this->entityName = $entityName;
        $this->_registry->getResponse()->appendContent('Scaffold views for ' . $entityName);

        $viewsDirectory = './application/views/scripts/' . $this->_getEntityVarName();

        if(!file_exists($viewsDirectory)) {
            mkdir($viewsDirectory);
        }

        $actions = array(
            'list',
            'show',
            'new',
            'edit',
            'delete'
        );
        foreach($actions as $action) {
            $scaffoldClass = 'LoSo_Zend_Tool_Provider_Scaffold_' . ucfirst($action) . 'View';
            $scaffold = new $scaffoldClass($this->_getEntityName(), $this->_getModule(), $this->_getAppNamespace());
            $view = $this->_parse($scaffold->scaffold());
            $this->_write($view, './application/views/scripts/' . $this->_getEntityVarName() . '/' . $action . '.phtml', $forceOverwrite);
        }

        $partialScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Partial($this->_getEntityName(), $this->_getModule(), $this->_getAppNamespace());
        $partial = $this->_parse($partialScaffold->scaffold());
        $this->_write($partial, './application/views/scripts/' . $this->_getEntityVarName() . '/partial' . $this->_getEntityName() . '.phtml', $forceOverwrite);
    }

    public function service($entityName, $forceOverwrite = false)
    {
        $this->entityName = $entityName;
        $this->_registry->getResponse()->appendContent('Scaffold service for ' . $entityName);

        $serviceScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Service($this->_getEntityName(), $this->_getModule(), $this->_getAppNamespace());
        $service = $this->_parse($serviceScaffold->scaffold());
        $this->_write($service, './application/services/Doctrine/' . $entityName . 'Service.php', $forceOverwrite);
    }

    public function dao($entityName, $forceOverwrite = false)
    {
        $this->entityName = $entityName;
        $this->_registry->getResponse()->appendContent('Scaffold dao for ' . $entityName);

        $daoScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Dao($this->_getEntityName(), $this->_getModule(), $this->_getAppNamespace());
        $dao = $this->_parse($daoScaffold->scaffold());
        $this->_write($dao, './application/dao/Doctrine/' . $entityName . 'Dao.php', $forceOverwrite);

    }

    public function form($entityName, $forceOverwrite = false)
    {
        $this->entityName = $entityName;
        $this->_registry->getResponse()->appendContent('Scaffold form for ' . $entityName);

        $formScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Form($this->_getEntityName(), $this->_getModule(), $this->_getAppNamespace());
        $form = $formScaffold->scaffold();
        $this->_write($form, './application/forms/' . $entityName . '.php', $forceOverwrite);
    }

    public function translation($entityName, $forceOverwrite = false)
    {
        $this->entityName = $entityName;
        $this->_registry->getResponse()->appendContent('Scaffold translation for ' . $entityName);

        $translationScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Translation($this->_getEntityName(), $this->_getModule(), $this->_getAppNamespace());
        $translation = $this->_parse($translationScaffold->scaffold());
        $this->_write($translation, './languages/en/' . $this->_getEntityVarName() . '.ini', $forceOverwrite);

    }

    public function all($entityName, $forceOverwrite = false)
    {
        $this->entityName = $entityName;
        $this->_registry->getResponse()->appendContent('Scaffold all for ' . $entityName);

        $this->dao($entityName, $forceOverwrite);
        $this->service($entityName, $forceOverwrite);
        $this->form($entityName, $forceOverwrite);
        $this->controller($entityName, $forceOverwrite);
        $this->views($entityName, $forceOverwrite);
        $this->translation($entityName, $forceOverwrite);
    }

    protected function _getEntityName()
    {
        return $this->entityName;
    }

    protected function _getEntityVarName()
    {
        return lcfirst($this->entityName);
    }

    protected function _getEntitiesVarName()
    {
        return $this->_getEntityVarName() . 's';
    }

    protected function _getModule()
    {
        return 'default';
    }

    protected function _getAppNamespace()
    {
        return 'Application';
    }

    protected function _write($content, $path, $forceOverwrite = false)
    {
        $doWrite = false;
        if ($this->_registry->getRequest()->isPretend()) {
            $this->_registry->getResponse()->appendContent('Would write at: ' . $path);
        }
        else {
            if(!$forceOverwrite && file_exists($path)) {
                $response = $this->_registry->getClient()
                    ->promptInteractiveInput('File ' . $path . ' already exists, do you want to overwrite it?')
                    ->getContent();
                if('y' == $response || 'Y' == $response) {
                    $doWrite = true;
                }
            } else {
                $doWrite = true;
            }

            if($doWrite) {
                if(false !== file_put_contents($path, $content)) {
                    $this->_registry->getResponse()->appendContent('Successfully wrote: ' . $path);
                } else  {
                    $this->_registry->getResponse()->appendContent('Error writing: ' . $path);
                }
            }
        }
    }

    protected function _parse($string)
    {
        $fields = array(
            'entity' => $this->_getEntityName(),
            'entityVar' => $this->_getEntityVarName(),
            'entitiesVar' => $this->_getEntitiesVarName(),
            'module' => $this->_getModule(),
            'appnamespace' => $this->_getAppNamespace()
        );
        foreach($fields as $field => $value) {
            $string = str_replace('{%' . $field . '}', $value, $string);
        }
        return $string;
    }

    protected function _prepare()
    {
        define('APPLICATION_PATH', './application');
        define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

        /** Zend_Application */
        require_once 'Zend/Application.php';

        // Create application, bootstrap, and run
        $application = new Zend_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
        );
        $application->bootstrap('FrontController');
        $frontController = $application->getBootstrap()->frontController;

        $this->_module = $frontController->getDefaultModule();
        $this->_controllersDirectory = $frontController->getControllerDirectory();
    }
}
