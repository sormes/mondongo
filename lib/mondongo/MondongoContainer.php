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

  /**
   * Set the default Mondongo.
   *
   * @param Mondongo $mondongo A Mondongo.
   *
   * @return void
   */
  static public function setDefault(Mondongo $mondongo)
  {
    self::$default = $mondongo;
  }

  /**
   * Returns if exists the default Mondongo.
   *
   * @return boolean If exists the default Mondongo.
   */
  static public function hasDefault()
  {
    return null !== self::$default;
  }

  /**
   * Returns the default Mondongo.
   *
   * @return Mondongo The default Mondongo.
   *
   * @throws RuntimeException If the default Mondongo does not exists.
   */
  static public function getDefault()
  {
    if (!self::hasDefault())
    {
      throw new RuntimeException('The default Mondongo does not exists.');
    }

    return self::$default;
  }

  /**
   * Clear the default Mondongo.
   *
   * @return void
   */
  static public function clearDefault()
  {
    self::$default = null;
  }

  /**
   * Set a Mondongo for a document name.
   *
   * @param string   $name     The document name.
   * @param Mondongo $mondongo The Mondongo.
   *
   * @return void
   */
  static public function setForName($name, Mondongo $mondongo)
  {
    self::$mondongos[$name] = $mondongo;
  }

  /**
   * Returns if exists a Mondongo for a document name.
   *
   * @param string $name The document name.
   *
   * @return boolean Returns if exists the Mondongo.
   */
  static public function hasForName($name)
  {
    return isset(self::$mondongos[$name]);
  }

  /**
   * Return the Mondongo for a document name.
   *
   * @param string $name The document name.
   *
   * @return Mondongo The Mondongo.
   *
   * @throws InvalidArgumentException If does not exists the Mondongo for the name and the default Mondongo.
   */
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

  /**
   * Remove the Mondongo for a document name.
   *
   * @param string $name The document name.
   *
   * @return void
   *
   * @throws InvalidArgumentException If does not exists the Mondongo for the name.
   */
  static public function removeForName($name)
  {
    if (!isset(self::$mondongos[$name]))
    {
      throw new InvalidArgumentException(sprintf('The Mondongo for name "%s" does not exists.', $name));
    }

    unset(self::$mondongos[$name]);
  }

  /**
   * Clear the Mondongos for the document names.
   *
   * @return void
   */
  static public function clearForNames()
  {
    self::$mondongos = array();
  }

  /**
   * Returns a definition.
   *
   * @param string $name The document name.
   *
   * @return MondongoDefinition The definition.
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

  /**
   * Returns the definitions.
   *
   * @return array The definitions.
   */
  static public function getDefinitions()
  {
    return self::$definitions;
  }

  /**
   * Clear the definitons.
   *
   * @return void
   */
  static public function clearDefinitions()
  {
    self::$definitions = array();
  }
}
