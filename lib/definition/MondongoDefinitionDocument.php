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
 * Definition for documents.
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

  /*
   * Close.
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

  /*
   * DefaultData.
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

  /*
   * HasFile.
   */
  public function hasFile()
  {
    return $this->hasFile;
  }

  /*
   * Events
   */
  public function getEvents()
  {
    $this->checkClosed();

    return $this->events;
  }

  /*
   * Connection.
   */
  public function setConnection($connection)
  {
    $this->connection = $connection;

    return $this;
  }

  public function getConnection()
  {
    return $this->connection;
  }

  /*
   * Collection.
   */
  public function setCollection($collection)
  {
    $this->collection = $collection;

    return $this;
  }

  public function getCollection()
  {
    return null !== $this->collection ? $this->collection : MondongoInflector::underscore($this->getName());
  }

  /*
   * Embeds.
   */
  public function embed($name, array $embed)
  {
    $this->checkName($name);

    $this->embeds[$name] = $embed;

    return $this;
  }

  public function hasEmbed($name)
  {
    return isset($this->embeds[$name]);
  }

  public function getEmbeds()
  {
    return $this->embeds;
  }

  public function getEmbed($name)
  {
    if (!$this->hasEmbed($name))
    {
      throw new InvalidArgumentException(sprintf('The embed "%s" does not exists.', $name));
    }

    return $this->embeds[$name];
  }

  /*
   * Relations
   */
  public function relation($name, array $relation)
  {
    $this->checkName($name);

    $this->relations[$name] = $relation;

    return $this;
  }

  public function hasRelation($name)
  {
    return isset($this->relations[$name]);
  }

  public function getRelations()
  {
    return $this->relations;
  }

  public function getRelation($name)
  {
    if (!$this->hasRelation($name))
    {
      throw new InvalidArgumentException(sprintf('The relation "%s" does not exists.', $name));
    }

    return $this->relations[$name];
  }

  /*
   * Extensions.
   */
  public function addExtension(MondongoExtension $extension)
  {
    $this->extensions[] = $extension;

    return $this;
  }

  public function getExtensions()
  {
    return $this->extensions;
  }

  /*
   * Indexes.
   */
  public function addIndex(array $index)
  {
    $this->indexes[] = $index;

    return $this;
  }

  public function getIndexes()
  {
    return $this->indexes;
  }

  /*
   * CheckName.
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
