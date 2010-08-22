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
 * Interface of Mondongo Groups.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
interface MondongoGroup extends ArrayAccess, Countable, IteratorAggregate
{
  /**
   * Add an element.
   *
   * @param mixed $element An element.
   *
   * @return void
   */
  public function add($element);

  /**
   * Set an element.
   *
   * @param mixed $key     The key.
   * @param mixed $element The element.
   *
   * @return void
   */
  public function set($key, $element);

  /**
   * Returns if exists an element by key.
   *
   * @param mixed $key The key.
   *
   * @return boolean Returns if exists an element.
   */
  public function exists($key);

  /**
   * Returns if exists an element by element.
   *
   * @param mixed $element The element.
   *
   * @return boolean Returns if exists an element.
   */
  public function existsElement($element);

  /**
   * Returns the key of an element.
   *
   * @param mixed $elemen The element.
   *
   * @return mixed The key if the element exists, NULL otherwise.
   */
  public function indexOf($element);

  /**
   * Get an element by key.
   *
   * @param mixed $key The key.
   *
   * @return mixed The element if exists, NULL otherwise.
   */
  public function get($key);

  /**
   * Remove an element by key.
   *
   * @param mixed $key The key.
   *
   * @return void
   */
  public function remove($key);

  /**
   * Clear the group.
   *
   * @return void
   */
  public function clear();
}
