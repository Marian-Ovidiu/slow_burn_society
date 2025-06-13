<?php

namespace Models;

use Core\Bases\BaseGroupAcf;

class AziendeFields extends BaseGroupAcf
{

    public $hero_titolo;
    public $hero_sottotitolo;
    public $perche_titolo;
    public $perche_testo;
    public $come_titolo;
    public $come_testo;
    public $form_titolo;
    public $form_testo;
    public $immagine_hero;
    public $immagine_banner;
    public $shortcode_form;
    public function __construct($postId = null) {
        parent::__construct('group_6735fa35e43e1', $postId ?: get_the_ID());
        $this->defineAttributes();
    }

    public function defineAttributes()
    {
        $this->addField('hero_titolo');
        $this->addField('hero_sottotitolo');
        $this->addField('perche_titolo');
        $this->addField('perche_testo');
        $this->addField('come_titolo');
        $this->addField('come_testo');
        $this->addField('form_titolo');
        $this->addField('form_testo');
        $this->addField('immagine_hero');
        $this->addField('immagine_banner');
        $this->addField('shortcode_form');
    }
}