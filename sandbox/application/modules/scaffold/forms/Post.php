<?php

class Scaffold_Form_Post extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        
        $this->addElement('text', 'title', array(
            'required' => true,
            'validators' => array(
                array('stringLength', false, array(0, 100))
            ),
            'label' => 'Title:'
        ));
        
        $this->addElement('textarea', 'body', array(
            'required' => true,
            'label' => 'Body:'
        ));
        
        $this->addElement('submit', 'submit', array(
            'label' => 'Save Post'
        ));
    }

    public function populate($post)
    {
        $this->title->setValue($post->getTitle());
        $this->body->setValue($post->getBody());
    }


}

