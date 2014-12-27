<?php

namespace Liip\TranslationBundle\Translation\Loader;

use Liip\TranslationBundle\Translation\MessageCatalogue;
use Symfony\Component\Translation\Loader\XliffFileLoader as BaseLoader;

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
        $base_catalogue = parent::load($resource, $locale, $domain);
        $catalogue = new MessageCatalogue($locale);
        $catalogue->addCatalogue($base_catalogue);

        // Process a second pass over the file to collect metadata
        $xml = simplexml_load_file($resource);
        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:1.2');
        foreach ($xml->xpath('//xliff:trans-unit') as $translation) {
            // Read the attributes
            $attributes = (array) $translation->attributes();
            $attributes = $attributes['@attributes'];
            if (!(isset($attributes['resname']) || isset($translation->source)) || !isset($translation->target)) {
                continue;
            }
            $key = isset($attributes['resname']) && $attributes['resname'] ? $attributes['resname'] : $translation->source;
            $metadata = (array) $attributes;

            // read the notes
            if (isset($translation->note)) {
                $metadata['note'] = (string) $translation->note;
            }

            $catalogue->setMetadata((string) $key, $metadata, $domain);
        }

        return $catalogue;
    }
}
