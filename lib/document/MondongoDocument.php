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
 * Abstract class for documents.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class MondongoDocument extends MondongoDocumentBase
{
  protected $id;

  /**
   * Returns the Mondongo (using MondongoContainer).
   *
   * @return Mondongo The Mondongo.
   */
  public function getMondongo()
  {
    return MondongoContainer::getForName(get_class($this));
  }

  /**
   * Returns the Repository (using MondongoContainer).
   *
   * @return MondongoRepository The repository.
   */
  public function getRepository()
  {
    return $this->getMondongo()->getRepository(get_class($this));
  }

  /**
   * Returns if the document is new.
   *
   * @return bool Returns if the document is new.
   */
  public function isNew()
  {
    return null === $this->id;
  }

  /**
   * Set the MongoId.
   *
   * @param MongoId The MongoId.
   *
   * @return void
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * Returns the MongoId.
   *
   * @return MongoId The MongoId.
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Save the document (using MondongoContainer).
   *
   * @return void
   */
  public function save()
  {
    $this->getRepository()->save($this);
  }

  /*
   * Delete the document (using MondongoContainer).
   *
   * @return void
   */
  public function delete()
  {
    $this->getRepository()->delete($this);
  }

  /**
   * Returns the query for save.
   *
   * @return array The query for save.
   */
  public function getQueryForSave()
  {
    $query = array();

    // fields
    foreach (array_keys($this->getFieldsModified()) as $field)
    {
      if ($this->isNew())
      {
        $query[$field] = $this->data['fields'][$field];
      }
      else
      {
        if (null === $value = $this->data['fields'][$field])
        {
          $query['$unset'][$field] = 1;
        }
        else
        {
          $query['$set'][$field] = $value;
        }
      }
    }

    if ($this->isNew())
    {
      $closure = $this->getDefinition()->getClosureToMongo();
      $query   = $closure($query);
    }
    else if (isset($query['$set']))
    {
      $closure       = $this->getDefinition()->getClosureToMongo();
      $query['$set'] = $this->getDefinition()->dataToMongo($query['$set']);
    }

    // embeds
    if (isset($this->data['embeds']))
    {
      foreach (array_keys($this->data['embeds']) as $name)
      {
        $embed = $this->get($name);

        if (null !== $embed)
        {
          $value = $this->queryForSaveEmbed($embed);

          if ($this->isNew())
          {
            $query[$name] = $value;
          }
          else
          {
            $query['$set'][$name] = $value;
          }
        }
      }
    }

    return $query;
  }

  protected function queryForSaveEmbed($embed)
  {
    // one
    if ($embed instanceof MondongoDocumentEmbed)
    {
      $definition = $embed->getDefinition();

      if ($value = $embed->toArray(false))
      {
        $value = $definition->dataToMongo($value);
      }

      foreach ($definition->getEmbeds() as $name => $embedDefinition)
      {
        $value[$name] = $this->queryForSaveEmbed($embed->get($name));
      }
    }
    // many
    else
    {
      $value = array();
      foreach ($embed as $key => $e)
      {
        $value[$key] = $this->queryForSaveEmbed($e);
      }
    }

    return $value;
  }

  /**
   * @see MondongoDocumentBaseSpeed
   */
  protected function hasDoGetMore($name)
  {
    return isset($this->data['relations']) ? array_key_exists($name, $this->data['relations']) : false;
  }

  /**
   * @see MondongoDocumentBaseSpeed
   */
  protected function doGetMore($name)
  {
    // relations
    if (isset($this->data['relations']) && array_key_exists($name, $this->data['relations']))
    {
      if (null === $this->data['relations'][$name])
      {
        $relation = $this->getDefinition()->getRelation($name);

        $class = $relation['class'];
        $field = $relation['field'];

        // one
        if ('one' == $relation['type'])
        {
          $value = MondongoContainer::getForName($class)->getRepository($class)->findOne(array($field => $this->getId()));
        }
        // many
        else
        {
          $value = MondongoContainer::getForName($class)->getRepository($class)->find(array($field => $this->getId()));
        }

        $this->data['relations'][$name] = $value;
      }

      return $this->data['relations'][$name];
    }
  }

  /**
   * @see MondongoDocumentBaseSpeed
   */
  protected function getMutators()
  {
    return array_merge(
      parent::getMutators(),
      isset($this->data['relations']) ? array_keys($this->data['relations']) : array()
    );
  }

  /**
   * __call
   *
   * @param string $name      The function name.
   * @param array  $arguments The arguments.
   *
   * @return mixed The return of the extension.
   *
   * @throws BadMethodCallException If the method does not exists.
   */
  public function __call($name, $arguments)
  {
    try
    {
      return parent::__call($name, $arguments);
    }
    catch (BadMethodCallException $e)
    {
    }

    foreach ($this->getDefinition()->getExtensions() as $extension)
    {
      if (method_exists($extension, $method = $name))
      {
        $extension->setInvoker($this);
        $retval = call_user_func_array(array($extension, $method), $arguments);
        $extension->clearInvoker();

        return $retval;
      }
    }

    throw new BadMethodCallException(sprintf('The method "%s" does not exists.', $name));
  }
}
