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
 * Autoload.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class MondongoAutoload
{
  static protected $registered = false;

  static protected $classes = array(
    // collection
    'MondongoCollection'       => '/collection/MondongoCollection.php',
    'MondongoCollectionGridFS' => '/collection/MondongoCollectionGridFS.php',
    // connection
    'MondongoConnection' => '/connection/MondongoConnection.php',
    // definition
    'MondongoDefinition'              => '/definition/MondongoDefinition.php',
    'MondongoDefinitionDocument'      => '/definition/MondongoDefinitionDocument.php',
    'MondongoDefinitionDocumentEmbed' => '/definition/MondongoDefinitionDocumentEmbed.php',
    // document
    'MondongoDocument'           => '/document/MondongoDocument.php',
    'MondongoDocumentBase'       => '/document/MondongoDocumentBase.php',
    'MondongoDocumentBaseSpeed'  => '/document/MondongoDocumentBaseSpeed.php',
    'MondongoDocumentEmbed'      => '/document/MondongoDocumentEmbed.php',
    'MondongoDocumentEmbedSpeed' => '/document/MondongoDocumentEmbedSpeed.php',
    'MondongoDocumentSpeed'      => '/document/MondongoDocumentSpeed.php',
    // extension
    'MondongoExtension' => '/extension/MondongoExtension.php',
    // group
    'MondongoGroup'      => '/group/MondongoGroup.php',
    'MondongoGroupArray' => '/group/MondongoGroupArray.php',
    // mondongo
    'Mondongo'          => '/mondongo/Mondongo.php',
    'MondongoContainer' => '/mondongo/MondongoContainer.php',
    // repository
    'MondongoRepository' => '/repository/MondongoRepository.php',
    // type
    'MondongoType'          => '/type/MondongoType.php',
    'MondongoTypeArray'     => '/type/MondongoTypeArray.php',
    'MondongoTypeBinData'   => '/type/MondongoTypeBinData.php',
    'MondongoTypeBoolean'   => '/type/MondongoTypeBoolean.php',
    'MondongoTypeContainer' => '/type/MondongoTypeContainer.php',
    'MondongoTypeDate'      => '/type/MondongoTypeDate.php',
    'MondongoTypeFile'      => '/type/MondongoTypeFile.php',
    'MondongoTypeFloat'     => '/type/MondongoTypeFloat.php',
    'MondongoTypeId'        => '/type/MondongoTypeId.php',
    'MondongoTypeInteger'   => '/type/MondongoTypeInteger.php',
    'MondongoTypeRaw'       => '/type/MondongoTypeRaw.php',
    'MondongoTypeString'    => '/type/MondongoTypeString.php',
    // util
    'MondongoInflector' => '/util/MondongoInflector.php',
  );

  static public function register()
  {
    if (self::$registered)
    {
      return;
    }

    if (false === spl_autoload_register(array('MondongoAutoload', 'autoload')))
    {
      throw new RuntimeException('Unable to register Mondongo::autoload() as an autoloading method.');
    }

    self::$registered = true;
  }

  static public function unregister()
  {
    spl_autoload_register(array('MondongoAutoload', 'autoload'));
    self::$registered = false;
  }

  static public function autoload($class)
  {
    if (isset(self::$classes[$class]))
    {
      require(dirname(__FILE__).'/../'.self::$classes[$class]);

      return true;
    }

    return false;
  }
}
