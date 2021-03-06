<?php

namespace tuneefy\Utils;

use Slim\Http\Request;
use Slim\Http\Response;
use tuneefy\DB\DatabaseHandler;

class ApiStatsMiddleware
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $response = $next($request, $response);

        $method = isset($this->container['api_method']) ? $this->container['api_method'] : null;
        $clientId = isset($this->container['token']) ? $this->container['token']['client_id'] : null;

        if (null !== $clientId) { // If we're using oauth, else it's unecessary
            $db = DatabaseHandler::getInstance(null);
            $db->addApiCallingStat($clientId, $method ?? DatabaseHandler::METHOD_OTHER);
        }

        return $response;
    }
}
