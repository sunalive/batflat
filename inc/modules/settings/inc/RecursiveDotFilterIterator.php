<?php
    namespace Inc\Modules\Settings\Inc;

class RecursiveDotFilterIterator extends \RecursiveFilterIterator
{
    public function accept():bool
    {
        return '.' !== substr($this->current()->getFilename(), 0, 1);
    }
}
