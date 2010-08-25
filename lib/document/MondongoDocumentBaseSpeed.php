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

  /**
   * Returns the definition (using MondongoContainer).
   *
   * @return MondongoDefinition The definition.
   */
  public function getDefinition()
  {
    return MondongoContainer::getDefinition(get_class($this));
  }

  /**
   * Returns if the document is modified.
   *
   * @return bool Returns if the document is modified.
   */
  public function isModified()
  {
    if ($this->fieldsModified)
    {
      return true;
    }

    if (isset($this->data['embeds']))
    {
      foreach ($this->data['embeds'] as $embed)
      {
        if (null !== $embed)
        {
          if ($embed instanceof MondongoDocumentEmbed)
          {
            if ($embed->isModified())
            {
              return true;
            }
          }
          else
          {
            foreach ($embed as $e)
            {
              if ($e->isModified())
              {
                return true;
              }
            }
          }
        }
      }
    }

    return false;
  }

  /**
   * Returns the fields modified with old values.
   *
   * @return array The fields modified.
   */
  public function getFieldsModified()
  {
    return $this->fieldsModified;
  }

  /**
   * Clear the fields modified.
   *
   * @return void
   */
  public function clearFieldsModified()
  {
    $this->fieldsModified = array();
  }

  /**
   * Revert the fields modified.
   *
   * @return void
   */
  public function revertFieldsModified()
  {
    foreach ($this->fieldsModified as $name => $value)
    {
      $this->data['fields'][$name] = $value;
    }
    $this->clearFieldsModified();
  }

  /**
   * Clear the modifieds of the document.
   *
   * @return void
   */
  public function clearModified()
  {
    $this->clearFieldsModified();

    if (isset($this->data['embeds']))
    {
      foreach ($this->data['embeds'] as $embed)
      {
        if (null !== $embed)
        {
          if ($embed instanceof MondongoDocumentEmbed)
          {
            $embed->clearFieldsModified();
          }
          else
          {
            foreach ($embed as $e)
            {
              $e->clearFieldsModified();
            }
          }
        }
      }
    }
  }

  /**
   * Set the data of the document (hydrate).
   *
   * @param array   $data    The data.
   * @param Closure $closure The closure to PHP.
   *
   * @return void
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
      $this->fromArray($data);
    }

    // PERFORMANCE
    /*
    $this->clearFieldsModified();
    */
    $this->fieldsModified = array();
  }

  /**
   * Set a datum.
   *
   * @param string $name  The name.
   * @param mixed  $value The value.
   *
   * @return void
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

  /**
   * Returns a datum.
   *
   * @param string $name The name.
   *
   * @return mixed The datum.
   */
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

  /**
   * Do the set of a datum,
   *
   * @param string $name     The name.
   * @param mixed  $value    The value.
   * @param bool   $modified If the change is modified or no.
   *
   * @return void
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

    // embeds
    if (isset($this->data['embeds']) && array_key_exists($name, $this->data['embeds']))
    {
      $embed = $this->getDefinition()->getEmbed($name);
      $class = $embed['class'];

      // one
      if ('one' == $embed['type'])
      {
        if (!$value instanceof $class)
        {
          throw new InvalidArgumentException(sprintf('The embed "%s" is not a instance of "%s".', $name, $class));
        }
      }
      // many
      else
      {
        if (!$value instanceof MondongoGroup)
        {
          throw new InvalidArgumentException(sprintf('The embed "%s" is not a instanceof MondongoGroup.', $name));
        }

        foreach ($value as $v)
        {
          if (!$v instanceof $class)
          {
            throw new InvalidArgumentException(sprintf('The embed "%s" is not a instance of "%s".', $name, $class));
          }
        }

      }

      $this->data['embeds'][$name] = $value;

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

  /**
   * Returns if has more doSet for a name.
   *
   * @param string $name The name,
   *
   * @return bool Returns if has more doSet.
   */
  protected function hasDoSetMore($name)
  {
    return false;
  }

  /**
   * Process more doSet.
   *
   * @param string $name     The name.
   * @param mixed  $value    The value.
   * @param bool   $modified If the change is modified or no.
   *
   * @return void
   */
  protected function doSetMore($name, $value, $modified)
  {
  }

  /**
   * Do the get of a datum.
   *
   * @param string The name.
   *
   * @return mixed The datum.
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
            $value = new MondongoGroup($value, array($this, 'updateReferences'));
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

    // embeds
    if (isset($this->data['embeds']) && array_key_exists($name, $this->data['embeds']))
    {
      if (null === $this->data['embeds'][$name])
      {
        $embed = $this->getDefinition()->getEmbed($name);
        $class = $embed['class'];

        // one
        if ('one' == $embed['type'])
        {
          $value = new $class();
        }
        // many
        else
        {
          $value = new MondongoGroup();
        }

        $this->data['embeds'][$name] = $value;
      }

      return $this->data['embeds'][$name];
    }

    // more
    if ($this->hasDoGetMore($name))
    {
      return $this->doGetMore($name);
    }

    throw new InvalidArgumentException(sprintf('The data "%s" does not exists.', $name));
  }

  /**
   * Returns if has more doGet for a name.
   *
   * @param string $name The name,
   *
   * @return bool Returns if has more doGet.
   */
  protected function hasDoGetMore($name)
  {
    return false;
  }

  /**
   * Process more doGet.
   *
   * @param string $name The name.
   *
   * @return void
   */
  protected function doGetMore($name)
  {
  }

  /**
   * Update the references.
   *
   * @return void
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

  /**
   * Import the data from array.
   *
   * @param array $array The array.
   *
   * @return void
   */
  public function fromArray(array $array)
  {
    foreach ($array as $name => $value)
    {
      if (isset($this->data['fields']) && array_key_exists($name, $this->data['fields']))
      {
        $this->set($name, $value);

        continue;
      }

      if (isset($this->data['embeds']) && array_key_exists($name, $this->data['embeds']))
      {
        $embed = $this->get($name);

        // one
        if ($embed instanceof MondongoDocumentEmbed)
        {
          $embed->fromArray($value);
        }
        // many
        else
        {
          $embedDefinition = $this->getDefinition()->getEmbed($name);
          $class           = $embedDefinition['class'];

          $elements = array();
          foreach ($value as $datum)
          {
            $elements[] = $element = new $class();
            $element->fromArray($datum);
          }

          $embed->setElements($elements);
        }

        continue;
      }

      throw new InvalidArgumentException(sprintf('The data "%s" does not exists.', $name));
    }
  }

  /**
   * Export the data to array.
   *
   * @param bool $withEmbeds If export embeds (TRUE by default).
   *
   * @return array The data.
   */
  public function toArray($withEmbeds = true)
  {
    $array = array();

    if (isset($this->data['fields']))
    {
      foreach ($this->data['fields'] as $name => $value)
      {
        if (null === $value)
        {
          continue;
        }

        $array[$name] = $value;
      }
    }

    if ($withEmbeds && isset($this->data['embeds']))
    {
      foreach ($this->data['embeds'] as $name => $value)
      {
        if (null === $value)
        {
          continue;
        }

        // one
        if ($value instanceof MondongoDocumentEmbed)
        {
          $array[$name] = $value->toArray();
        }
        // many
        else
        {
          $arrayEmbed = array();
          foreach ($value as $key => $element)
          {
            $arrayEmbed[$key] = $element->toArray();
          }

          $array[$name] = $arrayEmbed;
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

  /**
   * Returns the mutators.
   *
   * @return array The mutators.
   */
  protected function getMutators()
  {
    return array_merge(
      isset($this->data['fields']) ? array_keys($this->data['fields']) : array(),
      isset($this->data['references']) ? array_keys($this->data['references']) : array(),
      isset($this->data['embeds']) ? array_keys($this->data['embeds']) : array()
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
