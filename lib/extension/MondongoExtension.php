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
 * Base class for extensions.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class MondongoExtension
{
  protected $definition;

  protected $options = array();

  protected $invoker;

  public function __construct(MondongoDefinitionDocument $definition, array $options = array())
  {
    $this->definition = $definition;

    if ($diff = array_diff(array_keys($options), array_keys($this->options)))
    {
      throw new RuntimeException(sprintf('Options invalids "%s".', implode(', ', $diff)));
    }

    $this->options = array_merge($this->options, $options);

    $this->setup($this->definition);
  }

  protected function setup($definition)
  {
  }

  public function getDefinition()
  {
    return $this->definition;
  }

  public function hasOption($name)
  {
    return array_key_exists($name, $this->options);
  }

  public function getOption($name)
  {
    if (!$this->hasOption($name))
    {
      throw new InvalidArgumentException(sprintf('The option "%s" does not exists.', $name));
    }

    return $this->options[$name];
  }

  public function getOptions()
  {
    return $this->options;
  }

  public function setInvoker($invoker)
  {
    $this->invoker = $invoker;
  }

  public function getInvoker()
  {
    return $this->invoker;
  }

  public function clearInvoker()
  {
    $this->invoker = null;
  }
}
