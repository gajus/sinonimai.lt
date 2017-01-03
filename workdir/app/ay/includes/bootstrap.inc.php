<?php
require_once __DIR__ . '/../../vendor/autoload.php';

session_start();

define('AY_ROOT', realpath(__DIR__ . '/..'));
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

class Ay_Exception extends Exception {
	public function __construct($message, $code = 0, Exception $previous = NULL) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}";

        exit;
    }
}

require_once AY_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'helpers.inc.php';
require_once ay_path('includes', 'facebook.inc.php');
require_once ay_path('includes', 'project', 'helpers.inc.php');

if(!isset($_POST['ay']))
{
	$_POST['ay']	= array();
}
