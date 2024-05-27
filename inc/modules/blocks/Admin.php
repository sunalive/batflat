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

use Inc\Core\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            $this->lang('manage', 'general') => 'manage',
            $this->lang('add') => 'add',
        ];
    }

    /**
     * list of blocks
     */
    public function getManage()
    {
        $rows = $this->db('blocks')->toArray();
        if (count($rows)) {
            foreach ($rows as &$row) {
                $row['tag'] = $this->tpl->noParse('{$block.' . $row['slug'] . '}');
                $row['editURL'] = url([ADMIN, 'blocks', 'edit', $row['id']]);
                $row['delURL'] = url([ADMIN, 'blocks', 'delete', $row['id']]);
            }
        }

        return $this->draw('manage.html', ['blocks' => $rows]);
    }

    /**
     * add new block
     */
    public function getAdd()
    {
        return $this->getEdit();
    }

    /**
     * edit block
     */
    public function getEdit($id = null)
    {
        $this->_add2header();

        if (!empty($redirectData = getRedirectData())) {
            $assign = $redirectData;
        }

        if ($id === null) {
            $row = ['name' => isset_or($assign['name'], null), 'content' => isset_or($assign['content'], null)];
            $assign['title'] = $this->lang('add');
        } elseif (!empty($row = $this->db('blocks')->oneArray($id))) {
            $assign['title'] = $this->lang('edit');
        } else {
            redirect(url([ADMIN, 'blocks', 'manage']));
        }

        $assign = array_merge($assign, htmlspecialchars_array($row));
        $assign['languages'] = $this->_getLanguages($this->settings('settings', 'lang_site'));

        $assign['content'] = [];
        preg_match_all("/{lang: ([a-z]{2}_[a-z]+)}(.*?){\/lang}/ms", $row['content'], $matches);

        foreach ($matches[1] as $key => $value) {
            $assign['content'][trim($value)] = $this->tpl->noParse(trim($matches[2][$key]));
        }

        $assign['editor'] = $this->settings('settings', 'editor');

        return $this->draw('form.html', ['blocks' => $assign]);
    }

    /**
     * remove block
     */
    public function getDelete($id)
    {
        if ($this->db('blocks')->delete($id)) {
            $this->notify('success', $this->lang('delete_success'));
        } else {
            $this->notify('failure', $this->lang('delete_failure'));
        }

        redirect(url([ADMIN, 'blocks', 'manage']));
    }

    /**
     * save block
     */
    public function postSave($id = null)
    {
        unset($_POST['save']);
        $formData = htmlspecialchars_array($_POST);

        if (checkEmptyFields(['name'], $formData)) {
            $this->notify('failure', $this->lang('empty_inputs', 'general'));

            if (!$id) {
                redirect(url([ADMIN, 'blocks', 'add']));
            } else {
                redirect(url([ADMIN, 'blocks', 'edit', $id]));
            }
        }

        $formData['name'] = trim($formData['name']);
        $formData['slug'] = createSlug($formData['name']);

        $tmp = null;
        foreach ($formData['content'] as $lang => $content) {
            $tmp .= "{lang: $lang}" . $content . "{/lang}";
        }

        $formData['content'] = $tmp;

        if ($id === null) { // new
            $location = url([ADMIN, 'blocks', 'add']);
            if (!$this->db('blocks')->where('slug', $formData['slug'])->count()) {
                if ($this->db('blocks')->save($formData)) {
                    $location = url([ADMIN, 'blocks', 'edit', $this->db()->lastInsertId()]);
                    $this->notify('success', $this->lang('save_success'));
                } else {
                    $this->notify('failure', $this->lang('save_failure'));
                }
            } else {
                $this->notify('failure', $this->lang('already_exists'));
            }
        } else {    // edit
            if (!$this->db('blocks')->where('slug', $formData['slug'])->where('id', '<>', $id)->count()) {
                if ($this->db('blocks')->where($id)->save($formData)) {
                    $this->notify('success', $this->lang('save_success'));
                } else {
                    $this->notify('failure', $this->lang('save_failure'));
                }
            } else {
                $this->notify('failure', $this->lang('already_exists'));
            }

            $location =  url([ADMIN, 'blocks', 'edit', $id]);
        }

        redirect($location, $formData);
    }

    /**
     * module JavaScript
     */
    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES . '/blocks/js/admin/blocks.js');
        exit();
    }

    private function _add2header()
    {
        // WYSIWYG
        $this->core->addCSS(url('inc/jscripts/wysiwyg/summernote.min.css'));
        $this->core->addJS(url('inc/jscripts/wysiwyg/summernote.min.js'));
        if ($this->settings('settings', 'lang_admin') != 'en_english') {
            $this->core->addJS(url('inc/jscripts/wysiwyg/lang/' . $this->settings('settings', 'lang_admin') . '.js'));
        }

        // HTML EDITOR
        $this->core->addCSS(url('/inc/jscripts/editor/markitup.min.css'));
        $this->core->addCSS(url('/inc/jscripts/editor/markitup.highlight.min.css'));
        $this->core->addCSS(url('/inc/jscripts/editor/sets/html/set.min.css'));
        $this->core->addJS(url('/inc/jscripts/editor/highlight.min.js'));
        $this->core->addJS(url('/inc/jscripts/editor/markitup.min.js'));
        $this->core->addJS(url('/inc/jscripts/editor/markitup.highlight.min.js'));
        $this->core->addJS(url('/inc/jscripts/editor/sets/html/set.min.js'));

        // ARE YOU SURE?
        $this->core->addJS(url('inc/jscripts/are-you-sure.min.js'));

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'blocks', 'javascript']));
    }
}
