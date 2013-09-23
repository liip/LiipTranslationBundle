<?php

namespace Symfony\Component\Translation;

if (! interface_exists('MetadataAwareInterface')) {
    interface MetadataAwareInterface
    {
        public function getMetadata($key = '', $domain = 'messages');
        public function setMetadata($key, $value, $domain = 'messages');
        public function deleteMetadata($key = '', $domain = 'messages');
    }
}

namespace Liip\TranslationBundle\Translation;

use Symfony\Component\Translation\MessageCatalogue as BaseMessageCatalogue;
use Symfony\Component\Translation\MetadataAwareInterface;

if(in_array('Symfony\Component\Translation\MetadataAwareInterface', class_implements('Symfony\Component\Translation\MessageCatalogue'))) {
    class MessageCatalogue extends BaseMessageCatalogue
    {
    }
} else {
    class MessageCatalogue extends BaseMessageCatalogue implements MetadataAwareInterface
    {
        public function getMetadata($key = '', $domain = 'messages')
        {
            // TODO: Implement getMetadata() method.
        }

        public function setMetadata($key, $value, $domain = 'messages')
        {
            // TODO: Implement setMetadata() method.
        }

        public function deleteMetadata($key = '', $domain = 'messages')
        {
            // TODO: Implement deleteMetadata() method.
        }
    }
}