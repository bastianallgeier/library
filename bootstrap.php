<?php

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once(__DIR__ . DS . 'toolkit' . DS . 'bootstrap.php');

load(array(
  'library'             => __DIR__ . DS . 'library' . DS . 'library.php',
  'library\\api'        => __DIR__ . DS . 'library' . DS . 'api.php',
  'library\\item'       => __DIR__ . DS . 'library' . DS . 'item.php',
  'library\\query'      => __DIR__ . DS . 'library' . DS . 'query.php',
));