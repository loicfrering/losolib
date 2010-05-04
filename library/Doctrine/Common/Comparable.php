<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Doctrine\Common;

/**
 * Contract for classes whose instances have a natural ordering.
 *
 * @author Roman Borschel <roman@code-factory.org>
 */
interface Comparable
{
    /**
     * Compares this instance to the given instance for their natural ordering.
     *
     * @param object $other The instance to compare to for natural ordering.
     * @return integer -1, zero, +1 as this instance is less than, equal to, or
     *                 greater than the specified instance.
     */
    function compareTo($other);
}
