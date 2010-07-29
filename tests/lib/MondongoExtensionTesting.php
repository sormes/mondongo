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

class MondongoExtensionTesting extends MondongoExtension
{
  protected $options = array(
    'option1' => 10,
    'option2' => 20,
    'field'   => 'testing',
  );

  protected $events = array();

  public function setup($definition)
  {
    $definition->setField($this->getOption('field'), 'string');
  }


  public function getEvents()
  {
    return $this->events;
  }

  public function clearEvents()
  {
    $this->events = array();
  }

  public function preInsert()
  {
    $this->events[] = 'preInsert';
    $this->getInvoker()->notifyExtensionEvent($this, 'preInsert');
  }

  public function postInsert()
  {
    $this->events[] = 'postInsert';
    $this->getInvoker()->notifyExtensionEvent($this, 'postInsert');
  }

  public function preUpdate()
  {
    $this->events[] = 'preUpdate';
    $this->getInvoker()->notifyExtensionEvent($this, 'preUpdate');
  }

  public function postUpdate()
  {
    $this->events[] = 'postUpdate';
    $this->getInvoker()->notifyExtensionEvent($this, 'postUpdate');
  }

  public function preSave()
  {
    $this->events[] = 'preSave';
    $this->getInvoker()->notifyExtensionEvent($this, 'preSave');
  }

  public function postSave()
  {
    $this->events[] = 'postSave';
    $this->getInvoker()->notifyExtensionEvent($this, 'postSave');
  }

  public function preDelete()
  {
    $this->events[] = 'preDelete';
    $this->getInvoker()->notifyExtensionEvent($this, 'preDelete');
  }

  public function postDelete()
  {
    $this->events[] = 'postDelete';
    $this->getInvoker()->notifyExtensionEvent($this, 'postDelete');
  }
}
