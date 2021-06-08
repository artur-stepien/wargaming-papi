<?php

namespace Wargaming\Server;

use Exception;

/**
 * Class ServerPrototype
 * @package Wargaming\Server
 */
abstract class ServerPrototype
{

    /**
     * Api Server URL
     */
    const URL = '';

    /**
     * ServerPrototype constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        if( !is_string($this::URL) || $this::URL==='' ) {
            throw new Exception('Server object is missing URL constant.');
        }
    }

    /**
     * Get server URL.
     *
     * @return string
     */
    public function getURL(): string
    {
        return $this::URL;
    }

    /**
     * Convert server to url string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this::URL;
    }

}