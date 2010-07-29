<?php

/*
 * Copyright 2010 Pablo Díez Pascual <pablodip@gmail.com>
 *
 * This file is part of Mondongo.
 *
 * Mondongo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mondongo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Mondongo. If not, see <http://www.gnu.org/licenses/>.
 */

class MondongoCollectionGridFSTest extends MondongoTestCase
{
  protected $grid;

  public function setup()
  {
    parent::setup();

    $this->grid = $this->mongo->selectDB('mondongo_tests')->getGridFS();
    $this->grid->remove();
  }

  public function testMongoCollection()
  {
    $collection = new MondongoCollectionGridFS($this->grid);

    $this->assertSame($this->grid, $collection->getMongoCollection());
  }

  public function testSaveFileFile()
  {
    $collection = new MondongoCollectionGridFS($this->grid);

    $a = array(
      'file' => __FILE__,
      'foo'  => 'bar',
    );
    $collection->saveFile($a);

    $file = $this->grid->findOne();

    $result = $file->file;
    $result['file'] = $file;

    $this->assertEquals($result, $a);
    $this->assertSame(file_get_contents(__FILE__), $result['file']->getBytes());
  }

  public function testSaveFileBytes()
  {
    $collection = new MondongoCollectionGridFS($this->grid);

    $a = array(
      'file' => file_get_contents(__FILE__),
      'foo'  => 'bar',
    );
    $collection->saveFile($a);

    $file = $this->grid->findOne();

    $result = $file->file;
    $result['file'] = $file;

    $this->assertEquals($result, $a);
    $this->assertSame(file_get_contents(__FILE__), $result['file']->getBytes());
  }

  /**
   * @expectedException RuntimeException
   */
  public function testSaveFileUpdateFile()
  {
    $collection = new MondongoCollectionGridFS($this->grid);

    $a = array(
      'file' => __FILE__,
      'foo'  => 'bar',
    );
    $collection->saveFile($a);

    $a['file'] = $filename = dirname(__FILE__).'/MondongoCollectionTest.php';
    $a = $collection->saveFile($a);

    /*
    $file = $this->grid->findOne(array('_id' => $a['_id']));

    $result = $file->file;
    $result['file'] = $file;

    $this->assertEquals($result, $a);
    $this->assertSame(file_get_contents($filename), $result['file']->getBytes());
    */
  }

  public function testSaveFileUpdateMetadata()
  {
    $collection = new MondongoCollectionGridFS($this->grid);

    $a = array(
      'file' => __FILE__,
      'foo'  => 'bar',
    );
    $collection->saveFile($a);

    $file = $this->grid->findOne();

    $result = $file->file;
    $result['file'] = $file;
    $result['bar'] = 'foo';

    $collection->saveFile($a);

    $file = $this->grid->findOne(array('_id' => $result['_id']));

    $result = $file->file;
    $result['file'] = $file;

    $this->assertEquals($result, $a);
    $this->assertSame(file_get_contents(__FILE__), $result['file']->getBytes());
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testSaveFileFileNotExists()
  {
    $collection = new MondongoCollectionGridFS($this->grid);

    $a = array('foo' => 'bar');
    $collection->saveFile($a);
  }

  public function testBatchInsert()
  {
    $collection = new MondongoCollectionGridFS($this->grid);

    $files = array();
    for ($i = 1; $i <= 5; $i++)
    {
      $files[] = array('file' => __FILE__, 'foo' => 'bar', 'bar' => 'foo');
    }

    $collection->batchInsert($files);

    $this->assertSame(5, $collection->find()->count());
    foreach ($files as $file)
    {
      $r = $this->grid->findOne(array('_id' => $file['_id']));

      $a = $r->file;
      $a['file'] = $r;

      $this->assertEquals($file, $a);
    }
  }
}
