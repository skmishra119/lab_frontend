<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'config/db_config.php';

$app = new \Slim\App;

/*$app->get('/', function() {
  return 'Hello World';
});

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});*/

require_once('app/api/common_calls.php');
require_once('app/api/users/index.php');

$app->run();
