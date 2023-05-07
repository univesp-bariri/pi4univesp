<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo $_ENV['postgres_pw'];
#$password = shell_exec('echo $postgres_pw');
#echo "$password";
#phpinfo();

?>
