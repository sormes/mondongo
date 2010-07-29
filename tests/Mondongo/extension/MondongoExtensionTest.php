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

class MondongoExtensionTest extends MondongoTestCase
{
  public function testConstruct()
  {
    $definition = $definition = new MondongoDefinitionDocument('Article');
    $extension  = new MondongoExtensionTesting($definition, array('option1' => 15));

    $this->assertEquals(15, $extension->getOption('option1'));
    $this->assertTrue($definition->hasField('testing'));
  }

  /**
   * @expectedException RuntimeException
   */
  public function testConstructOptionsNotExists()
  {
    new MondongoExtensionTesting(new MondongoDefinitionDocument('Article'), array('foo' => 'bar'));
  }

  public function testGetDefinition()
  {
    $definition = $definition = new MondongoDefinitionDocument('Article');
    $extension  = new MondongoExtensionTesting($definition);

    $this->assertSame($definition, $extension->getDefinition());
  }

  public function testGetOptions()
  {
    $extension = new MondongoExtensionTesting(new MondongoDefinitionDocument('Article'));

    $this->assertEquals(array('option1' => 10, 'option2' => 20, 'field' => 'testing'), $extension->getOptions());
  }

  public function testInvoker()
  {
    $extension = new MondongoExtensionTesting(new MondongoDefinitionDocument('Article'));

    $invoker = new DateTime();

    $this->assertNull($extension->getInvoker());

    $extension->setInvoker($invoker);
    $this->assertSame($invoker, $extension->getInvoker());

    $extension->clearInvoker();
    $this->assertNull($extension->getInvoker());
  }
}
