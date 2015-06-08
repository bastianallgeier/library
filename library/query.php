<?php

namespace Library;

use Library;

class Query extends \Database\Query {

  public $library = null;

  public function __construct(Library $library) {
    $this->library = $library;
    parent::__construct($library->database, 'items');
  }

  protected function query($query, $params = array()) {

    $result = parent::query($query, $params);

    if(is_a($result, 'Collection')) {
      $library = $this->library;
      $result  = $result->map(function($item) use($library) {
        return $library->item((array)$item);
      });
      return $result;
    } else if(is_a($result, 'Obj')) {

      if(isset($result->aggregation)) {
        return $result;
      } else {
        return $this->library->item((array)$result);        
      }

    } else {
      return $result;      
    }

  }

  public function type($type) {
    return $this->where('type', '=', $type);    
  }

  public function status($status) {
    return $this->where('status', '=', $status);    
  }

  public function delete($where = null) {
    foreach($this->all() as $item) {
      $item->delete();
    }
  }

  public function search($q, $columns = array()) {

    $query = $this;    

    if(empty($columns)) {
      $columns = $this->library->columns()->pluck('name');
      $columns = array_diff($columns, Item::$required);      
    }

    $query->where(function($where) use($q, $columns) {
      foreach($columns as $column) {
        $where->orWhere($column, 'LIKE', '%' . $q . '%');
      }        
      return $where;
    });      

    return $query;

  }

}