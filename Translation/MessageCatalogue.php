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
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\MetadataAwareInterface;

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle. For more information concerning
 * the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Persistence
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @author Sylvain Fankhauser <sylvain.fankhauser@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
if(in_array('Symfony\Component\Translation\MetadataAwareInterface', class_implements('Symfony\Component\Translation\MessageCatalogue'))) {
    class MessageCatalogue extends BaseMessageCatalogue
    {
    }
} else {
    class MessageCatalogue extends BaseMessageCatalogue implements MetadataAwareInterface
    {
        private $metadata = array();

        public function addCatalogue(MessageCatalogueInterface $catalogue)
        {
            parent::addCatalogue($catalogue);

            if ($catalogue instanceof MetadataAwareInterface) {
                $metadata = $catalogue->getMetadata('', '');
                $this->addMetadata($metadata);
            }
        }

        public function getMetadata($key = '', $domain = 'messages')
        {
            if ('' == $domain) {
                return $this->metadata;
            }

            if (isset($this->metadata[$domain])) {
                if ('' == $key) {
                    return $this->metadata[$domain];
                }

                if (isset($this->metadata[$domain][$key])) {
                    return $this->metadata[$domain][$key];
                }
            }

            return null;
        }

        public function setMetadata($key, $value, $domain = 'messages')
        {
            $this->metadata[$domain][$key] = $value;
        }

        public function deleteMetadata($key = '', $domain = 'messages')
        {
            if ('' == $domain) {
                $this->metadata = array();
            } elseif ('' == $key) {
                unset($this->metadata[$domain]);
            } else {
                unset($this->metadata[$domain][$key]);
            }
        }

        private function addMetadata(array $values)
        {
            foreach ($values as $domain => $keys) {
                foreach ($keys as $key => $value) {
                    $this->setMetadata($key, $value, $domain);
                }
            }
        }
    }
}