LiipTranslationBundle
=====================

[![Build Status](https://magnum.travis-ci.com/liip/LiipTranslationBundle.png?token=qRGqYhgyyFcdZKKPqBhb&branch=master)](https://magnum.travis-ci.com/liip/LiipTranslationBundle)

This Bundle provides various tools to ease translations management of a Sf2 app.

Installation
------------

  1. Via composer

          composer require liip/translation-bundle master-dev

Security
--------

Access to translation management can be restricted by domains or by locales. By default, those restrictions are
disabled, you can turn them ON in the config, with:

    liip_translation:
        ...
        security:
            by_domain: false
            by_locale: true

You can activate, one or even both restrictions in parallels. Once this have been activated, you have to attribute
associated roles to your users. The existing roles are:

    ROLE_TRANSLATOR_ADMIN
        ROLE_TRANSLATOR_ALL_DOMAINS
            ROLE_TRANSLATOR_DOMAIN_XXX
        ROLE_TRANSLATOR_ALL_LOCALES
            ROLE_TRANSLATOR_LOCALE_XXX

You can create those roles manually in your security hierarchy configuration, or import the provided security
description into your config:

    - { resource: @LiipTranslationBundle/Resources/config/security.yml }


Contributing
------------

Pull requests are welcome. Please see our [CONTRIBUTING](https://github.com/liip/LiipTranslationBundle/blob/master/CONTRIBUTING.md) guide.

This bundle is fully tested with PHPUnit, the tests suite can be run using the following commands :

          git clone git@github.com:liip/LiipTranslationBundle.git && cd LiipTranslationBundle
          composer install --dev
          phpunit

Thanks to [everyone who has contributed](https://github.com/liip/LiipTranslationBundle/graphs/contributors) already.
