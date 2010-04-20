<?php
class LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Form extends LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Abstract
{
    public function scaffold()
    {
        $form = $this->_getForm();
        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($form);
        return $file->generate();
    }

    protected function _getForm()
    {
        $metadata = $this->_getMetadata();
        $form = new Zend_CodeGenerator_Php_Class();
        $form->setName($this->_getAppNamespace() . '_Form_' . $this->_getEntityName());
        $form->setExtendedClass('Zend_Form');

        $init = new Zend_CodeGenerator_Php_Method();
        $init->setName('init');

        $body = '$this->setMethod(\'post\');' . PHP_EOL;
        $fields = $metadata->getFieldNames();
        $identifierFields = $metadata->getIdentifierFieldNames();
        foreach($fields as $field) {
            if(!in_array($field, $identifierFields)) {
                switch($metadata->getTypeOfField('firstname')) {
                    default:
                    $body .= $this->_getTextElement($field) . PHP_EOL;
                }
            }
        }
        $body .= $this->_getSubmitElement();

        $init->setBody($body);

        $form->setMethod($init);

        return $form;
    }

    protected function _getTextElement($field)
    {
        $metadata = $this->_getMetadata();
        $element = '
$this->addElement(\'text\', \'' . $field . '\', array(';
        if(!$metadata->isNullable($field)) {
            $element .= '
    \'required\' => true,';
        }
        $element .= '
    \'label\' => \'' . ucfirst($field) . ':\'
));';
        return $element;
    }

    protected function _getSubmitElement()
    {
        $submit = '
$this->addElement(\'submit\', \'submit\', array(
    \'label\' => \'Save\'
));';
        return $submit;
    }
}
