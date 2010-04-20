<?php

class Application_Form_Post extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        
        $this->addElement('text', 'title', array(
            'required' => true,
            'label' => 'Title:'
        ));
        
        $this->addElement('text', 'body', array(
            'required' => true,
            'label' => 'Body:'
        ));
        
        $this->addElement('submit', 'submit', array(
            'label' => 'Save'
        ));
    }


}

