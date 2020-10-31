<?php

/**
 * ORM/SimpleORMInterface.php
 *
 * @author Jérémy 'Jejem' Desvages <jejem@phyrexia.org>
 * @copyright Jérémy 'Jejem' Desvages
 * @license The MIT License (MIT)
 **/

namespace Phyrexia\ORM;

interface SimpleORMInterface
{
    public static function load($id): ?self;

    public function save(): ?self;

    public function delete(): bool;
}
