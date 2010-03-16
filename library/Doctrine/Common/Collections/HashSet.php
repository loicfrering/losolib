<?php

namespace Doctrine\Common\Collections;

class HashSet implements Set, \IteratorAggregate
{
    private $_elements = array();
    private $_count = 0;

    public function add($element)
    {
        if ($this->contains($element)) {
            return false;
        }
        if (is_object($element)) {
            $hashCode = $element instanceof Equatable ? $element->hashCode() : spl_object_hash($element);
            $this->_elements[$hashCode][] = $element;
        } else if (is_string($element) || is_int($element)) {
            $this->_elements[$element] = $element;
        } else {
            throw new \InvalidArgumentException("Element type " . gettype($element) . " not supported by Set.");
        }
        ++$this->_count;
    }
    
    public function remove($element)
    {
        if (is_object($element)) {
            $hashCode = $element instanceof Equatable ? $element->hashCode() : spl_object_hash($element);
            if (isset($this->_elements[$hashCode])) {
                foreach ($this->_elements[$hashCode] as $otherElement) {
                    if ($element->equals($otherElement)) {
                        unset($this->_elements[$hashCode]);
                        --$this->_count;
                        return true;
                    }
                }
            }
            return false;
        } else if (is_string($element) || is_int($element)) {
            if (isset($this->_elements[$element])) {
                unset($this->_elements[$element]);
                --$this->_count;
                return true;
            }
            return false;
        }
    }

    public function clear()
    {

    }

    public function contains($element)
    {
        if (is_object($element)) {
            $hashCode = $element instanceof Equatable ? $element->hashCode() : spl_object_hash($element);
            if (isset($this->_elements[$hashCode])) {
                var_dump($this->_elements[$hashCode]);
                foreach ($this->_elements[$hashCode] as $otherElement) {
                    if ($element->equals($otherElement)) {
                        echo "EQUALS!";
                        return true;
                    }
                }
            }
            return false;
        } else if (is_string($element) || is_int($element)) {
            return isset($this->_elements[$element]);
        }
        return false;
    }

    public function count()
    {
        return $this->_count;
    }

    /**
     * Checks whether the set is empty.
     *
     * Note: This is preferrable over count() == 0.
     *
     * @return boolean TRUE if the set is empty, FALSE otherwise.
     */
    public function isEmpty()
    {
        // Note: Little "trick". Empty arrays evaluate to FALSE. No need to count().
        return ! (bool) $this->_elements;
    }
    
    public function getIterator() {
        return new \ArrayIterator($this);
    }
}