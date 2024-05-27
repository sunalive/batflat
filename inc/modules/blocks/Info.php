<?php

/**
 * This file is part of Batflat ~ the lightweight, fast and easy CMS
 *
 * @author       Ravi Iyer <ravi@divilogic.com>
 * @copyright    2024 Ravi Iyer
 * @license      https://batflat.org/license
 * @link         https://batflat.org
 */

return [
    'name'          => $core->lang['blocks']['module_name'],
    'description'   => $core->lang['blocks']['module_desc'],
    'author'        => 'Ravi Iyer',
    'version'       => '1.2',
    'compatibility' => '1.3.*',
    'icon'          => 'cubes',
    'install'       => function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `blocks` (
            `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT,
            `name` text NOT NULL,
            `slug` text NOT NULL,
            `content` text NOT NULL
            )");
    },
    'uninstall'     => function () use ($core) {
        $core->db()->pdo()->exec("DROP TABLE `blocks`");
    }
];
