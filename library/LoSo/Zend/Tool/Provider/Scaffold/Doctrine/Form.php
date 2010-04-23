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
        $form = new Zend_CodeGenerator_Php_Class();
        $form->setName('{%moduleNamespace}_Form_{%entity}');
        $form->setExtendedClass('Zend_Form');


        $form->setMethod($this->_getInitMethod());
        $form->setMethod($this->_getPopulateMethod());

        return $form;
    }

    protected function _getInitMethod()
    {
        $metadata = $this->_getMetadata();
        $init = new Zend_CodeGenerator_Php_Method();
        $init->setName('init');

        $body = '$this->setMethod(\'post\');' . PHP_EOL . PHP_EOL;
        $fieldMappings = $metadata->getFieldMappings();
        $identifierFields = $metadata->getIdentifierFieldNames();
        foreach($fieldMappings as $field => $fieldMapping) {
            if(!in_array($field, $identifierFields)) {
                $body .= $this->_getElement($fieldMapping) . PHP_EOL;
            }
        }
        $body .= $this->_getSubmitElement();

        $init->setBody($body);
        return $init;
    }

    protected function _getPopulateMethod()
    {
        $metadata = $this->_getMetadata();
        $entityVar = $this->_getEntityVarName();
        $populate = new Zend_CodeGenerator_Php_Method();
        $populate->setName('populate');
        $populate->setParameter(array('name' => $entityVar));

        $body = '';
        $fields = $metadata->getFieldNames();
        $identifierFields = $metadata->getIdentifierFieldNames();
        foreach($fields as $field) {
            if(!in_array($field, $identifierFields)) {
                switch($metadata->getTypeOfField($field)) {
                    default:
                    $body .= '$this->' . $field . '->setValue(${%entityVar}->get' . ucfirst($field) . '());' . PHP_EOL;
                }
            }
        }

        $populate->setBody($body);
        return $populate;
    }

    protected function _getElement($fieldMapping)
    {
        $field = $fieldMapping['fieldName'];
        $element = '$this->addElement(\'' . $this->_getElementType($fieldMapping) . '\', \'' . $field . '\', array(' . PHP_EOL;
        if(!$fieldMapping['nullable']) {
            $element .= '    \'required\' => true,' . PHP_EOL;
        }
        $element .= $this->_getValidators($fieldMapping);
        $element .= '    \'label\' => \'' . ucfirst($field) . ':\'' . PHP_EOL;
        $element .= '));' . PHP_EOL;
        return $element;
    }

    protected function _getElementType($fieldMapping)
    {
        switch($fieldMapping['type']) {
            case 'string':
                return (null === $fieldMapping['length']) ? 'textarea' : 'text';

            default:
                return 'text';
        }
    }

    protected function _getSubmitElement()
    {
        $submit = '$this->addElement(\'submit\', \'submit\', array(
    \'label\' => \'{%module.entity.form.save}\'
));';
        return $submit;
    }

    protected function _getValidators($fieldMapping)
    {
        $validators = '';
        switch($fieldMapping['type']) {
            case 'integer':
                $validators .= '        \'int\'' . PHP_EOL;
                break;

            case 'decimal':
                $validators .= '        \'float\'' . PHP_EOL;
                break;

            case 'string':
                if(null !== $fieldMapping['length']) {
                    $validators .= '        array(\'stringLength\', false, array(0, ' . $fieldMapping['length'] . '))' . PHP_EOL;
                }
                break;
        }

        if(!empty($validators)) {
            $validators = '    \'validators\' => array(' . PHP_EOL . $validators . '    ),' . PHP_EOL;
        }

        return $validators;
    }
}
