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
 * Mondongo.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Mondongo
{
  const VERSION = '0.9.0';

  protected $connections = array();

  protected $defaultConnectionName;

  protected $definitions = array();

  protected $repositories = array();

  protected $logCallable;

  /*
   * Connections.
   */
  public function setConnections(array $connections)
  {
    $this->connections = array();
    foreach ($connections as $name => $connection)
    {
      $this->setConnection($name, $connection);
    }
  }

  public function setConnection($name, MondongoConnection $connection)
  {
    $this->connections[$name] = $connection;
  }

  public function removeConnection($name)
  {
    $this->checkConnection($name);

    unset($this->connections[$name]);
  }

  public function clearConnections()
  {
    $this->connections = array();
  }

  public function hasConnection($name)
  {
    return isset($this->connections[$name]);
  }

  public function getConnection($name)
  {
    $this->checkConnection($name);

    return $this->connections[$name];
  }

  public function getConnections()
  {
    return $this->connections;
  }

  public function setDefaultConnectionName($name)
  {
    $this->defaultConnectionName = $name;
  }

  public function getDefaultConnectionName()
  {
    return $this->defaultConnectionName;
  }

  public function getDefaultConnection()
  {
    if (null !== $this->defaultConnectionName)
    {
      if (!isset($this->connections[$this->defaultConnectionName]))
      {
        throw new RuntimeException(sprintf('The default connection "%s" does not exists.', $this->defaultConnectionName));
      }

      $connection = $this->connections[$this->defaultConnectionName];
    }
    else if (!$connection = reset($this->connections))
    {
      throw new RuntimeException('There is not connections.');
    }

    return $connection;
  }

  protected function checkConnection($name)
  {
    if (!$this->hasConnection($name))
    {
      throw new InvalidArgumentException(sprintf('The connection "%s" does not exists.', $name));
    }
  }

  /*
   * Repositories.
   */
  public function getRepository($name)
  {
    if (!isset($this->repositories[$name]))
    {
      if (!class_exists($class = $name.'Repository'))
      {
        $class = 'MondongoRepository';
      }

      $this->repositories[$name] = $repository = new $class($name, $this);

      if ($this->logCallable)
      {
        $repository->setLogCallable($this->logCallable);
      }
    }

    return $this->repositories[$name];
  }

  /*
   * logCallable.
   */
  public function setLogCallable($logCallable)
  {
    $this->logCallable = $logCallable;

    foreach ($this->repositories as $repository)
    {
      $repository->setLogCallable($logCallable);
    }
  }

  public function getLogCallable()
  {
    return $this->logCallable;
  }

  /*
   * Find.
   */
  public function find($name, $query = array(), $options = array())
  {
    return $this->getRepository($name)->find($query, $options);
  }

  public function findOne($name, $query = array(), $options = array())
  {
    return $this->getRepository($name)->findOne($query, $options);
  }

  public function get($name, $id)
  {
    return $this->getRepository($name)->get($id);
  }

  /*
   * Save.
   */
  public function save($name, $documents)
  {
    $this->getRepository($name)->save($documents);
  }

  /*
   * Delete.
   */
  public function delete($name, $documents)
  {
    $this->getRepository($name)->delete($documents);
  }
}
