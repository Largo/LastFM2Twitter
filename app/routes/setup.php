<?php

$app->get('/setup', function() use ($app) {
  if(!User::isLoggedIn()) {
    die('Not logged in');
  }

  $setting = Settings::getSettings();

  $app->view->appendData([
    'setting' => $setting
  ]);
  $app->render('setup.twig');
});

$app->post('/setup', function() use ($app) {
  if(!User::isLoggedIn()) {
    die('Not logged in');
  }

  $request = $app->request;
  $lastfmname = $request->post('lastfmname');
  $twittertext = $request->post('twittertext');
  $searchForSetting = Settings::factory()->where('user_id', User::getCurrentUserId())->find_one();
  if(!empty($searchForSetting)){
    $setting = $searchForSetting;
  } else {
    $setting = Settings::create();
  }
  $setting->user_id = User::getCurrentUserId();
  $setting->lastfmname = $lastfmname;
  $setting->twittertext = $twittertext;
  $setting->save();

  $app->view->appendData([
    'setting' => $setting,
    'wasSaved' => true
  ]);
  $app->render('setup.twig');
});
