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

class MondongoDefinitionDocumentTest extends MondongoTestCase
{
  public function testDefault()
  {
    $definition = MondongoContainer::getDefinition('Article');

    $this->assertSame(array(
      'fields'     => array(
        'author_id'    => null,
        'title'        => null,
        'content'      => null,
        'category_ids' => null,
        'is_active'    => false,
        'options'      => null,
      ),
      'references' => array(
        'author'     => null,
        'categories' => null,
      ),
      'embeds'     => array(
        'source'   => null,
        'comments' => null,
      ),
      'relations'  => array(),
    ), $definition->getDefaultData());
  }

  public function testHasFile()
  {
    $definition = new MondongoDefinitionDocument('MondongoDocumentEvents');

    $this->assertNull($definition->hasFile());

    $definition->setFields(array(
      'title' => 'string',
      'file'  => 'file',
    ));
    $definition->close();

    $this->assertTrue($definition->hasFile());
  }

  /**
   * @expectedException RuntimeException
   */
  public function testHasFileFieldNameNotFile()
  {
    $definition = new MondongoDefinitionDocument('MondongoDocumentEvents');

    $definition->setFields(array(
      'file1' => 'file',
    ));
    $definition->close();
  }

  /**
   * @expectedException RuntimeException
   */
  public function testHasFileDoCloseTwoFiles()
  {
    $definition = new MondongoDefinitionDocument('MondongoDocumentEvents');

    $definition->setFields(array(
      'file1' => 'file',
      'file2' => 'file',
    ));
    $definition->close();
  }

  public function testDoCloseEvents()
  {
    $definition = MondongoContainer::getDefinition('MondongoDocumentEvents');

    $this->assertSame(array(
      'document' => array(
        'preInsert'  => true,
        'postInsert' => true,
        'preUpdate'  => true,
        'postUpdate' => true,
        'preSave'    => true,
        'postSave'   => true,
        'preDelete'  => true,
        'postDelete' => true,
      ),
      'extensions' => array(
        'preInsert'  => array(0 => true),
        'postInsert' => array(0 => true),
        'preUpdate'  => array(0 => true),
        'postUpdate' => array(0 => true),
        'preSave'    => array(0 => true),
        'postSave'   => array(0 => true),
        'preDelete'  => array(0 => true),
        'postDelete' => array(0 => true),
      ),
    ), $definition->getEvents());
  }

  /**
   * @expectedException RuntimeException
   */
  public function getEventsWhenNotClosed()
  {
    $definition = new MondongoDefinitionDocument('MondongoDocumentEvents');
    $definition->getEvents();
  }

  public function testConnection()
  {
    $definition = new MondongoDefinitionDocument('Article');

    $this->assertNull($definition->getConnection());

    $this->assertSame($definition, $definition->setConnection('global'));
    $this->assertEquals('global', $definition->getConnection());
  }

  public function testCollection()
  {
    $definition = new MondongoDefinitionDocument('ArticleCategory');

    $this->assertEquals('article_category', $definition->getCollection());

    $this->assertSame($definition, $definition->setCollection('foobar'));
    $this->assertEquals('foobar', $definition->getCollection());
  }

  public function testRelations()
  {
    $definition = new MondongoDefinitionDocument('Author');

    $this->assertFalse($definition->hasRelation('articles'));

    $articles = array('class' => 'Article', 'field' => 'author_id', 'type' => 'many');
    $address  = array('class' => 'AuthorAddress', 'field' => 'author_id', 'type' => 'one');

    $this->assertSame($definition, $definition->relation('articles', $articles));
    $this->assertTrue($definition->hasRelation('articles'));
    $this->assertSame($articles, $definition->getRelation('articles'));

    $this->assertSame($definition, $definition->relation('address', $address));
    $this->assertSame($address, $definition->getRelation('address'));

    $this->assertSame(array('articles' => $articles, 'address' => $address), $definition->getRelations());
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testGetRelationNotExists()
  {
    $definition = new MondongoDefinitionDocument('Author');
    $definition->getRelation('no');
  }

  public function testExtensions()
  {
    $definition = new MondongoDefinitionDocument('Article');

    $this->assertSame(array(), $definition->getExtensions());

    $extension1 = new MondongoExtensionTesting($definition, array('field' => 'field1'));
    $extension2 = new MondongoExtensionTesting($definition, array('field' => 'field2'));

    $this->assertSame($definition, $definition->addExtension($extension1));
    $this->assertSame($definition, $definition->addExtension($extension2));

    $this->assertSame(array($extension1, $extension2), $definition->getExtensions());
  }

  public function testIndexes()
  {
    $definition = new MondongoDefinitionDocument('Article');

    $this->assertSame(array(), $definition->getIndexes());

    $index1 = array('field1');
    $index2 = array('field1', 'field2');

    $this->assertSame($definition, $definition->addIndex($index1));
    $this->assertSame($definition, $definition->addIndex($index2));

    $this->assertSame(array($index1, $index2), $definition->getIndexes());
  }
}
