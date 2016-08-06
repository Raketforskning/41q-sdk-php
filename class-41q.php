<?php




class Sdk41q {

  static $client_id = '';
  static $client_secret = '';
  static $lang = 'en';
  static $url = 'http://api.41q.com/v1/';
  static $_previousQuestionCache = null;
  static $_questionCache = null;
  static $_ajaxUrl = null;
  static $_urlExtra = null;

  static function setup($client_id, $client_secret, $lang, $cache = null) {
    self::$client_id = $client_id;
    self::$client_secret = $client_secret;
    self::$lang = $lang;
    self::$_previousQuestionCache = $cache;
  }

  static function request($action, $config) {
    $config = (object)$config;

    $config->response_format = 'json';
    $config->client_id = self::$client_id;
    $config->client_secret = self::$client_secret;
    $config->lang = self::$lang;

    $url = self::$url . $action . '.json';

    // Open connection
    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($config));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($config));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute post
    $result = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $r = new stdClass;
    $r->code = (int)$code;
    $r->result = json_decode($result);

    // Close connection
    curl_close($ch);

    return $r;
  }

  static function question($config) {
    if (self::$_previousQuestionCache) {
      $result = new stdClass;
      $result->code = 200;
      $result->result = self::$_previousQuestionCache;
    } else {
      $result = self::request('questions', $config);
    }

    $data = $result->result;

    if ($result->code == 200) {
      self::$_questionCache = $data;
      return self::render_questions_html($result->result);
    } else {
      return self::render_error_html('The questions could not be loaded. Error code ' . $data->code . '.');
    }
  }

  static function result($config) {
    return self::request('result', $config);
  }

  static function render_questions($config) {
    return self::question($config);
  }

  static function render_questions_html($result) {
    $html = '';

    $html.= '<script type="text/javascript" id="api-41q-widget" class="api-41q-widget">' . "\n";
    $html.= "  var _API_41Q = [];" . "\n";
    $html.= "  _API_41Q.push(['render_questions', " . json_encode($result) . ", " . (self::$_ajaxUrl ? ("'" . self::$_ajaxUrl . "'") : "null") . ", " . (self::$_urlExtra ? ("'" . self::$_urlExtra . "'") : "null") . "]);" . "\n";
    $html.= "  var s = document.createElement('script');" . "\n";
    $html.= "  s.type = 'text/javascript';" . "\n";
    $html.= "  s.async = true;" . "\n";
    $html.= "  s.src = 'http://cdn.41q.com/api/v1.js';" . "\n";
    $html.= "  var embedder = document.getElementById('api-41q-widget');" . "\n";
    $html.= "  embedder.parentNode.insertBefore(s, embedder);" . "\n";
    $html.= "</script>" . "\n";

    return $html;

  }

  static function request_api_details($email, $whitelist, $requester = '') {
    $config = new stdClass;
    $config->email     = $email;
    $config->whitelist = $whitelist;
    $config->requester = $requester;

    self::$lang = 'en';

    return self::request('request-api-details', $config);
  }


  static function render_error_html($result) {
    return $result;
  }

}
