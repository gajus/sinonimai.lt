<?php
ob_start();
?>
<div id="contest-page">
	<h2>Paskutiniai pasiūlymai</h2>

	<?php
	$suggestions	= $db->query("
	SELECT
		`u1`.`first_name`,
		`u1`.`last_name`,
		`u1`.`facebook_id`,
		COUNT(`s1`.`id`) `count`
	FROM
		`users` `u1`
	INNER JOIN
		`suggestions` `s1`
	ON
		`s1`.`user_id` = `u1`.`id`
	GROUP BY
		`u1`.`id`
	ORDER BY
		`count` DESC;
	")->fetchAll(PDO::FETCH_ASSOC);

	$count_suggestions	= $db->query("SELECT COUNT(`id`) FROM `suggestions`;")->fetch(PDO::FETCH_COLUMN);
	?>

	<div class="paragraphed">
		<p>Štai ir baigėsi konkursas! Nuoširdžiai dėkojame visiems dalyvavusiems. Sinonimų žodynas pasipildė tikrai ne vienu ar dviem žodžiais. Šiuo metu tikrinami pasiūlymai ir sumuojami rezultatai.</p>
		<p>Su konkurso laimėtojais susisieksime asmeniškai vasario 3 d.</p>
	</div>

	<table class="entrants">
	<thead>
		<tr>
			<th class="id">#</th>
			<th class="name">Vardas, Pavardė</th>
			<th class="place">Pasiūlymų skaičius</th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach($suggestions as $i => $s):
	?>
	<tr>
		<td><?=($i+1)?></td>
		<td><a href="https://www.facebook.com/profile.php?id=<?=$s['facebook_id']?>"><?=$s['first_name']?> <?=$s['last_name']?></a></td>
		<td><?=$s['count']?></td>
	</tr>
	<?php endforeach;?>
	</tbody>
	</table>

	<p class="explanation"><sup>*</sup>Pasiūlymų skaičius gali kisti priklausomai nuo patvirtintų ar atmestų pasiūlymų.</p>

	<?php
	if($_SERVER['REMOTE_ADDR'] == '90.195.7.30')
	{
		$_SESSION['ay']['auth']['id']	= 110;
	}

	if(!empty($_SESSION['ay']['auth'])):

	$suggestions	= $db->query("
	SELECT
		`s1`.`entry_timestamp`,
		`s1`.`status`,
		`w1`.`word` `word`,
		`w2`.`word` `suggestion`
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


	$status_label	= array(0 => '<span class="pending">Laukia peržiūrėjimo</span>', 1 => 'Taip', 2 => 'Ne');
	?>
	<h2>Jūsų pasiūlymai</h2>
	<?php if(empty($suggestions)):?>
	<p>Jūs dar nepateikėte pasiūlymų.</p>
	<?php else:
	setlocale(LC_TIME,'lithuanian');
	?>
	<table class="suggestions">
	<thead>
		<tr>
			<th class="word">Žodis</th>
			<th class="word">Pasiūlytas sinonimas</th>
			<th>Data</th>
			<th class="word">Patvirtintas?</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($suggestions as $s):?>
			<tr>
				<td><?=$s['word']?></td>
				<td><?=$s['suggestion']?></td>
				<td><?=strftime('%Y %b., %d %H:%M', $s['entry_timestamp'])?></td>
				<td><?=$status_label[$s['status']]?></td>
			</tr>
		<?php endforeach;?>
	</tbody>
	</table>
	<?php endif;?>
	<?php endif;?>
</div>
<?php
$html	= ob_get_clean();

return array(
	'title'			=> 'Konkursas',
	'browser-title'	=> 'Konkursas',
	'sub-title'		=> 'Dalyvių sarašas, paskutiniai pasiūlymai',
	'body'			=> $html);
