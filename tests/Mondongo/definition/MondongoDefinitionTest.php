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

class MondongoDefinitionTesting extends MondongoDefinition
{
}

class MondongoDefinitionTest extends MondongoTestCase
{
  public function testConstructor()
  {
    $definition = new MondongoDefinitionTesting('Category');
    $this->assertEquals('Category', $definition->getName());
  }

  public function testClose()
  {
    $definition = new MondongoDefinitionTesting('Category');

    $this->assertFalse($definition->isClosed());
    $definition->close();
    $this->assertTrue($definition->isClosed());
  }

  /**
   * @expectedException RuntimeException
   */
  public function testCloseWhenIsClosed()
  {
    $definition = new MondongoDefinitionTesting('Category');
    $definition->close();
    $definition->close();
  }

  public function testName()
  {
    $definition = new MondongoDefinitionTesting('Category');
    $definition->setName('Article');

    $this->assertEquals('Article', $definition->getName());
  }

  /**
   * @expectedException RuntimeException
   */
  public function testSetNameWhenIsClosed()
  {
    $definition = new MondongoDefinitionTesting('Article');
    $definition->close();

    $definition->setName('Category');
  }

  public function testFields()
  {
    $definition = new MondongoDefinitionTesting('Article');

    $this->assertSame($definition, $definition->setFields(array(
      'title'   => 'string',
      'content' => array('type' => 'string', 'default' => 'empty'),
      'date'    => 'date',
    )));;

    $this->assertTrue($definition->hasField('title'));
    $this->assertFalse($definition->hasField('no'));

    $this->assertSame(array('type' => 'string'), $definition->getField('title'));
    $this->assertSame(array('type' => 'string', 'default' => 'empty'), $definition->getField('content'));
    $this->assertSame(array('type' => 'date'), $definition->getField('date'));

    $this->assertSame($definition, $definition->setField('is_active', 'boolean'));

    $this->assertSame(array(
      'title'     => array('type' => 'string'),
      'content'   => array('type' => 'string', 'default' => 'empty'),
      'date'      => array('type' => 'date'),
      'is_active' => array('type' => 'boolean'),
    ), $definition->getFields());

    return $definition;
  }

  /**
   * @depends           testFields
   * @expectedException InvalidArgumentException
   */
  public function testGetFieldNotExists($definition)
  {
    $definition->getField('no');
  }

  public function testReferences()
  {
    $definition = new MondongoDefinitionTesting('Article');
    $definition->setFields(array(
      'author_id'    => 'id',
      'category_ids' => 'raw',
    ));

    $author     = array('class' => 'Author', 'field' => 'author_id', 'type' => 'one');
    $categories = array('class' => 'Category', 'field' => 'category_ids', 'type' => 'many');

    $this->assertSame($definition, $definition->reference('author', $author));
    $this->assertSame($definition, $definition->reference('categories', $categories));

    $this->assertTrue($definition->hasReference('author'));
    $this->assertFalse($definition->hasReference('no'));

    $this->assertSame($author, $definition->getReference('author'));
    $this->assertSame($categories, $definition->getReference('categories'));

    $this->assertSame(array(
      'author'     => $author,
      'categories' => $categories,
    ), $definition->getReferences());

    return $definition;
  }

  /**
   * @depends           testReferences
   * @expectedException InvalidArgumentException
   */
  public function testGetReferenceNotExists($definition)
  {
    $definition->getReference('no');
  }

  public function testEmbeds()
  {
    $definition = new MondongoDefinitionDocument('Author');

    $this->assertFalse($definition->hasEmbed('address'));

    $address  = array('class' => 'Address', 'type' => 'one');
    $comments = array('class' => 'Comment', 'type' => 'many');

    $this->assertSame($definition, $definition->embed('address', $address));
    $this->assertTrue($definition->hasEmbed('address'));
    $this->assertSame($address, $definition->getEmbed('address'));

    $this->assertSame($definition, $definition->embed('comments', $comments));
    $this->assertSame($comments, $definition->getEmbed('comments'));

    $this->assertSame(array('address' => $address, 'comments' => $comments), $definition->getEmbeds());
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testGetEmbedNotExists()
  {
    $definition = new MondongoDefinitionDocument('Author');
    $definition->getEmbed('no');
  }

  public function testDefault()
  {
    $definition = new MondongoDefinitionTesting('Article');
    $definition->setFields(array(
      'foo'       => 'string',
      'bar'       => array('type' => 'string', 'default' => 'barfoo'),
      'foobar_id' => 'id',
    ));
    $definition->reference('foobar', array('class' => 'Foobar', 'field' => 'foobar_id', 'type' => 'one'));
    $definition->close();

    $this->assertSame(array(
      'fields'     => array('foo' => null, 'bar' => 'barfoo', 'foobar_id' => null),
      'references' => array('foobar' => null),
      'embeds'     => array(),
    ), $definition->getDefaultData());

    $this->assertSame(array(
      'bar' => null,
    ), $definition->getDefaultFieldsModified());
  }

  public function testDataTo()
  {
    $definition = new MondongoDefinitionTesting('Article');
    $definition->setFields(array(
      'title'     => 'string',
      'is_active' => 'boolean',
      'options'   => 'array',
    ));

    $this->assertSame(array(
      'title'     => '123',
      'is_active' => true,
      'options'   => serialize(array('foo' => 'bar')),
    ), $definition->dataToMongo(array(
      'title'     => 123,
      'is_active' => 1,
      'options'   => array('foo' => 'bar'),
    )));

    $this->assertSame(array(
      'title'     => '123',
      'is_active' => false,
      'options'   => array('foo' => 'bar'),
    ), $definition->dataToPHP(array(
      'title'     => 123,
      'is_active' => 0,
      'options'   => serialize(array('foo' => 'bar')),
    )));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testDataToMongoFieldNotExists()
  {
    $definition = new MondongoDefinitionTesting('Article');
    $definition->dataToMongo(array('foo' => 'bar'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testDataToPHPFieldNotExists()
  {
    $definition = new MondongoDefinitionTesting('Article');
    $definition->dataToPHP(array('foo' => 'bar'));
  }
}
