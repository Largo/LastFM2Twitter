<?php

$app->get('/login/twitter', function() use ($app) {
  $config = array(
      "base_url" => $app->config('hosturl') . $app->config('absolutePath'),
      'callback' => $app->config('hosturl') . $app->config('absolutePath') . 'login/twitter',
      "providers" => array (
        "Twitter" => array (
          "enabled" => true,
          "keys"    => array ( "key" => $app->config('twitter_consumer_key'), "secret" => $app->config('twitter_consumer_secret') ),
          //"scope"   => ['email'], // optional
          //"photo_size" => 200, // optional
        )
    ));

    try{
        $hybridauth =  new Hybridauth\Hybridauth( $config );

        $adapter = $hybridauth->authenticate( "Twitter" );

        //Returns a boolean of whether the user is connected with Twitter
        $isConnected = $adapter->isConnected();
        $accessToken = $adapter->getAccessToken();
        $oauth_access_token = $accessToken['access_token'];
        $oauth_access_token_secret = $accessToken['access_token_secret'];


        $userProfile = $adapter->getUserProfile();

        //Inspect profile's public attributes
         //var_dump($userProfile);

         // displayName
         // emailVerified

        // if no email verified => fail, later ask for email in intermediate step
        $request = $app->request();
        User::logout();

        $email = $userProfile->emailVerified;
        $username = $userProfile->displayName;

        if(empty($email)) {
          echo "oops: please signup normally, since your twitter profile does not have an email";
          exit;
        }
        if(empty($username)) {
          echo "oops: please signup normally, since your facebook profile does not have a name";
          exit;
          // redirect to signup page with an alert flash.
        }

        // if email schon vorhanden =>dann login
        if(User::findEmail($email) != null) {
          $user = User::findEmail($email);
        } else {
          $user = Model::factory('User')->create();
          $user->email = $email;
          $user->username = $username;

          $generatePassword = function($length) {
            $chars =  'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.
            '0123456789`-=~!@#$%^&*()_+,./<>?;:[]{}\|';
            $str = '';
            $max = strlen($chars) - 1;

            for ($i=0; $i < $length; $i++)
              $str .= $chars[random_int(0, $max)];

            return $str;
          };

          $user->setPassword($generatePassword(random_int(50,70)));
        }

        $_SESSION['user'] = $user;

        $user->oauth_access_token = $oauth_access_token;
        $user->oauth_access_token_secret = $oauth_access_token_secret;

        $user->save();

        $user->setLoginCookie();

        //Disconnect the adapter
        $adapter->disconnect();
        $app->redirect('/setup');
  }
  catch(\Exception $e){
      echo 'Oops, we ran into an issue! ' . $e->getMessage();
  }
})->name('twitter-login');
