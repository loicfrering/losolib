<?php

namespace Doctrine\DBAL;

interface ReadWriteConnectionResolver
{
    function resolveReadConnection(array $allConnParams);
    function resolveWriteConnection(array $allConnParams);
}