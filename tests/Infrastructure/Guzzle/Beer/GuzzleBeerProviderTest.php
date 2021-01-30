<?php

namespace App\Tests\Infrastructure\Guzzle\Beer;

use App\Domain\Model\Beer\BeerProvider;
use App\Domain\Model\Beer\BeerProviderException;
use App\Infrastructure\Guzzle\Beer\GuzzleBeerProvider;
use App\Tests\Domain\Model\Beer\BeerProviderTest;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class GuzzleBeerProviderTest extends BeerProviderTest
{
    public function testCatchErrors(): void
    {
        $provider = $this->getBeerProvider();

        $this->expectException(BeerProviderException::class);
        $provider->findByFood('error');
    }

    public function getBeerProvider(): BeerProvider
    {
        $handler = function (RequestInterface $request) {
            $data = [];

            $uri = $request->getUri()->getQuery();

            $match = preg_match('/food\=error/', $uri);
            if ($match > 0) {
                return new FulfilledPromise(new Response(500));
            }

            $match = preg_match('/food=foo/', $uri);
            if ($match > 0) {
                $data = [
                    [
                        'id' => 1,
                        'name' => 'Buff',
                        'description' => "Homer Simpson's favorite beer",
                    ],
                    [
                        'id' => 4,
                        'name' => 'Beeeeer',
                        'description' => 'Unknow beer',
                    ],
                ];
            }

            $match = preg_match('/food=Bravas/', $uri);
            if ($match > 0) {
                $data = [
                    [
                        'id' => 2,
                        'name' => 'Mahou',
                        'description' => 'La cerveza que gusta en Madrid',
                    ],
                ];
            }

            return new FulfilledPromise(new Response(200, [], json_encode($data, JSON_THROW_ON_ERROR)));
        };

        $handlerStack = HandlerStack::create($handler);

        $client = new Client(['handler' => $handlerStack]);

        return new GuzzleBeerProvider($client);
    }
}
