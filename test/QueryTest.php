<?php

require(__DIR__ . '/lib/bootstrap.php');

class QueryTest extends LibraryTestCase {

  public function dummyData() {

    // create some todos
    $this->library->create('todo', array('text' => 'first todo'));
    $this->library->create('todo', array('text' => 'second todo'));

    // create some articles
    $this->library->create('article', array('text' => 'first article'));
    $this->library->create('article', array('text' => 'second article'));
    $this->library->create('article', array('text' => 'third article'));

  }

  public function testType() {

    $this->dummyData();

    $this->assertEquals(2, $this->library->type('todo')->count());
    $this->assertEquals(3, $this->library->type('article')->count());

  }

}