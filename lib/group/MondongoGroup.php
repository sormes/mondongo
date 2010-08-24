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
 * Mondongo Groups.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class MondongoGroup implements ArrayAccess, Countable, IteratorAggregate
{
  protected $elements = array();

  protected $callback = array();

  /*
   * Constructor.
   *
   * @param array $elements An array of elements (optional).
   * @param mixed $callback A callback for the changes (optional).
   *
   * @return void
   */
  public function __construct(array $elements = array(), $callback = null)
  {
    $this->elements = $elements;
    $this->callback = $callback;
  }

  /*
   * Set the elements.
   *
   * @param array $elements An array of elements.
   *
   * return void
   */
  public function setElements(array $elements)
  {
    $this->elements = $elements;
  }

  /**
   * Returns the elements.
   *
   * @return array The elements.
   */
  public function getElements()
  {
    return $this->elements;
  }

  /*
   * Set the callback for the changes.
   *
   * @param mixed $callback A callback.
   *
   * @return voic
   */
  public function setCallback($callback)
  {
    $this->callback = $callback;
  }

  /**
   * Returns the callback for the changes.
   *
   * @return mixed The callback.
   */
  public function getCallback()
  {
    return $this->callback;
  }

  /**
   * Call the callback for the changes.
   *
   * @return void
   */
  protected function callback()
  {
    if ($this->callback)
    {
      call_user_func($this->callback, $this);
    }
  }

  /**
   * Add an element.
   *
   * @param mixed $element An element.
   *
   * @return void
   */
  public function add($element)
  {
    $this->elements[] = $element;

    $this->callback();
  }

  /**
   * Set an element.
   *
   * @param mixed $key     The key.
   * @param mixed $element The element.
   *
   * @return void
   */
  public function set($key, $element)
  {
    $this->elements[$key] = $element;

    $this->callback();
  }

  /**
   * Returns if exists an element by key.
   *
   * @param mixed $key The key.
   *
   * @return boolean Returns if exists an element.
   */
  public function exists($key)
  {
    return isset($this->elements[$key]);
  }

  /**
   * Returns if exists an element by element.
   *
   * @param mixed $element The element.
   *
   * @return boolean Returns if exists an element.
   */
  public function existsElement($element)
  {
    return in_array($element, $this->elements, true);
  }

  /**
   * Returns the key of an element.
   *
   * @param mixed $elemen The element.
   *
   * @return mixed The key if the element exists, NULL otherwise.
   */
  public function indexOf($element)
  {
    return array_search($element, $this->elements, true);
  }

  /**
   * Get an element by key.
   *
   * @param mixed $key The key.
   *
   * @return mixed The element if exists, NULL otherwise.
   */
  public function get($key)
  {
    return isset($this->elements[$key]) ? $this->elements[$key] : null;
  }

  /**
   * Remove an element by key.
   *
   * @param mixed $key The key.
   *
   * @return void
   */
  public function remove($key)
  {
    unset($this->elements[$key]);

    $this->callback();
  }

  /**
   * Clear the group.
   *
   * @return void
   */
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
