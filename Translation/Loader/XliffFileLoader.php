<?php

namespace Liip\TranslationBundle\Translation\Loader;

use Symfony\Component\Translation\Loader\XliffFileLoader as BaseLoader;
use Symfony\Component\Translation\MessageCatalogue;

class XliffFileLoader extends BaseLoader
{
    /**
     * Load translations and metadata of the trans-unit
     *
     * {@inheritdoc}
     * @api
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        /** @var MessageCatalogue $catalogue */
        $catalogue = parent::load($resource, $locale, $domain);

        // Process a second pass over the file to collect metadata
        $xml=simplexml_load_file($resource);
        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:1.2');
        foreach ($xml->xpath('//xliff:trans-unit') as $translation) {
            $attributes = (array)$translation->attributes();
            $attributes = $attributes['@attributes'];
            if (!(isset($attributes['resname']) || isset($translation->source)) || !isset($translation->target)) {
                continue;
            }
            $key = isset($attributes['resname']) && $attributes['resname'] ? $attributes['resname'] : $translation->source;
            $catalogue->setMetadata((string)$key, (array)$attributes, $domain);
        }

        return $catalogue;
    }

}