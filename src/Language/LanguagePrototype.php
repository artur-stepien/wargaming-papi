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
     * Language for the API
     * @var string
     */
    private $lang;

    /**
     * LanguagePrototype constructor.
     * @param string $lang Lang of Api
     */
    public function __construct(string $lang = '')
    {

        $this->lang = ($lang!== '')?$lang:strtolower((new \ReflectionClass($this))->getShortName());
    }
    /**
     * Convert server to url string.
     *
     * @return string
     *
     * @throws Exception
     */
    public function __toString(): string
    {
        return $this->lang;
    }

}