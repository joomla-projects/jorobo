## How-to use in your own extension

Do a composer require joomla-projects/jorobo:dev

Make sure your RoboFile.php loads the tasks:

```
<?php
require 'vendor/autoload.php';

class RoboFile extends \Robo\Tasks
{
	use \Joomla\Jorobo\Tasks\Tasks;
	..
```

Then you can use it for your own tasks for example:

`$this->taskMap($target)->run();`

or

`$this->taskBuild($params)->run()`

Look at the RoboFile.php in the library root for a sample file.

## Usage in your own extension

### Directory setup

In order to use JoRobo you should use the following directory structure (it's like the "common" joomla one)

#### Components

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
