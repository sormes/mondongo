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

/**
 * MondongoGroupArray.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class MondongoGroupArray implements MondongoGroup
{
  protected $elements = array();

  protected $callback = array();

  /*
   * Constructor.
   */
  public function __construct(array $elements = array(), $callback = null)
  {
    $this->elements = $elements;
    $this->callback = $callback;
  }

  /*
   * setElements, getElements.
   */
  public function setElements(array $elements)
  {
    $this->elements = $elements;
  }

  public function getElements()
  {
    return $this->elements;
  }

  /*
   * Callback.
   */
  public function setCallback($callback)
  {
    $this->callback = $callback;
  }

  public function getCallback()
  {
    return $this->callback;
  }

  protected function callback()
  {
    if ($this->callback)
    {
      call_user_func($this->callback, $this);
    }
  }

  /*
   * Methods.
   */
  public function add($element)
  {
    $this->elements[] = $element;

    $this->callback();
  }

  public function set($key, $element)
  {
    $this->elements[$key] = $element;

    $this->callback();
  }

  public function exists($key)
  {
    return isset($this->elements[$key]);
  }

  public function existsElement($element)
  {
    return in_array($element, $this->elements, true);
  }

  public function indexOf($element)
  {
    return array_search($element, $this->elements, true);
  }

  public function get($key)
  {
    return isset($this->elements[$key]) ? $this->elements[$key] : null;
  }

  public function remove($key)
  {
    unset($this->elements[$key]);

    $this->callback();
  }

  public function clear()
  {
    $this->elements = array();

    $this->callback();
  }

  /*
   * ArrayAccess.
   */
  public function offsetExists($key)
  {
    return $this->exists($key);
  }

  public function offsetSet($key, $element)
  {
    return $this->set($key, $element);
  }

  public function offsetGet($key)
  {
    return $this->get($key);
  }

  public function offsetUnset($key)
  {
    return $this->remove($key);
  }

  /*
   * Countable.
   */
  public function count()
  {
    return count($this->elements);
  }

  /*
   * IteratorAggregate.
   */
  public function getIterator()
  {
    return new ArrayIterator($this->elements);
  }
}
