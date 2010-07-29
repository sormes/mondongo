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

class MondongoTypeDateTest extends MondongoTestCase
{
  public function testToMongo()
  {
    $type = new MondongoTypeDate();
    $time = time();

    $this->assertEquals(new MongoDate($time), $type->toMongo($time));

    $date = new DateTime();
    $date->setTimestamp($time);
    $this->assertEquals(new MongoDate($time), $type->toMongo($date));

    $string = '2010-02-20';
    $this->assertEquals(new MongoDate(strtotime($string)), $type->toMongo($string));
  }

  public function testToPHP()
  {
    $type = new MondongoTypeDate();
    $time = time();

    $date = new DateTime();
    $date->setTimestamp($time);

    $this->assertEquals($date, $type->toPHP(new MongoDate($time)));
  }
}
