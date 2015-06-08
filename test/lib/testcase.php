<?php 

class LibraryTestCase extends PHPUnit_Framework_TestCase {

  public $root = '/tmp/kirby-library-test';
  public $library = null;

  protected function setUp() {

    // create the test root
    dir::make($this->root);

    // set up a new library
    $this->library();

  }

  protected function tearDown() {
    dir::remove($this->root);
  }

  public function library() {
    return $this->library = new Library($this->root);
  }

  public function item() {
    return new Library\Item($this->library, array('type' => 'test'));
  }

}