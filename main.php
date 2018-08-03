<?php

declare(strict_types = 1);
require_once './vendor/autoload.php';

use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Server;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Socket;

// route part
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

// log part
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Run this script, then visit http://localhost:1337/ in your browser.

Amp\Loop::run(function () {
    $sockets = [
        Socket\listen("0.0.0.0:1337"),
        Socket\listen("[::]:1337"),
    ];

    $logger = new Logger('app');
    $logger->pushHandler(new StreamHandler('app.log', Logger::DEBUG));

    $routes = require_once 'router.php';
    $context = new RequestContext('/');
    $matcher = new UrlMatcher($routes, $context);
    
    $server = new Server($sockets, new CallableRequestHandler(function (Request $request) use ($matcher, $logger) {
        try {
            $params = $matcher->match($request->getUri()->getPath());
            if (!empty($params['_controller'])) {
                $controller = new $params['_controller'][0]($request);
                $method = $params['_controller'][1];
                return call_user_func([$controller, $method]);
            } elseif (!empty($params['_handler'])) {
                return $params['handler']($request);
            }
        } catch (ResourceNotFoundException $e) {
            return new Response(Status::NOT_FOUND, ['content-type' => 'text/plain; charset=utf-8'], '404 not found');
        } catch (\Exception $e) {
            return new Response(Status::INTERNAL_SERVER_ERROR, [], '500 internal error');
        }
    }), $logger);

    yield $server->start();

    // Stop the server gracefully when SIGINT is received.
    // This is technically optional, but it is best to call Server::stop().
    Amp\Loop::onSignal(SIGINT, function (string $watcherId) use ($server) {
        Amp\Loop::cancel($watcherId);
        yield $server->stop();
    });
});

function fooHandler(Request $request): Response {
    return new Response(Status::OK, ['content-type' => 'text/plain; charset=utf-8'], 'foo handler');
}