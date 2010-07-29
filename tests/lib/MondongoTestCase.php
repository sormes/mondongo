<?php

abstract class MondongoTestCase extends PHPUnit_Framework_TestCase
{
  protected $mondongo;

  public function setUp()
  {
    MondongoContainer::clearDefault();
    MondongoContainer::clearForNames();
    MondongoContainer::clearDefinitions();

    MondongoTypeContainer::resetTypes();

    $this->mongo = new Mongo();

    $this->db = $this->mongo->selectDB('mondongo_tests');

    $this->db->address->remove();
    $this->db->article->remove();
    $this->db->author->remove();
    $this->db->category->remove();
    $this->db->getGridFS('file')->remove();

    $this->mondongo = new Mondongo();
    $this->mondongo->setConnection('default', new MondongoConnection($this->db));

    MondongoContainer::setDefault($this->mondongo);
  }
}
