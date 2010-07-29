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

class MondongoGroupArrayTest extends MondongoTestCase
{
  protected $elements = array('foo' => 'foobar', 'bar' => 'barfoo');

  protected $callback = array();

  protected $value = false;

  public function setUp()
  {
    $this->callback = array($this, 'changeValue');
    $this->value    = false;
  }

  public function changeValue()
  {
    $this->value = true;
  }

  public function testConstructorSetElementsGetElements()
  {
    $group = new MondongoGroupArray($this->elements);
    $this->assertSame($this->elements, $group->getElements());

    $group->setElements($elements = array('ups', 'spu'));
    $this->assertSame($elements, $group->getElements());
  }

  public function testCallback()
  {
    $group = new MondongoGroupArray(array(), 'callback');

    $this->assertEquals('callback', $group->getCallback());
    $group->setCallback('foobar');
    $this->assertEquals('foobar', $group->getCallback());
  }

  public function testAdd()
  {
    $elements = $this->elements;
    $group    = new MondongoGroupArray($elements);

    $group->add('ups');
    array_push($elements, 'ups');
    $this->assertSame($elements, $group->getElements());

    $group->setCallback($this->callback);
    $group->add('ups');
    $this->assertTrue($this->value);
  }

  public function testSet()
  {
    $elements = $this->elements;
    $group    = new MondongoGroupArray($elements);

    $group->set('ups', 'spu');
    $elements['ups'] = 'spu';
    $this->assertSame($elements, $group->getElements());

    $group->setCallback($this->callback);
    $group->set('foobar', true);
    $this->assertTrue($this->value);
  }

  public function testExists()
  {
    $group = new MondongoGroupArray($this->elements);

    $this->assertTrue($group->exists('foo'));
    $this->assertFalse($group->exists('no'));
  }

  public function testExistsElement()
  {
    $group = new MondongoGroupArray(array('foo' => $date = new DateTime(), 'bar' => 12));

    $this->assertTrue($group->existsElement($date));
    $this->assertTrue($group->existsElement(12));

    $this->assertFalse($group->indexOf(new DateTime()));
    $this->assertFalse($group->indexOf('foo'));
  }

  public function testIndexOf()
  {
    $group = new MondongoGroupArray(array('foo' => $date = new DateTime(), 'bar' => 12));

    $this->assertSame('foo', $group->indexOf($date));
    $this->assertSame('bar', $group->indexOf(12));

    $this->assertFalse($group->indexOf(new DateTime()));
    $this->assertFalse($group->indexOf('foo'));
  }

  public function testRemove()
  {
    $group = new MondongoGroupArray($this->elements);

    $group->remove('bar');

    $this->assertTrue($group->exists('foo'));
    $this->assertFalse($group->exists('bar'));

    $group->setCallback($this->callback);
    $group->remove('foo');
    $this->assertTrue($this->value);
  }

  public function testClear()
  {
    $group = new MondongoGroupArray($this->elements);

    $group->clear();
    $this->assertSame(array(), $group->getElements());

    $group = new MondongoGroupArray($this->elements, $this->callback);
    $group->clear();
    $this->assertTrue($this->value);
  }

  public function testArrayAccess()
  {
    $group = new MondongoGroupArray($this->elements);

    $this->assertTrue(isset($group['foo']));
    $this->assertFalse(isset($group['no']));

    $this->assertSame('foobar', $group['foo']);
    $this->assertNull($group['no']);

    $group['ups'] = 'spu';
    $this->assertSame('spu', $group['ups']);

    unset($group['ups']);
    $this->assertNull($group['ups']);
  }

  public function testCountable()
  {
    $group = new MondongoGroupArray($this->elements);

    $this->assertSame(2, $group->count());
    $this->assertSame(2, count($group));
  }
}
