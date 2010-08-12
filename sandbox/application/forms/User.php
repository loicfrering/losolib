<?php
class Application_Form_User extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');

        $this->addElement('text', 'firstname', array(
            'required' => true,
            'label' => 'Firstname:'
        ));

        $this->addElement('text', 'lastname', array(
            'required' => true,
            'label' => 'Lastname:'
        ));

        $this->addElement('text', 'email', array(
            'required' => true,
            'label' => 'Email:'
        ));

        $this->addElement('submit', 'submit', array(
            'label' => 'Save'
        ));
    }
}
