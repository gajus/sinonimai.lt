<?php
require_once __DIR__ . '/../../vendor/autoload.php';

session_start();

$locator = new \Gajus\Director\Locator('http://' . $_SERVER['HTTP_HOST'] . '/');

define('AY_DOMAIN', $locator->url());

define('AY_REDIRECT_REFERRER', 1);

define('AY_MESSAGE_NOTICE', 1);
define('AY_MESSAGE_SUCCESS', 2);
define('AY_MESSAGE_ERROR', 3);

define('AY_DATE_FORMAT', 'M j, Y H:i');

$db = new PDO(
  'mysql:dbname=' . getenv('DATABASE_NAME') . ';host=' . getenv('DATABASE_HOST'),
  getenv('DATABASE_USER'),
  getenv('DATABASE_PASSWORD'),
  [
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';"
  ]
);

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

require_once __DIR__ . '/helpers.inc.php';
require_once __DIR__ . '/facebook.inc.php';
require_once __DIR__ . '/project/helpers.inc.php';

if(!isset($_POST['ay']))
{
	$_POST['ay']	= array();
}
