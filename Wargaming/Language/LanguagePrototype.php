<?php

namespace Wargaming\Language;

use Exception;

/**
 * Class LanguagePrototype
 * @package Wargaming\Language
 */
abstract class LanguagePrototype
{

    /**
     * Convert server to url string.
     *
     * @return string
     *
     * @throws Exception
     */
    public function __toString()
    {
        return strtolower((new \ReflectionClass($this))->getShortName());
    }

}