<?php
require_once __DIR__ . '/vendor/autoload.php';

session_start();

$fb = new \Facebook\Facebook([
  'app_id' => 'YOUR_APP_ID',
  'app_secret' => 'YOUR_APP_SECRET',
  'default_graph_version' => 'v10.0',
]);

$helper = $fb->getRedirectLoginHelper();

try {
  $accessToken = $helper->getAccessToken();
} catch(\Facebook\Exceptions\FacebookResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (!isset($accessToken)) {
  if ($helper->getError()) {
    header('HTTP/1.0 401 Unauthorized');
    echo "Error: " . $helper->getError() . "\n";
    echo "Error Code: " . $helper->getErrorCode() . "\n";
    echo "Error Reason: " . $helper->getErrorReason() . "\n";
    echo "Error Description: " . $helper->getErrorDescription() . "\n";
  } else {
    header('HTTP/1.0 400 Bad Request');
    echo 'Bad request';
  }
  exit;
}

$_SESSION['fb_access_token'] = (string) $accessToken;

try {
  $response = $fb->get('/me?fields=id,name,picture.type(large)', $accessToken);
  $user = $response->getGraphUser();
  
  $_SESSION['user_name'] = $user['name'];
  $_SESSION['user_picture'] = $user['picture']['url'];

  header("Location: index.php"); // Chuyển hướng đến trang success.php sau khi lấy thông tin người dùng
  exit;

} catch(\Facebook\Exceptions\FacebookResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}
?>
