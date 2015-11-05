# jorobo (Robo.li tasks for Joomla! extensions)

[![Join the chat at https://gitter.im/joomla-projects/jorobo](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/joomla-projects/jorobo?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Latest Stable Version](https://poser.pugx.org/joomla-projects/jorobo/v/stable)](https://packagist.org/packages/joomla-projects/jorobo) [![Total Downloads](https://poser.pugx.org/joomla-projects/jorobo/downloads)](https://packagist.org/packages/joomla-projects/jorobo) [![Latest Unstable Version](https://poser.pugx.org/joomla-projects/jorobo/v/unstable)](https://packagist.org/packages/joomla-projects/jorobo) [![License](https://poser.pugx.org/yvesh/jbuild/license)](https://packagist.org/packages/joomla-projects/jorobo)

#### Warning: Currently in alpha stage!

Tool and Task based on Robo.li to manage Joomla Extension Development and Releases

## Installation (Standalone):

  * composer install
  * configure jbuild.ini
  * vendor/bin/robo
  

## Function overview:

  * vendor/bin/robo map destination - Symlinks an extension into an Joomla installation (WIP @rdeutz working solution)
  * vendor/bin/robo build - Builds your extension into an installable Joomla package or zip file including replacements
  * vendor/bin/robo generate [--mod_xy --com_xy --plg_system_xy] (not integrated yet)
  
  
## How-to use in your own extension

Do a composer require joomla-projects/jorobo:dev

Make sure your RoboFile.php loads the tasks:

```
<?php
require 'vendor/autoload.php';

class RoboFile extends \Robo\Tasks
{
	use \JBuild\Tasks\loadTasks;
	..
```

Then you can use it your own tasks for example:

`$this->taskMap($target)->run();`

or

`$this->taskBuild($params)->run()`

Look at the RoboFile.php in the library root for a sample file

## Usage in your own extension

### Directory setup

In order to use Jorobo you should use the following directory structure (it's like the "common" joomla one)

####Components

```
source/administrator/components/com_name/
source/administrator/components/com_name/name.xml
source/administrator/components/com_name/script.php (Optional)
source/components/com_name/
source/administrator/language/en-GB/en-GB.com_name.ini
source/administrator/language/en-GB/en-GB.com_name.sys.ini
source/language/en-GB/en-GB.com_name.ini
source/media/com_name
```

#### Modules

```
source/modules/mod_something
source/media/mod_something
source/language/en-GB/en-GB.mod_something.ini
```

#### Plugins

```
source/plugins/type/name
source/media/plg_type_name
source/administrator/language/en-GB/en-GB.plg_type_name.ini
```

### Extension setup

Either use the sample RoboFile or extend your own with it.
