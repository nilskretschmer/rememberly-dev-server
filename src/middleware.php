<?php
use Tuupola\Middleware\HttpBasicAuthentication\PdoAuthenticator;

$container = $app->getContainer();
$settings = $container->get('settings')['db'];

$pdo = new PDO("mysql:host=" . $settings['host'] . ";dbname=" . $settings['dbname'],
        $settings['user'], $settings['pass']);
$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/login",
    "realm" => "Protected",
    "authenticator" => new PdoAuthenticator([
        "pdo" => $pdo,
        "table" => "users",
        "user" => "username",
        "hash" => "passwordhash"
    ])
]));
$app->add(new \Tuupola\Middleware\JwtAuthentication([
    "path" => "/api", /* or ["/api", "/admin"] */
    "attribute" => "decoded_token_data",
    "secret" => getenv('REM_TOKEN_ENV'),
    "algorithm" => ["HS256"],
    "error" => function ($response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);
