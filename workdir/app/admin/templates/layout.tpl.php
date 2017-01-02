<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

  <link rel="stylesheet" href="static/css/default.css" type="text/css" charset="utf-8" />

  <title>Sinonimų žodynas</title>

  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js" type="text/javascript"></script>
  <script src="static/js/jquery.cookie.js" type="text/javascript"></script>
  <script src="static/js/default.js" type="text/javascript"></script>
</head>
<body>
	<?php if($user):
	$count_updates	= $db->query("SELECT COUNT(DISTINCT(`word_id`)) FROM `updates_history` WHERE `user_id`={$db->quote($user['id'])} AND `type`=1;")->fetch(PDO::FETCH_COLUMN);
	$coun_entries	= $db->query("SELECT COUNT(`id`) FROM `updates_history` WHERE `user_id`={$db->quote($user['id'])} AND `type`=2;")->fetch(PDO::FETCH_COLUMN);
	?>
	<div class="wrapper summary">
		<p style="font-weight: bold;"><?=$user['full_name']?> ( <?=$user['email']?> )</p>
		<p>Per šį atsiskaitymo perdiodą jūs sutvarkėte <span class="highlight"><?=$count_updates?></span> žodius, pridėjote <span class="highlight"><?=$coun_entries?></span> sinonimus ir uždirbote <span class="highlight">LTL <?=$count_updates*$user['rate_1']+$coun_entries*$user['rate_2']-131?></span>. (<span class="highlight">LTL <?=$user['rate_1']?></span> per sutvarkytą žodį; <span class="highlight">LTL <?=$user['rate_2']?></span> per pridėtą naują sinonimą)</p>
		<p>Pinigai į sąskaitą pervedami ne dažniau kaip kartą per savaitę. Mažiausia suma <span class="highlight">LTL 100</span>.</p>
	</div>
	<?php endif;?>
  <div class="wrapper body">
  	<?=hp_display_messages()?>

  	<?php if($user):?>
  	<ul class="tab-menu">
  		<?php foreach($controllers as $k => $v):?>
  <li<?=$k == $controller ? ' class="active"' : ''?>><a href="?controller=<?=$k?>"><?=$v?></a></li>
  <?php endforeach;?>

  <li class="right no-tab"><a href="?action=signout">Atsijungti nuo sistemos</a></li>
  </ul>
	<?php endif;?>

	<?=$tpl_body?>
	<div class="clear"></div>
  </div>
</body>
</html>
