LiipTranslationBundle
=====================

[![Build Status](https://travis-ci.org/liip/LiipTranslationBundle.png?branch=master)](https://travis-ci.org/liip/LiipTranslationBundle)

This Bundle provides various tools to ease translations management of a Sf2 app. Here is a small [presentation](https://docs.google.com/presentation/d/1JK6vff6cVa92VxRIJ5ORzSUrmbtDPc5bPsFE35soEpw/edit?usp=sharing) of the bundle.

Introduction
------------

This bundle add a new layer in top of the translation mechanisms. This allows your customer to edit and override any translations directly from the website.

### Separation of concern

Using such a tool allows a clear separation between "key" and "value". Developers are responsible for defining new keys and removing old keys, while client/customer are responsible for translating the website.

### Key values on steroid

The current basic key-value system could be better. We extend it and allow developers to complete keys with metadata (like it's possible with XLiFF).

Extend your keys with information like maxbytes, comment, description, urls, screenshot, etc... Anything that could help translators.

A "value" is the translation for a "key" in a given locale, it's also possible to complete it with metadata (comments, update date, validity, etc...)

### Storage layer

The intermediate storage is currently available for:

 * Propel (database)
 * YAML (file or in Git)

but adding a new persistence is very easy (you just have to implement a small interface)

### Symfony compatibility

This bundle work on any symfony 2.3+ version. Unit and functional tests have been written to ensure this compatibility.

Features
--------

### Translation interface in the backend

 * Edit through a contextual popup
 * Fast inline editing
 * Possibility to view various translated column at the same time (en, fr, pt, etc...)
 * Filter by locale, domain, date
 * Filter for untranslated key
 * Search by key name or translation value
 * Display help messages from the developers

### Import/Export

Useful to provide files to an external agency, or to transfer translations from a staging environment to production

 * Export translation to a YAML file
 * Export based on the current list filter
 * Export/import multi domain/language with a zip file
 * Review change interface to handle collision at import time

### New translation keys insertion

 * Developers can provide context information to a translation keys (maxsize, description, comment, url, etc..)
 * Symfony command for developers to insert new complex keys into Xliff

### Security

 * Rights management (restricted to given locale or given domain)

Installation
------------

  1. Via composer

          composer require liip/translation-bundle master-dev

Configuration
-------------

In your ``config.yml`` add the given Bundle specific configuration, for example:

    liip_translation:
        locale_list: [en_JP, en_US, en, fr_CH, fr]
        security:
            by_domain: false
            by_locale: true
        persistence:
            class: Liip\TranslationBundle\Persistence\YamlFilePersistence
            options:
                folder: "%kernel.root_dir%/data/translations"
        interface:
            default_filters:
              domain: ['messages']
              languages: ['en', 'fr']

Also load the routes:

    _liip_translation:
        resource: "@LiipTranslationBundle/Resources/config/routing.yml"
        prefix: /translation

Alternatively you can load the translation and import interface routes separately:

    _liip_translation_interface:
        resource: "@LiipTranslationBundle/Resources/config/routing_translation_interface.yml"
    
    _liip_translation_import_interface:
        resource: "@LiipTranslationBundle/Resources/config/routing_import_interface.yml"


### Security

Access to translation management can be restricted by domains or by locales. By default, those restrictions are
disabled, you can turn them on in the config, with:

    liip_translation:
        ...
        security:
            by_domain: false
            by_locale: true

You can activate, one or both restrictions together. Once this have been activated, you have to attribute
associated roles to your users. The existing roles are:

    ROLE_TRANSLATOR_ADMIN
        ROLE_TRANSLATOR_ALL_DOMAINS
            ROLE_TRANSLATOR_DOMAIN_XXX
        ROLE_TRANSLATOR_ALL_LOCALES
            ROLE_TRANSLATOR_LOCALE_XXX

!! Warning, if you use security by domain, you have to explicitly list the domains. Example:

        security:
            by_domain: true
            domain_list: [messages, validators, forms]


Contributing
------------

Pull requests are welcome. Please see our [CONTRIBUTING](https://github.com/liip/LiipTranslationBundle/blob/master/CONTRIBUTING.md) guide.

This bundle is fully tested with PHPUnit, the tests suite can be run using the following commands :

          git clone git@github.com:liip/LiipTranslationBundle.git && cd LiipTranslationBundle
          composer install --dev
          phpunit

Thanks to [everyone who has contributed](https://github.com/liip/LiipTranslationBundle/graphs/contributors) already.
