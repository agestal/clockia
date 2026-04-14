<?php

namespace App\Tools\Exceptions;

use RuntimeException;

class EntityNotFoundException extends RuntimeException
{
    public function __construct(string $entity, mixed $id)
    {
        parent::__construct("{$entity} con ID {$id} no encontrado.");
    }
}
