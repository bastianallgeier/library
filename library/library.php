<?php

use Library\Item;
use Library\Query;
use Library\Api;

class Library {

  public $root;
  public $index;
  public $database;

  public function __construct($root) {
    
    $this->root = $root;

    if(!$this->isWritable()) {
      throw new Exception('The library is not writable');
    }

    $this->index();

  }

  public function root() {
    return $this->root;
  }

  public function columns() {
    return $this->database->query('PRAGMA table_info(items)');
  }

  public function folder() {
    return new Folder($this->root);
  }

  public function isWritable() {
    return is_writable($this->root);
  }

  public function index() {

    if(!is_null($this->index)) return $this->index;

    $file  = $this->root . DS . 'library.sqlite';
    $setup = !file_exists($file);

    $this->database = new Database(array(
      'database' => $file,
      'type'     => 'sqlite'
    ));

    if($setup) {
      $this->setup();
    }

    return $this->index = new Query($this);

  }

  public function database() {
    return $this->database;
  }

  public function id() {

    $id = str::random(32);

    while($this->index()->where('id', '=', $id)->one()) {
      $id = str::random(32);
    }

    return $id;

  }

  public function create($type, $data = array()) {
    return Item::create($this, $type, $data);
  }

  public function item($data) {
    return new Item($this, $data['type'], $data);
  }

  public function delete($id) {
    if($item = $this->find($id)) {
      return $item->delete();
    } else {
      throw new Exception('The item could not be found');
    }
  }

  public function __call($method, $args) {

    // allowed query calls
    $queries = array(
      'status', 
      'type', 
      'find', 
      'first',
      'all', 
      'page', 
      'search', 
      'where', 
      'count',
      'year',
      'month',
      'day',
      'years',
      'months',
      'days',
      'order'
    );

    if(in_array($method, $queries)) {
      return call(array($this->index, $method), $args);
    } else {
      throw new Exception('Invalid library method');
    }

  }

  public function setup() {

    $this->database->createTable('items', array(
      'id' => array(
        'type' => 'text',
        'key'  => 'unique',
      ),
      'type' => array(
        'type' => 'text',
        'key'  => 'index',
      ),
      'status' => array(
        'type' => 'text',
        'key'  => 'index',
      ),
      'created' => array(
        'type' => 'timestamp',
        'key'  => 'index',
      ),
      'updated' => array(
        'type' => 'timestamp'
      )
    ));    

    $this->rebuild();

  }

  public function rebuild() {

    foreach($this->folder()->children() as $year) {
      foreach($year->children() as $month) {
        foreach($month->children() as $day) {
          foreach($day->children() as $item) {
            $data = data::read($item->root() . DS . 'item.yaml');
            $item = new Item($this, $data['type'], $data); 
            $item->store();
          }
        }
      }
    }

  }

  public function clean($root) {

    if(!is_dir($root)) {
      throw new Exception('The given directory does not exist');
    }

    if(!str::startsWith($root, $this->root)) {
      throw new Exception('Invalid directory. Must be within the library');
    }

    while($root != $this->root) {
      $files = dir::read($root);
      if(count($files) === 0) {
        dir::remove($root);
      } else {
        break;
      }
      $root = dirname($root);
    }

  }

}
