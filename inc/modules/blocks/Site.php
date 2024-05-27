<?php

/**
 * This file is part of Batflat ~ the lightweight, fast and easy CMS
 *
 * @author       Paweł Klockiewicz <klockiewicz@sruu.pl>
 * @author       Wojciech Król <krol@sruu.pl>
 * @copyright    2017 Paweł Klockiewicz, Wojciech Król <Sruu.pl>
 * @license      https://batflat.org/license
 * @link         https://batflat.org
 */

namespace Inc\Modules\Blocks;

use Inc\Core\SiteModule;

class Site extends SiteModule
{
    public function init()
    {
        $this->_importBlocks();
    }

    private function _importBlocks()
    {
        $rows = $this->db('blocks')->toArray();

        $blocks = [];
        foreach ($rows as $row) {
            $blocks[$row['slug']] = $row['content'];
        }

        return $this->tpl->set('block', $blocks);
    }
}
