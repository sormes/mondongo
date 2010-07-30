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
 * Base class for documents speed.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class MondongoDocumentBaseSpeed implements ArrayAccess
{
  static protected $setters = array();

  static protected $getters = array();

  protected $data = array();

  protected $fieldsModified = array();

  /*
   * Definition
   */
  public function getDefinition()
  {
    return MondongoContainer::getDefinition(get_class($this));
  }

  /*
   * Modified.
   */
  public function isModified()
  {
    return (bool) $this->fieldsModified;
  }

  public function getFieldsModified()
  {
    return $this->fieldsModified;
  }

  public function clearFieldsModified()
  {
    $this->fieldsModified = array();
  }

  public function revertFieldsModified()
  {
    foreach ($this->fieldsModified as $name => $value)
    {
      $this->data['fields'][$name] = $value;
    }
    $this->clearFieldsModified();
  }

  /*
   * setData
   */
  public function setData($data, $closureToPHP = null)
  {
    if (isset($data['_id']))
    {
      $this->id = $data['_id'];
      unset($data['_id']);
    }

    if (null === $closureToPHP)
    {
      $closureToPHP = $this->getDefinition()->getClosureToPHP();
    }

    $closureToPHP($data, $this->data);

    if ($data)
    {
      foreach ($data as $name => $value)
      {
        $this->doSet($name, $value);
      }
    }

    // PERFORMANCE
    /*
    $this->clearFieldsModified();
    */
    $this->fieldsModified = array();
  }

  /*
   * set, get
   */
  public function set($name, $value)
  {
    $class = get_class($this);

    if (!isset(self::$setters[$class]))
    {
      self::$setters[$class] = array();

      foreach (array_keys($this->getDefinition()->getFields()) as $fieldName)
      {
        if (method_exists($this, $method = 'set'.MondongoInflector::camelize($fieldName)))
        {
          self::$setters[$class][$fieldName] = $method;
        }
      }
    }

    if (isset(self::$setters[$class][$name]))
    {
      $method = self::$setters[$class][$name];

      return $this->$method($value);
    }

    return $this->doSet($name, $value);
  }

  public function get($name)
  {
    $class = get_class($this);

    if (!isset(self::$getters[$class]))
    {
      self::$getters[$class] = array();

      foreach (array_keys($this->getDefinition()->getFields()) as $fieldName)
      {
        if (method_exists($this, $method = 'get'.MondongoInflector::camelize($fieldName)))
        {
          self::$getters[$class][$fieldName] = $method;
        }
      }
    }

    if (isset(self::$getters[$class][$name]))
    {
      $method = self::$getters[$class][$name];

      return $this->$method();
    }

    return $this->doGet($name);
  }

  /*
   * doSet.
   */
  protected function doSet($name, $value, $modified = true)
  {
    // fields
    if (isset($this->data['fields']) && array_key_exists($name, $this->data['fields']))
    {
      if ($modified)
      {
        if (!array_key_exists($name, $this->fieldsModified))
        {
          // PERFORMANCE
          /*
          $type = $this->getDefinition()->getField($name)->getType();

          if (null === $this->data['fields'][$name] || $type->toMongo($this->data['fields'][$name]) != $type->toMongo($value))
          {
            $this->fieldsMmodified[$name] = $this->data['fields'][$name];
          }
          */
          $this->fieldsModified[$name] = $this->data['fields'][$name];
        }
        else if ($value === $this->fieldsModified[$name])
        {
          unset($this->fieldsModified[$name]);
        }
      }

      $this->data['fields'][$name] = $value;

      return;
    }

    // references
    if (isset($this->data['references']) && array_key_exists($name, $this->data['references']))
    {
      $reference = $this->getDefinition()->getReference($name);

      $class = $reference['class'];
      $field = $reference['field'];

      // one
      if ('one' == $reference['type'])
      {
        if (!$value instanceof $class)
        {
          throw new InvalidArgumentException(sprintf('The reference "%s" is not a instance of "%s".', $name, $class));
        }

        $referenceValue = $value->getId();
      }
      // many
      else
      {
        if (!$value instanceof MondongoGroup)
        {
          throw new InvalidArgumentException(sprintf('The reference "%s" is not a instance of MondongoGroup.', $name));
        }
        $value->setCallback(array($this, 'updateReferences'));

        $referenceValue = array();
        foreach ($value as $v)
        {
          if (!$v instanceof $class)
          {
            throw new InvalidArgumentException(sprintf('The reference "%s" is not a instance of "%s".', $name, $class));
          }

          $referenceValue[] = $v->getId();
        }
      }

      $this->set($reference['field'], $referenceValue);
      $this->data['references'][$name] = $value;

      return;
    }

    // more
    if ($this->hasDoSetMore($name))
    {
      $this->doSetMore($name, $value, $modified);

      return;
    }

    throw new InvalidArgumentException(sprintf('The data "%s" does not exists.', $name));
  }

  protected function hasDoSetMore($name)
  {
    return false;
  }

  protected function doSetMore($name, $value, $modified)
  {
  }

  /*
   * doGet.
   */
  protected function doGet($name)
  {
    // fields
    if (isset($this->data['fields']) && array_key_exists($name, $this->data['fields']))
    {
      return $this->data['fields'][$name];
    }

    // references
    if (isset($this->data['references']) && array_key_exists($name, $this->data['references']))
    {
      if (null === $this->data['references'][$name])
      {
        $reference = $this->getDefinition()->getReference($name);

        $class = $reference['class'];
        $field = $reference['field'];

        $id = $this->get($field);

        $repository = MondongoContainer::getForName($class)->getRepository($class);

        // one
        if ('one' == $reference['type'])
        {
          $value = $repository->get($id);
        }
        // many
        else
        {
          foreach ($id as &$i)
          {
            $i = $i;
          }

          if ($value = $repository->find(array('_id' => array('$in' => $id))))
          {
            $value = new MondongoGroupArray($value, array($this, 'updateReferences'));
          }
        }

        if (!$value)
        {
          throw new RuntimeException(sprintf('The reference "%s" does not exists.', $name));
        }

        $this->data['references'][$name] = $value;
      }

      return $this->data['references'][$name];
    }

    // more
    if ($this->hasDoGetMore($name))
    {
      return $this->doGetMore($name);
    }

    throw new InvalidArgumentException(sprintf('The data "%s" does not exists.', $name));
  }

  protected function hasDoGetMore($name)
  {
    return false;
  }

  protected function doGetMore($name)
  {
  }

  /*
   * UpdateReferences.
   */
  public function updateReferences()
  {
    if (isset($this->data['references']))
    {
      foreach ($this->data['references'] as $name => $value)
      {
        if ($value instanceof MondongoGroup)
        {
          $reference = $this->getDefinition()->getReference($name);

          $field = $reference['field'];

          $ids = array();
          foreach ($value as $v)
          {
            $ids[] = $v->getId();
          }

          if ($this->data['fields'][$field] != $ids)
          {
            $this->set($field, $ids);
          }
        }
      }
    }
  }

  /*
   * toArray, fromArray.
   */
  public function fromArray(array $array)
  {
    foreach ($array as $name => $value)
    {
      $this->set($name, $value);
    }
  }

  public function toArray()
  {
    $array = array();

    if (isset($this->data['fields']))
    {
      foreach ($this->data['fields'] as $name => $value)
      {
        if (null !== $value)
        {
          $array[$name] = $value;
        }
      }
    }

    return $array;
  }

  /*
   * Magic Setters.
   */
  public function __set($name, $value)
  {
    return $this->set($name, $value);
  }

  public function __get($name)
  {
    return $this->get($name);
  }

  /*
   * ArrayAccess.
   */
  public function offsetSet($name, $value)
  {
    return $this->set($name, $value);
  }

  public function offsetGet($name)
  {
    return $this->get($name);
  }

  public function offsetExists($name)
  {
    throw new LogicException('Cannot isset data.');
  }

  public function offsetUnset($name)
  {
    throw new LogicException('Cannot isset data.');
  }

  /*
   * mutators
   */
  protected function getMutators()
  {
    return array_merge(
      array_keys(isset($this->data['fields']) ? $this->data['fields'] : array()),
      array_keys(isset($this->data['references']) ? $this->data['references'] : array())
    );
  }

  /*
   * __call
   */
  public function __call($name, $arguments)
  {
    if (0 === strpos($name, 'set') || 0 === strpos($name, 'get'))
    {
      $datum = MondongoInflector::underscore(substr($name, 3));

      if (in_array($datum, $this->getMutators()))
      {
        array_unshift($arguments, $datum);

        return call_user_func_array(array($this, substr($name, 0, 3)), $arguments);
      }
    }

    throw new BadMethodCallException(sprintf('The method "%s" does not exists.', $name));
  }
}
