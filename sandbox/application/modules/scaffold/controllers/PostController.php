<?php
/**
 * @Controller
 */
class Scaffold_PostController extends LoSo_Zend_Controller_Action
{
    /**
     * @var Scaffold_Repository_Doctrine_PostRepository
     * @Inject
     */
    protected $postRepository;

    public function setPostRepository($postRepository)
    {
        $this->postRepository = $postRepository;
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
        $posts = $this->postRepository->findAll();
        $this->view->posts = $posts;
    }

    public function showAction()
    {
        $post = $this->_findPost($this->_getParam('id'));
        $this->view->post = $post;
    }

    public function newAction()
    {
        $postForm = new Scaffold_Form_Post();
        $postForm->setAction($this->view->url(array('action' => 'create')));
        $this->view->postForm = $postForm;
    }

    public function editAction()
    {
        $post = $this->_findPost($this->_getParam('id'));
        $postForm = new Scaffold_Form_Post();
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
        $postForm = new Scaffold_Form_Post();
        if($this->getRequest()->isPost()) {
            if($postForm->isValid($_POST)) {
                $post = new Scaffold_Model_Post();
                $this->postRepository->populate($post, $postForm->getValues());
                $this->postRepository->create($post);
                $this->postRepository->flush();

                $this->_helper->flashMessenger($this->view->translate('Post successfully created.'));
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
        $postForm = new Scaffold_Form_Post();
        if($this->getRequest()->isPost()) {
            if($postForm->isValid($_POST)) {
                $this->postRepository->populate($post, $postForm->getValues());
                $this->postRepository->update($post);
                $this->postRepository->flush();

                $this->_helper->flashMessenger($this->view->translate('Post successfully updated.'));
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
            $this->postRepository->delete($post);
            $this->postRepository->flush();

            $this->_helper->flashMessenger($this->view->translate('Post successfully deleted.'));
            return $this->_helper->redirector('list');
        }
        return $this->_helper->redirector('list');
    }

    protected function _findPost($id)
    {
        $post = $this->postRepository->find($id);
        if(null === $post) {
            $this->_helper->flashMessenger($this->view->translate('Post with id %s not found.', $id));
            return $this->_helper->redirector('list');
        }
        return $post;
    }
}
