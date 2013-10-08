<?php

namespace Liip\TranslationBundle\Persistence\Propel\Model;

use Liip\TranslationBundle\Persistence\Propel\Model\om\BaseUnitQuery;


/**
 * Query class of the propel representation of a translation
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
class UnitQuery extends BaseUnitQuery
{

    /**
     * Find a unit by domain and key
     *
     * @param $domain
     * @param $key
     *
     * @return Unit
     */
    public function findOneByDomainAndKey($domain, $key)
    {
        return $this->filterByDomain($domain)->filterByKey($key)->findOne();
    }
}
