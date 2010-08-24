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
 * Represents a repository.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class MondongoRepository
{
  protected $name;

  protected $mondongo;

  protected $definition;

  protected $connection;

  protected $collection;

  protected $logCallable;

  /**
   * Constructor.
   *
   * @param string   $name     The document name (class).
   * @param Mondongo $mondongo The Mondongo.
   *
   * @return void
   */
  public function __construct($name, Mondongo $mondongo)
  {
    $this->name     = $name;
    $this->mondongo = $mondongo;

    $this->definition = MondongoContainer::getDefinition($this->name);

    if (null !== $connection = $this->definition->getConnection())
    {
      $this->connection = $this->mondongo->getConnection($connection);
    }
    else
    {
      $this->connection = $this->mondongo->getDefaultConnection();
    }

    $collectionName = $this->getDefinition()->getCollection();

    if ($this->definition->hasFile())
    {
      $this->collection = new MondongoCollectionGridFS($this->connection->getDB()->getGridFS($collectionName));
    }
    else
    {
      $this->collection = new MondongoCollection($this->connection->getDB()->$collectionName);
    }
  }

  /**
   * Returns the document name.
   *
   * @return string The document name.
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Returns the Mondongo.
   *
   * @return Mondongo The Mondongo.
   */
  public function getMondongo()
  {
    return $this->mondongo;
  }

  /**
   * Returns the definition.
   *
   * @return MondongoDefinitionDocument The definition.
   */
  public function getDefinition()
  {
    return $this->definition;
  }

  /**
   * Returns the connection.
   *
   * @return MondongoConnection The connection.
   */
  public function getConnection()
  {
    return $this->connection;
  }

  /**
   * Returns the collection.
   *
   * @return MondongoCollection The collection.
   */
  public function getCollection()
  {
    return $this->collection;
  }

  /**
   * Returns the Mongo collection.
   *
   * @return MongoCollection The Mongo collection.
   */
  public function getMongoCollection()
  {
    return $this->collection->getMongoCollection();
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
    $this->collection->setLogCallable($logCallable);

    $this->collection->setLogDefault(array(
      'connection' => array_search($this->connection, $this->mondongo->getConnections(), true)
    ));
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
   * Options:
   *
   *   * query:    the query (array)
   *   * fields:   the fields (array)
   *   * sort:     the sort
   *   * limit:    the limit
   *   * skip:     the skip
   *   * one:      if returns one result (incompatible with limit)
   *   * index_by: if index the results by a field
   *
   * @param array  $options An array of options.
   *
   * @return mixed The document/s found within the parameters.
   */
  public function find($options = array())
  {
    // query
    if (!isset($options['query']))
    {
      $options['query'] = array();
    }

    // fields
    if (!isset($options['fields']))
    {
      $options['fields'] = array();
    }

    $cursor = $this->collection->find($options['query'], $options['fields']);

    // sort
    if (isset($options['sort']))
    {
      $cursor->sort($options['sort']);
    }

    // one
    if (isset($options['one']))
    {
      $cursor->limit(1);
    }
    // limit
    else if (isset($options['limit']))
    {
      $cursor->limit($options['limit']);
    }

    // skip
    if (isset($options['skip']))
    {
      $cursor->skip($options['skip']);
    }

    // index_by
    if (isset($options['index_by']))
    {
      $indexBy = $options['index_by'];

      if ('_id' != $indexBy && !$this->getDefinition()->hasField($indexBy))
      {
        throw new InvalidArgumentException('The indexBy is not the _id or a field.');
      }
    }
    else
    {
      $indexBy = false;
    }

    if ($hasFile = $this->definition->hasFile())
    {
      $fileKeys = array_merge(
        array_keys($this->definition->getFields())
      );
    }

    $closureToPHP = $this->definition->getClosureToPHP();

    $results = array();
    foreach ($cursor as $c)
    {
      if ($hasFile)
      {
        $a = array('_id' => $c->file['_id'], 'file' => $c);
        foreach ($fileKeys as $key)
        {
          if (isset($c->file[$key]))
          {
            $a[$key] = $c->file[$key];
          }
        }
      }
      else
      {
        $a = $c;
      }

      $d = new $this->name();
      $d->setData($a, $closureToPHP);

      if ($indexBy)
      {
        $results['_id' == $indexBy ? $d->getId()->__toString() : $d[$indexBy]] = $d;
      }
      else
      {
        $results[] = $d;
      }
    }

    if ($results)
    {
      if (isset($options['one']))
      {
        return array_shift($results);
      }

      return $results;
    }

    return null;
  }

  /**
   * Find one document.
   *
   * @param array  $options An array of options.
   *
   * @return mixed The document found within the parameters.
   *
   * @see ::find()
   */
  public function findOne($options = array())
  {
    return $this->find(array_merge($options, array('one' => true)));
  }

  /**
   * Find a document by id.
   *
   * @param mixed  $id The document id (string or MongoId object).
   *
   * @return mixed The document or NULL if it does not exists.
   */
  public function get($id)
  {
    $data = $this->collection->findOne(array('query' => array('_id' => is_string($id) ? new MongoId($id) : $id)));

    return $data ? $this->buildDocument($data) : null;
  }

  /**
   * Remove documents.
   *
   * Options:
   *
   *   * query: the query
   *
   * @param string $options An array of options.
   *
   * @return void
   */
  public function remove($options = array())
  {
    if (!isset($options['query']))
    {
      $options['query'] = array();
    }

    $this->getCollection()->remove($options['query']);
  }

  /**
   * Build a document.
   *
   * @param array $data The data.
   *
   * @return MondongoDocument The document.
   */
  protected function buildDocument($data)
  {
    $document = new $this->name();
    $document->setData($data);

    return $document;
  }

  /**
   * Save documents.
   *
   * @param array  $documents An array of documents.
   *
   * @return void
   */
  public function save($documents)
  {
    if (!is_array($documents))
    {
      $documents = array($documents);
    }

    $events     = $this->definition->getEvents();
    $extensions = $this->definition->getExtensions();

    $inserts = array();
    $updates = array();

    foreach ($documents as $document)
    {
      if (!$document->isModified())
      {
        throw new LogicException('Cannot save a unmodified document.');
      }

      if ($document->isNew())
      {
        $inserts[spl_object_hash($document)] = $document;
      }
      else
      {
        $updates[] = $document;
      }
    }

    if ($inserts || $updates)
    {
      if ($inserts)
      {
        // preInsert
        $this->notifyEvent($inserts, $events, $extensions, 'preInsert');

        // preSave
        $this->notifyEvent($inserts, $events, $extensions, 'preSave');

        $data = array();
        foreach ($inserts as $oid => $document)
        {
          $data[$oid] = $document->getQueryForSave();
        }

        $this->collection->batchInsert($data);

        foreach ($data as $oid => $datum)
        {
          $inserts[$oid]->setId($datum['_id']);
          $inserts[$oid]->clearModified();
        }

        // postInsert
        $this->notifyEvent($inserts, $events, $extensions, 'postInsert');

        // postSave
        $this->notifyEvent($inserts, $events, $extensions, 'postSave');
      }

      if ($updates)
      {
        foreach ($updates as $document)
        {
          // preUpdate
          $this->notifyEvent($document, $events, $extensions, 'preUpdate');

          // preSave
          $this->notifyEvent($document, $events, $extensions, 'preSave');

          $this->collection->update(array('_id' => $document->getId()), $document->getQueryForSave());

          $document->clearModified();

          // postUpdate
          $this->notifyEvent($document, $events, $extensions, 'postUpdate');

          // postSave
          $this->notifyEvent($document, $events, $extensions, 'postSave');
        }
      }
    }
  }

  /**
   * Delete documents.
   *
   * @param array $documents An array of documents.
   *
   * @return void
   */
  public function delete($documents)
  {
    if (!is_array($documents))
    {
      $documents = array($documents);
    }

    $events     = $this->definition->getEvents();
    $extensions = $this->definition->getExtensions();

    $ids  = array();

    foreach ($documents as $document)
    {
      if ($document->isNew())
      {
        throw new LogicException('Cannot delete a new document.');
      }

      $ids[] = $document->getId();
    }

    if ($ids)
    {
      // preDelete
      $this->notifyEvent($documents, $events, $extensions, 'preDelete');

      $this->collection->remove(array('_id' => array('$in' => $ids)));

      // postDelete
      $this->notifyEvent($documents, $events, $extensions, 'postDelete');
    }
  }

  /**
   * Notify an event.
   *
   * @param array  $docs       The documents
   * @param array  $events     The events.
   * @param array  $extensions The extensions.
   * @param string $event      The event.
   *
   * @return void
   */
  protected function notifyEvent($docs, $events, $extensions, $event)
  {
    if (!is_array($docs))
    {
      $docs = array($docs);
    }

    foreach ($docs as $doc)
    {
      if (isset($events['document'][$event]))
      {
        $doc->$event();
      }

      if (isset($events['extensions'][$event]))
      {
        foreach (array_keys($events['extensions'][$event]) as $key)
        {
          $extensions[$key]->setInvoker($doc);
          $extensions[$key]->$event();
          $extensions[$key]->clearInvoker();
        }
      }
    }
  }

  /**
   * Ensure the indexes.
   *
   * @return void
   */
  public function ensureIndexes()
  {
    foreach ($this->definition->getIndexes() as $index)
    {
      $this->collection->getMongoCollection()->ensureIndex(
        $index['fields'],
        array_merge(isset($index['options']) ? $index['options'] : array(), array('safe' => true))
      );
    }
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
    foreach ($this->getDefinition()->getExtensions() as $extension)
    {
      if (method_exists($extension, $method = $name.'RepositoryProxy'))
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
