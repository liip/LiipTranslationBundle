<?php

namespace Symfony\Component\Translation;

if (! interface_exists('Symfony\Component\Translation\MetadataAwareInterface')) {
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

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Storage\Persistence
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
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