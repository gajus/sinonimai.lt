<?php
if (!empty($_POST['email']) && !empty($_POST['password'])) {
  $user	= $db
    ->query("SELECT `id` FROM `admins` WHERE `email`={$db->quote($_POST['email'])} AND `password`={$db->quote($_POST['password'])};")
    ->fetch(PDO::FETCH_COLUMN);

  if ($user) {
    $_SESSION['auth']	= [];
    $_SESSION['auth']['id']	= $user;

    hp_redirect(NULL, 'Sveiki sugrįžę.', HP_M_SUCCESS);
  } else {
    unset($_SESSION['auth']);

    hp_redirect(NULL, 'Neteisingas el. pašto adresas arba slaptažodis.', HP_M_ERROR);
  }
}
?>
<form action="" method="post">
  <?=hp_row('text', 'El. pašto adresas', 'email')?>
  <?=hp_row('password', 'Slaptažodis', 'password')?>

  <input type="submit" value="Prisijungti" />
</form>
