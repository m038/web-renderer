<?php

/**
 * This file is part of the PHP SDK library for the Superdesk Content API.
 *
 * Copyright 2015 Sourcefabric z.u. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.superdesk.org/license
 */

namespace SWP\SuperdeskBridgeBundle\Client;

use GuzzleHttp\Client as BaseClient;
use GuzzleHttp\Exception\TransferException;
use SWP\SuperdeskBridgeBundle\Exception\BridgeException;

/**
 * Request service that implements all method regarding basic request/response
 * handling.
 */
class Client extends BaseClient implements ClientInterface
{
    /**
     * Default values based on Superdesk.
     *
     * @var array
     */
    protected $config = array(
        'scheme' => 'http',
        'host' => 'localhost',
        'port' => 5050,
    );

    /**
     * Default request options.
     *
     * @var array
     */
    protected $options = array(
        'Content-Type' => 'application/json',
    );

    /**
     * Construct method for class.
     *
     * @param array $config Configuration array
     */
    public function setConfig($config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function makeApiCall($endpoint, $queryParameters = null, $options = null)
    {
        try {
            $response = $this->get(
                $this->buildUrl($endpoint, $this->processParameters($queryParameters)),
                $this->processOptions($options)
            );
        } catch (TransferException $e) {
            throw new BridgeException($e->getMessage(), $e->getCode(), $e);
        }

        return (string) $response->getBody();
    }

    /**
     * Returns base url based on configuration.
     *
     * @return string
     */
    private function getBaseUrl()
    {
        return sprintf(
            '%s://%s:%s',
            $this->config['scheme'],
            $this->config['host'],
            $this->config['port']
        );
    }

    /**
     * Builds full url from getBaseUrl method and additional query parameters.
     *
     * @param string $url    Url path
     * @param mixed  $params See http_build_query for possibilities
     *
     * @return string
     */
    private function buildUrl($url, $params)
    {
        $url = sprintf(
            '%s%s?%s',
            $this->getBaseUrl(),
            $url,
            ((!is_null($params)) ? http_build_query($params) : '')
        );

        return $url;
    }

    /**
     * Process request parameters.
     *
     * @param array $params
     *
     * @return array
     */
    private function processParameters($params)
    {
        // TODO: Check if we need something useful here
        // Filter invalid params
        // Convert datatypes

        return $params;
    }

    /**
     * Process options. Default class options will be overridden with the
     * options from the first argument. Via the options key it's possible to
     * override options globally via .yml file.
     *
     * @param array|null $options Guzzle request headers / options
     *
     * @return array()
     */
    private function processOptions($options)
    {
        // Override class defaults
        if (is_array($options)) {
            $options = array_merge($this->options, $options);
        } else {
            $options = $this->options;
        }

        // Add options from config
        if (isset($this->config['options']) && is_array($this->config['options'])) {
            $options = array_merge((array) $options, $this->config['options']);
        }

        return $options;
    }
}
