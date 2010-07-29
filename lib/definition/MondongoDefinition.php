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
 * Base class for definitions.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class MondongoDefinition
{
  protected $closed = false;

  protected $setters = array();

  protected $name;

  protected $fields = array();

  protected $references = array();

  protected $defaultData = array();

  protected $defaultFieldsModified = array();

  protected $closureToMongo;

  protected $closureToPHP;

  /*
   * Constructor.
   */
  public function __construct($name)
  {
    $this->name = $name;
  }

  /*
   * Close.
   */
  public function close()
  {
    $this->checkNotClosed();

    $this->doClose();

    $this->closed = true;
  }

  protected function doClose()
  {
    $this->defaultData           = $this->generateDefaultData();
    $this->defaultFieldsModified = $this->generateDefaultFieldsModified();

    $this->closureToMongo = $this->generateClosureToMongo();
    $this->closureToPHP   = $this->generateClosureToPHP();
  }

  public function isClosed()
  {
    return $this->closed;
  }

  protected function checkClosed()
  {
    if (!$this->closed)
    {
      throw new RuntimeException('The definition is not closed.');
    }
  }

  protected function checkNotClosed()
  {
    if ($this->closed)
    {
      throw new RuntimeException('The definition is closed.');
    }
  }

  /*
   * Name.
   */
  public function setName($name)
  {
    $this->checkNotClosed();

    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }

  /*
   * Fields.
   */
  public function setFields(array $fields)
  {
    $this->fields = array();

    foreach ($fields as $name => $field)
    {
      $this->setField($name, $field);
    }

    return $this;
  }

  public function setField($name, $field)
  {
    $this->checkName($name);

    if (is_string($field))
    {
      $field = array('type' => $field);
    }

    $this->fields[$name] = $field;

    return $this;
  }

  public function hasField($name)
  {
    return isset($this->fields[$name]);
  }

  public function getField($name)
  {
    if (!$this->hasField($name))
    {
      throw new InvalidArgumentException(sprintf('The field "%s" does not exists.', $name));
    }

    return $this->fields[$name];
  }

  public function getFields()
  {
    return $this->fields;
  }

  /*
   * References.
   */
  public function reference($name, array $reference)
  {
    $this->checkName($name);

    $this->references[$name] = $reference;

    return $this;
  }

  public function hasReference($name)
  {
    return isset($this->references[$name]);
  }

  public function getReference($name)
  {
    if (!$this->hasReference($name))
    {
      throw new InvalidArgumentException(sprintf('The reference "%s" does not exists.', $name));
    }

    return $this->references[$name];
  }

  public function getReferences()
  {
    return $this->references;
  }

  /*
   * DefaultData.
   */
  public function getDefaultData()
  {
    return $this->defaultData;
  }

  protected function generateDefaultData()
  {
    $data = array();

    // fields
    $data['fields'] = array();
    foreach ($this->getFields() as $name => $field)
    {
      $data['fields'][$name] = isset($field['default']) ? $field['default'] : null;
    }

    // references
    $data['references'] = array();
    foreach (array_keys($this->getReferences()) as $name)
    {
      $data['references'][$name] = null;
    }

    return $data;
  }

  /*
   * DefaultFieldsModified.
   */
  public function getDefaultFieldsModified()
  {
    return $this->defaultFieldsModified;
  }

  protected function generateDefaultFieldsModified()
  {
    $fieldsModified = array();

    foreach ($this->getFields() as $name => $field)
    {
      if (isset($field['default']))
      {
        $fieldsModified[$name] = null;
      }
    }

    return $fieldsModified;
  }

  /*
   * ClosureToMongo.
   */
  public function getClosureToMongo()
  {
    return $this->closureToMongo;
  }

  protected function generateClosureToMongo()
  {
    $function = '';

    // fields
    foreach ($this->getFields() as $name => $field)
    {
      $function .= sprintf(<<<EOF
  if (isset(\$data['%1\$s']))
  {
    \$value = \$data['%1\$s'];

    %2\$s

    \$data['%1\$s'] = \$return;
  }

EOF
        ,
        $name,
        MondongoTypeContainer::getType($field['type'])->closureToMongo()
      );
    }

    $eval = sprintf(<<<EOF
\$closure = function(\$data)
{
  %s

  return \$data;
};
EOF
      ,
      $function
    );

    eval($eval);

    return $closure;
  }

  /*
   * ClosureToPHP.
   */
  public function getClosureToPHP()
  {
    return $this->closureToPHP;
  }

  protected function generateClosureToPHP()
  {
    $function = '';

    // fields
    foreach ($this->getFields() as $name => $field)
    {
      $function .= sprintf(<<<EOF
  if (isset(\$data['%1\$s']))
  {
    \$value = \$data['%1\$s'];

    %2\$s

    \$documentData['fields']['%1\$s'] = \$return;
    unset(\$data['%1\$s']);
  }

EOF
        ,
        $name,
        MondongoTypeContainer::getType($field['type'])->closureToPHP()
      );
    }

    $eval = sprintf(<<<EOF
\$closure = function(&\$data, &\$documentData)
{
  %s
};
EOF
      ,
      $function
    );

    eval($eval);

    return $closure;
  }

  /*
   * dataTo.
   */
  public function dataToMongo(array $data)
  {
    return $this->dataTo($data, 'mongo');
  }

  public function dataToPHP(array $data)
  {
    return $this->dataTo($data, 'php');
  }

  protected function dataTo(array $data, $to)
  {
    if (!in_array($to, array('mongo', 'php')))
    {
      throw new InvalidArgumentException(sprintf('To "%s" invalid.', $to));
    }
    $method = 'mongo' == $to ? 'toMongo' : 'toPHP';

    $return = array();
    foreach ($data as $name => $datum)
    {
      if ('_id' == $name)
      {
        $return[$name] = $datum;
        continue;
      }

      $return[$name] = null;

      if (null !== $datum)
      {
        $field = $this->getField($name);

        $return[$name] = MondongoTypeContainer::getType($field['type'])->$method($datum);
      }
    }

    return $return;
  }

  /*
   * CheckName.
   */
  protected function checkName($name)
  {
    if ($this->doCheckName($name))
    {
      throw new LogicException(sprintf('The datum "%s" already exists.', $name));
    }
  }

  protected function doCheckName($name)
  {
    return
      $this->hasField($name)
      ||
      $this->hasReference($name)
    ;
  }
}
