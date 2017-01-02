<?php
require_once ay_path('includes', 'facebook', 'facebook.php');

header('P3P: CP="CAO PSA OUR"');

define('FACEBOOK_APP_ID', getenv('FACEBOOK_APP_SECRET'));
define('FACEBOOK_APP_SECRET', getenv('FACEBOOK_APP_SECRET'));

$user = FALSE;

$facebook = new Facebook([
	'appId' => FACEBOOK_APP_ID,
	'secret' => FACEBOOK_APP_SECRET,
	'fileUpload' => TRUE,
]);

$authenticate_user	= function($params = array()) use ($facebook)
{
	if($facebook->getUser())
	{
		return;
	}

	$redirect_uri		= AY_TAB_URL;

	if(!empty($params))
	{
		$url			= parse_url($redirect_uri);

		$query_array	= array();

		parse_str($url['query'], $query_array);

		$url['query']	= http_build_query(array_merge($query_array, array('app_data' => $params)));

		$redirect_uri	= http_build_url($url);
	}

	$login_url	= $facebook->getLoginUrl(array('scope' => 'email', 'redirect_uri' => $redirect_uri));

	echo '
		<noscript>javascript must be enabled.</noscript>
		<script type="text/javascript">top.location.href = \'' . $login_url . '\';</script>
	';

	exit;
};

$signed_request 	= $facebook->getSignedRequest();

if(!empty($_GET['action']))
{
	switch($_GET['action'])
	{
		case 'deauthorize':

			$db->exec("DELETE FROM `users` WHERE `facebook_id`={$db->quote($signed_request['user_id'])};");

			exit;

			break;
	}
}

define('AY_TAB_URL', 'http://sinonimai.lt/');

$user		= $facebook->getUser();

if($user)
{
	try
	{
		$user_profile	= $facebook->api('/me', 'get', array('fields' => 'id,first_name,last_name,email,gender'));

		$user			= $db->query("SELECT `id` FROM `users` WHERE `facebook_id`={$db->quote($user_profile['id'])};")->fetch(PDO::FETCH_ASSOC);

		$data	= [
			'facebook_id' => $user_profile['id'],
			'first_name' => $user_profile['first_name'],
			'last_name' => $user_profile['last_name'],
			'email' => $user_profile['email'],
			'sign_in_timestamp' => time(),
			'gender' => $user_profile['gender'] == 'male' ? 1 : 0
		];

		if($user)
		{
			$db->exec("UPDATE `users` SET " . ay_build_query($data) . " WHERE `id`={$db->quote($user['id'])};");
		}
		else
		{
			$data['sign_up_timestamp']		= time();

			$db->exec("INSERT INTO `users` SET " . ay_build_query($data) . ";");

			$user['id'] = $db->lastInsertId();
		}

		$user = $db->query("SELECT * FROM `users` WHERE `id`={$db->quote($user['id'])};")->fetch(PDO::FETCH_ASSOC);

		$_SESSION['ay']['auth']				= $user;
	}
	catch(FacebookApiException $e)
	{
		ay_redirect(AY_DOMAIN);
	}
}
else
{
	unset($_SESSION['ay']['auth']);
}
