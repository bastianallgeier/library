<?php

namespace Library;

use Library;
use Exception;

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

  public function find($id) {
    return $this->where('id', '=', $id)->one();     
  }

  public function type($type) {
    return $this->where('type', '=', $type);    
  }

  public function day($day) {

    if(!preg_match('!^[\d]{4}-[\d]{2}-[\d]{2}$!', $day)) {
      throw new Exception('Invalid day format. Must be YYYY-MM-DD');
    }

    $start = strtotime($day);
    $end   = strtotime('+1 day', $start);
    return $this->where('created', '>=', $start)->where('created', '<', $end);    

  }

  public function month($month) {

    if(!preg_match('!^[\d]{4}-[\d]{2}$!', $month)) {
      throw new Exception('Invalid month format. Must be YYYY-MM');
    }

    $start = strtotime($month . '-01');
    $end   = strtotime('+1 month', $start);
    return $this->where('created', '>=', $start)->where('created', '<', $end);    

  }

  public function year($year) {

    if(!preg_match('!^[\d]{4}$!', $year)) {
      throw new Exception('Invalid year. Must be YYYY');
    }

    $start = strtotime($year . '-01-01');
    $end   = strtotime('+1 year', $start);
    return $this->where('created', '>=', $start)->where('created', '<', $end);    
  }

  public function years() {
    return $this->library->database->query('select distinct strftime("%Y", created, "unixepoch") as year from items')->pluck('year');
  }

  public function months() {
    return $this->library->database->query('select distinct strftime("%Y-%m", created, "unixepoch") as month from items')->pluck('month');
  }

  public function days() {
    return $this->library->database->query('select distinct strftime("%Y-%m-%d", created, "unixepoch") as day from items')->pluck('day');
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