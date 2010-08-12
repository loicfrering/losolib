<?php
class LoSo_Zend_Tool_Provider_Scaffold_NewView extends LoSo_Zend_Tool_Provider_Scaffold_Abstract
{
    public function scaffold()
    {
        $controller = file_get_contents(realpath(dirname(__FILE__) . '/Templates/new.phtml'));
        return $controller;
    }
}
