<?php

namespace Saraf;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Http\Middleware\LimitConcurrentRequestsMiddleware;
use React\Http\Middleware\RequestBodyBufferMiddleware;
use React\Http\Middleware\RequestBodyParserMiddleware;
use React\Http\Middleware\StreamingRequestMiddleware;
use React\Promise\PromiseInterface;
use React\Socket\SocketServer;

class JsonSnitchServer
{
    protected AsyncRequestJson $api;

    public function __construct()
    {
        $this->api = new AsyncRequestJson();
    }

    public function __invoke(string $host = "0.0.0.0", string|int $port = "98981"): void
    {
        $http = new HttpServer(
            new StreamingRequestMiddleware(),
            new LimitConcurrentRequestsMiddleware(100),
            new RequestBodyBufferMiddleware(2 * 1024 * 1024), // 2MB
            new RequestBodyParserMiddleware(),
            [$this, 'server']
        );

        $socket = new SocketServer($host . ':' . $port);
        $http->listen($socket);
    }

    private function server(ServerRequestInterface $request): PromiseInterface|Response
    {
        $method = $request->getMethod();
        $headers = $request->getHeaders();

        if (!isset($headers['X-PROXY-TO']))
            return new Response(451, ['Content-Type' => 'application/json'], json_encode([
                'result' => false,
                'error' => 'X-PROXY-TO header is required'
            ]));

        $body = @$request->getParsedBody() ?? [];
        $query = @$request->getQueryParams() ?? [];

        $url = $headers['X-PROXY-TO'][0];
        if (!isset($headers['X-PROXY-CONFIG'])) {
            $config = json_decode($headers['X-PROXY-CONFIG'][0], true);
        } else {
            $config = [
                "followRedirects" => true,
                "timeout" => 5,
                "headers" => [
                    "Content-Type" => 'application/json'
                ]
            ];
        }

        echo json_encode($url) . PHP_EOL;

        return $this->executeAPICall($method, $url, [...$body, ...$query], $headers, $config);
    }


    private function executeAPICall(
        string       $method,
        string       $url,
        string|array $body,
        array        $headers,
        array        $config
    ): PromiseInterface
    {
        $this->api = $this->api->setConfig($config);
        $request = match ($method) {
            'GET' => $this->api->get($url, $body, $headers),
            'DELETE' => $this->api->delete($url, $body, $headers),
            'POST' => $this->api->post($url, $body, $headers),
            'PUT' => $this->api->put($url, $body, $headers),
            'PATCH' => $this->api->patch($url, $body, $headers)
        };

        return $request->then(function ($response) {
            if (!$response['result'])
                return new Response(504, @$response['headers'] ?? [], @$response['body'] ?? '');

            return new Response($response['code'], $response['headers'], $response['body']);
        });
    }
}