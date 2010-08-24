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

class MondongoTest extends MondongoTestCase
{
  public function testConnections()
  {
    $mondongo = new Mondongo();

    $connections = array(
      'local'  => new MondongoConnection($this->mongo->selectDB('mondongo_tests_local')),
      'global' => new MondongoConnection($this->mongo->selectDB('mondongo_tests_global')),
      'extra'  => new MondongoConnection($this->mongo->selectDB('mondongo_tests_extra')),
    );

    // setConnections, getConnections
    $mondongo->setConnection('extra', $connections['extra']);
    $mondongo->setConnections($setConnections = array(
      'local'  => $connections['local'],
      'global' => $connections['global'],
    ));
    $this->assertEquals($setConnections, $mondongo->getConnections());

    // removeConnection
    $mondongo->setConnections($connections);
    $mondongo->removeConnection('local');
    $this->assertEquals(array(
      'global' => $connections['global'],
      'extra'  => $connections['extra'],
    ), $mondongo->getConnections());

    // clearConnections
    $mondongo->clearConnections();
    $this->assertEquals(array(), $mondongo->getConnections());

    // setConnection
    $mondongo->setConnection('local', $connections['local']);
    $mondongo->setConnection('global', $connections['global']);

    // hasConnection
    $this->assertTrue($mondongo->hasConnection('local'));
    $this->assertFalse($mondongo->hasConnection('no'));

    // getConnection
    $this->assertSame($connections['local'], $mondongo->getConnection('local'));
    $this->assertSame($connections['global'], $mondongo->getConnection('global'));

    // defaultConnection
    $mondongo->setDefaultConnectionName('global');

    $this->assertEquals('global', $mondongo->getDefaultConnectionName());
    $this->assertSame($connections['global'], $mondongo->getDefaultConnection());

    $mondongo->setDefaultConnectionName(null);

    $this->assertSame($connections['local'], $mondongo->getDefaultConnection());
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testConnectionsRemoveConnectionNotExists()
  {
    $mondongo = new Mondongo();
    $mondongo->removeConnection('no');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testConnectionsGetConnectionNotExists()
  {
    $mondongo = new Mondongo();
    $mondongo->getConnection('no');
  }

  /**
   * @expectedException RuntimeException
   */
  public function testConnectionsGetDefaultConnectionNotExists()
  {
    $mondongo = new Mondongo();
    $mondongo->setDefaultConnectionName('no');
    $mondongo->getDefaultConnection();
  }

  public function testRepositories()
  {
    $articleRepository  = $this->mondongo->getRepository('Article');
    $categoryRepository = $this->mondongo->getRepository('Category');

    $this->assertEquals('MondongoRepository', get_class($articleRepository));
    $this->assertEquals('MondongoRepository', get_class($categoryRepository));

    $this->assertSame($articleRepository, $this->mondongo->getRepository('Article'));
  }

  public function testLogCallable()
  {
    $mondongo = new Mondongo();
    $mondongo->setConnection('default', new MondongoConnection($this->mongo->selectDB('mondongo_tests')));

    $mondongo->setLogCallable('foobar');
    $this->assertSame('foobar', $mondongo->getLogCallable());

    $repository = $mondongo->getRepository('Article');
    $this->assertEquals('foobar', $repository->getLogCallable());

    $mondongo->setLogCallable('barfoo');
    $this->assertEquals('barfoo', $mondongo->getLogCallable());
    $this->assertEquals('barfoo', $repository->getLogCallable());
  }

  public function testFindFindOneGet()
  {
    $articles = array();
    for ($i = 1; $i <= 9; $i++)
    {
      $articles[] = $article = new Article();
      $article->set('title', 'Article '.$i);
    }
    $this->mondongo->save('Article', $articles);

    $this->assertEquals($articles, $this->mondongo->find('Article', array('sort' => array('title' => 1))));

    $this->assertEquals($articles[2], $this->mondongo->findOne('Article', array('query' => array('_id' => $articles[2]->getId()))));

    $this->assertEquals($articles[2], $this->mondongo->get('Article', $articles[2]->getId()));
  }

  public function testRemove()
  {
    $articles = array();
    for ($i = 1; $i <= 9; $i++)
    {
      $articles[] = $article = new Article();
      $article->set('title', 'Article '.$i);
    }
    $this->mondongo->save('Article', $articles);

    $this->mondongo->remove('Article', array('query' => array('title' => 'Article 1')));
    $this->assertEquals(8, count($this->mondongo->find('Article')));
  }

  public function testSaveBasic()
  {
    $article = new Article();
    $article->set('title', 'Mondongo');

    $this->mondongo->save('Article', $article);

    $this->assertEquals(1, $this->db->article->find()->count());
  }

  public function testSaveMultiple()
  {
    $articles = array();
    for ($i = 1; $i <= 10; $i++)
    {
      $articles[$i] = $article = new Article();
      $article->set('title', 'Mondongo '.$i);
    }

    $this->mondongo->save('Article', $articles);

    $this->assertEquals(10, $this->db->article->find()->count());
  }

  public function testDelete()
  {
    $articles = array();
    for ($i = 1; $i <= 10; $i++)
    {
      $articles[$i] = $article = new Article();
      $article->set('title', 'Mondongo');
    }
    $this->mondongo->save('Article', $articles);

    $article = array_pop($articles);
    $this->mondongo->delete('Article', $article);

    $this->assertEquals(9, $this->db->article->find()->count());
    $this->assertEquals(0, $this->db->article->find(array('_id' => $article->getId()))->count());

    $delete = array($articles[5], $articles[6]);
    $this->mondongo->delete('Article', $delete);

    $this->assertEquals(7, $this->db->article->find()->count());
    $this->assertEquals(0, $this->db->article->find(array(
      '_id' => array('$in' => array($articles[5]->getId(), $articles[6]->getId()))
    ))->count());
  }
}
