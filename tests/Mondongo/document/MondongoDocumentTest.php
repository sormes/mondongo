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

class MondongoDocumentTest extends MondongoTestCase
{
  public function testGetMondongo()
  {
    $article = new Article();
    $this->assertSame($this->mondongo, $article->getMondongo());
  }

  public function testGetRepository()
  {
    $article = new Article();
    $this->assertSame($this->mondongo->getRepository('Article'), $article->getRepository());
  }

  public function testModifiedEmbedOne()
  {
    $article = new Article();
    $article->clearFieldsModified();

    $source = new Source();
    $article->set('source', $source);

    $this->assertFalse($article->isModified());

    $source->set('title', 'Ups');
    $this->assertTrue($article->isModified());

    $article->clearModified();
    $this->assertFalse($article->isModified());
    $this->assertFalse($source->isModified());
  }

  public function testModifiedEmbedMany()
  {
    $article = new Article();
    $article->clearFieldsModified();

    $comments = new MondongoGroupArray(array($comment = new Comment()));
    $article->set('comments', $comments);

    $this->assertFalse($article->isModified());

    $comment->set('name', 'Ups');
    $this->assertTrue($article->isModified());

    $article->clearModified();
    $this->assertFalse($article->isModified());
    $this->assertFalse($comment->isModified());
  }

  public function testNew()
  {
    $article = new Article();
    $this->assertTrue($article->isNew());

    $article->setId(new MongoId('123'));
    $this->assertFalse($article->isNew());
  }

  public function testId()
  {
    $article = new Article();
    $this->assertNull($article->getId());

    $article->setId('123');
    $this->assertSame('123', $article->getId());
  }

  public function testQueryForSave()
  {
    $article = new Article();
    $this->assertSame(array('is_active' => false), $article->getQueryForSave());

    $article->set('options', $options = array('foo' => 'bar'));
    $this->assertSame(array('is_active' => false, 'options' => serialize($options)), $article->getQueryForSave());

    $source = new Source();
    $source->set('title', 'Foo');
    $article->set('source', $source);

    $comment1 = new Comment();
    $comment1->set('name', 'Pablo');
    $comment2 = new Comment();
    $comment2->set('email', 'foo@bar.com');
    $article->set('comments', new MondongoGroupArray(array($comment1, $comment2)));

    $this->assertSame(array(
      'is_active' => false,
      'options'   => serialize($options),
      'source'    => array('title' => 'Foo'),
      'comments'  => array(
        array('name'  => 'Pablo'),
        array('email' => 'foo@bar.com'),
      ),
    ), $article->getQueryForSave());

    $this->mondongo->save('Article', $article);

    $article->set('title', 'Mondongo');
    $article->set('is_active', true);

    $this->assertSame(array('$set' => array(
      'title'     => 'Mondongo',
      'is_active' => true,
      'source'    => array('title' => 'Foo'),
      'comments'  => array(
        array('name'  => 'Pablo'),
        array('email' => 'foo@bar.com'),
      ),
    )), $article->getQueryForSave());

    $article = new Article();
    $article->set('title', 'Mondongo');
    $this->mondongo->save('Article', $article);
    $article->set('title', null);

    $this->assertSame(array(
      '$unset' => array('title' => 1),
    ), $article->getQueryForSave());
  }

  public function testEmbedsOne()
  {
    $article = new Article();

    $source = $article->get('source');
    $this->assertEquals('Source', get_class($source));
    $this->assertSame($source, $article->get('source'));

    $article = new Article();

    $source = new Source();
    $article->set('source', $source);
    $this->assertSame($source, $article->get('source'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testEmbedsOneInvalidClass()
  {
    $article = new Article();
    $article->set('source', new Category());
  }

  public function testEmbedsMany()
  {
    $article = new Article();

    $comments = $article->get('comments');
    $this->assertEquals('MondongoGroupArray', get_class($comments));
    $this->assertSame($comments, $article->get('comments'));

    $article = new Article();

    $comments = new MondongoGroupArray(array(new Comment(), new Comment()));
    $article->set('comments', $comments);
    $this->assertSame($comments, $article->get('comments'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testEmbedsManyInvalidGroup()
  {
    $article = new Article();
    $article->set('comments', array(new Comment()));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testEmbedsManyInvalidClass()
  {
    $article = new Article();
    $article->set('comments', new MondongoGroupArray(array(new Comment(), new DateTime())));
  }

  public function testRelationsOne()
  {
    $author = new Author();
    $author->set('name', 'Pablo');
    $this->mondongo->save('Author', $author);

    $this->assertNull($author->get('address'));

    $address = new Address();
    $address->set('author', $author);
    $address->set('calle', 'Mayor');
    $this->mondongo->save('Address', $address);

    $this->assertEquals($address->getId(), $author->get('address')->getId());
  }

  public function testRelationsMany()
  {
    $author = new Author();
    $author->set('name', 'Pablo');
    $this->mondongo->save('Author', $author);

    $this->assertNull($author->get('articles'));

    $articles = array();
    for ($i = 1; $i <= 5; $i++)
    {
      $articles[$i] = $article = new Article();
      $article->set('author', $author);
    }
    $this->mondongo->save('Article', $articles);

    $ids = array();
    foreach ($articles as $article)
    {
      $ids[] = $article->getId();
    }

    $retval = $author->get('articles');
    $this->assertSame(5, count($retval));
    foreach ($retval as $r)
    {
      $this->assertTrue(in_array($r->getId(), $ids));
    }
  }

  public function testSave()
  {
    $article = new Article();
    $article->set('title', 'Mondongo');
    $article->save();

    $this->assertEquals(1, $this->db->article->find()->count());
    $this->assertFalse($article->isNew());

    $result = $this->db->article->findOne();

    $this->assertEquals($article->getId(), $result['_id']);
    $this->assertEquals('Mondongo', $result['title']);
  }

  public function testDelete()
  {
    $article = new Article();
    $article->set('title', 'Mondongo');
    $this->mondongo->getRepository('Article')->save($article);

    $article->delete();

    $this->assertEquals(0, $this->db->article->find()->count());
  }

  public function testMutatorsEmbeds()
  {
    $source  = new Source();
    $article = new Article();

    $article->setSource($source);
    $this->assertSame($source, $article->getSource());
  }

  public function testMutatorsRelations()
  {
    $author = new Author();
    $author->set('name', 'Pablo');
    $this->mondongo->save('Author', $author);

    $address = new Address();
    $address->setAuthor($author);
    $this->mondongo->save('Address', $address);

    $this->assertEquals($address->getId(), $author->getAddress()->getId());
  }

  public function testExtensionsMethods()
  {
    $author = new Author();

    $this->assertSame('DocumentMethodFoo', $author->documentMethod('Foo'));
  }

  /**
   * @expectedException BadMethodCallException
   */
  public function testExtensionsMethodBadMethodCallException()
  {
    $author = new Author();

    $author->extensionBadMethod();
  }
}