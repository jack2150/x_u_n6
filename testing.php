<?php
//header("location: index.php");

define("IN_PAGE",'FOLDERS');

include "includes/inc.php";
include "includes/folder.inc.php";

$baseUrl='folders.php?';

# checking if logined
if($user->logined==0) header('location:login.php');
if ($user->account_status == -1) { $user->logout(); header('location:'.$baseWeb.'/redirect.php?error=1&code=LoginErrSuspended'); }

getChildInheritList("3cb42f7");
//print_r();

?>

<a href="packages.php">packages.php</a>