<?php

namespace Doctrine\Common\Collections;

interface Comparable
{
    function compareTo(Comparable $other);
}