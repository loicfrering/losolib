<?php

namespace Doctrine\Common\Collections;

interface Equatable
{
    function equals(Equatable $other);
    function hashCode();
}