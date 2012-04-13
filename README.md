# As3et

**[View builds](http://travis-ci.org/acoulton/as3et) 
develop: ![Build Status - develop branch](https://secure.travis-ci.org/acoulton/as3et.png?branch=develop)

As3et is yet another asset manager module for 
[Kohana Framework v3.2 and up](http://kohanaframework.org) which is different
because it's geared around ultimately deploying asset files to Amazon S3 for use
as a CDN.

As3et is under initial development, but will ultimately support:

* Collating all asset files from the cascading file system to produce an overall
  asset collection.
* Uploading the collection to Amazon S3 with a deploy-specific version number.
  that allows the operation to be atomic and allows automatic client cache refresh.
* Prefixing asset URLS with an Amazon S3 endpoint in the production environment.
* Serving assets locally in developer environments.
* Asset classes that can optionally compile/thumbnail/minify/concatenate assets 
  on-the-fly in developer environments and/or as a build step prior to uploading.
* All the other things that every other asset manager out there does.

As3et is built with TDD, so the develop branch should work, but expect significant
API changes until a releasable version is tagged.

## Unit Tests
As3et is fully unit tested (PHPUnit tests are included in this repository).

To run the tests on a standard Kohana installation (with the unittest module)
just run the following from the base folder:

    phpunit --bootstrap=modules/unittest/bootstrap.php --group=as3et modules/unittest/tests.php

The As3et test suite also runs continuously on [Travis CI](http://travis-ci.org/acoulton/as3et) 
against the current codebase. Helper scripts are provided in the dev/ folder to 
create a sandbox with up to date Kohana core and required modules.

## Further Documentation
Is included with the module, integrated into the Kohana online userguide.

## Licence
As3et &copy; 2011 [Ingenerator](http://www.ingenerator.com) and is
released under the [Kohana Licence](http://kohanaframework.org/licence)

## Issues and contributions
Bug reports and feature requests are welcome via the project's Github issue tracker,
preferably with a pull request attached.

Pull requests should:

* be targeted against the relevant develop branch
* be contained in a separate topic/bug branch in your forked respository (in case
  further commits are required to complete your solution)
* generally speaking, include a unit test or new dataset for an existing test that
  fails before your new code is merged and passes afterwards
* adopt the Kohana coding standards
