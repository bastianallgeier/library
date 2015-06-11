<?php

namespace Library;

use Library;
use F;
use Folder;
use Data;
use Dir;
use Str;
use Remote;
use Exception;
use V;

class Item {

  static public $statuses = array('draft', 'public', 'private');
  static public $required = array('id', 'type', 'status', 'updated', 'created');

  protected $library;

  protected $old  = array();
  protected $data = array();

  public function __construct(Library $library, $type, $data = array()) {

    // set the parent library object
    $this->library = $library;

    // set the type
    $this->type = $type;

    // add all fields
    $this->set($data);

    // auto-create an id for new items
    if(!isset($this->data['id'])) {
      $this->id = $library->id();
    }

    // auto-set the status to draft if nothing is set
    if(!isset($this->data['status'])) {
      $this->status = 'draft';
    }

    // auto-set the updated timestamp
    if(!isset($this->data['updated'])) {
      $this->updated = time();
    }

    // auto-set the created timestamp
    if(!isset($this->data['created'])) {
      $this->created = time();
    }

    // validate the object
    $this->validate();

  }

  public function validate() {

    // check for a valid library object
    if(!is_a($this->library, 'Library')) {
      throw new Exception('The library object is invalid');
    }

    // check for all required fields
    foreach(static::$required as $field) {
      if(empty($this->data[$field])) {
        throw new Exception('Missing required field: ' . $field);
      }
    }

    // id validation
    if(!is_string($this->data['id']) or !v::alphanum($this->data['id']) or str::length($this->data['id']) !== 32) {
      throw new Exception('Invalid id');
    }

    // type validation
    if(!is_string($this->data['type']) or !v::between($this->data['type'], 2, 32)) {
      throw new Exception('Invalid type');
    }

    // status validation
    if(!in_array($this->data['status'], static::$statuses)) {
      throw new Exception('Invalid status: ' . $this->data['status']);
    }

    // check for invalid updated timestamp
    if(!is_int($this->data['updated']) or !v::between(date('Y', $this->data['updated']), 1980, 2500)) {
      throw new Exception('Invalid updated timestamp');
    }

    // check for invalid created timestamp
    if(!is_int($this->data['created']) or !v::between(date('Y', $this->data['created']), 1980, 2500) or $this->data['created'] > time()) {
      throw new Exception('Invalid created timestamp');
    }

  }

  static public function create(Library $library, $type, $data = array()) {
    $item = new static($library, $type, $data);
    return $item->store();
  }

  public function __set($key, $value) {

    // convert nulls to empty strings
    if(is_null($value)) $value = '';

    // avoid invalid values
    if(!is_scalar($value)) {
      throw new Exception('Invalid value type');
    }

    // avoid invalid keys
    if(!is_string($key)) {
      throw new Exception('Invalid key type');
    }

    // sanitize the key
    $key = str_replace('-', '_', str::slug($key));

    // sanitize the timestamps
    if($key == 'updated' or $key == 'created') {
      $value = intval($value);
    }

    // clean string values
    if(is_string($value)) {
      $value = trim($value);      
    }

    // store the last state
    if(isset($this->data[$key])) {
      $this->old[$key] = $this->$key; 
    } else {
      $this->old[$key] = $value;
    }

    // store it
    $this->data[$key] = $value;

  }

  public function __get($key) {
    $key = str::slug($key);
    if(isset($this->data[$key])) {
      return $this->data[$key];
    } else {
      return null;
    }
  }

  public function get($key) {
    return $this->__get($key);
  }

  public function __call($key, $args) {
    return $this->__get($key);
  }

  public function set($key, $value = null) {
    if(is_array($key)) {
      foreach($key as $k => $v) {
        $this->__set($k, $v);
      }
      return $this;
    }
    $this->__set($key, $value);
    return $this;
  }

  public function status($status = null) {

    if(is_null($status)) {
      return $this->status;      
    }

    $this->status = $status;
    $this->store();

    return $this;

  }

  public function type($type = null) {

    if(is_null($type)) {
      return $this->type;      
    }

    $this->type = $type;
    $this->store();

    return $this;

  }

  public function created($created = null) {

    if(is_null($created)) {
      return $this->created;      
    }

    // handles all parseable strings
    if(!is_int($created)) {
      $created = strtotime($created);
    }

    // store the new creation date and validate it
    $this->created = $created;
    $this->store();

    return $this;

  }

  public function update($data = array()) {

    $this->set($data);
    $this->set('updated', time());
    $this->store();

    return $this;

  }

  public function attach($file, $filename = null) {

    // if the item has not been stored yet 
    // throw an exception
    if(!$this->exists()) {
      throw new Exception('Unstored item');
    }

    // filename fallback
    if(is_null($filename)) {
      $filename = basename($file);
    }

    // sanitize the filename
    $filename = f::safeName($filename);

    // the item.yaml cannot be overwritten
    if($filename == 'item.yaml') {
      throw new Exception('item.yaml is a reserved filename');
    }

    // files cannot be overwritten
    if(file_exists($this->root() . DS . $filename)) {
      throw new Exception('The file exists and cannot be overwritten');
    }

    // attach a remote url
    if(v::url($file)) {
      $response = remote::get($file);
      if($response->code() < 400) {
        if(!f::write($this->root() . DS . $filename, $response->content())) {
          throw new Exception('The file could not be saved');
        }
      } else {
        throw new Exception('The file could not be fetched');
      }
    } else if(file_exists($file)) {
      if(!f::copy($file, $this->root() . DS . $filename)) {
        throw new Exception('The file could not be copied');
      }
    }

  }

  public function detach($filename) {

    $filename = f::safeName($filename);

    if($filename == 'item.yaml') {
      throw new Exception('The item.yaml file cannot be removed');
    }

    if(!f::remove($this->root() . DS . $filename)) {
      throw new Exception('The file cannot be removed');
    }

  }

  public function delete() {

    // get this before the item is killed
    $dayroot = $this->root('day');

    if(!dir::remove($this->root())) {
      throw new Exception('The item directory could not be removed');
    }

    if(!$this->library->database->query('delete from items where id = :id', array('id' => $this->id))) {
      throw new Exception('The delete query failed');
    }
  
    // make sure to clean up the directory attic
    $this->library->clean($dayroot);

  }

  public function toArray() {
    return $this->data;
  }

  public function exists() {
    return is_dir($this->root());
  }

  public function store() {

    // make sure the item is valid before storing it
    $this->validate();

    // make sure the directory is at the right position
    $this->relocate();

    $index   = $this->library->index();
    $data    = $this->toArray();
    $file    = $this->root() . DS . 'item.yaml';
    $columns = $this->library->columns()->pluck('name');
    $missing = array_diff(array_keys($data), $columns);

    // add additional columns
    foreach($missing as $column) {
      $this->library->database->query('alter table items add ' . $column . ' text');
    }

    // store the data in the file
    data::write($file, $data);    

    // clean the index first
    $this->library->database->query('delete from items where id = :id', array('id' => $this->id));
    
    // and then re-add
    $index->insert($data);

    return $this;

  }

  public function folder() {
    return new Folder($this->root());
  }

  public function files() {
    return $this->folder()->files()->not('item.yaml');
  }

  public function images() {
    return $this->files()->filterBy('type', 'image');
  }

  public function videos() {
    return $this->files()->filterBy('type', 'video');
  }

  public function documents() {
    return $this->files()->filterBy('type', 'document');
  }

  public function path($type = null) {

    switch(strtolower($type)) {
      case 'year':
        return date('Y', $this->created);
      case 'month':
        return date('Y/m', $this->created);
      case 'day':
        return date('Y/m/d', $this->created);
      default:
        return date('Y/m/d', $this->created) . '/' . $this->id;
    }

  }

  public function root($type = null) {
    return $this->library->root . DS . str_replace('/', DS, $this->path($type));
  }

  protected function relocate() {

    if($this->old['created'] == $this->data['created']) return;

    $old = clone $this;
    $old->created = $this->old['created'];

    if(!$old->exists()) return;

    if(!dir::make($this->root())) {
      throw new Exception('The new directory could not be created');
    }

    if(!dir::move($old->root(), $this->root())) {
      throw new Exception('The directory could not be moved');
    }

    $this->library->clean($old->root('day'));

  }

}