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
 * Represents a GridFS Collection.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class MondongoCollectionGridFS extends MondongoCollection
{
  /**
   * Constructor.
   *
   * @param MongoGridFS A MongoGridFS object.
   *
   * @return void
   */
  public function __construct(MongoGridFS $mongoCollection)
  {
    $this->mongoCollection = $mongoCollection;
  }

  /**
   * @see MondongoCollection
   */
  public function batchInsert(&$a, $options = array())
  {
    foreach ($a as &$data)
    {
      $this->saveFile($data);
    }
  }

  /*
   * Save a document with file.
   *
   * Based on DoctrineMongo save file:
   * http://github.com/doctrine/mongodb-odm/blob/master/lib/Doctrine/ODM/MongoDB/MongoCollection.php
   *
   * @param array $a The document data.
   *
   * @return array The document with the file.
   */
  public function saveFile(&$a)
  {
    if (!isset($a['file']))
    {
      throw new InvalidArgumentException('Data without file.');
    }
    $file = $a['file'];
    unset($a['file']);

    if ($file instanceof MongoGridFSFile)
    {
      if (!isset($a['_id']))
      {
        throw new InvalidArgumentException('Data without _id.');
      }

      $id  = $a['_id'];
      unset($a['_id']);

      $this->mongoCollection->update(array('_id' => $id), array('$set' => $a));
    }
    else
    {
      if (isset($a['_id']))
      {
        throw new RuntimeException('Cannot update a file.');
      }

      if (file_exists($file))
      {
        $id = $this->mongoCollection->storeFile($file, $a);
      }
      else if (is_string($file))
      {
        $id = $this->mongoCollection->storeBytes($file, $a);
      }
      else
      {
        throw new InvalidArgumentException('The file is not valid.');
      }
    }

    $file = $this->mongoCollection->findOne(array('_id' => $id));

    $a = $file->file;
    $a['file'] = $file;
  }
}
