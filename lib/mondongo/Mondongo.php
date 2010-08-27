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
  const VERSION = '0.9.1';

  protected $connections = array();

  protected $defaultConnectionName;

  protected $definitions = array();

  protected $repositories = array();

  protected $logCallable;

  /**
   * Set the connections.
   *
   * @param array $connections An array of connections.
   *
   * @return void
   */
  public function setConnections(array $connections)
  {
    $this->connections = array();
    foreach ($connections as $name => $connection)
    {
      $this->setConnection($name, $connection);
    }
  }

  /**
   * Set a connection.
   *
   * @param string             $name       The connection name.
   * @param MondongoConnection $connection The connection.
   *
   * @return void
   */
  public function setConnection($name, MondongoConnection $connection)
  {
    $this->connections[$name] = $connection;
  }

  /**
   * Remove a connection.
   *
   * @param string $name The connection name.
   *
   * @return void
   */
  public function removeConnection($name)
  {
    $this->checkConnection($name);

    unset($this->connections[$name]);
  }

  /**
   * Clear the connections.
   *
   * @return void
   */
  public function clearConnections()
  {
    $this->connections = array();
  }

  /**
   * Returns if a connection exists.
   *
   * @param string $name The connection name.
   *
   * @return boolean Returns if a connection exists.
   */
  public function hasConnection($name)
  {
    return isset($this->connections[$name]);
  }

  /**
   * Return a connection.
   *
   * @param string $name The connection name.
   *
   * @return MondongoConnection The connection.
   *
   * @throws InvalidArgumentException If the connection does not exists.
   */
  public function getConnection($name)
  {
    $this->checkConnection($name);

    return $this->connections[$name];
  }

  /**
   * Returns the connections.
   *
   * @return array The array of connections.
   */
  public function getConnections()
  {
    return $this->connections;
  }

  /**
   * Set the default connection name.
   *
   * @param string $name The connection name.
   *
   * @return void
   */
  public function setDefaultConnectionName($name)
  {
    $this->defaultConnectionName = $name;
  }

  /**
   * Returns the default connection name.
   *
   * @return string The default connection name.
   */
  public function getDefaultConnectionName()
  {
    return $this->defaultConnectionName;
  }

  /**
   * Returns the default connection.
   *
   * @return MondongoConnection The default connection.
   *
   * @throws RuntimeException If the default connection does not exists.
   * @throws RuntimeException If there is not connections.
   */
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

  /**
   * Check that a connection exists.
   *
   * @param string $name The connection name.
   *
   * @throws InvalidArgumentException If the connection does not exists.
   */
  protected function checkConnection($name)
  {
    if (!$this->hasConnection($name))
    {
      throw new InvalidArgumentException(sprintf('The connection "%s" does not exists.', $name));
    }
  }

  /**
   * Returns a repository.
   *
   * @param string $name The document name.
   *
   * @return MondongoRepository The repository.
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

  /**
   * Set the log callable.
   *
   * @param mixed $logCallable The log callable.
   *
   * @return void
   */
  public function setLogCallable($logCallable)
  {
    $this->logCallable = $logCallable;

    foreach ($this->repositories as $repository)
    {
      $repository->setLogCallable($logCallable);
    }
  }

  /**
   * Returns the log callable.
   *
   * @return mixed The log callable.
   */
  public function getLogCallable()
  {
    return $this->logCallable;
  }

  /**
   * Find documents.
   *
   * @param string $name    The document name.
   * @param array  $options An array of options.
   *
   * @return mixed The documents found within the parameters.
   *
   * @see MondongoRepository::find()
   */
  public function find($name, $options = array())
  {
    return $this->getRepository($name)->find($options);
  }

  /**
   * Find one document.
   *
   * @param string $name    The document name.
   * @param array  $options An array of options.
   *
   * @return mixed The document found within the parameters.
   *
   * @see MondongoRepository::findOne()
   */
  public function findOne($name, $options = array())
  {
    return $this->getRepository($name)->findOne($options);
  }

  /**
   * Find a document by id.
   *
   * @param string $name The document name.
   * @param mixed  $id   The document id (string or MongoId object).
   *
   * @return mixed The document or NULL if it does not exists.
   */
  public function get($name, $id)
  {
    return $this->getRepository($name)->get($id);
  }

  /**
   * Remove documents.
   *
   * @param string $name    The document name.
   * @param array  $options An array of options.
   *
   * @return void
   *
   * @see MondongoRepository::remove()
   */
  public function remove($name, $options = array())
  {
    $this->getRepository($name)->remove($options);
  }

  /**
   * Save documents.
   *
   * @param string $name      The document name.
   * @param array  $documents An array of documents.
   *
   * @return void
   */
  public function save($name, $documents)
  {
    $this->getRepository($name)->save($documents);
  }

  /**
   * Delete documents.
   *
   * @param string $name      The document name.
   * @param array  $documents An array of documents.
   *
   * @return void
   */
  public function delete($name, $documents)
  {
    $this->getRepository($name)->delete($documents);
  }
}
