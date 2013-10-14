LiipTranslationBundle
=====================

[![Build Status](https://magnum.travis-ci.com/liip/LiipTranslationBundle.png?token=qRGqYhgyyFcdZKKPqBhb&branch=master)](https://magnum.travis-ci.com/liip/LiipTranslationBundle)

This Bundle provides various tools to ease translations management of a Sf2 app.


Introduction
------------

This bundle add a new layer in top of the translation mecanisms. This allows your customer to edit, override any translations directly from the website.

### Separation of concern

Using such a tool allows a clear separation between "key" and "value". Developers are responsible for defining new keys and removing old keys.
Client/customer are responsible for translating the website.

### Key values on steroid

The current basic key-value system could be better. We extend it and allow developers to complete keys with metadata (like it's possible with XLiFF).
Extend your keys with informations like maxbytes, comment, description, urls, screenshot, etc... Anything that could help translators.

A "value" is the translation for a "key" in a given locale, it's also possible to complete it with metadata (comments, update date, validity, etc...)

### Storage layer

The intermediate storage is currently available for:

 * Propel (database)
 * YAML (file)

but adding a new persistance is very easy (you just have to implement a small interface)

### Symfony compatibility

This bundle work on any symfony version: from 2.0 to 2.4. Unit and functionnal tests have been written to ensure this compatibilty.


Functionalities
---------------

### Translation interface in the backend

 * Edit through a contextual popin
 * Fast inline editing
 * Possibility to view various translated column at the same time (en, fr, pt, etc...)
 * Filter by locale, domain, date
 * Filter for untranslated key
 * Search by key name or translation value
 * Display help messages from the developpers

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
