<?php

namespace Saraf;

use React\Promise\PromiseInterface;
use React\Socket\Connector;

class JsonSnitchClient extends AsyncRequestJson
{
    protected string $proxyURL;
    protected string $baseURL = "";

    public function __construct(string $proxyURL, Connector $connector = new Connector(['verify_peer' => false, 'verify_peer_name' => false]))
    {
        parent::__construct($connector);
        $this->proxyURL = $proxyURL;
    }

    public function setConfig(array $config): static
    {
        if (isset($config['timeout'])) {
            $this->browser = $this->browser->withTimeout($config['timeout']);
        }

        if (isset($config['headers'])) {
            foreach ($config['headers'] as $key => $value) {
                $this->browser = $this->browser->withHeader($key, $value);
            }
        }

        if (isset($config['baseURL'])) {
            $this->baseURL = $config['baseURL'];
        }

        if (isset($config['followRedirects'])) {
            $this->browser = $this->browser->withFollowRedirects($config['followRedirects']);
        }

        return $this;
    }


    public function post(string $path, array|string $body = [], array $headers = [], array $config = []): PromiseInterface
    {
        $headers['X-PROXY-TO'] = $this->baseURL . $path;
        $headers['X-PROXY-CONFIG'] = json_encode($config);
        return parent::post($this->proxyURL, $body, $headers);
    }

    public function put(string $path, array|string $body = [], array $headers = [], array $config = []): PromiseInterface
    {
        $headers['X-PROXY-TO'] = $this->baseURL . $path;
        $headers['X-PROXY-CONFIG'] = json_encode($config);
        return parent::put($this->proxyURL, $body, $headers);
    }

    public function patch(string $path, array|string $body = [], array $headers = [], array $config = []): PromiseInterface
    {
        $headers['X-PROXY-TO'] = $this->baseURL . $path;
        $headers['X-PROXY-CONFIG'] = json_encode($config);
        return parent::patch($this->proxyURL, $body, $headers);
    }

    public function get(string $path, array $params = [], array $headers = [], array $config = []): PromiseInterface
    {
        $headers['X-PROXY-TO'] = $this->baseURL . $path;
        $headers['X-PROXY-CONFIG'] = json_encode($config);
        return parent::get($this->proxyURL, $params, $headers);
    }
}