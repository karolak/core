<?php

declare(strict_types=1);

namespace Karolak\Core\Domain;

interface EntityInterface
{
    /**
     * @return IdInterface
     */
    public function getId(): IdInterface;
}