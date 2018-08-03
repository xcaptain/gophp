<?php

namespace App\Http\Controllers;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;

class FooController
{
    private $request;

    public function __construct(Request $request) 
    {
        $this->request = $request;
    }

    public function index() : Response 
    {
        return new Response(Status::OK, ['content-type' => 'text/plain; charset=utf-8'], 'FooController@index: ' . (string)$this->request->getUri());
    }
}