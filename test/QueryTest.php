<?php

require(__DIR__ . '/lib/bootstrap.php');

class QueryTest extends LibraryTestCase {

  public function dummyData() {

    // create some todos
    $this->library->create('todo', array('text' => 'first todo'));
    $this->library->create('todo', array('text' => 'second todo', 'status' => 'public'));

    // create some articles
    $this->library->create('article', array('text' => 'first article', 'created' => strtotime('2012-12-12 22:33')));
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

  public function testYear() {

    $this->dummyData();

    $this->assertEquals(1, $this->library->year(2012)->count());
    $this->assertEquals(4, $this->library->year(date('Y'))->count());

  }

  public function testMonth() {

    $this->dummyData();

    $this->assertEquals(1, $this->library->month('2012-12')->count());
    $this->assertEquals(4, $this->library->month(date('Y-m'))->count());

  }

  public function testDay() {

    $this->dummyData();

    $this->assertEquals(1, $this->library->day('2012-12-12')->count());
    $this->assertEquals(4, $this->library->day(date('Y-m-d'))->count());

  }

  public function testSearch() {

    $this->dummyData();
    $this->assertEquals(3, $this->library->search('article')->count());

  }

  public function testCount() {

    $this->dummyData();
    $this->assertEquals(5, $this->library->count());

  }

  public function testYears() {

    $this->dummyData();
    $this->assertEquals($this->library->years(), array(2012, date('Y')));

  }

  public function testMonths() {

    $this->dummyData();
    $this->assertEquals($this->library->months(), array('2012-12', date('Y-m')));

  }

  public function testDays() {

    $this->dummyData();
    $this->assertEquals($this->library->days(), array('2012-12-12', date('Y-m-d')));

  }

}