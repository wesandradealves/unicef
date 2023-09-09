<?php

/**
 * @file
 * Script to switch code.
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

require '../vendor/autoload.php';

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;

// Credentials (application) - from ddev's account.
$key_id = "60e04662-d7a7-490c-86e5-98ce6c3f69ba";
$secret = "77NEedK8JIcSVT5sAUp5aeGXn54IAAKE7oeNWK3Kmmk=";

if (count($argv) != 3) {
  echo "missing arguments. Format: php script-cloud-switch-code.php giturl existingbranch \n";
  exit;
}
$arg1 = $argv[1];
$arg2 = $argv[2];

$arg1_data = explode("@", $arg1);
$appname = $arg1_data[0];

echo "Using app - $appname, branch - $arg2, repo - $arg1\n";

$key = new Key($key_id, $secret);

$middleware = new HmacAuthMiddleware($key);

$stack = HandlerStack::create();
$stack->push($middleware);

$client = new Client([
  'handler' => $stack,
]);

try {
  $response = $client->get('https://cloud.acquia.com/api/applications');
}
catch (ClientException $e) {
  print $e->getMessage();
  $response = $e->getResponse();
}
$body = (string) $response->getBody();
$respose_array = json_decode($body);

$app_array = $respose_array->_embedded->items;
if (!$app_array) {
  echo "Unable to fetch applications\n";
}
foreach ($app_array as $app) {
  if (strtolower($app->name) == strtolower($appname)) {
    $app_uuid = $app->uuid;
    try {
      $response = $client->get('https://cloud.acquia.com/api/applications/' . $app_uuid . '/environments');
    }
    catch (ClientException $e) {
      print $e->getMessage();
      $response = $e->getResponse();
    }

    $env_json_data = (string) $response->getBody();
    $respose_array = json_decode($env_json_data);

    $env_array = $respose_array->_embedded->items;
    if (!$env_array) {
      echo "Unable to fetch environments\n";
    }
    foreach ($env_array as $env) {
      if ($env->vcs->url == $arg1 && $arg2 == $env->vcs->path) {
        $env_id = $env->id;

        try {
          $response = $client->post('https://cloud.acquia.com/api/environments/' . $env_id . '/code/actions/switch', ['json' => ['branch' => 'tags/WELCOME']]);
        }
        catch (ClientException $e) {
          print $e->getMessage();
          $response = $e->getResponse();
        }
        $response_code = $response->getStatusCode();
        $response_code_switch = (string) $response->getBody();
        $response_code_switch_array = json_decode($response_code_switch);

        if ($response_code == 202) {
          echo "code switch accepted \n";
        }
        else {
          echo "code switch failed \n";
          print_r($response_code_switch_array);
        }
      }
    }
  }
}

echo "done\n";
