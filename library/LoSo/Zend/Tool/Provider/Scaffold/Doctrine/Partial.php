<?php
class LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Partial extends LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Abstract
{
    public function scaffold()
    {
        return $this->_getPartial();
    }

    protected function _getPartial()
    {
        $metadata = $this->_getMetadata();
        $partial = '<dl>' . PHP_EOL;
        $fields = $metadata->fieldNames;
        $identifierFields = $metadata->getIdentifierFieldNames();
        foreach($fields as $field) {
            if(!in_array($field, $identifierFields)) {
                switch($metadata->getTypeOfField('firstname')) {
                    default:
                    $partial .= '    <dt><?= $this->translate(\'' . ucfirst($field) . '\') ?></dt>' . PHP_EOL;
                    $partial .= '    <dd><?= $this->entity->get' . ucfirst($field) . '() ?></dd>' . PHP_EOL;
                }
            }
        }
        $partial .= '</dl>' . PHP_EOL;

        $partial .= '<p>' . PHP_EOL;
        $partial .= '    <a href="<?= $this->url(array(\'action\' => \'show\', \'id\' => $this->entity->getId())) ?>" title="<?= $this->translate(\'{%module.entity.action.show.link}\') ?>"><?= $this->translate(\'{%module.entity.action.show.link}\') ?></a>' . PHP_EOL;
        $partial .= '    <a href="<?= $this->url(array(\'action\' => \'edit\', \'id\' => $this->entity->getId())) ?>" title="<?= $this->translate(\'{%module.entity.action.edit.link}\') ?>"><?= $this->translate(\'{%module.entity.action.edit.link}\') ?></a>' . PHP_EOL;
        $partial .= '    <a href="<?= $this->url(array(\'action\' => \'delete\', \'id\' => $this->entity->getId())) ?>" title="<?= $this->translate(\'{%module.entity.action.delete.link}\') ?>"><?= $this->translate(\'{%module.entity.action.delete.link}\') ?></a>' . PHP_EOL;
        $partial .= '</p>' . PHP_EOL;

        return $partial;
    }
}
