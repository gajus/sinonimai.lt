<?php
/*
updates_history:
1 - full word update
2 - new synonym added (synonym id)
3 - synonym deleted (synonym id)
4 - partial word update
*/
?>
<table class="default history">
<thead>
<tr>
	<th>Žodis</th>
	<th class="regular">Atnaujinta</th>
</tr>
</thead>
<tbody>
<?php
foreach($user['updates'] as $u):
?>
<tr>
	<?php if($u['type'] == 1):?>
	<td class="update">Pilnas žodžio <a href="index.php?controller=synonym&query=<?=$u['word']?>"><?=$u['word']?></a> sutvarkymas.</td>
	<?php elseif($u['type'] == 2):
	$u['synonym_word']	= $db->query("SELECT `word` FROM `words` WHERE `word_id`={$db->quote($u['data'])};")->fetch(PDO::FETCH_COLUMN);
	?>
	<td class="update">Pridėtas naujas sinonimas žodžiui <a href="index.php?controller=synonym&query=<?=$u['word']?>"><?=$u['word']?></a>: <a href="index.php?controller=synonym&query=<?=$u['data']?>"><?=$u['synonym_word']?></a>.</td>
	<?php elseif($u['type'] == 3):
	$u['synonym_word']	= $db->query("SELECT `word` FROM `words` WHERE `word_id`={$db->quote($u['data'])};")->fetch(PDO::FETCH_COLUMN);
	?>
	<td class="delete">Žodis <a href="index.php?controller=synonym&query=<?=$u['data']?>"><?=$u['synonym_word']?></a> pašalintas, <a href="index.php?controller=synonym&query=<?=$u['word']?>"><?=$u['word']?></a>.</td>
	<?php elseif($u['type'] == 4):?>
	<td class="partial">Dalinis žodžio <a href="index.php?controller=synonym&query=<?=$u['word']?>"><?=$u['word']?></a> sutvarkymas.</td>
	<?php else:?>
	<td>Klaida.</td>
	<?php endif;?>
	
	<td><?=date('Y m j H:i', $u['date'])?></td>
</tr>
<?php endforeach;?>
</tbody>
</table>