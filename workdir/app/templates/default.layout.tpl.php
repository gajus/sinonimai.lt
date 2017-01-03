<!DOCTYPE html>
<html lang="lt">
    <head>
    	<meta name="google-site-verification" content="IGj2KPkwgwVY6tFlxdvoM5CCgLp5r_jlf2aRvQfzcEI" />

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<base href="http://sinonimai.lt/" />
		<link rel="icon" href="public/img/favicon.gif" type="image/gif" />
		<link rel="stylesheet" href="public/css/default.css" type="text/css" />



		<meta property="og:title" content="Sinonimų Žodynas"/>
	    <meta property="og:type" content="website"/>
	    <meta property="og:url" content="http://sinonimai.lt"/>
	    <meta property="og:image" content="http://sinonimai.lt/public/img/og-thumbnail.jpg"/>
	    <meta property="og:site_name" content="Sinonimų Žodynas"/>
	    <meta property="fb:admins" content="612460713"/>
	    <meta property="fb:app_id" content="257825524265543"/>
	    <meta property="og:locale" content="lt_LT"/>
	    <meta property="og:description"
	          content="Sinonimų žodynas yra gyvosios kalbos turtinimo projektas, skatinantis domėtis turimais kalbos turtais, jais naudotis ir juos kurti, įtraukti visuomenę, ypatingai jaunimą, į lietuvių kalbos puoselėjimo, jos vartojimo, aktyvinimo kūrybinę veiklą."/>

		<script type="text/javascript" src="http://apis.google.com/js/plusone.js"></script>


		<title><?=empty($title) ? 'Sinonimų žodynas'. (!empty($template['browser-title']) ? ' – ' . $template['browser-title'] : '') : $title?></title>

		<script type="text/javascript">var _kmq = _kmq || [];
		var _kmk = _kmk || 'dabda14aa4856859522a386877cedd4c7d0e5e13';
		function _kms(u){
		  setTimeout(function(){
		    var d = document, f = d.getElementsByTagName('script')[0],
		    s = d.createElement('script');
		    s.type = 'text/javascript'; s.async = true; s.src = u;
		    f.parentNode.insertBefore(s, f);
		  }, 1);
		}
		_kms('//i.kissmetrics.com/i.js');
		_kms('//doug1izaerwt3.cloudfront.net/' + _kmk + '.1.js');
		</script>
    </head>
    <body>

		<div id="header">
			<a href="http://sinonimai.lt/" class="logo" title="Sinonimų žodynas"></a>

			<?php if($user):?>
			<div class="connected">
				<p>Sveika<?php if($user['gender'] == 1):?>s<?php endif;?>, <?=$user['first_name']?> sugrįž<?=$user['gender'] == 1 ? 'ęs' : 'usi'?>!</p>
				<?php
				$suggestions	= $db->query("
				SELECT
					`s1`.`entry_timestamp`,
					`s1`.`status`,
					`w1`.`word` `word`,
					`w1`.`word` `suggestion`
				FROM
					`suggestions` `s1`
				INNER JOIN
					`words` `w1`
				ON
					`w1`.`word_id` = `s1`.`word_id`
				INNER JOIN
					`words` `w2`
				ON
					`w2`.`word_id` = `s1`.`suggestion_id`
				WHERE
					`s1`.`user_id`={$db->quote($_SESSION['ay']['auth']['id'])}
				ORDER BY
					`s1`.`entry_timestamp` DESC
				")->fetchAll(PDO::FETCH_ASSOC);

				$approved	= count(array_filter($suggestions, function($e){ return $e['status'] == 1; }));
				?>
				<p>
				<?php if(count($suggestions) == 0):?>
				Kol kas jūs nepasiūlėte sinonimų.
				<?php elseif(count($suggestions) == 1):?>
				Kol kas jūs pasiūlėte 1 sinonimą, kuris <?=$approved ? 'jau yra patvirtintas' : 'dar nėra patvirtintas'?>.
				<?php elseif(count($suggestions) < 10):?>
				Kol kas jūs pasiūlėte <?=count($suggestions)?> sinonimus iš kurių <?=$approved?> buvo pavirtint<?=$approved == 1 ? 'as' : 'i'?>.				
				<?php else:?>
				Kol kas jūs pasiūlėte <?=count($suggestions)?> sinonimų iš kurių <?=$approved?> buvo pavirtint<?=$approved == 1 ? 'as' : 'i'?>. 
				<?php endif;?> Jūs naudojate bandomąją Facebook prisijungimo sistemą. Radę klaidų praneškite kontaktai@sinonimai.lt.</p>
			</div>
			<?php else:?>
			<div class="connect">
				<p><a href="http://sinonimai.lt/?connect=facebook"><span class="facebook"></span>Prisijunkite su Facebook</a></p>

				<p>Prisijungę naudodami Facebook galėsite pildyti Sinonimų Žodyną, dalyvauti konkursuose ir sužinoti apie svarbias naujienas.</p>
			</div>
			<?php endif;?>

			<div class="navigation">
				<a href="http://sinonimai.lt/"<?=in_array($display, array(0,1,2,3,4,5)) ? ' class="active"' : ''?>>Sinonimų žodynas</a>
				<?php /*<a href="http://sinonimai.lt/konkursas.html"<?=$display == 11 ? ' class="active"' : ''?>>Konkursas</a>*/?>
				<a href="http://sinonimai.lt/sutrumpinimu-rodykle.html"<?=$display == 6 ? ' class="active"' : ''?>>Sutrumpinimų rodyklė</a>
				<a href="http://sinonimai.lt/apie-sinonimu-zodyna.html"<?=$display == 7 ? ' class="active"' : ''?>>Apie sinonimų žodyną</a>
				<a href="http://sinonimai.lt/kontaktai.html"<?=$display == 8 ? ' class="active"' : ''?>>Kontaktai</a>
			</div>
		</div>

		<div id="body">
			<?=$body?>
		</div>

		<div id="footer">
			<ul>
				<?php /*if(!empty($result['word']['last_updated'])): ?>
				<li class="last-modified">Puslapis atnaujintas <time datetime="<?=date('Y-m-d', strtotime($result['word']['last_updated']))?>"><?=formated_date($result['word']['last_updated'])?></time></li>
				<?php endif;*/ ?>
				<li><a href="<?=u()?>">http://sinonimai.lt</a>, Internetinis sinonimų žodynas. <a href="http://anuary.com" title="Anuary – developers and social media experts.">Anuary</a> Ltd.</li>
				<li>Turinio naudojimas yra ribojamas autorinių teisių. Daugiau informacijos <a href="<?=u('/naudojimo-salygos.html')?>">naudojimo sąlygose</a>.</li>
			</ul>
		</div>

		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) {return;}
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1&appId=257825524265543";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>
		<script type="text/javascript" src="public/js/jquery-1.7.min.js"></script>
		<script type="text/javascript" src="public/js/jquery.debounce-1.0.5.js"></script>
		<script type="text/javascript" src="public/js/jquery.ayTagBox.js"></script>
		<script type="text/javascript" src="public/js/default.js"></script>

		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-18690922-2']);
		  _gaq.push(['_trackPageview']);

		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();

		</script>
    </body>
</html>
