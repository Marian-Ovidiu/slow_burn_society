<?php

namespace Models\Options;

use Core\Bases\BaseGroupAcf;

class OpzioniProdottoFields extends BaseGroupAcf
{
    protected $groupKey = 'group_683fe07a62da6';

    public $logo;

    public function __construct($postId = null)
    {
        parent::__construct($this->groupKey, $postId);
        $this->defineAttributes();
    }

    public function defineAttributes()
    {
        $this->addField('logo');
    }
}
