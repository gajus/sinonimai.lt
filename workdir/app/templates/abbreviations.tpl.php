<?php
$body_str = '<p>Žodyne naudojamų sutrumpinimų sąrašas kartu su vartojimo reikšme. Kiekvienas sutrumpinimas į žodyną įvedamas atsižvelgiant į pasikartojimo dažnumą, dėl to ne visi sutrumpinimai gali būti standartizuoti. Patariame peržiūrėti sutrumpinimų rodyklę prieš vartojant sinonimų žodyną.</p><br />';

$properties = $db->query("SELECT p1.abbreviation, p1.augmentation FROM properties p1 ORDER BY p1.abbreviation ASC;")->fetchAll(PDO::FETCH_ASSOC);

foreach ($properties as $p) {
  $body_str .= '<dt><abbr>' . $p['abbreviation'] . '.</abbr></dt><dd>' . $p['augmentation'] . '</dd>';
}

return array(
  'title' => 'Sutrumpinimų rodyklė',
  'browser-title' => 'sutrumpinimų rodyklė',
  'sub-title' => 'Žodyne naudojami sutrumpinimai',
  'body' => '<dl id="properties">' . $body_str . '</dl><div class="clear"></div>'
);
