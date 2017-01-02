<?php
if(!empty($_POST['approve']))
{
	$db->exec("UPDATE `suggestions` SET `status`=1, `inspection_timestamp`=UNIX_TIMESTAMP() WHERE `id`={$db->quote($_POST['approve'])};");
	
	#header('Content-Type: json/application'); echo json_encode(array('Ok'));
	
	exit;
}

if(!empty($_POST['decline']))
{
	$db->exec("UPDATE `suggestions` SET `status`=2, `inspection_timestamp`=UNIX_TIMESTAMP() WHERE `id`={$db->quote($_POST['decline'])};");
	
	#header('Content-Type: json/application'); echo json_encode(array('Ok'));
	
	exit;
}

$suggestions	= $db->query("
SELECT
	`s1`.`id`,
	`s1`.`entry_timestamp`,
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
	`s1`.`status` = 0
ORDER BY
	`s1`.`entry_timestamp` DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<p>Viso liko patikrinti <?=count($suggestions)?>.</p>

<table class="default">
<thead>
	<tr>
		<th class="regular">Žodis</th>
		<th class="regular">Pasiūlytas sinonimas</th>
		<th class="regular">Data</th>
		<th></th>
	</tr>
</thead>
<tbody>
	<?php foreach($suggestions as $s):?>
		<tr>
			<td><a href="?controller=synonym&query=<?=urlencode($s['word'])?>"><?=$s['word']?></a></td>
			<td><?=$s['suggestion']?></td>
			<td><?=strftime('%Y %b., %d %H:%M', $s['entry_timestamp'])?></td>
			<td><span class="approve" data-id="<?=$s['id']?>">Patvirtinti</span><span class="decline" data-id="<?=$s['id']?>">Atmesti</span></td>
		</tr>
	<?php endforeach;?>
</tbody>
</table>