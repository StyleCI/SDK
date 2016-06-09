<?php

/*
 * This file is part of the StyleCI SDK.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StyleCI\SDK;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * This is the client class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class Client
{
    /**
     * The base url.
     *
     * @var string
     */
    const BASE_URL = 'https://api.styleci.io/';

    /**
     * The guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * Create a new client instance.
     *
     * @param \GuzzleHttp\ClientInterface|null $client
     *
     * @return void
     */
    public function __construct(ClientInterface $client = null)
    {
        if ($client) {
            $this->client = $client;
        } else {
            $stack = HandlerStack::create();
            $stack->push(Middleware::retry(function ($retries, RequestInterface $request, ResponseInterface $response = null, TransferException $exception = null) {
                return $retries < 3 && ($exception instanceof ConnectException || ($response && $response->getStatusCode() >= 500));
            }, function ($retries) {
                return (int) pow(2, $retries) * 1000;
            }));
            $this->client = new GuzzleClient([
                'base_uri' => static::BASE_URL,
                'handler'  => $stack,
                'headers'  => ['Accept' => 'application/json', 'User-Agent' => 'styleci-sdk/1.1'],
            ]);
        }
    }

    /**
     * Get the fixers.
     *
     * @return array
     */
    public function fixers()
    {
        return $this->get('fixers');
    }

    /**
     * Get the presets.
     *
     * @return array
     */
    public function presets()
    {
        return $this->get('presets');
    }

    /**
     * Validate the given config.
     *
     * @param string $config
     *
     * @return array
     */
    public function validate($config)
    {
        return $this->get('validate', ['query' => ['config' => $config]]);
    }

    /**
     * Send a get request, and parse the result as json.
     *
     * @param string $uri
     *
     * @return array
     */
    protected function get($uri)
    {
        $response = $this->client->request('GET', $uri);

        return json_decode($response->getBody(), true);
    }
}
