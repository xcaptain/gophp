<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use App\Http\Controllers\FooController;

$routes = new RouteCollection();
$routes->add('foo', new Route('/foo', array('_controller' => [App\Http\Controllers\FooController::class, 'index'])));

return $routes;