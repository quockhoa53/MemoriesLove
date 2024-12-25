<?php
require_once __DIR__ . '/vendor/autoload.php';

session_start();

$fb = new \Facebook\Facebook([
  'app_id' => '467047476112105', // Thay bằng App ID của bạn
  'app_secret' => '467047476112105', // Thay bằng App Secret của bạn
  'default_graph_version' => 'v10.0',
]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; // Quyền mà bạn muốn yêu cầu từ người dùng
$loginUrl = $helper->getLoginUrl('http://localhost:8080/web_memories/fb-callback', $permissions);

echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
?>