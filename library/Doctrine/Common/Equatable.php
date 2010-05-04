<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Doctrine\Common;

/**
 * Contract for classes whose instances can be compared for equality by means of
 * an explicit {@link equals} method.
 *
 * @author Roman Borschel <roman@code-factory.org>
 */
interface Equatable
{
    /**
     * Checks whether this instance is equal to another instance.
     *
     * @param object $other The object to compare for equality.
     * @return boolean TRUE if this instance is equal to the given instance, FALSE otherwise.
     */
    function equals($other);
}
