<?php

namespace Rememberly\Authentication;

use \Firebase\JWT\JWT;

class TokenManager
{
    private $settings;
    public function __construct($settings)
    {
        $this->settings = $settings;
    }
    public function createUserToken($user_id, $username, $todolistPermissions, $noticesPermissions, $androidAppID)
    {
        $iat = time();
        $exp = time() + 7200; // Token expires after 2 Hours
  // TODO: Add REG_ID from Android
  $settingsArray = $this->settings->get('settings'); // get settings array.
  $token = JWT::encode(
      ['user_id' => $user_id, 'username' => $username, 'todolistPermissions' => $todolistPermissions,
  'noticesPermissions' => $noticesPermissions, 'androidAppID' => $androidAppID, 'iat' => $iat, 'exp' => $exp],
   $this->settings['jwt']['secret'],
      "HS256"
  );
        return $token;
    }
}
