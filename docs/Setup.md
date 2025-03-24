## Setup of your repository
JoRobo allows to host all custom extensions for a project or all extensions for an extension pakage in one repository. The goal is to easily commit all changes to your code to a versioning system, to build packages automatically from this source and to ensure constant quality of the code.

### Quick-Setup
In an empty repository, run the following commands to create a composer.json, add JoRobo as a dependency and then initialise the repository with the selected features:
```bash
composer init
composer require --dev joomla-projects/jorobo
vendor/bin/jorobo init
```

### Folder structure
The advised structure is to have a git repository with the following files and folders:
```
root
├─ docs
├─ src
└─ tests
```
An example for such a repository would be the [Weblinks](https://github.com/joomla-extensions/weblinks) repository or this repository itself.
While the names should be self-explanatory, the `src` folder holds your source code in a structure so that you can simply copy this over a Joomla installation and everything should be in the right folder.

This means that you would for example have the following structure:
```
root
└─ src
  ├─ administrator
  │  └─ components
  │     └─ com_example
  │        ├─ ...
  │        └─ ...
  ├─ components
  │  └─ com_example
  │     ├─ ...
  │     └─ ...
  └─ modules
     └─ mod_example
        ├─ ...
        └─ ...
```

### Initialising JoRobo in the repo
You will need `composer`. In the folder of your repository run `composer require --dev joomla-projects/jorobo` to install both JoRobo and Robo.li. To run JoRobo tasks, you now need a `RoboFile.php` in the root of your repository. As a start, you can copy a default file from `/vendor/joomla-project/jorobo/assets/`. You also have to create a `jorobo.ini` in the root. An example for this can also be found in the mentioned assets folder. Alternatively JoRobo provides an init script to prepare your repository automatically. Simply call `vendor/bin/jorobo init`.


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
src/administrator/components/com_name/
src/administrator/components/com_name/name.xml
src/administrator/components/com_name/script.php (Optional)
src/components/com_name/
src/administrator/language/en-GB/en-GB.com_name.ini
src/administrator/language/en-GB/en-GB.com_name.sys.ini
src/language/en-GB/en-GB.com_name.ini
src/media/com_name
```

#### Modules

```
src/modules/mod_something
src/media/mod_something
src/language/en-GB/en-GB.mod_something.ini
```

#### Plugins

```
src/plugins/type/name
src/media/plg_type_name
src/administrator/language/en-GB/en-GB.plg_type_name.ini
```

### Extension setup

Either use the sample RoboFile or extend your own with it.
