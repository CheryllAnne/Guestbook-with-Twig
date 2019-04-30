<?php
/**
 * Created by PhpStorm.
 * User: CHERYLLANNE
 * Date: 4/2/2019
 * Time: 9:47 AM
 */

use utility\Session;
include_once('Session.php');
$session = new Session();

$session->unsetSession();
header('location: login.php');
exit;

?>