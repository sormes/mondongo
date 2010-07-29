<?php

require_once(dirname(__FILE__).'/../lib/autoload/MondongoAutoload.php');
MondongoAutoload::register();

foreach (array('lib', 'model') as $dir)
{
  foreach (new DirectoryIterator(dirname(__FILE__).'/'.$dir) as $file)
  {
    if ($file->isFile())
    {
      require($file->getPathname());
    }
  }
}
