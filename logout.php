<?php
// Using for cross domain logout
include "includes/inc.php";

$user->logout();

@$db->close_db();

header("location: http://www.xun6.com/redirect.php?error=1&code=LogoutMessage");
?>
