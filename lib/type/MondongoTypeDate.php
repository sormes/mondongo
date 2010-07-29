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
 * Type Date.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class MondongoTypeDate extends MondongoType
{
  public function toMongo($value)
  {
    if ($value instanceof DateTime)
    {
      $value = $value->getTimestamp();
    }
    else if (is_string($value))
    {
      $value = strtotime($value);
    }

    return new MongoDate($value);
  }

  public function toPHP($value)
  {
    $date = new DateTime();
    $date->setTimestamp($value->sec);

    return $date;
  }

  public function closureToMongo()
  {
    return 'if ($value instanceof DateTime) { $value = $value->getTimestamp(); } else if (is_string($value)) { $value = strtotime($value); } $return = new MongoDate($value);';
  }

  public function closureToPHP()
  {
    return '$date = new DateTime(); $date->setTimestamp($value->sec); $return = $date;';
  }
}
