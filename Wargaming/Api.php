<?php
/**
 * @package     Wargaming.API
 * @version     1.4.0
 * @author      Artur Stępień (artur.stepien@bestproject.pl)
 * @copyright   Copyright (C) 2015-2021 Artur Stępień, All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Wargaming;

use Exception;
use Wargaming\Language\LanguagePrototype;
use Wargaming\Server\ServerPrototype;

/**
 * Class takes care of accessing and processing Wargaming API requests.
 */
class Api
{
    /**
     * Application ID from Wargaming Developer Room
     * @var   Integer
     */
    protected $application_id;

    /**
     * Language for data retrieved from Wargaming servers (language code).
     *
     * @var   LanguagePrototype
     */
    protected $language;

    /**
     * API server URL. This one depends on server you use.
     *
     * @var   ServerPrototype
     */
    protected $server;

    /**
     * CURL connection handle
     *
     * @var
     */
    protected $connection = null;

    /**
     * Should Curl verify SSL connection safety?
     *
     * @var bool
     */
    protected $ssl_verification = true;

    /**
     * Create Wargaming API instance
     *
     * @param   string             $application_id  Application ID obtainable from Wargaming websites
     * @param   LanguagePrototype  $language        Language of data (mostly errors).
     * @param   ServerPrototype    $server          Server/Cluster that should be used as source.
     */
    public function __construct(string $application_id, LanguagePrototype $language, ServerPrototype $server)
    {
        $this->application_id = $application_id;
        $this->language       = $language;
        $this->server         = $server;
    }

    /**
     * Return data from Wargaming servers. Documentation for all API methods can be found here: https://eu.wargaming.net/developers/api_reference
     *
     * @param   string   $namespace    Namespace of data you want to get(for example wgn/servers/info or wot/account/list )
     * @param   array    $options      All the options required for this field to work except application_id and language (for example array('fields'=>'server','game'=>'wot'))
     * @param   boolean  $assoc        If set to true function will return associative array instead of object/array of objects.
     * @param   string   $ETag         ETag string to validate data (without quotation marks). If in response server will return HTTP 304 Not Modified status method will return boolean TRUE. That means that data did not changed. Documentation: https://eu.wargaming.net/developers/documentation/guide/getting-started/#etag
     * @param   boolean  $HTTPHeaders  If this parameter is set to TRUE, method will return also HTTP headers sent with response in format: array('headers'=>array(), 'data'=>array()).
     *
     * @return  mixed
     *
     * @throws Exception
     */
    public function get(
        string $namespace,
        array $options = [],
        bool $assoc = false,
        string $ETag = null,
        bool $HTTPHeaders = false
    ) {

        // Build query url
        $url = 'https://' . $this->server . '/' . $namespace . '/?application_id=' . $this->application_id . '&language=' . $this->language . '&' . http_build_query($options);

        // Get response
        $buff = $this->getUrlContents($url, $ETag, $HTTPHeaders);

        // Wrong response (probably wrong server URL)
        if ($buff['data'] === false) {

            throw new \Exception('Wrong server or namespace.', 404);

            // Data did not changed on server
        } else {
            if ($buff['data'] === true) {

                // If HTTPHeaders parameter is set, return assocative array containing data and headers
                if ($HTTPHeaders) {
                    return $buff;
                }

                // Return plain data
                return $buff['data'];

                // New data available
            } else {

                // Convert response to object or array depending on $assoc param
                $response = json_decode($buff['data'], $assoc);

                // User chose object format
                if (is_object($response)) {

                    // Servers return correct data
                    if ($response->status === 'ok') {

                        // If HTTPHeaders parameter is set, return assocative array containing also headers
                        if ($HTTPHeaders) {
                            return ['data' => $response->data, 'headers' => $buff['headers']];

                            // Return plain data
                        } else {
                            return $response->data;
                        }

                        // Api server return error
                    } elseif ($response->status === 'error') {

                        // Create exception
                        throw new \Exception($this->translateError($response->error->message,
                            $namespace), $response->error->code);

                        // Page not found
                    } else {

                        throw new \Exception('You set wrong server or namespace.',
                            404);
                    }

                    // User chose array format
                } elseif (is_array($response)) {

                    // Servers return correct data
                    if ($response['status'] === 'ok') {

                        // If HTTPHeaders parameter is set, return assocative array containing also headers
                        if ($HTTPHeaders) {

                            return ['data' => $response['data'], 'headers' => $buff['headers']];

                            // Return plain data
                        } else {

                            return $response['data'];
                        }

                        // Api server return error
                    } elseif ($response['status'] === 'error') {

                        // Create exception
                        throw new \Exception($this->translateError($response['error']['message'],
                            $namespace), $response['error']['code']);

                        // Page not found
                    } else {

                        throw new \Exception('You set wrong server or namespace.',
                            404);
                    }

                    // Unsupported response format
                } else {

                    throw new \Exception('Wrong response format.', 502);
                }
            }
        }

        return false;
    }

    /**
     * Returns data from url provided in $url. This function use same curl handle for each request
     *
     * @param   String   $url          Data url to process
     * @param   String   $ETag         ETag HTTP header value (without quotation marks) to be used for request.
     * @param   boolean  $HTTPHeaders  If this parameter is set to TRUE, method will return also HTTP headers sent with response in format: array('headers'=>array(), 'data'=>'').
     *
     * @return   mixed   Array if success, FALSE on failure, TRUE if data did not changed (only when ETag is used).
     *
     * @throws Exception
     */
    protected function getUrlContents(
        string $url,
        string $ETag = null,
        bool $HTTPHeaders = false
    ): array {

        // Connection needs to be created
        if (!is_resource($this->connection)) {

            // Initialise connection
            $this->connection = curl_init();

            // Make curl return response instead of printing it
            curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, $this->ssl_verification);
            if ($HTTPHeaders) {
                curl_setopt($this->connection, CURLOPT_HEADER, true);
            }
        }

        // User provided ETag so use it
        if (!is_null($ETag)) {
            curl_setopt($this->connection, CURLOPT_HTTPHEADER,
                [
                    'If-None-Match: "' . $ETag . '"',
                ]);
        }

        // Set connection URL
        curl_setopt($this->connection, CURLOPT_URL, $url);

        // Get response
        $buffer = curl_exec($this->connection);

        // Check if curl did not return erro
        $error = curl_error($this->connection);

        if ($error !== '') {
            throw new \Exception('(Curl) ' . $error, curl_errno($this->connection));
        }

        // Prepare headers
        if ($HTTPHeaders) {

            // Split response headers and response body
            list($headers, $body) = explode("\r\n\r\n", $buffer, 2);

            // Process headers
            $headers = explode("\r\n", $headers);

            $tmp = [];
            foreach ($headers as $header) {
                $row = explode(': ', $header, 2);
                if (count($row) === 1) {
                    $tmp[$row[0]] = '';
                } else {
                    $tmp[$row[0]] = $row[1];
                }
            }

            // Set formatted headers
            $headers = $tmp;
        } else {
            $headers = [];
            $body    = $buffer;
        }

        // Get response status code
        $status_code = curl_getinfo($this->connection, CURLINFO_HTTP_CODE);

        // If data did not changed, return TRUE
        if ((int)$status_code === 304) {
            $body = true;
        }

        return ['headers' => $headers, 'data' => $body];
    }

    /**
     * Returns human readable error message.
     *
     * @param   string  $error      The error to translate.
     * @param   string  $namespace  Namespace passed into get() function.
     *
     * @return string
     */
    protected function translateError(string $error, $namespace = null): string
    {

        $messages = [
            'SEARCH_NOT_SPECIFIED'           => 'Parameter <b>search</b> not specified.',
            'NOT_ENOUGH_SEARCH_LENGTH'       => '<b>Search</b> parameter is not long enough. Minimum length: 3 characters.',
            'ACCOUNT_ID_LIST_LIMIT_EXCEEDED' => 'Limit of passed-in <b>account_id</b> IDs exceeded. Maximum: 100.',
            'METHOD_NOT_FOUND'               => 'Invalid API method <b>' . $namespace . '</b>.',
            'METHOD_DISABLED'                => 'Specified method is disabled.',
            'APPLICATION_IS_BLOCKED'         => 'Application is blocked by the administration.',
            'INVALID_APPLICATION_ID'         => 'Invalid <b>application_id</b>.',
            'INVALID_IP_ADDRESS'             => 'Invalid IP-address for the server application.',
            'REQUEST_LIMIT_EXCEEDED'         => 'Request limit is exceeded.',
            'SOURCE_NOT_AVAILABLE'           => 'Data source is not available.',
            'INVALID_FIELDS'                 => 'Invalid fields specified in <b>fields</b> parameter.',
            'AUTH_CANCEL'                    => 'Application authorization cancelled by user.',
            'AUTH_EXPIRED'                   => 'User authorization timed out.',
            'AUTH_ERROR'                     => 'Authentication error.',
            'MEMBER_ID_LIST_LIMIT_EXCEEDED'  => 'Limit of passed-in <b>member_id</b> IDs exceeded. Maximum: 100.',
            'CLAN_ID_LIST_LIMIT_EXCEEDED'    => 'Limit of passed-in <b>clan_id</b> IDs exceeded. Maximum: 100.',
            'INCOMPATIBLE_MODULE_IDS'        => 'Specified modules are incompatible in a single configuration.',
            'ACCOUNT_ID_NOT_SPECIFIED'       => 'Required parameter <b>account_id</b> was not specified.',
            'TYPE_NOT_SPECIFIED'             => 'Required parameter <b>type</b> was not specified.',
            'INVALID_TYPE'                   => 'Invalid value set in <b>type</b> parameter.',
            'RATINGS_NOT_FOUND'              => 'No rating details for specified date.',
            'RANK_FIELD_NOT_SPECIFIED'       => 'Required parameter <b>rank_field</b> not specified.',
            'INVALID_RANK_FIELD'             => 'Invalid value set in <b>rank_field</b> parameter.',
            'INVALID_CLAN_ID'                => 'Invalid value set in <b>clan_id</b> parameter. Clan with that ID probably don\'t exist.',
            'CLAN_ID_NOT_SPECIFIED'          => 'Required parameter <b>clan_id</b> was not specified.',
            'INVALID_LIMIT'                  => 'Invalid value set in <b>limit</b> parameter.',
        ];

        if (isset($messages[$error])) {
            return $messages[$error];
        }

        if (stripos($error, '_NOT_SPECIFIED')) {
            return 'Required field <b>' . strtolower(str_ireplace('_NOT_SPECIFIED',
                    '', $error)) . '</b> is not specified.';
        }

        if (stripos($error, '_NOT_FOUND')) {
            return 'Data for <b>' . strtolower(str_ireplace('_NOT_FOUND', '',
                    $error)) . '</b> not found.';
        }

        if (stripos($error, '_LIST_LIMIT_EXCEEDED')) {
            return 'Limit of passed-in identifiers in the <b>' . strtolower(str_ireplace('_LIST_LIMIT_EXCEEDED',
                    '', $error)) . '</b> exceeded.';
        }

        if (stripos($error, 'INVALID_')) {
            return 'Specified field value <b>' . strtolower(str_ireplace('INVALID_',
                    '', $error)) . '</b> is not valid.';
        }

        return $error;
    }

    /**
     * Set CURL verification of connection safety (eg. certificates).
     *
     * @param   bool  $state
     */
    public function setSSLVerification(bool $state)
    {
        $this->ssl_verification = $state;
    }
}