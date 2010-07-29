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
 * Container of types.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class MondongoTypeContainer
{
  static protected $map = array(
    'array'    => 'MondongoTypeArray',
    'bin_data' => 'MondongoTypeBinData',
    'boolean'  => 'MondongoTypeBoolean',
    'date'     => 'MondongoTypeDate',
    'file'     => 'MondongoTypeFile',
    'float'    => 'MondongoTypeFloat',
    'id'       => 'MondongoTypeId',
    'integer'  => 'MondongoTypeInteger',
    'raw'      => 'MondongoTypeRaw',
    'string'   => 'MondongoTypeString',
  );

  static protected $types = array();

  static public function hasType($name)
  {
    return isset(self::$map[$name]);
  }

  static public function addType($name, $class)
  {
    if (self::hasType($name))
    {
      throw new LogicException(sprintf('The type "%s" already exists.', $name));
    }

    $r = new ReflectionClass($class);
    if (!$r->isSubclassOf('MondongoType'))
    {
      throw new InvalidArgumentException(sprintf('The class "%s" is not a subclass of MondongoType.', $class));
    }

    self::$map[$name] = $class;
  }

  static public function getType($name)
  {
    if (!isset(self::$types[$name]))
    {
      if (!self::hasType($name))
      {
        throw new RuntimeException(sprintf('The type "%s" does not exists.', $name));
      }

      self::$types[$name] = new self::$map[$name]();
    }

    return self::$types[$name];
  }

  static public function removeType($name)
  {
    if (!self::hasType($name))
    {
      throw new RuntimeException(sprintf('The type "%s" does not exists.', $name));
    }

    unset(self::$map[$name], self::$types[$name]);
  }

  static public function resetTypes()
  {
    self::$map = array(
      'array'    => 'MondongoTypeArray',
      'bin_data' => 'MondongoTypeBinData',
      'boolean'  => 'MondongoTypeBoolean',
      'date'     => 'MondongoTypeDate',
      'file'     => 'MondongoTypeFile',
      'float'    => 'MondongoTypeFloat',
      'id'       => 'MondongoTypeId',
      'integer'  => 'MondongoTypeInteger',
      'raw'      => 'MondongoTypeRaw',
      'string'   => 'MondongoTypeString',
    );

    self::$types = array();
  }
}
