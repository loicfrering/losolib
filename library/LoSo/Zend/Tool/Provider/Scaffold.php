<?php
class LoSo_Zend_Tool_Provider_Scaffold extends Zend_Tool_Framework_Provider_Abstract implements Zend_Tool_Framework_Provider_Pretendable
{
    protected $entityName;
    protected $appNamespace;
    protected $module;
    protected $frontController;

    public function controller($entityName, $module = null, $forceOverwrite = false)
    {
        $this->_prepare($entityName, $module);
        $this->_registry->getResponse()->appendContent('Scaffold controller for ' . $entityName);

        $controllerScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Controller($this->_getEntityName(), $this->_getModule(), $this->_getModuleNamespace());
        $controller = $this->_parse($controllerScaffold->scaffold());
        $this->_write($controller, $this->_getControllerDirectory() . '/' . $entityName . 'Controller.php', $forceOverwrite);
    }

    public function views($entityName, $module = null, $forceOverwrite = false)
    {
        $this->_prepare($entityName, $module);
        $this->_registry->getResponse()->appendContent('Scaffold views for ' . $entityName);

        $viewsDirectory = $this->_getModuleDirectory() . '/views/scripts/' . $this->_getEntityVarName();

        $actions = array(
            'list',
            'show',
            'new',
            'edit',
            'delete'
        );
        foreach($actions as $action) {
            $scaffoldClass = 'LoSo_Zend_Tool_Provider_Scaffold_' . ucfirst($action) . 'View';
            $scaffold = new $scaffoldClass($this->_getEntityName(), $this->_getModule(), $this->_getModuleNamespace());
            $view = $this->_parse($scaffold->scaffold());
            $this->_write($view, $viewsDirectory . '/' . $action . '.phtml', $forceOverwrite);
        }

        $partialScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Partial($this->_getEntityName(), $this->_getModule(), $this->_getModuleNamespace());
        $partial = $this->_parse($partialScaffold->scaffold());
        $this->_write($partial, $viewsDirectory . '/partial' . $this->_getEntityName() . '.phtml', $forceOverwrite);
    }

    public function service($entityName, $module = null, $forceOverwrite = false)
    {
        $this->_prepare($entityName, $module);
        $this->_registry->getResponse()->appendContent('Scaffold service for ' . $entityName);

        $serviceScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Service($this->_getEntityName(), $this->_getModule(), $this->_getModuleNamespace());
        $service = $this->_parse($serviceScaffold->scaffold());
        $this->_write($service, $this->_getModuleDirectory() . '/services/Doctrine/' . $entityName . 'Service.php', $forceOverwrite);
    }

    public function dao($entityName, $module = null, $forceOverwrite = false)
    {
        $this->_prepare($entityName, $module);
        $this->_registry->getResponse()->appendContent('Scaffold dao for ' . $entityName);

        $daoScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Dao($this->_getEntityName(), $this->_getModule(), $this->_getModuleNamespace());
        $dao = $this->_parse($daoScaffold->scaffold());
        $this->_write($dao, $this->_getModuleDirectory() . '/dao/Doctrine/' . $entityName . 'Dao.php', $forceOverwrite);

    }

    public function form($entityName, $module = null, $forceOverwrite = false)
    {
        $this->_prepare($entityName, $module);
        $this->_registry->getResponse()->appendContent('Scaffold form for ' . $entityName);

        $formScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Form($this->_getEntityName(), $this->_getModule(), $this->_getModuleNamespace());
        $form = $this->_parse($formScaffold->scaffold());
        $this->_write($form, $this->_getModuleDirectory() . '/forms/' . $entityName . '.php', $forceOverwrite);
    }

    public function translation($entityName, $module = null, $forceOverwrite = false)
    {
        $this->_prepare($entityName, $module);
        $this->_registry->getResponse()->appendContent('Scaffold translation for ' . $entityName);

        $translationScaffold = new LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Translation($this->_getEntityName(), $this->_getModule(), $this->_getAppNamespace(), $this->_getMessageIds());
        $translation = $this->_parse($translationScaffold->scaffold());
        $this->_write($translation, 'languages/en/' . $this->_getEntityVarName() . '.php', $forceOverwrite);

    }

    public function all($entityName, $module = null, $forceOverwrite = false)
    {
        $this->_registry->getResponse()->appendContent('Scaffold all for ' . $entityName);
        $this->_registry->getResponse()->appendContent('');

        $this->dao($entityName, $module, $forceOverwrite);
        $this->_registry->getResponse()->appendContent('');
        $this->service($entityName, $module, $forceOverwrite);
        $this->_registry->getResponse()->appendContent('');
        $this->form($entityName, $module, $forceOverwrite);
        $this->_registry->getResponse()->appendContent('');
        $this->controller($entityName, $module, $forceOverwrite);
        $this->_registry->getResponse()->appendContent('');
        $this->views($entityName, $module, $forceOverwrite);
        $this->_registry->getResponse()->appendContent('');
        $this->translation($entityName, $module, $forceOverwrite);
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
        if(null === $this->module) {
            $this->module = $this->frontController->getDefaultModule();
        }
        return $this->module;
    }
    
    protected function _getModuleNamespace()
    {
        return $this->_getModule() != $this->frontController->getDefaultModule() ? ucfirst($this->_getModule()) : $this->_getAppNamespace();
    }

    protected function _getControllerNamespace()
    {
        return $this->_getModule() != $this->frontController->getDefaultModule() ? ucfirst($this->_getModule()) . '_' : '';
    }

    protected function _getAppNamespace()
    {
        return $this->appNamespace;
    }

    protected function _getModuleDirectory()
    {
        return $this->frontController->getModuleDirectory($this->_getModule());
    }

    protected function _getControllerDirectory()
    {
        return $this->frontController->getControllerDirectory($this->_getModule());
    }

    protected function _write($content, $path, $forceOverwrite = false)
    {
        if ($this->_registry->getRequest()->isPretend()) {
            $this->_registry->getResponse()->appendContent('Would write at: ' . $path);
        }
        else {
            $doWrite = true;

            $dir = dirname($path);
            if(!is_dir($dir)) {
                $response = $this->_registry->getClient()
                    ->promptInteractiveInput('Folder ' . $dir . ' does not exist, do you want to create it?')
                    ->getContent();
                if('y' == strtolower($response)) {
                    if(mkdir($dir, 0777, true)) {
                        $this->_registry->getResponse()->appendContent('Directory created: ' . $dir);
                    }
                    else {
                        $this->_registry->getResponse()->appendContent('Error creating dir: ' . $dir);
                        $doWrite = false;
                    }
                }
                else {
                    $doWrite = false;
                }
            }

            if(!$forceOverwrite && file_exists($path)) {
                $response = $this->_registry->getClient()
                    ->promptInteractiveInput('File ' . $path . ' already exists, do you want to overwrite it?')
                    ->getContent();
                if('y' != strtolower($response)) {
                    $doWrite = false;
                }
            }

            if($doWrite) {
                if(false !== file_put_contents($path, $content)) {
                    $this->_registry->getResponse()->appendContent('Successfully wrote: ' . $path);
                } else  {
                    $this->_registry->getResponse()->appendContent('Error writing: ' . $path);
                }
            } else {
                $this->_registry->getResponse()->appendContent('Abording scaffolding for: ' . $path);
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
            'controllerNamespace' => $this->_getControllerNamespace(),
            'moduleNamespace' => $this->_getModuleNamespace()
        );
        foreach($fields as $field => $value) {
            $string = str_replace('{%' . $field . '}', $value, $string);
        }

        $messageIds = $this->_getMessageIds();
        foreach($messageIds as $key => $value) {
            $string = str_replace('{%' . $key . '}', $value, $string);
        }
        return $string;
    }

    protected function _prepare($entityName, $module = null)
    {
        $this->entityName = $entityName;
        $this->module = $module;

        require_once 'Zend/Loader/Autoloader.php';
        Zend_Loader_Autoloader::getInstance()->registerNamespace('LoSo');

        $applicationPath = isset($this->_registry->getConfig()->application->path) ? $this->_registry->getConfig()->application->path : 'application';
        $applicationConfigPath = isset($this->_registry->getConfig()->application->configPath) ? $this->_registry->getConfig()->application->configPath : 'configs/application.ini';
        $applicationEnv = isset($this->_registry->getConfig()->application->env) ? $this->_registry->getConfig()->application->env : 'development';

        defined('APPLICATION_PATH') || define('APPLICATION_PATH', $applicationPath);

        /** Zend_Application */
        require_once 'Zend/Application.php';

        // Create application, bootstrap, and run
        $application = new Zend_Application(
            'production',
            $applicationPath . '/' . $applicationConfigPath
        );
        $application->bootstrap(array('FrontController', 'Modules'));
        $this->appNamespace = $application->getBootstrap()->getAppNamespace();
        $this->frontController = $application->getBootstrap()->frontController;
    }

    protected function _getMessageIds()
    {
        $entity = $this->_getEntityName();
        return array(
            'module.entity.action.list.title' => 'List ' . $entity,
            'module.entity.action.list.link' => 'List ' . $entity,
            'module.entity.action.show.title' => 'Show ' . $entity,
            'module.entity.action.show.link' => 'Show ' . $entity,
            'module.entity.action.new.title' => 'New ' . $entity,
            'module.entity.action.new.link' => 'New ' . $entity,
            'module.entity.action.edit.title' => 'Edit ' . $entity,
            'module.entity.action.edit.link' => 'Edit ' . $entity,
            'module.entity.action.delete.title' => 'Delete ' . $entity,
            'module.entity.action.delete.link' => 'Delete ' . $entity,
            'module.entity.action.create.success' => $entity . ' successfully created.',
            'module.entity.action.update.success' => $entity . ' successfully updated.',
            'module.entity.action.destroy.success' => $entity . ' successfully deleted.',
            'module.entity.notfound.id' => $entity . ' with id %s not found.',
            'module.entity.form.save' => 'Save ' . $entity,
            'global.delete' => 'Delete',
            'global.cancel' => 'Cancel',
            'global.return' => 'Return'
        );
    }
}
