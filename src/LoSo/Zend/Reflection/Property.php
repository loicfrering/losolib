<?php
/**
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Reflection_Property extends Zend_Reflection_Property
{
    public function getDocComment()
    {
        $docComment = parent::getDocComment('LoSo_Zend_Reflection_Docblock');
        return $docComment ? $docComment->getContents() : false;
    }
}
