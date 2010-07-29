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

class MondongoRepositoryTest extends MondongoTestCase
{
  public function testConstruct()
  {
    $repository = new MondongoRepository('Article', $this->mondongo);

    $this->assertSame($this->mondongo, $repository->getMondongo());
  }

  public function testConnectionAndCollection()
  {
    $mondongo = new Mondongo();
    $mondongo->setConnections(array(
      'local'  => $local  = new MondongoConnection($this->mongo->selectDB('mondongo_tests_local')),
      'global' => $global = new MondongoConnection($this->mongo->selectDB('mondongo_tests_global')),
    ));

    $repositoryLocal  = new MondongoRepository('MondongoTestConnectionsLocal', $mondongo);
    $repositoryGlobal = new MondongoRepository('MondongoTestConnectionsGlobal', $mondongo);

    $this->assertSame($local, $repositoryLocal->getConnection());
    $collection = new MondongoCollection($this->mongo->selectDB('mondongo_tests_global')->foo);
    $this->assertEquals($collection, $repositoryGlobal->getCollection());

    $this->assertSame($global, $repositoryGlobal->getConnection());
    $collection = new MondongoCollection($this->mongo->selectDB('mondongo_tests_global')->bar);
    $this->assertEquals($collection, $repositoryGlobal->getCollection());

    $this->assertEquals(
      $repositoryGlobal->getCollection()->getMongoCollection(),
      $repositoryGlobal->getMongoCollection()
    );
  }

  public function testCollectionGridFS()
  {
    $mondongo = new Mondongo();
    $mondongo->setConnection('default', new MondongoConnection($this->mongo->selectDB('mondongo_tests')));

    $repository = new MondongoRepository('MondongoTestRepositoryCollectionGridFS', $mondongo);

    $collection = new MondongoCollectionGridFS($this->mongo->selectDB('mondongo_tests')->getGridFS('foobar'));
    $this->assertEquals($collection, $repository->getCollection());
  }

  public function testLogCallable()
  {
    $mondongo = new Mondongo();
    $mondongo->setConnection('default', new MondongoConnection($this->mongo->selectDB('mondongo_tests')));

    $repository = new MondongoRepository('Article', $mondongo);
    $collection = $repository->getCollection();

    $repository->setLogCallable('foobar');
    $this->assertSame('foobar', $repository->getLogCallable());
    $this->assertSame('foobar', $collection->getLogCallable());
  }

  public function testFindFindOneGet()
  {
    $articles = array();
    for ($i = 1; $i <= 8; $i++)
    {
      $articles[] = $article = new Article();
      $article->set('title', 'Article '.$i);
      $article->set('content', 'Content '.$i);
    }
    $this->mondongo->save('Article', $articles);

    $repository = $this->mondongo->getRepository('Article');

    $this->assertEquals(array($articles[2]), $repository->find(array('_id' => new MongoId($articles[2]->getId()))));
    $this->assertEquals(array($articles[5]), $repository->find(array('title' => 'Article 6')));

    $results = $repository->find();
    $this->assertEquals(8, count($results));
    foreach ($articles as $article)
    {
      $this->assertTrue(in_array($article, $results));
    }

    $this->assertEquals($articles, $repository->find(array(), array('sort' => array('title' => 1))));

    $this->assertEquals(
      array($articles[0], $articles[1], $articles[2]),
      $repository->find(array(), array('sort' => array('title' => 1), 'limit' => 3))
    );

    $this->assertEquals(
      array($articles[6], $articles[7]),
      $repository->find(array(), array('sort' => array('title' => 1), 'skip' => 6))
    );

    $this->assertEquals(
      array((string) $articles[6]->getId() => $articles[6], (string) $articles[7]->getId() => $articles[7]),
      $repository->find(array(), array('sort' => array('title' => 1), 'skip' => 6, 'index_by' => '_id'))
    );

    $this->assertEquals(
      array($articles[6]->get('title') => $articles[6], $articles[7]->get('title') => $articles[7]),
      $repository->find(array(), array('sort' => array('title' => 1), 'skip' => 6, 'index_by' => 'title'))
    );

    $this->assertEquals($articles[0], $repository->find(array(), array('sort' => array('title' => 1), 'one' => true)));

    $this->assertNull($repository->find(array('_id' => 'no')));

    // findOne
    $this->assertEquals($articles[0], $repository->findOne(array(), array('sort' => array('title' => 1))));

    // get
    $this->assertEquals($articles[3], $repository->get($articles[3]->getId()));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testFindIndexByInvalid()
  {
    $this->mondongo->getRepository('Article')->find(array(), array('index_by' => 'no'));
  }

  public function testRemove()
  {
    $articles = array();
    for ($i = 1; $i <= 8; $i++)
    {
      $articles[] = $article = new Article();
      $article->set('title', 'Article '.$i);
      $article->set('content', 'Content '.$i);
    }
    $this->mondongo->getRepository('Article')->save($articles);

    $repository = $this->mondongo->getRepository('Article');

    $repository->remove(array('_id' => new MongoId($id = $articles[5]->getId())));
    $this->assertNull($repository->get($id));

    $repository->remove();
    $this->assertNull($repository->find());
  }

  public function testSaveBasic()
  {
    $repository = $this->mondongo->getRepository('Article');

    // insert
    $article = new Article();
    $article->set('title', 'Mondongo');
    $article->set('options', $options = array('saved' => true));

    $repository->save($article);

    $this->assertEquals(1, $this->db->article->find()->count());

    $result = $this->db->article->findOne();

    $this->assertEquals($article->getId(), $result['_id']);
    $this->assertEquals('Mondongo', $result['title']);
    $this->assertEquals(serialize($options), $result['options']);
    $this->assertEquals($options, $article->get('options'));

    $this->assertFalse($article->isModified());

    // update
    $id = $article->getId();
    $article->set('title', 'Mondongo 2');

    $repository->save($article);

    $this->assertEquals(1, $this->db->article->find()->count());

    $result = $this->db->article->findOne();

    $this->assertSame($id, $article->getId());
    $this->assertEquals($id, $result['_id']);
    $this->assertEquals('Mondongo 2', $result['title']);
    $this->assertEquals(serialize($options), $result['options']);

    $this->assertFalse($article->isModified());
  }

  public function testSaveBasicEvents()
  {
    $repository = $this->mondongo->getRepository('MondongoDocumentEvents');

    $document   = new MondongoDocumentEvents();
    $extensions = $document->getDefinition()->getExtensions();
    $extension  = reset($extensions);

    // insert
    $document->set('field1', 'Mondongo');
    $repository->save($document);

    $this->assertSame(array(
      'preInsert',
      'preSave',
      'postInsert',
      'postSave',
    ), $document->getEvents());
    $document->clearEvents();

    $this->assertSame(array(
      'preInsert',
      'preSave',
      'postInsert',
      'postSave',
    ), $extension->getEvents());
    $extension->clearEvents();

    $this->assertSame(array(
      'MondongoExtensionTesting' => array(
        'preInsert',
        'preSave',
        'postInsert',
        'postSave',
      ),
    ), $document->getExtensionsEvents());
    $document->clearExtensionsEvents();

    // update
    $document->set('field1', 'Mondongo2');
    $repository->save($document);

    $this->assertSame(array(
      'preUpdate',
      'preSave',
      'postUpdate',
      'postSave',
    ), $document->getEvents());
    $document->clearEvents();

    $this->assertSame(array(
      'preUpdate',
      'preSave',
      'postUpdate',
      'postSave',
    ), $extension->getEvents());
    $extension->clearEvents();

    $this->assertSame(array(
      'MondongoExtensionTesting' => array(
        'preUpdate',
        'preSave',
        'postUpdate',
        'postSave',
      ),
    ), $document->getExtensionsEvents());
    $document->clearExtensionsEvents();
  }

  public function testSaveMultiple()
  {
    $repository = $this->mondongo->getRepository('Article');

    // insert
    $articles = array();
    for ($i = 1; $i <= 10; $i++)
    {
      $articles[$i] = $article = new Article();
      $article->set('title', 'Mondongo '.$i);
      $article->set('content', 'Content '.$i);
    }

    $repository->save($articles);

    $this->assertEquals(10, $this->db->article->find()->count());

    foreach ($articles as $i => $article)
    {
      $this->assertNotNull($article->getId());

      $result = $this->db->article->findOne(array('_id' => new MongoId($article->getId())));

      $this->assertEquals('Mondongo '.$i, $result['title']);
      $this->assertEquals('Content '.$i, $result['content']);

      $this->assertFalse($article->isModified());
    }

    // update
    $ids = array();
    foreach ($articles as $i => $article)
    {
      $ids[$i] = $article->getId();

      $article->set('title', 'Mondongo '.($i * 100));
    }

    $repository->save($articles);

    $this->assertEquals(10, $this->db->article->find()->count());

    foreach ($articles as $i => $article)
    {
      $result = $this->db->article->findOne(array('_id' => new MongoId($ids[$i])));

      $this->assertEquals($ids[$i], $article->getId());
      $this->assertEquals('Mondongo '.($i * 100), $result['title']);
      $this->assertEquals('Content '.$i, $result['content']);

      $this->assertFalse($article->isModified());
    }
  }

  /**
   * @expectedException LogicException
   */
  public function testSaveNoModifiedDocument()
  {
    $this->mondongo->getRepository('Author')->save(new Author());
  }

  public function testDelete()
  {
    $repository = $this->mondongo->getRepository('Article');

    $articles = array();
    for ($i = 1; $i <= 10; $i++)
    {
      $articles[$i] = $article = new Article();
      $article->set('title', 'Mondongo');
    }
    $repository->save($articles);

    $article = array_pop($articles);
    $repository->delete($article);

    $this->assertEquals(9, $this->db->article->find()->count());
    $this->assertEquals(0, $this->db->article->find(array('_id' => $article->getId()))->count());

    $delete = array($articles[5], $articles[6]);
    $repository->delete($delete);

    $this->assertEquals(7, $this->db->article->find()->count());
    $this->assertEquals(0, $this->db->article->find(array(
      '_id' => array('$in' => array($articles[5]->getId(), $articles[6]->getId()))
    ))->count());
  }

  public function testDeleteEvents()
  {
    $repository = $this->mondongo->getRepository('MondongoDocumentEvents');

    $document   = new MondongoDocumentEvents();
    $extensions = $document->getDefinition()->getExtensions();
    $extension  = reset($extensions);

    $document->set('field1', 'Mondongo');
    $repository->save($document);
    $document->clearEvents();
    $extension->clearEvents();
    $document->clearExtensionsEvents();

    $repository->delete($document);

    $this->assertSame(array(
      'preDelete',
      'postDelete',
    ), $document->getEvents());
    $document->clearEvents();

    $this->assertSame(array(
      'preDelete',
      'postDelete',
    ), $extension->getEvents());
    $extension->clearEvents();
    $this->assertNull($extension->getInvoker());

    $this->assertSame(array(
      'MondongoExtensionTesting' => array(
        'preDelete',
        'postDelete',
      ),
    ), $document->getExtensionsEvents());
    $document->clearExtensionsEvents();
  }

  /**
   * @expectedException LogicException
   */
  public function testDeleteNewDocument()
  {
    $this->mondongo->getRepository('Article')->delete(new Article());
  }

  public function testFindGridFS()
  {
    $repository = $this->mondongo->getRepository('File');
    $grid       = $repository->getCollection()->getMongoCollection();

    $files = array();
    for ($i = 1; $i <= 5; $i++)
    {
      $files[] = $file = new File();
      $file['name'] = 'Mondongo '.$i;
      $file['file'] = __FILE__;
    }
    $repository->save($files);

    $fs = array();
    foreach ($files as $file)
    {
      $fs[$file->getId()->__toString()] = $file;
    }

    $results = $repository->find();

    $this->assertSame(5, count($results));
    foreach ($results as $r)
    {
      $this->assertTrue(isset($fs[$r->getId()->__toString()]));

      $f = $fs[$r->getId()->__toString()];
      $this->assertEquals($f['name'], $r['name']);
      $this->assertEquals(file_get_contents(__FILE__), $r['file']->getBytes());

    }
  }

  public function testFindOneGridFS()
  {
    $repository = $this->mondongo->getRepository('File');
    $grid       = $repository->getCollection()->getMongoCollection();

    $file = new File();
    $file['name'] = 'Mondongo';
    $file['file'] = __FILE__;

    $repository->save($file);

    $this->assertSame(1, $grid->find()->count());

    $r = $grid->findOne();
    $f = $repository->findOne(array('_id' => $r->file['_id']));

    $this->assertTrue($f instanceof File);
    $this->assertEquals($r->file['name'], $f->get('name'));
    $this->assertEquals($r, $f->get('file'));
  }

  public function testEnsureIndexes()
  {
    $this->db->dropCollection('indexes');
    $collection = $this->db->selectCollection('indexes');

    $repository = $this->mondongo->getRepository('MondongoTestIndexes');
    $repository->ensureIndexes();

    $info = $collection->getIndexInfo();

    $this->assertSame(3, count($info));
    $this->assertSame(array('title' => 1, 'date' => -1), $info[1]['key']);
    $this->assertFalse(isset($info[1]['unique']));
    $this->assertSame(array('slug' => 1), $info[2]['key']);
    $this->assertSame(true, $info[2]['unique']);

    $this->db->dropCollection('indexes');
    $collection = $this->db->selectCollection('indexes');
    $collection->ensureIndex(array('slug' => 1), array('unique' => true));

    $repository->ensureIndexes();

    $info = $collection->getIndexInfo();

    $this->assertSame(3, count($info));
    $this->assertSame(array('title' => 1, 'date' => -1), $info[2]['key']);
    $this->assertFalse(isset($info[2]['unique']));
    $this->assertSame(array('slug' => 1), $info[1]['key']);
    $this->assertSame(true, $info[1]['unique']);
  }

  public function testExtensionMethods()
  {
    $repository = $this->mondongo->getRepository('Author');

    $this->assertSame('RepositoryMethodBar', $repository->repositoryMethod('Bar'));
  }

  /**
   * @expectedException BadMethodCallException
   */
  public function testExceptionMethodBadCallException()
  {
    $repository = $this->mondongo->getRepository('Author');

    $repository->extensionBadMethod();
  }
}
