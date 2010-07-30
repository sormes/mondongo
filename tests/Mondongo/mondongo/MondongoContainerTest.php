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

class MondongoContainerTest extends MondongoTestCase
{
  public function setUp()
  {
    MondongoContainer::clearDefault();
    MondongoContainer::clearForNames();
  }

  public function testDefault()
  {
    $mondongo = new Mondongo();

    $this->assertFalse(MondongoContainer::hasDefault());

    MondongoContainer::setDefault($mondongo);
    $this->assertSame($mondongo, MondongoContainer::getDefault());

    $this->assertTrue(MondongoContainer::hasDefault());

    MondongoContainer::clearDefault();
    $this->assertFalse(MondongoContainer::hasDefault());
  }

  /**
   * @expectedException RuntimeException
   */
  public function testGetDefaultNotExists()
  {
    MondongoContainer::getDefault();
  }

  public function testMondongos()
  {
    $mondongo1 = new Mondongo();
    $mondongo2 = new Mondongo();

    $this->assertFalse(MondongoContainer::hasForName('Article'));

    MondongoContainer::setForName('Article', $mondongo1);
    MondongoContainer::setForName('Category', $mondongo2);
    MondongoContainer::setForName('User', $mondongo2);

    $this->assertTrue(MondongoContainer::hasForName('Article'));
    $this->assertSame($mondongo1, MondongoContainer::getForName('Article'));
    $this->assertSame($mondongo2, MondongoContainer::getForName('Category'));

    MondongoContainer::removeForName('Category');
    $this->assertFalse(MondongoContainer::hasForName('Category'));
    $this->assertTrue(MondongoContainer::hasForName('Article'));

    MondongoContainer::clearForNames();
    $this->assertFalse(MondongoContainer::hasForName('Article'));
    $this->assertFalse(MondongoContainer::hasForName('User'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testGetForNameNotExists()
  {
    MondongoContainer::getForName('Article');
  }

  public function testDefinitions()
  {
    $definition1 = $definition = MondongoContainer::getDefinition('MondongoDocumentTesting');

    $this->assertTrue(is_object($definition));
    $this->assertEquals('MondongoDefinitionDocument', get_class($definition));
    $this->assertSame('MondongoDocumentTesting', $definition->getName());
    $this->assertTrue($definition->hasField('field1'));
    $this->assertTrue($definition->isClosed());
    $this->assertSame($definition, MondongoContainer::getDefinition('MondongoDocumentTesting'));

    $definition2 = $definition = MondongoContainer::getDefinition('Source');

    $this->assertTrue(is_object($definition));
    $this->assertEquals('MondongoDefinitionDocumentEmbed', get_class($definition));
    $this->assertSame('Source', $definition->getName());
    $this->assertTrue($definition->hasField('title'));
    $this->assertSame($definition, MondongoContainer::getDefinition('Source'));

    $this->assertSame(array(
      'MondongoDocumentTesting' => $definition1,
      'Source'                  => $definition2,
    ), MondongoContainer::getDefinitions());

    MondongoContainer::clearDefinitions();

    $this->assertSame(array(), MondongoContainer::getDefinitions());
  }
}
