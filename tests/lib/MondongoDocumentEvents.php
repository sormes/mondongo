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

class MondongoDocumentEvents extends MondongoDocument
{
  protected $events = array();

  protected $extensionsEvents = array();

  static public function define($definition)
  {
    $definition->setFields(array(
      'field1' => 'string',
    ));

    $definition->addExtension(new MondongoExtensionTesting($definition));
  }

  public function getEvents()
  {
    return $this->events;
  }

  public function clearEvents()
  {
    $this->events = array();
  }

  public function getExtensionsEvents()
  {
    return $this->extensionsEvents;
  }

  public function clearExtensionsEvents()
  {
    $this->extensionsEvents = array();
  }

  public function notifyExtensionEvent(MondongoExtension $extension, $type)
  {
    $this->extensionsEvents[get_class($extension)][] = $type;
  }

  public function preInsert()
  {
    $this->events[] = 'preInsert';
  }

  public function postInsert()
  {
    $this->events[] = 'postInsert';
  }

  public function preUpdate()
  {
    $this->events[] = 'preUpdate';
  }

  public function postUpdate()
  {
    $this->events[] = 'postUpdate';
  }

  public function preSave()
  {
    $this->events[] = 'preSave';
  }

  public function postSave()
  {
    $this->events[] = 'postSave';
  }

  public function preDelete()
  {
    $this->events[] = 'preDelete';
  }

  public function postDelete()
  {
    $this->events[] = 'postDelete';
  }
}
