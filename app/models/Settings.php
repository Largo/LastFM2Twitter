<?php

class Settings extends Basemodel {
  public static function getSettings() {
    return Model::factory("Settings")
      ->where('user_id', User::getCurrentUserId())
      ->find_one();
  }

  public function user() {
    return $this->belongs_to('user', 'user_id');
  }

  public function getUser() {
      if ($this->user() == null) {
          return null;
      }

      return $this->user()->find_one();
  }

}
