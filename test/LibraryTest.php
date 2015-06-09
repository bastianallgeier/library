<?php

require(__DIR__ . '/lib/bootstrap.php');


class LibraryTest extends LibraryTestCase {

  public function testRoot() {
    $this->assertEquals($this->library->root(), $this->root);
  }

  public function testIsWritable() {
    $this->assertTrue($this->library->isWritable());
  }

  public function testDatabase() {
    $this->assertInstanceOf('Database', $this->library->database());
  }

  public function testIndex() {
    $this->assertInstanceOf('Database\\Query', $this->library->index());    
  }

  public function testFolder() {

    // test the folder object
    $this->assertInstanceOf('Folder', $this->library->folder());
    $this->assertEquals($this->library->folder()->root(), $this->library->root());

  }

  public function testId() {

    $this->assertTrue(is_string($this->library->id()));
    $this->assertTrue(strlen($this->library->id()) == 32);

  }

  public function testColumns() {

    $columns = array(
      'id', 
      'type', 
      'status', 
      'created', 
      'updated'
    );

    $this->assertEquals($this->library->columns()->pluck('name'), $columns);

  }

  public function testCreate() {

    $item = $this->library->create('todo', array(
      'text' => 'Test this thing'
    ));

    $this->assertInstanceOf('Library\\Item', $item);

  }

  public function testItem() {

    $item = $this->library->item(array(
      'id'      => $this->library->id(),
      'type'    => 'test',
      'status'  => 'draft',
      'updated' => time(),
      'created' => time(),
    ));
    
    $this->assertInstanceOf('Library\\Item', $item);

  }

  public function testDelete() {

    $item = $this->library->create('todo');
    $id   = $item->id();

    // check if the item can be found
    $this->assertInstanceOf('Library\\Item', $this->library->find($id));

    // now delete it
    $item->delete();

    // and check again
    $this->assertFalse($this->library->find($id));

  }

  public function testRebuild() {

    $a = $this->library->create('test');
    $b = $this->library->create('test');

    // delete the index
    f::remove($this->library->root() . DS . 'library.sqlite');

    $this->library->rebuild();

    $this->assertEquals(2, $this->library->count());
    $this->assertInstanceOf('Library\\Item', $this->library->find($b->id()));

  }

}