<?php

namespace Models;

use Core\Bases\BaseGroupAcf;

class LinearSlider extends BaseGroupAcf
{

    public $titolo_logo_1;
    public $titolo_logo_2;
    public $titolo_logo_3;

    public $logo_1;
    public $logo_2;
    public $logo_3;

    public function __construct($postId = null) {
        parent::__construct('group_67ecfc307a8f5', $postId ?: get_the_ID());
        $this->defineAttributes();
    }

    public function defineAttributes()
    {
        //Slider
        $this->addField('titolo_logo_1');
        $this->addField('titolo_logo_2');
        $this->addField('titolo_logo_3');
        $this->addField('logo_1');
        $this->addField('logo_2');
        $this->addField('logo_3');
    }
}