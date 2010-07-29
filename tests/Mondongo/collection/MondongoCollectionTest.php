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

class MondongoCollectionTest extends MondongoTestCase
{
  protected $mongoCollection;

  public function setup()
  {
    parent::setup();

    $this->mongoCollection = $this->mongo->selectDB('mondongo_tests')->collection;

    $this->mongoCollection->remove();
  }

  public function insertArticles($nb = 10)
  {
    $articles = array();
    for ($i = 1; $i <= $nb; $i++)
    {
      $articles[] = array('title' => 'Article '.$i, 'content' => 'Content '.$i);
    }
    $this->mongoCollection->batchInsert($articles);
  }

  public function testMongoCollection()
  {
    $collection = new MondongoCollection($this->mongoCollection);

    $this->assertSame($this->mongoCollection, $collection->getMongoCollection());
  }

  public function testLogCallable()
  {
    $collection = new MondongoCollection($this->mongoCollection);

    $collection->setLogCallable('logCallable');
    $this->assertSame('logCallable', $collection->getLogCallable());
  }

  public function testLogDefault()
  {
    $collection = new MondongoCollection($this->mongoCollection);

    $collection->setLogDefault($logDefault = array('connection' => 'local'));
    $this->assertSame($logDefault, $collection->getLogDefault());
  }

  public function testBatchInsert()
  {
    $collection = new MondongoCollection($this->mongoCollection);

    $articles = array();
    for ($i = 1; $i <= 5; $i++)
    {
      $articles[] = array('title' => 'Mondongo '.$i, 'content' => 'Content '.$i);
    }
    $collection->batchInsert($articles);

    $this->assertEquals(5, $this->mongoCollection->find()->count());

    foreach ($articles as $article)
    {
      $this->assertEquals($article, $this->mongoCollection->findOne(array('_id' => $article['_id'])));
    }
  }

  public function testUpdate()
  {
    $collection = new MondongoCollection($this->mongoCollection);

    $article = array('foobar' => 'barfoo');
    $this->mongoCollection->insert($article);

    $collection->update(array('_id' => $article['_id']), array('$set' => array('foo' => 'bar')));

    $this->assertEquals(array(
      '_id'    => $article['_id'],
      'foo'    => 'bar',
      'foobar' => 'barfoo',
    ), $this->mongoCollection->findOne());
  }

  public function testFind()
  {
    $this->insertArticles();

    $collection = new MondongoCollection($this->mongoCollection);

    $this->assertSame(10, $collection->find()->count());
  }

  public function testFindOne()
  {
    $this->insertArticles();

    $collection = new MondongoCollection($this->mongoCollection);

    $this->assertEquals($this->mongoCollection->findOne(), $collection->findOne());
  }

  public function testRemove()
  {
    $this->insertArticles();

    $collection = new MondongoCollection($this->mongoCollection);

    $collection->remove();
    $this->assertSame(0, $this->mongoCollection->find()->count());
  }
}
