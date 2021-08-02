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
     *
     * @var string
     */
    protected $URL = '';

    /**
     * Api key (application id)
     *
     * @var string
     */
    protected $application_id = '';

    /**
     * ServerPrototype constructor.
     *
     * @param   string  $application_id  Application ID registered in this server.
     * @param   string  $url  API-Server URL, can be defined by using explicit classes
     *
     * @throws Exception
     */
    public function __construct(string $application_id = '', string $url = '')
    {

        //set new url if provided
        if($url !== ''){
            $this->URL = $url;
        }

        // If there was an api key provided, use it.
        if ($application_id !== '') {
            $this->application_id = $application_id;
        }

        if (!is_string($this->URL) || $this->URL === '') {
            throw new Exception('Server object is missing URL constant.');
        }
    }

    /**
     * Get server URL.
     *
     * @return string
     * @throws Exception
     */
    public function getURL(): string
    {

        if (!is_string($this->application_id) || $this->application_id === '') {
            throw new Exception('This server instance is missing application id ($application_id). Create your own class extending ServerPrototype or any of the existing servers and set the application id.');
        }

        return $this->getURL();
    }

    /**
     * Convert server to url string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->URL;
    }

    /**
     * Get application id used in this server.
     *
     * @return string
     */
    public function getApplicationId(): string
    {
        return $this->application_id;
    }

    /**
     * Set application id registered for this server.
     *
     * @param   string  $application_id  Application id (api key).
     */
    public function setApplicationId(string $application_id)
    {
        $this->application_id = $application_id;
    }


}