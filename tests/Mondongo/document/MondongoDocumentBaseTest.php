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

class MondongoDocumentBaseTest extends MondongoTestCase
{
  public function testModified()
  {
    $article = new Article();

    $this->assertTrue($article->isModified());
    $this->assertSame(array('is_active' => null), $article->getFieldsModified());

    $article->clearFieldsModified();
    $this->assertFalse($article->isModified());
    $this->assertSame(array(), $article->getFieldsModified());

    $article = new Article();
    $article->set('is_active', null);
    $this->assertSame(array(), $article->getFieldsModified());
  }

  public function testSetGetFields()
  {
    $document = new MondongoDocumentTesting();

    $this->assertNull($document->get('field1'));
    $this->assertEquals('Field2', $document->get('field2'));

    $document->set('field1', 'Field1');
    $this->assertEquals('Field1', $document->get('field1'));
  }

  public function testSettersGetters()
  {
    $document = new SetterGetter();

    $document->set('setter', 'FooBar');
    $this->assertSame('foobar', $document->get('setter'));

    $document->set('getter', 'FooBar');
    $this->assertSame('FOOBAR', $document->get('getter'));
  }

  public function testSetGetReferencesOne()
  {
    $author = new Author();
    $author->set('name', 'Pablo Díez');
    $this->mondongo->save('Author', $author);

    // set
    $article = new Article();
    $article->set('author', $author);

    $this->assertEquals($author->getId(), $article->get('author_id'));

    // get
    $article = new Article();
    $article->set('author_id', $author->getId());

    $this->assertEquals($author, $article->get('author'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testSetReferencesOneInvalidClass()
  {
    $article = new Article();
    $article->set('author', new DateTime());
  }

  public function testSetGetReferenceMany()
  {
    $categories = array();
    for ($i = 1; $i <= 4; $i++)
    {
      $categories[] = $category = new Category();
      $category->set('name', 'Category '.$i);
    }
    $this->mondongo->save('Category', $categories);

    // set
    $article = new Article();
    $article->set('categories', $group = new MondongoGroup(array($categories[1], $categories[2])));
    $this->assertSame(
      array($categories[1]->getId(), $categories[2]->getId()),
      $article->get('category_ids')
    );

    $group->add($categories[3]);
    $this->assertEquals(
      array($categories[1]->getId(), $categories[2]->getId(), $categories[3]->getId()),
      $article->get('category_ids')
    );

    // get
    $categoryIds = array();
    foreach ($categories as $category)
    {
      $categoryIds[] = $category->getId();
    }

    $article = new Article();
    $article->set('category_ids', $categoryIds);

    $retval = $article->get('categories');
    $this->assertEquals('MondongoGroup', get_class($retval));
    $this->assertEquals($categories, $retval->getElements());
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testSetReferencesManyInvalidGroup()
  {
    $article = new Article();
    $article->set('categories', array(new Category(), new Category()));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testSetReferencesManyInvalidClass()
  {
    $article = new Article();
    $article->set('categories', array(new Category(), new DateTime()));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testDoSetDataNotExists()
  {
    $document = new MondongoDocumentTesting();

    $document->set('no', 'foo');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testDoGetDataNotExists()
  {
    $document = new MondongoDocumentTesting();

    $document->get('no');
  }

  public function testMagicSetters()
  {
    $article = new Article();

    $article->title = 'Mondongo';
    $this->assertEquals('Mondongo', $article->get('title'));
    $this->assertEquals('Mondongo', $article->title);
  }

  public function testArrayAccess()
  {
    $article = new Article();

    $article['title'] = 'Mondongo';
    $this->assertEquals('Mondongo', $article->get('title'));
    $this->assertEquals('Mondongo', $article['title']);
  }

  /**
   * @expectedException LogicException
   */
  public function testArrayAccessIssetLogicException()
  {
    $article = new Article();
    isset($article['title']);
  }

  /**
   * @expectedException LogicException
   */
  public function testArrayAccessUnsetLogicException()
  {
    $article = new Article();
    unset($article['title']);
  }

  public function testMutatorsFields()
  {
    $article = new Article();

    $article->setTitle('foobar');
    $this->assertEquals('foobar', $article->get('title'));

    $article->set('title', 'barfoo');
    $this->assertEquals('barfoo', $article->getTitle());
  }

  public function testMutatorsReferences()
  {
    $author = new Author();
    $author->set('name', 'Pablo Díez');
    $this->mondongo->save('Author', $author);

    $article = new Article();

    $article->setAuthor($author);
    $this->assertEquals($author->getId(), $article->getAuthorId());
    $this->assertSame($author, $article->getAuthor());
  }

  /**
   * @expectedException BadMethodCallException
   */
  public function testCallBadMethodCallException()
  {
    $article = new Article();

    $article->badMethodCall();
  }
}
