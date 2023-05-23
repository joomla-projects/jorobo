# JoRobo (Robo.li tasks for Joomla!)

[![Latest Stable Version](https://poser.pugx.org/joomla-projects/jorobo/v/stable)](https://packagist.org/packages/joomla-projects/jorobo) [![Total Downloads](https://poser.pugx.org/joomla-projects/jorobo/downloads)](https://packagist.org/packages/joomla-projects/jorobo) [![License](https://poser.pugx.org/joomla-projects/jorobo/license)](https://packagist.org/packages/joomla-projects/jorobo)

Tools and Tasks based on [Robo.li](https://robo.li) for Joomla Extension Development and Releases

## Installation (Standalone):

  * `composer require joomla-projects/jorobo`
  * configure jorobo.ini
  * `vendor/bin/robo`
  
## Function overview:

  * `vendor/bin/robo build` - Builds your extension into an installable Joomla! package or zip file including replacements
  * `vendor/bin/robo generate` - Generate extension skeletons
  * `vendor/bin/robo map` - Map (Symlink) your extension into a running Joomla! installation
  * `vendor/bin/robo headers` - Adds / updates the copyright headers in the source directory (set them in the jorobo.ini)
  * `vendor/bin/robo bump` - Exchanges the string `__DEPLOY_VERSION__` in each file in the source directory with the version number set in the jorobo.ini.
  
## Documentation
You can find the documentation [here](docs/index.md). The following topics are covered:
* [Setup Process](docs/Setup.md)
* [Build Process](docs/Build.md)
* [Deploy Process](docs/Deploy.md)
* [Generate Process](docs/Generate.md)
* [Additional Tools](docs/Misc.md)

## Copyright
* (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
* Distributed under the GNU General Public License version 2 or later
* See [License details](LICENSE)
