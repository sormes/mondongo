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
 * Represents a Collection.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class MondongoCollection
{
  protected $mongoCollection;

  protected $logCallable;

  protected $logDefault = array();

  public function __construct(MongoCollection $mongoCollection)
  {
    $this->mongoCollection = $mongoCollection;
  }

  public function getMongoCollection()
  {
    return $this->mongoCollection;
  }

  /*
   * Log.
   */
  public function setLogCallable($logCallable)
  {
    $this->logCallable = $logCallable;
  }

  public function getLogCallable()
  {
    return $this->logCallable;
  }

  public function setLogDefault(array $logDefault)
  {
    $this->logDefault = $logDefault;
  }

  public function getLogDefault()
  {
    return $this->logDefault;
  }

  protected function log(array $log)
  {
    if ($this->logCallable)
    {
      call_user_func($this->logCallable, array_merge($this->logDefault, $this->getCollectionLogDefault(), $log));
    }
  }

  protected function getCollectionLogDefault()
  {
    return array(
      'database'   => $this->mongoCollection->db->__toString(),
      'collection' => $this->mongoCollection->getName(),
    );
  }

  /*
   * Collection methods.
   */
  public function batchInsert(&$a, $options = array())
  {
    if ($this->logCallable)
    {
      $this->log(array(
        'batchInsert' => true,
        'nb'          => count($a),
        'data'        => $a,
        'options'     => $options,
      ));
    }

    $this->mongoCollection->batchInsert($a, $options);

    return $a;
  }

  public function update($criteria, $newobj, $options = array())
  {
    if ($this->logCallable)
    {
      $this->log(array(
        'update'   => true,
        'criteria' => $criteria,
        'newobj'   => $newobj,
        'options'  => $options,
      ));
    }

    return $this->mongoCollection->update($criteria, $newobj, $options);
  }

  public function find($query = array(), $fields = array())
  {
    if ($this->logCallable)
    {
      $this->log(array(
        'find'   => true,
        'query'  => $query,
        'fields' => $fields,
      ));
    }

    return $this->mongoCollection->find($query, $fields);
  }

  public function findOne($query = array(), $fields = array())
  {
    if ($this->logCallable)
    {
      $this->log(array(
        'findOne' => true,
        'query'   => $query,
        'fields'  => $fields,
      ));
    }

    return $this->mongoCollection->findOne($query, $fields);
  }

  public function remove($criteria = array(), $options = array())
  {
    if ($this->logCallable)
    {
      $this->log(array(
        'remove'   => true,
        'criteria' => $criteria,
        'options'  => $options,
      ));
    }

    return $this->mongoCollection->remove($criteria, $options);
  }
}
