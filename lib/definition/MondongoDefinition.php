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

  protected $embeds = array();

  protected $defaultData = array();

  protected $defaultFieldsModified = array();

  protected $closureToMongo;

  protected $closureToPHP;

  /**
   * Constructor.
   *
   * @param string $name The name (class) of the document.
   *
   * @return void.
   */
  public function __construct($name)
  {
    $this->name = $name;
  }

  /**
   * Close the definition.
   *
   * @return void.
   *
   * @throws RuntimeException If the definition is closed.
   */
  public function close()
  {
    $this->checkNotClosed();

    $this->doClose();

    $this->closed = true;
  }

  /**
   * Do the close process of the definition.
   *
   * @return void
   */
  protected function doClose()
  {
    $this->defaultData           = $this->generateDefaultData();
    $this->defaultFieldsModified = $this->generateDefaultFieldsModified();

    $this->closureToMongo = $this->generateClosureToMongo();
    $this->closureToPHP   = $this->generateClosureToPHP();
  }

  /**
   * Returns if the definition is closed.
   *
   * @return boolean Returns if the definition is closed.
   */
  public function isClosed()
  {
    return $this->closed;
  }

  /**
   * Check if the definition is not closed.
   *
   * @return void
   *
   * @throws RuntimeException If the definition is not closed.
   */
  protected function checkClosed()
  {
    if (!$this->closed)
    {
      throw new RuntimeException('The definition is not closed.');
    }
  }

  /**
   * Check if the definition is closed.
   *
   * @return void
   *
   * @throws RuntimeException If the definition is closed.
   */
  protected function checkNotClosed()
  {
    if ($this->closed)
    {
      throw new RuntimeException('The definition is closed.');
    }
  }

  /**
   * Set the name.
   *
   * @param string $name The name (class) of the document.
   *
   * @return void
   *
   * @throws RuntimeException If the definition is closed.
   */
  public function setName($name)
  {
    $this->checkNotClosed();

    $this->name = $name;
  }

  /**
   * Return the name.
   *
   * @return string The name.
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set the fields (reset).
   *
   * @param array $fields An array of fields.
   *
   * @return MondongoDefinition The current instance.
   *
   * @throws LogicException If some field name is busy.
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

  /**
   * Set a field.
   *
   * @param string $name  The field name.
   * @param mixed  $field The field definition.
   *
   * @return MondongoDefinition The current instance.
   */
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

  /**
   * Returns if the field exists.
   *
   * @param string $name The field name.
   *
   * @return boolean Returns if the field exists.
   */
  public function hasField($name)
  {
    return isset($this->fields[$name]);
  }

  /**
   * Return a field definition.
   *
   * @param string $name The field name.
   *
   * @return array The field definition.
   *
   * @throws InvalidArgumentException If the field does not exists.
   */
  public function getField($name)
  {
    if (!$this->hasField($name))
    {
      throw new InvalidArgumentException(sprintf('The field "%s" does not exists.', $name));
    }

    return $this->fields[$name];
  }

  /**
   * Return the fields.
   *
   * @return array The fields.
   */
  public function getFields()
  {
    return $this->fields;
  }

  /**
   * Add a reference.
   *
   * @param string $name      The reference name.
   * @param array  $reference The reference definition.
   *
   * @return MondongoDefinition The current instance.
   *
   * @throws LogicException If the reference name is busy.
   */
  public function reference($name, array $reference)
  {
    $this->checkName($name);

    $this->references[$name] = $reference;

    return $this;
  }

  /**
   * Returns if the reference exists.
   *
   * @return boolean Returns if the reference exists.
   */
  public function hasReference($name)
  {
    return isset($this->references[$name]);
  }

  /**
   * Return a reference definition.
   *
   * @param string $name The reference name.
   *
   * @return array The reference definition.
   *
   * @throws InvalidArgumentException If the reference does not exists.
   */
  public function getReference($name)
  {
    if (!$this->hasReference($name))
    {
      throw new InvalidArgumentException(sprintf('The reference "%s" does not exists.', $name));
    }

    return $this->references[$name];
  }

  /**
   * Return the references.
   *
   * @return array The references.
   */
  public function getReferences()
  {
    return $this->references;
  }

  /*
   * Add an embed.
   *
   * @param string $name  The embed name.
   * @param array  $embed The embed definition.
   *
   * @return MondongoDefinitionDocument The current instance.
   *
   * @throws LogicException If the name is busy.
   */
  public function embed($name, array $embed)
  {
    $this->checkName($name);

    $this->embeds[$name] = $embed;

    return $this;
  }

  /**
   * Returns if an embed exists.
   *
   * @param string $name The embed name.
   *
   * @return boolean Returns if the embed exists.
   */
  public function hasEmbed($name)
  {
    return isset($this->embeds[$name]);
  }

  /**
   * Return an embed definition.
   *
   * @param string $name The embed name.
   *
   * @return array The embed definition.
   *
   * @throws InvalidArgumentException If the embed does not exists.
   */
  public function getEmbed($name)
  {
    if (!$this->hasEmbed($name))
    {
      throw new InvalidArgumentException(sprintf('The embed "%s" does not exists.', $name));
    }

    return $this->embeds[$name];
  }

  /**
   * Return the embeds definitions.
   *
   * @return array The embeds definitions.
   */
  public function getEmbeds()
  {
    return $this->embeds;
  }

  /*
   * Return the default data.
   *
   * The default data is the default value of the data var of the documents.
   *
   * @return array The default data.
   */
  public function getDefaultData()
  {
    return $this->defaultData;
  }

  /**
   * Generate the default data.
   *
   * @return array The default data generated.
   */
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

    // embeds
    $data['embeds'] = array();
    foreach (array_keys($this->getEmbeds()) as $name)
    {
      $data['embeds'][$name] = null;
    }

    return $data;
  }

  /**
   * Return the default fields modified.
   *
   * The default fields modified is the default value of the fieldsModified var of the documents.
   *
   * @return array The default fields modified.
   */
  public function getDefaultFieldsModified()
  {
    return $this->defaultFieldsModified;
  }

  /**
   * Generate the default fields modified.
   *
   * @return array The default fields modified.
   */
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

  /**
   * Return the closure to Mongo.
   *
   * The closure to Mongo is the closure to convert the documents data for Mongo.
   *
   * @return Closure The closure to Mongo.
   */
  public function getClosureToMongo()
  {
    return $this->closureToMongo;
  }

  /**
   * Generate the closure to Mongo.
   *
   * @return Closure The closure to Mongo.
   */
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
   * Return the closure to PHP.
   *
   * The closure to Mongo is the closure to convert the documents data for PHP.
   *
   * @return Closure The closure to PHP.
   */
  public function getClosureToPHP()
  {
    return $this->closureToPHP;
  }

  /**
   * Generate the closure to PHP.
   *
   * @return Closure The closure to PHP.
   */
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

  /**
   * Check if a name is busy.
   *
   * @param string $name The name.
   *
   * @return void
   *
   * @throws LogicException If the name is busy.
   */
  protected function checkName($name)
  {
    if ($this->doCheckName($name))
    {
      throw new LogicException(sprintf('The datum "%s" already exists.', $name));
    }
  }

  /**
   * Returns if the name is busy.
   *
   * @param string $name The name.
   *
   * @return boolean Returns if the name is busy.
   */
  protected function doCheckName($name)
  {
    return
      $this->hasField($name)
      ||
      $this->hasReference($name)
      ||
      $this->hasEmbed($name)
    ;
  }
}
