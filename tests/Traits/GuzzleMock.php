<?php

namespace Telegram\Bot\Tests\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Guzzle\Http\Exception\RequestException;
use Illuminate\Support\Collection;
use Telegram\Bot\HttpClients\GuzzleHttpClient;

trait GuzzleMock
{
    /**
     * This collection contains a history of all requests and responses
     * sent using the client.
     *
     * @var Collection
     */
    protected $history;

    /**
     *
     * @param array $responsesToQueue
     *
     * @return GuzzleHttpClient
     */
    public function getGuzzleHttpClient(array $responsesToQueue = [])
    {
        $client = $this->createClientWithQueuedResponse($responsesToQueue);

        return new GuzzleHttpClient($client);
    }

    /**
     * @param array $responsesToQueue
     *
     * @return Client
     */
    protected function createClientWithQueuedResponse(array $responsesToQueue)
    {
        $this->history = collect();
        $handler = HandlerStack::create(new MockHandler($responsesToQueue));
        $handler->push(Middleware::history($this->history));

        return new Client(['handler' => $handler]);
    }

    /**
     * @param array|bool $data
     * @param int        $status_code
     * @param array      $headers
     *
     * @return Response
     */
    public function makeFakeServerResponse($data, $status_code = 200, $headers = [])
    {
        return new Response(
            $status_code,
            $headers,
            json_encode([
                "ok"     => true,
                "result" => $data,
            ])
        );
    }

    public function makeFakeInboundUpdate(array $data, $status_code = 200, $headers = [])
    {
        return new Response(
            $status_code,
            $headers,
            json_encode($data)
        );
    }


    protected function makeFakeServerErrorResponse($error_code, $description, $status_code = 200, $headers = [])
    {
        return new Response(
            $status_code,
            $headers,
            json_encode([
                "ok"          => false,
                "error_code"  => $error_code,
                "description" => "$description",
            ])
        );
    }

//    protected function makeFakeExceptionResponse($text, $uri)
//    {
//        return new RequestException($text, new Request('GET', $uri));
//    }

}