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

class Article extends MondongoDocument
{
  static public function define($definition)
  {
    $definition->setFields(array(
      'author_id'    => 'id',
      'title'        => 'string',
      'content'      => 'string',
      'category_ids' => 'raw',
      'is_active'    => array('type' => 'boolean', 'default' => false),
      'options'      => 'array',
    ));

    $definition->reference('author', array('class' => 'Author', 'field' => 'author_id', 'type' => 'one'));
    $definition->reference('categories', array('class' => 'Category', 'field' => 'category_ids', 'type' => 'many'));

    $definition->embed('source', array('class' => 'Source', 'type' => 'one'));
    $definition->embed('comments', array('class' => 'Comment', 'type' => 'many'));
  }
}
