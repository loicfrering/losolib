<?php
class PostController extends Zend_Controller_Action
{
    /**
     * @var Application_Service_Doctrine_PostService
     * @Inject
     */
    protected $postService;

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
        $posts = $this->postService->findAll();
        $this->view->posts = $posts;
    }

    public function showAction()
    {
        $post = $this->_findPost($this->_getParam('id'));
        $this->view->post = $post;
    }

    public function newAction()
    {
        $postForm = new Application_Form_Post();
        $postForm->setAction($this->view->url(array('action' => 'create')));
        $this->view->postForm = $postForm;
    }

    public function editAction()
    {
        $post = $this->_findPost($this->_getParam('id'));
        $postForm = new Application_Form_Post();
        $postForm->setAction($this->view->url(array('action' => 'update')))
                         ->populate($post);
        $this->view->postForm = $postForm;
        
    }

    public function deleteAction()
    {
        $post = $this->_findPost($this->_getParam('id'));
        $this->view->post = $post;
    }

    public function createAction()
    {
        $postForm = new Application_Form_Post();
        if($this->getRequest()->isPost()) {
            if($postForm->isValid($_POST)) {
                $post = new Application_Model_Post();
                $this->postService->populate($post, $postForm->getValues());
                $this->postService->create($post);
                $this->postService->flush();

                $this->_helper->flashMessenger('default.post.create.success');
                return $this->_helper->redirector('show', null, null, array('id' => $post->getId()));
            }
            else {
                $this->view->postForm = $postForm;
                return $this->render('new');
            }
        }
        return $this->_helper->redirector('list');
    }

    public function updateAction()
    {
        $post = $this->_findPost($this->_getParam('id'));
        $postForm = new Application_Form_Post();
        if($this->getRequest()->isPost()) {
            if($postForm->isValid($_POST)) {
                $this->postService->populate($post, $postForm->getValues());
                $this->postService->create($post);
                $this->postService->flush();

                $this->_helper->flashMessenger('default.post.update.success');
                return $this->_helper->redirector('show', null, null, array('id' => $post->getId()));
            }
            else {
                $this->view->postForm = $postForm;
                return $this->render('edit');
            }
        }
        return $this->_helper->redirector('list');

    }

    public function destroyAction()
    {
        $post = $this->_findPost($this->_getParam('id'));
        if($this->getRequest()->isPost()) {
            $this->postService->delete($post);
            $this->postService->flush();

            $this->_helper->flashMessenger('default.post.destroy.success');
            return $this->_helper->redirector('list');
        }
        return $this->_helper->redirector('list');
    }

    protected function _findPost($id)
    {
        $post = $this->postService->find($this->_getParam('id'));
        if(null === $post) {
            $this->_helper->flashMessenger('default.post.notfound.id' . $this->_getParam('id'));
            return $this->_helper->redirector('list');
        }
        return $post;
    }
}
