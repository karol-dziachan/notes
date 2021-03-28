<?php

declare(strict_types=1);

namespace App;
use Throwable; 
require_once("src/Utils/debug.php");
require_once("src/Controller.php");
require_once("src/Exception/AppException.php");
require_once("src/Exception/ConfigurationException.php");
$configuration = require_once("config/config.php");


$request =[
  'get' => $_GET,
  'post' => $_POST
];

try
{
  Controller::initConfiguration($configuration);
  (new Controller($request)) -> run(); 
}
catch(ConfigurationException $e)
{
  echo "<h1> Popraw konfiguracje bazy danych </h1>"; 
}
catch(AppException $e)
{
  echo "<h1>Wystapil blad</h1>"; 
  echo $e -> getMessage(); 
}
catch(Throwable $e)
{
  echo "<h1>Wystapil blad</h1>"; 
  echo $e -> getMessage(); 
}

