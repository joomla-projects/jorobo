# JoRobo (Robo.li tasks for Joomla!)

[![Latest Stable Version](https://poser.pugx.org/joomla-projects/jorobo/v/stable)](https://packagist.org/packages/joomla-projects/jorobo) [![Total Downloads](https://poser.pugx.org/joomla-projects/jorobo/downloads)](https://packagist.org/packages/joomla-projects/jorobo) [![License](https://poser.pugx.org/joomla-projects/jorobo/license)](https://packagist.org/packages/joomla-projects/jorobo)

Tools and Tasks based on [Robo.li](https://robo.li) for Joomla Extension Development and Releases

```markdown
> **Fork notice.** This repository is a copy of
> [joomla-projects/jorobo](https://github.com/joomla-projects/jorobo),
> tracked here so extension builds do not depend on the upstream repository
> being reachable and unchanged.
>
> - Upstream branch: `develop`
> - Upstream commit: _(fill in)_
> - Local changes: _(none / list them)_
>
> Everything below is upstream documentation. Before changing a file here,
> check whether the change belongs upstream instead — anything kept only in
> this copy has to be re-applied by hand on every update.
```

The three placeholder lines are the part that matters. A fork without a recorded
base point cannot be diffed against upstream later, which is the same problem the
OSMap fork in `plg_osmap_wm_joomla` has.

## File structure

```
bin/jorobo                     CLI entry point (registered as a Composer binary)
RoboFile.php                   Sample RoboFile — the file a consuming project
                               copies and extends
jorobo.dist.ini                Sample configuration, documented inline
jorobo.ini                     The configuration this repository builds itself with
 
src/
├── Command/                   Standalone console commands
│   ├── InitCommand.php            Scaffolds a project (copies assets/init)
│   ├── CICommand.php              Installs a CI configuration (assets/ci)
│   └── RectorCommand.php
└── Tasks/
    ├── Tasks.php              The trait a RoboFile pulls in to get every task
    ├── JTask.php              Shared base: reads jorobo.ini, resolves paths
    ├── Build.php              Entry point of the build
    ├── Build/                 One class per extension type — Component, Module,
    │                          Plugin, Template, Library, Package, Language,
    │                          Media, File — plus Base and Extension
    ├── Deploy/                Package, Zip, FtpUpload, Release (GitHub)
    ├── Generate.php + Generate/   Skeleton generation per extension type
    ├── Map.php                Symlinks the extension into a Joomla installation
    ├── BumpVersion.php        Replaces __DEPLOY_VERSION__ with the configured version
    ├── CopyrightHeader.php    Adds or updates copyright headers
    ├── Changelog.php          Builds a changelog from commits or issues
    └── AssetJSON.php          joomla.asset.json handling
 
assets/
├── init/                      What `jorobo init` drops into a new project:
│                              RoboFile.php, jorobo.dist.ini, ruleset.xml,
│                              phpstan.neon, phpunit.xml.dist, .php-cs-fixer,
│                              .editorconfig, .gitignore
└── ci/                        Ready-made pipelines for GitHub Actions and GitLab
```

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

## Note on the documentation links
 
The `## Documentation` section links to `docs/index.md`, `docs/Setup.md`,
`docs/Build.md`, `docs/Deploy.md`, `docs/Generate.md` and `docs/Misc.md`. There is
no `docs/` folder in this copy, so all six links are dead here. Either pull the
folder in from upstream or point the links at the upstream repository.

## Copyright
* (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
* Distributed under the GNU General Public License version 2 or later
* See [License details](LICENSE)
