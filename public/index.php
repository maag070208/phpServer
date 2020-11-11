<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/config/db.php';
require '../src/settings.php';
$app = new \Slim\App;

function utf8ize( $mixed ) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
}

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});



require '../src/route/login.php';
require '../src/route/reservation.php';
require '../src/route/Retailer.php';
require '../src/route/Locations.php';
require '../src/route/Employees.php';
require '../src/route/Clients.php';
require '../src/route/Services.php';
require '../src/route/Additionals.php';
require '../src/route/Promo.php';
require '../src/route/Sales.php';
require '../src/route/User.php';





$app->run();
