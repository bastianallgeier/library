<?php

require(__DIR__ . '/lib/bootstrap.php');

class QueryTest extends LibraryTestCase {

  public function dummyData() {

    // create some todos
    $this->library->create('todo', array('text' => 'first todo'));
    $this->library->create('todo', array('text' => 'second todo', 'status' => 'public'));

    // create some articles
    $this->library->create('article', array('text' => 'first article'));
    $this->library->create('article', array('text' => 'second article', 'status' => 'public'));
    $this->library->create('article', array('text' => 'third article', 'status' => 'private'));

  }

  public function testType() {

    $this->dummyData();

    $this->assertEquals(2, $this->library->type('todo')->count());
    $this->assertEquals(3, $this->library->type('article')->count());

  }

  public function testStatus() {

    $this->dummyData();

    $this->assertEquals(2, $this->library->status('draft')->count());
    $this->assertEquals(2, $this->library->status('public')->count());
    $this->assertEquals(1, $this->library->status('private')->count());

  }

  public function testSearch() {

    $this->dummyData();
    $this->assertEquals(3, $this->library->search('article')->count());

  }

  public function testCount() {

    $this->dummyData();
    $this->assertEquals(5, $this->library->count());

  }

}