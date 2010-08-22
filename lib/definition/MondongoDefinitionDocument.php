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
 * Class to define documents.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class MondongoDefinitionDocument extends MondongoDefinition
{
  static protected $typeEvents = array(
    'preInsert',
    'postInsert',
    'preUpdate',
    'postUpdate',
    'preSave',
    'postSave',
    'preDelete',
    'postDelete',
  );

  protected $hasFile;

  protected $events = array();

  protected $connection;

  protected $collection;

  protected $embeds = array();

  protected $relations = array();

  protected $extensions = array();

  protected $indexes = array();

  /**
   * @see MondongoDefinition
   */
  protected function doClose()
  {
    parent::doClose();

    // file
    $this->hasFile = false;
    foreach ($this->getFields() as $name => $field)
    {
      if (MondongoTypeContainer::getType($field['type']) instanceof MondongoTypeFile)
      {
        if ($this->hasFile)
        {
          throw new RuntimeException('Two file types in the same document.');
        }
        if ('file' != $name)
        {
          throw new RuntimeException('The field name of file is not "file".');
        }
        $this->hasFile = true;
      }
    }

    // events > document
    $r = new ReflectionClass($this->getName());
    foreach (self::$typeEvents as $event)
    {
      if ($r->hasMethod($event))
      {
        $this->events['document'][$event] = true;
      }
    }

    // events > extensions
    foreach ($this->getExtensions() as $key => $extension)
    {
      $r = new ReflectionClass(get_class($extension));
      foreach (self::$typeEvents as $event)
      {
        if ($r->hasMethod($event))
        {
          $this->events['extensions'][$event][$key] = true;
        }
      }
    }
  }

  /**
   * @see MondongoDefinition
   */
  protected function generateDefaultData()
  {
    $data = parent::generateDefaultData();

    // embeds
    $data['embeds'] = array();
    foreach (array_keys($this->getEmbeds()) as $name)
    {
      $data['embeds'][$name] = null;
    }

    // relations
    $data['relations'] = array();
    foreach (array_keys($this->getRelations()) as $name)
    {
      $data['relations'][$name] = null;
    }

    return $data;
  }

  /**
   * Returns if the document has file.
   *
   * @return boolean Returns if the document has file.
   */
  public function hasFile()
  {
    return $this->hasFile;
  }

  /**
   * Return the events of the document and extensions.
   *
   * @return array The events of the document and extensions.
   *
   * @throws LogicException If the definitions is not closed.
   */
  public function getEvents()
  {
    $this->checkClosed();

    return $this->events;
  }

  /**
   * Set the connection name.
   *
   * @param string $connection The connection name.
   *
   * @return MondongoDefinitionDocument The current instance.
   */
  public function setConnection($connection)
  {
    $this->connection = $connection;

    return $this;
  }

  /**
   * Return the connection name.
   *
   * @return mixed The connection name.
   */
  public function getConnection()
  {
    return $this->connection;
  }

  /**
   * Set the collection name.
   *
   * @param string $collection The collection name.
   *
   * @return MondongoDefinitionDocument The current instance.
   */
  public function setCollection($collection)
  {
    $this->collection = $collection;

    return $this;
  }

  /**
   * Return the collection name.
   *
   * By default the unserscore of the  document name.
   *
   * @return string The collection name.
   */
  public function getCollection()
  {
    return null !== $this->collection ? $this->collection : MondongoInflector::underscore($this->getName());
  }

  /*
   * Add an embed.
   *
   * @param string $name  The embed name.
   * @param array  $embed The embed definition.
   *
   * @return MondongoDefinitionDocument The current instance.
   *
   * @throws LogicException If the name is busy.
   */
  public function embed($name, array $embed)
  {
    $this->checkName($name);

    $this->embeds[$name] = $embed;

    return $this;
  }

  /**
   * Returns if an embed exists.
   *
   * @param string $name The embed name.
   *
   * @return boolean Returns if the embed exists.
   */
  public function hasEmbed($name)
  {
    return isset($this->embeds[$name]);
  }

  /**
   * Return the embeds definitions.
   *
   * @return array The embeds definitions.
   */
  public function getEmbeds()
  {
    return $this->embeds;
  }

  /**
   * Return an embed definition.
   *
   * @param string $name The embed name.
   *
   * @return array The embed definition.
   *
   * @throws InvalidArgumentException If the embed does not exists.
   */
  public function getEmbed($name)
  {
    if (!$this->hasEmbed($name))
    {
      throw new InvalidArgumentException(sprintf('The embed "%s" does not exists.', $name));
    }

    return $this->embeds[$name];
  }

  /**
   * Add a relation definition.
   *
   * @param string $name     The relation name.
   * @param array  $relation The relation definition.
   *
   * @return MondongoDefinitionDocument The current instance.
   *
   * @throws LogicException If the name is busy.
   */
  public function relation($name, array $relation)
  {
    $this->checkName($name);

    $this->relations[$name] = $relation;

    return $this;
  }

  /**
   * Returns if a relation exists.
   *
   * @param string $name The relation name.
   *
   * @return boolean Returns if the relation exists.
   */
  public function hasRelation($name)
  {
    return isset($this->relations[$name]);
  }

  /**
   * Returns the relations definitions.
   *
   * @return array The relations definitions.
   */
  public function getRelations()
  {
    return $this->relations;
  }

  /**
   * Return a relation definition.
   *
   * @param string $name The relation name.
   *
   * @return array The relation definition.
   *
   * @throws InvalidArgumentException If the relation does not exists.
   */
  public function getRelation($name)
  {
    if (!$this->hasRelation($name))
    {
      throw new InvalidArgumentException(sprintf('The relation "%s" does not exists.', $name));
    }

    return $this->relations[$name];
  }

  /**
   * Add a extension.
   *
   * @param MondongoExtension A Mondongo extension.
   *
   * @return MondongoDefinitionDocument The current instance.
   */
  public function addExtension(MondongoExtension $extension)
  {
    $this->extensions[] = $extension;

    return $this;
  }

  /**
   * Returns the extensions.
   *
   * @return array The extensions.
   */
  public function getExtensions()
  {
    return $this->extensions;
  }

  /**
   * Add an index.
   *
   * @param array $index An index definition.
   *
   * @return MondongoDefinitionDocument The current instance.
   */
  public function addIndex(array $index)
  {
    $this->indexes[] = $index;

    return $this;
  }

  /**
   * Return the indexes definitions.
   *
   * @return array The indexes definitions.
   */
  public function getIndexes()
  {
    return $this->indexes;
  }

  /**
   * @see MondongoDefinition
   */
  protected function doCheckName($name)
  {
    return
      parent::doCheckName($name)
      ||
      $this->hasEmbed($name)
      ||
      $this->hasRelation($name)
    ;
  }
}
