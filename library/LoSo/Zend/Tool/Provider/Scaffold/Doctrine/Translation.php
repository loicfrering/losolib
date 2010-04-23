<?php
class LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Translation extends LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Abstract
{
    protected $messageIds;

    public function __construct($entityName, $module, $appnamespace, $messageIds)
    {
        parent::__construct($entityName, $module, $appnamespace);
        $this->messageIds = $messageIds;
    }

    public function scaffold()
    {
        return $this->_getTranslation();
    }

    protected function _getTranslation()
    {
        $metadata = $this->_getMetadata();
        $messageIds = $this->messageIds;
        $translation = '<?php' . PHP_EOL . 'return array(' . PHP_EOL;
        $fields = $metadata->getFieldNames();
        $identifierFields = $metadata->getIdentifierFieldNames();
        foreach($fields as $field) {
            if(!in_array($field, $identifierFields)) {
                $translation .= '    \'' . ucfirst($field) . '\' => \'' . ucfirst($field) . '\',' . PHP_EOL;
            }
        }

        $translation .= PHP_EOL;

        foreach($messageIds as $key => $value) {
            if(strpos($key, 'global') !== 0) {
                $translation .= '    \'' . $value . '\' => \'' . $value . '\',' . PHP_EOL;
            }
        }

        $translation .= ');';

        return $translation;
    }
}
