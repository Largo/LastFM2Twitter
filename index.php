<?php

require 'composer_modules/autoload.php';

$app = new \Slim\Slim([
	'mode' => 'production',
	'view' => new \Slim\Views\Twig(),
	'templates.path' => 'app/views',
]);
header('Content-Type: text/html; charset=utf-8');
ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");

require 'app/config/config.production.php';

foreach (glob("app/models/*.php") as $filename) {
	require $filename;
}

foreach (glob("app/routes/*.php") as $filename) {
	require $filename;
}

if(!isset($_SESSION)){
  session_start();
}

$view = $app->view;

$view->parserOptions = array (
	'charset' => 'utf-8',
	'cache' => $app->config('cache'),
	'auto_reload' => true,
	'strict_variables' => false,
	'autoescape' => true
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$app->view->appendData([
  'absolutePath' => $app->config('absolutePath'),
]);

$app->get('/', function() use ($app) {
  $app->render('/index.twig');
});

$app->run();
