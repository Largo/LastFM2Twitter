<?php
  require 'composer_modules/autoload.php';
  $app = new \Slim\Slim([
  	'mode' => 'production',
  	'view' => new \Slim\Views\Twig(),
  	'templates.path' => 'app/views',
  ]);
  require 'app/config/config.production.php';

  foreach (glob("app/models/*.php") as $filename) {
  	require $filename;
  }

  foreach (glob("app/routes/*.php") as $filename) {
  	require $filename;
  }

  function getLastPlayedSong($username, $apiKey) {
        $url = "http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=" . $username .
        "&api_key=" . $apiKey ."&format=json";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $rawData = curl_exec($ch);
        curl_close($ch);
        if(!$rawData){
            return "";
        }
        $data = json_decode($rawData);
        $recenttracks = $data->recenttracks;

        $track = $recenttracks->track[0];
        $artistData = $track->artist;
        $artist = $artistData->{'#text'};
        $track = $track->name;

        return "$artist — $track";
  }

  $settingsItems = Settings::find_many();
  if(count($settingsItems) > 9000) {
    die('too many people!!!');
  }

  foreach ($settingsItems as $key => $setting) {
    sleep(0.01); // safety net so we never hit the twitter api to hard.
    $user = $setting->getUser();
    $settings = array(
        'oauth_access_token' => $user->oauth_access_token,
        'oauth_access_token_secret' => $user->oauth_access_token_secret,
        'consumer_key' => $app->config('twitter_consumer_key'),
        'consumer_secret' => $app->config('twitter_consumer_secret'),
        'lastfm_username' => $setting->lastfmname,
        'lastfm_apikey' => $app->config('lastfm_apikey')
    );

    $url = 'https://api.twitter.com/1.1/account/update_profile.json';
    $requestMethod = 'POST';

    $songname = getLastPlayedSong($settings["lastfm_username"], $settings["lastfm_apikey"]);

    $newTwitterName = $setting->twittertext . ' ' . $songname;

    if($user->lastTwitterName != $newTwitterName) {

      $postfields = array(
        'name' => $newTwitterName, // 50
      );
      $twitter = new TwitterAPIExchange($settings);
      $twitter->buildOauth($url, $requestMethod)
        ->setPostfields($postfields)
        ->performRequest();
    }

    $user->lastTwitterName = $newTwitterName;
    $user->save();
  }
