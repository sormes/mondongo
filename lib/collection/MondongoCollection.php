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

  /**
   * Constructor.
   *
   * @param MongoCollection $mongoCollection A MongoCollection object.
   */
  public function __construct(MongoCollection $mongoCollection)
  {
    $this->mongoCollection = $mongoCollection;
  }

  /**
   * Return the MongoCollection.
   *
   * @return MongoCollection The MongoCollection object.
   */
  public function getMongoCollection()
  {
    return $this->mongoCollection;
  }

  /**
   * Set the log callable.
   *
   * @param mixed $logCallable A PHP callable.
   *
   * @return void
   */
  public function setLogCallable($logCallable)
  {
    $this->logCallable = $logCallable;
  }

  /**
   * Return the log callable.
   *
   * @return mixed The log callable.
   */
  public function getLogCallable()
  {
    return $this->logCallable;
  }

  /**
   * Set the log default.
   *
   * The log default is the default data to log.
   *
   * @param array $logDefault The log default.
   *
   * @return void
   */
  public function setLogDefault(array $logDefault)
  {
    $this->logDefault = $logDefault;
  }

  /**
   * Return the log default.
   *
   * @return array The log default.
   */
  public function getLogDefault()
  {
    return $this->logDefault;
  }

  /**
   * Launch a log.
   *
   * @param array An array with the log values.
   *
   * @return void
   */
  protected function log(array $log)
  {
    if ($this->logCallable)
    {
      call_user_func($this->logCallable, array_merge($this->logDefault, $this->getCollectionLogDefault(), $log));
    }
  }

  /**
   * Return the collection log default.
   *
   * The collection log default is the collection default data to log (database and collection).
   *
   * @return array The collection log default.
   */
  protected function getCollectionLogDefault()
  {
    return array(
      'database'   => $this->mongoCollection->db->__toString(),
      'collection' => $this->mongoCollection->getName(),
    );
  }

  /*
   * Represents to batchInsert method of the MongoCollection.
   *
   * http://www.php.net/manual/en/mongocollection.batchinsert.php
   *
   * @param array $a       The data.
   * @param array $options An array of options.
   *
   * @return array The data processed.
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

  /**
   * Represents to update method of the MongoCollection.
   *
   * http://www.php.net/manual/en/mongocollection.update.php
   *
   * @param array $criteria Description of the objects to update.
   * @param array $newobj   The object with which to update the matching records.
   * @param array $options  An array of options.
   *
   * @return boolean Returns if the update was successfully sent to the database.
   */
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

  /**
   * Represents to find method of the MongoCollection.
   *
   * http://www.php.net/manual/en/mongocollection.find.php
   *
   * @param array $query  The fields for which to search.
   * @param array $fields Fields of the results to return.
   *
   * @return MongoCursor Returns a cursor for the search results.
   */
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

  /**
   * Represents a findOne method of the MongoCollection.
   *
   * http://www.php.net/manual/en/mongocollection.findone.php
   *
   * @param array $query  The fields for which to search.
   * @param array $fields Fields of the results to return.
   *
   * @param mixed Returns record matching the search or NULL.
   */
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

  /**
   * Represents a remove method of the MongoCollection.
   *
   * http://www.php.net/manual/en/mongocollection.remove.php
   *
   * @param array $criteria Description of records to remove.
   * @param array $options  An array of options.
   *
   * @return mixed The return of the remove method of the MongoCollection.
   */
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
