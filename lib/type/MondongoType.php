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
 * Abstract class for types.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class MondongoType
{
  /**
   * Convert a PHP value to Mongo value.
   *
   * @param mixed $value A value.
   *
   * @return The Mongo value.
   */
  abstract public function toMongo($value);

  /**
   * Convert a Mongo value to PHP value.
   *
   * @param mixed $value A value.
   *
   * @return The PHP value.
   */
  abstract public function toPHP($value);

  /**
   * Convert a PHP value to Mongo value (for closures).
   *
   * @return string The string for the closure.
   */
  abstract public function closureToMongo();

  /**
   * Convert a Mongo value to PHP value (for closures).
   *
   * @return string The string for the closure.
   */
  abstract public function closureToPHP();
}
