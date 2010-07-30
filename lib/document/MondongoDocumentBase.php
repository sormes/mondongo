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
 * Base class for documents.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class MondongoDocumentBase extends MondongoDocumentBaseSpeed implements ArrayAccess
{
  /*
   * Constructor.
   */
  public function __construct()
  {
    // PERFORMANCE
    /*
    $definition = $this->getDefinition();
    */
    $definition = MondongoContainer::getDefinition(get_class($this));

    $this->data           = $definition->getDefaultData();
    $this->fieldsModified = $definition->getDefaultFieldsModified();
  }
}
