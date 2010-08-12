<?php
/**
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Reflection_Docblock extends Zend_Reflection_Docblock
{
    /**
     * Constructor
     *
     * @param Reflector|string $commentOrReflector
     */
    public function __construct($comment)
    {
        $this->_docComment = $comment;
    }

    public function getContents()
    {
        return $this->_docComment;
    }
}
