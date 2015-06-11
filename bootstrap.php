<?php

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

if(!class_exists('toolkit')) {
  require(__DIR__ . DS . 'toolkit' . DS . 'bootstrap.php'); 
}

load(array(
  'library'        => __DIR__ . DS . 'library' . DS . 'library.php',
  'library\\item'  => __DIR__ . DS . 'library' . DS . 'item.php',
  'library\\query' => __DIR__ . DS . 'library' . DS . 'query.php',
));