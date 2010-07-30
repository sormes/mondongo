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
 * Container for Mondongos.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class MondongoContainer
{
  static protected $default;

  static protected $mondongos = array();

  static protected $definitions = array();

  /*
   * Default
   */
  static public function setDefault(Mondongo $mondongo)
  {
    self::$default = $mondongo;
  }

  static public function hasDefault()
  {
    return null !== self::$default;
  }

  static public function getDefault()
  {
    if (!self::hasDefault())
    {
      throw new RuntimeException('The default Mondongo does not exists.');
    }

    return self::$default;
  }

  static public function clearDefault()
  {
    self::$default = null;
  }

  /*
   * Mondongos.
   */
  static public function setForName($name, Mondongo $mondongo)
  {
    self::$mondongos[$name] = $mondongo;
  }

  static public function hasForName($name)
  {
    return isset(self::$mondongos[$name]);
  }

  static public function getForName($name)
  {
    if (!isset(self::$mondongos[$name]))
    {
      if (!self::hasDefault())
      {
        throw new InvalidArgumentException(sprintf('The Mondongo for name "%s" does not exists.', $name));
      }

      self::$mondongos[$name] = self::getDefault();
    }

    return self::$mondongos[$name];
  }

  static public function removeForName($name)
  {
    if (!isset(self::$mondongos[$name]))
    {
      throw new InvalidArgumentException(sprintf('The Mondongo for name "%s" does not exists.', $name));
    }

    unset(self::$mondongos[$name]);
  }

  static public function clearForNames()
  {
    self::$mondongos = array();
  }

  /**
   * Definitions.
   */
  static public function getDefinition($name)
  {
    if (!isset(self::$definitions[$name]))
    {
      $r = new ReflectionClass($name);
      if ($r->isSubClassOf('MondongoDocumentEmbed'))
      {
        $class = 'MondongoDefinitionDocumentEmbed';
      }
      else
      {
        $class = 'MondongoDefinitionDocument';
      }

      $definition = new $class($name);
      call_user_func(array($name, 'define'), $definition);
      $definition->close();

      self::$definitions[$name] = $definition;
    }

    return self::$definitions[$name];
  }

  static public function getDefinitions()
  {
    return self::$definitions;
  }

  static public function clearDefinitions()
  {
    self::$definitions = array();
  }
}
