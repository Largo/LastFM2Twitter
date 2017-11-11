<?php

$app->configureMode('production', function () use ($app) {
    $app->config(array(
        'log.enabled' => true,
        'debug' => false,
        'cache' => true,
        'hosturl' => 'http://mydomain.com',
        'absolutePath' => '/lastfmtwitter', // if this is not running in a subdomain write /
        'twitter_consumer_key' => "",
        'twitter_consumer_secret' => "",
        'lastfm_apikey' => ""
    ));

    ORM::configure('mysql:host=localhost;dbname=lastfmtwitter;charset=utf8mb4');
    ORM::configure('username', 'USERNAME HERE');
    ORM::configure('password', 'PASSWORD HERE');
	  ORM::configure('logging', false);
});
