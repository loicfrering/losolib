<?php
/**
 * @Controller
 */
class {%controllerNamespace}{%entity}Controller extends LoSo_Zend_Controller_Action
{
    /**
     * @var {%moduleNamespace}_Repository_Doctrine_{%entity}Repository
     * @Inject
     */
    protected ${%entityVar}Repository;

    public function set{%entity}Repository(${%entityVar}Repository)
    {
        $this->{%entityVar}Repository = ${%entityVar}Repository;
        return $this;
    }

    public function init()
    {
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->view->partial()->setObjectKey('entity');
        $this->view->partialLoop()->setObjectKey('entity');
    }

    public function indexAction()
    {
        $this->_helper->redirector('list');
    }

    public function listAction()
    {
        ${%entitiesVar} = $this->{%entityVar}Repository->findAll();
        $this->view->{%entitiesVar} = ${%entitiesVar};
    }

    public function showAction()
    {
        ${%entityVar} = $this->_find{%entity}($this->_getParam('id'));
        $this->view->{%entityVar} = ${%entityVar};
    }

    public function newAction()
    {
        ${%entityVar}Form = new {%moduleNamespace}_Form_{%entity}();
        ${%entityVar}Form->setAction($this->view->url(array('action' => 'create')));
        $this->view->{%entityVar}Form = ${%entityVar}Form;
    }

    public function editAction()
    {
        ${%entityVar} = $this->_find{%entity}($this->_getParam('id'));
        ${%entityVar}Form = new {%moduleNamespace}_Form_{%entity}();
        ${%entityVar}Form->setAction($this->view->url(array('action' => 'update')))
            ->populate(${%entityVar});
        $this->view->{%entityVar}Form = ${%entityVar}Form;
        
    }

    public function deleteAction()
    {
        ${%entityVar} = $this->_find{%entity}($this->_getParam('id'));
        $this->view->{%entityVar} = ${%entityVar};
    }

    public function createAction()
    {
        ${%entityVar}Form = new {%moduleNamespace}_Form_{%entity}();
        if($this->getRequest()->isPost()) {
            if(${%entityVar}Form->isValid($_POST)) {
                ${%entityVar} = new {%moduleNamespace}_Model_{%entity}();
                $this->{%entityVar}Repository->populate(${%entityVar}, ${%entityVar}Form->getValues());
                $this->{%entityVar}Repository->create(${%entityVar});
                $this->{%entityVar}Repository->flush();

                $this->_helper->flashMessenger($this->view->translate('{%module.entity.action.create.success}'));
                return $this->_helper->redirector('show', null, null, array('id' => ${%entityVar}->getId()));
            }
            else {
                $this->view->{%entityVar}Form = ${%entityVar}Form;
                return $this->render('new');
            }
        }
        return $this->_helper->redirector('list');
    }

    public function updateAction()
    {
        ${%entityVar} = $this->_find{%entity}($this->_getParam('id'));
        ${%entityVar}Form = new {%moduleNamespace}_Form_{%entity}();
        if($this->getRequest()->isPost()) {
            if(${%entityVar}Form->isValid($_POST)) {
                $this->{%entityVar}Repository->populate(${%entityVar}, ${%entityVar}Form->getValues());
                $this->{%entityVar}Repository->update(${%entityVar});
                $this->{%entityVar}Repository->flush();

                $this->_helper->flashMessenger($this->view->translate('{%module.entity.action.update.success}'));
                return $this->_helper->redirector('show', null, null, array('id' => ${%entityVar}->getId()));
            }
            else {
                $this->view->{%entityVar}Form = ${%entityVar}Form;
                return $this->render('edit');
            }
        }
        return $this->_helper->redirector('list');

    }

    public function destroyAction()
    {
        ${%entityVar} = $this->_find{%entity}($this->_getParam('id'));
        if($this->getRequest()->isPost()) {
            $this->{%entityVar}Repository->delete(${%entityVar});
            $this->{%entityVar}Repository->flush();

            $this->_helper->flashMessenger($this->view->translate('{%module.entity.action.destroy.success}'));
            return $this->_helper->redirector('list');
        }
        return $this->_helper->redirector('list');
    }

    protected function _find{%entity}($id)
    {
        ${%entityVar} = $this->{%entityVar}Repository->find($id);
        if(null === ${%entityVar}) {
            $this->_helper->flashMessenger($this->view->translate('{%module.entity.notfound.id}', $id));
            return $this->_helper->redirector('list');
        }
        return ${%entityVar};
    }
}
