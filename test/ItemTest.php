<?php

use Library\Item;

require(__DIR__ . '/lib/bootstrap.php');

class ItemTest extends LibraryTestCase {

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Missing required field: type
   */
  public function testCreateItemWithMissingType() {
    $item = new Item($this->library, array());
  }

  public function testSuccessfulItemCreation() {

    $item = new Item($this->library, array(
      'type' => 'todo',
    ));    

    $this->assertInstanceOf('Library\\Item', $item);

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid id
   */
  public function testCreateItemWithInvalidShortId() {
    $item = new Item($this->library, array(
      'id'   => 'abc',
      'type' => 'todo',
    ));
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid id
   */
  public function testCreateItemWithInvalidCharInId() {
    $item = new Item($this->library, array(
      'id'   => '#abGuMgoNV5HfbRZbJFCIbDHMXmATJ8d',
      'type' => 'todo',
    ));
  }


  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid type
   */
  public function testCreateItemWithInvalidCharsInType() {

    // not a string
    $item = new Item($this->library, array(
      'type' => 1,
    ));

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid type
   */
  public function testCreateItemWithInvalidShortType() {

    $item = new Item($this->library, array(
      'type' => 't',
    ));

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid type
   */
  public function testCreateItemWithInvalidLongType() {

    $item = new Item($this->library, array(
      'type' => 'taskdasdhkajhsdkjahskjdhakjsdhkjahsdkjahsdk',
    ));

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid status
   */
  public function testCreateItemWithInvalidStatus() {
    $item = new Item($this->library, array(
      'type'   => 'todo',
      'status' => 'abc'
    ));
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid updated timestamp
   */
  public function testCreateItemWithInvalidUpdatedTimestamp() {

    $item = new Item($this->library, array(
      'type'    => 'todo',
      'updated' => strtotime('1900-01-01')
    ));

    $item = new Item($this->library, array(
      'type'    => 'todo',
      'updated' => strtotime('2900-01-01')
    ));

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid created timestamp
   */
  public function testCreateItemWithInvalidCreatedTimestamp() {

    $item = new Item($this->library, array(
      'type'    => 'todo',
      'created' => strtotime('1900-01-01')
    ));

    $item = new Item($this->library, array(
      'type'    => 'todo',
      'created' => strtotime('2900-01-01')
    ));

    $item = new Item($this->library, array(
      'type'    => 'todo',
      'created' => time() + 10
    ));

  }

  public function testCreate() {

    $item = Item::create($this->library, 'todo');

    $this->assertEquals('todo', $item->type());
    $this->assertEquals('draft', $item->status());

    // search for the item in the library    
    $this->assertEquals($item, $this->library->find($item->id));

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid value type
   */  
  public function testSetWithInvalidArrayValue() {

    $item = $this->item();
    $item->tags = array('a','b','c');

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid value type
   */  
  public function testSetWithInvalidObjectValue() {

    $item = $this->item();
    $item->tags = new stdClass;

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid value type
   */  
  public function testSetWithInvalidResourceValue() {

    $item = $this->item();
    $item->tags = tmpfile();

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid key type
   */  
  public function testSetWithInvalidKey() {

    $item = $this->item();
    $item->set(1, 'test');

  }

  public function testMultipleSet() {

    $item = $this->item();
    $item->set(array(
      'keyA' => 'valueA',
      'keyB' => 'valueB'
    ));

    $this->assertEquals('valueA', $item->keyA);
    $this->assertEquals('valueB', $item->keyB);

  }

  public function testSet() {

    // Version A
    $item = $this->item();
    $item->title = 'test';

    $this->assertEquals('test', $item->title);

    // Version B
    $item = $this->item();
    $item->set('title', 'test');

    $this->assertEquals('test', $item->title);

    // Version C
    $item = $this->item();
    $item->__set('title', 'test');

    $this->assertEquals('test', $item->title);

  }

  public function testGet() {

    $item = $this->item();
    $item->title = 'test';

    $this->assertEquals('test', $item->title);
    $this->assertEquals('test', $item->title());
    $this->assertEquals('test', $item->get('title'));
    $this->assertEquals('test', $item->__get('title'));

    // different key versions
    $this->assertEquals('test', $item->Title);
    $this->assertEquals('test', $item->TITLE);
    $this->assertEquals('test', $item->tItLe);

  }

  public function testPath() {

    $item = $this->item();

    $this->assertEquals(date('Y'), $item->path('year'));
    $this->assertEquals(date('Y/m'), $item->path('month'));
    $this->assertEquals(date('Y/m/d'), $item->path('day'));
    $this->assertEquals(date('Y/m/d') . DS . $item->id, $item->path());

  }

  public function testRoot() {

    $item = $this->item();

    $this->assertEquals($this->root . DS . date('Y'), $item->root('year'));
    $this->assertEquals($this->root . DS . date('Y/m'), $item->root('month'));
    $this->assertEquals($this->root . DS . date('Y/m/d'), $item->root('day'));
    $this->assertEquals($this->root . DS . date('Y/m/d') . DS . $item->id, $item->root());

  }

  public function testStatus() {

    $item = $this->item();

    $this->assertEquals('draft', $item->status);

    $item->status('public');

    $this->assertEquals('public', $item->status);

    $item->status('private');

    $this->assertEquals('private', $item->status);

    $item->status('draft');

    $this->assertEquals('draft', $item->status);

  }

  public function testType() {

    $item = $this->item();

    $this->assertEquals('test', $item->type);

    $item->type('sometype');

    $this->assertEquals('sometype', $item->type);

  }

  public function testCreated() {

    $item = $this->item();

    $this->assertEquals(time(), $item->created);

    $item->created('2012-12-12 22:33');

    $this->assertEquals(strtotime('2012-12-12 22:33'), $item->created);

  }

  public function testUpdate() {

    $item = $this->item();
    $item->title   = 'A';
    $item->updated = time() - 10;
    $item->store();

    $updated = $item->updated();

    $this->assertEquals('A', $item->title);
    $this->assertEquals(1, $this->library->index->where('title', '=', 'A')->count());
    $this->assertEquals(0, $this->library->index->where('title', '=', 'B')->count());

    $item->update(array(
      'title' => 'B'
    ));

    $this->assertEquals('B', $item->title);
    $this->assertEquals(0, $this->library->index->where('title', '=', 'A')->count());
    $this->assertEquals(1, $this->library->index->where('title', '=', 'B')->count());

    $this->assertTrue($updated < $item->updated());

  }

  public function testDelete() {

    $item = $this->item();
    $root = $item->root();
    $id   = $item->id();

    $item->store();

    $this->assertTrue(is_dir($root));
    $this->assertEquals(1, $this->library->index->where('id', '=', $id)->count());

    $item->delete();

    // check for proper cleaning of directories
    $this->assertFalse(is_dir($root));
    $this->assertFalse(is_dir(dirname($root)));
    $this->assertFalse(is_dir(dirname(dirname($root))));
    $this->assertFalse(is_dir(dirname(dirname(dirname($root)))));

    // check for a clean index table
    $this->assertEquals(0, $this->library->index->where('id', '=', $id)->count());

  }

  public function testToArray() {

    $item = $this->item();        

    $item->type   = 'test';
    $item->status = 'public';
    $item->title  = 'test';

    $item->store();

    $result = array(
      'type'    => 'test',
      'id'      => $item->id,
      'status'  => 'public',
      'updated' => time(),
      'created' => time(),
      'title'   => 'test'
    );

    $this->assertEquals($result, $item->toArray());

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Unstored item
   */  
  public function testAttachWithUnstoredItem() {
  
    $item = $this->item();
    $item->attach('http://domain.com/image.jpg');

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage The file could not be fetched
   */  
  public function testAttachWithBrokenLink() {
  
    $item = $this->item();
    $item->store();
    $item->attach('http://getkirby.com/404.jpg');

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage item.yaml is a reserved filename
   */  
  public function testAttachItemYaml() {
  
    $item = $this->item();
    $item->store();
    $item->attach(__FILE__, 'item.yaml');

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage The file exists and cannot be overwritten
   */  
  public function testAttachOverwritingFiles() {
  
    $item = $this->item();
    $item->store();
    $item->attach(__FILE__, 'test.php');
    $item->attach(__FILE__, 'test.php');

  }

  public function testAttachFromUrl() {
  
    $item = $this->item();
    $item->store();
    $item->attach('http://getkirby.com/assets/images/logo.png');

    $this->assertTrue(file_exists($item->root() . DS . 'logo.png'));
    $this->assertEquals('logo.png', $item->images()->first()->filename());
    $this->assertEquals('image/png', $item->images()->first()->mime());
  
  }

  public function testAttachFromUrlWithCustomFilename() {
  
    $item = $this->item();
    $item->store();
    $item->attach('http://getkirby.com/assets/images/logo.png', 'myfile.png');

    $this->assertTrue(file_exists($item->root() . DS . 'myfile.png'));
    $this->assertEquals('myfile.png', $item->images()->first()->filename());
    $this->assertEquals('image/png', $item->images()->first()->mime());
  
  }

  public function testAttachFromFile() {
  
    $item = $this->item();
    $item->store();
    $item->attach(__FILE__);

    $filename = f::safeName(basename(__FILE__));

    $this->assertTrue(file_exists($item->root() . DS . $filename));
    $this->assertEquals($filename, $item->files()->first()->filename());
  
  }

  public function testAttachFromFileWithCustomFilename() {
  
    $item = $this->item();
    $item->store();
    $item->attach(__FILE__, 'myfile.php');

    $this->assertTrue(file_exists($item->root() . DS . 'myfile.php'));
    $this->assertEquals('myfile.php', $item->files()->first()->filename());
  
  }

  public function testDetach() {

    $item = $this->item();
    $item->store();

    $item->attach(__FILE__, 'myfile.php');

    $this->assertTrue(file_exists($item->root() . DS . 'myfile.php'));

    $item->detach('myfile.php');

    $this->assertFalse(file_exists($item->root() . DS . 'myfile.php'));

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage The file cannot be removed
   */  
  public function testDetachInvalidFile() {

    $item = $this->item();

    $item->store();

    $item->detach('myfile.php');

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage The item.yaml file cannot be removed
   */  
  public function testDetachItemYaml() {

    $item = $this->item();

    $item->store();

    $item->detach('item.yaml');

  }


  public function testExists() {

    $item = $this->item();

    $this->assertFalse($item->exists());

    $item->store();

    $this->assertTrue($item->exists());

  }

  public function testRelocate() {

    $item = $this->item();
    $root = $item->root();

    $item->store();

    $this->assertTrue(is_dir($root));    

    $item->created('2012-12-12 22:33');

    // check for proper cleaning of directories
    $this->assertFalse(is_dir($root));    
    $this->assertFalse(is_dir(dirname($root)));
    $this->assertFalse(is_dir(dirname(dirname($root))));
    $this->assertFalse(is_dir(dirname(dirname(dirname($root)))));

    // check for a new root
    $this->assertTrue(is_dir($item->root()));    

  }

  public function testColumnAddition() {

    $item     = $this->item();
    $columns  = $this->library->columns()->pluck('name');
    $expected = array(
      'id', 
      'type', 
      'status', 
      'created', 
      'updated'
    );

    $this->assertEquals($expected, $columns);

    $item->update(array(
      'title' => 'test'
    ));

    // check the list of columns again
    $columns = $this->library->columns()->pluck('name');

    // the list of columns should now also have the title
    $expected[] = 'title';    

    $this->assertEquals($expected, $columns);    

  }

}