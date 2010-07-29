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

class MondongoTypeTest extends MondongoType
{
  public function toMongo($value)
  {
    return $value;
  }

  public function toPHP($value)
  {
    return $value;
  }

  public function closureToMongo()
  {
  }

  public function closureToPHP()
  {
  }
}

class MondongoTypeContainerTest extends MondongoTestCase
{
  public function setup()
  {
    MondongoTypeContainer::resetTypes();
  }

  public function testHasType()
  {
    $this->assertTrue(MondongoTypeContainer::hasType('string'));
    $this->assertFalse(MondongoTypeContainer::hasType('no'));
  }

  public function testAddType()
  {
    MondongoTypeContainer::addType('test', 'MondongoTypeTest');
    $this->assertTrue(MondongoTypeContainer::hasType('test'));

    $type = MondongoTypeContainer::getType('test');
    $this->assertSame('MondongoTypeTest', get_class($type));
  }

  /**
   * @expectedException LogicException
   */
  public function testAddTypeAlreadyExists()
  {
    MondongoTypeContainer::addType('string', 'MondongoTypeTest');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testAddTypeInvalidClass()
  {
    MondongoTypeContainer::addType('type', 'DateTime');
  }

  public function testGetType()
  {
    $string = MondongoTypeContainer::getType('string');
    $float  = MondongoTypeContainer::getType('float');

    $this->assertEquals('MondongoTypeString', get_class($string));
    $this->assertEquals('MondongoTypeFloat',  get_class($float));

    $this->assertSame($string, MondongoTypeContainer::getType('string'));
    $this->assertSame($float,  MondongoTypeContainer::getType('float'));
  }

  /**
   * @expectedException RuntimeException
   */
  public function testGetTypeNotExists()
  {
    MondongoTypeContainer::getType('no');
  }

  public function testRemoveTypeMap()
  {
    MondongoTypeContainer::removeType('string');
    $this->assertFalse(MondongoTypeContainer::hasType('string'));
  }

  /**
   * @expectedException RuntimeException
   */
  public function removeTypeTypes()
  {
    $string = MondongoTypeContainer::getType('string');
    MondongoTypeContainer::removeType('string');

    MondongoTypeContainer::getType('string');
  }

  public function testResetTypes()
  {
    MondongoTypeContainer::addType('test', 'MondongoTypeTest');
    MondongoTypeContainer::resetTypes();

    $this->assertFalse(MondongoTypeContainer::hasType('test'));
    $this->assertTrue(MondongoTypeContainer::hasType('string'));
  }
}
