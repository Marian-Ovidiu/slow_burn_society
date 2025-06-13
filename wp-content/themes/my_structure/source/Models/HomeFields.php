<?php

namespace Models;

use Core\Bases\BaseGroupAcf;

class HomeFields extends BaseGroupAcf
{

    public $immagine_1;
    public $immagine_2;
    public $immagine_3;
    public $immagine_4;
    public $titolo_1;
    public $titolo_2;
    public $titolo_3;
    public $titolo_4;
    public $testo_1;
    public $testo_2;
    public $testo_3;
    public $testo_4;
    public $cta_1;
    public $cta_2;
    public $cta_3;
    public $cta_4;
    public $titolo_missione;
    public $testo_missione;
    public $cta_missione_dona_ora;
    public $cta_missione_galleria;
    public $titolo_progetti;
    public $descrizione_progetti;
    public $immagine_tutti_progetti;
    public $titolo_tutti_progetti;
    public $cta_tutti_progetti;
    public $immagine_sociale_ghana;
    public $immagine_sociale_nigeria;
    public $titolo_sociale_ghana;
    public $titolo_sociale_nigeria;
    public $cta_sociale_ghana;
    public $cta_sociale_nigeria;
    public $immagine_antibracconaggio;
    public $titolo_antibracconaggio;
    public $cta_antibracconaggio;
    public $immagine_cani;
    public $titolo_cani;
    public $cta_cani;
    public $repeater_progetti;
    public $titolo_chart;
    public $descrizione_chart;
    public $titolo_azienda;
    public $descrizione_azienda;
    public $cta_azienda;
    public $immagine_azienda;
    public function __construct($postId = null) {
        parent::__construct('group_6712db9b59faa', $postId ?: get_the_ID());
        $this->defineAttributes();
    }

    public function defineAttributes()
    {
        //Slider
        $this->addField('immagine_1');
        $this->addField('immagine_2');
        $this->addField('immagine_3');
        $this->addField('immagine_4');
        $this->addField('titolo_1');
        $this->addField('titolo_2');
        $this->addField('titolo_3');
        $this->addField('titolo_4');
        $this->addField('testo_1');
        $this->addField('testo_2');
        $this->addField('testo_3');
        $this->addField('testo_4');
        $this->addField('cta_1');
        $this->addField('cta_2');
        $this->addField('cta_3');
        $this->addField('cta_4');

        //La nostra missione
        $this->addField('titolo_missione');
        $this->addField('testo_missione');
        $this->addField('cta_missione_dona_ora');
        $this->addField('cta_missione_galleria');

        //Sezione progetti
        $this->addField('titolo_progetti');
        $this->addField('descrizione_progetti');
        $this->addField('immagine_tutti_progetti');
        $this->addField('titolo_tutti_progetti');
        $this->addField('cta_tutti_progetti');
        $this->addField('immagine_sociale_ghana');
        $this->addField('immagine_sociale_nigeria');
        $this->addField('titolo_sociale_ghana');
        $this->addField('titolo_sociale_nigeria');
        $this->addField('cta_sociale_ghana');
        $this->addField('cta_sociale_nigeria');
        $this->addField('immagine_antibracconaggio');
        $this->addField('titolo_antibracconaggio');
        $this->addField('cta_antibracconaggio');
        $this->addField('immagine_cani');
        $this->addField('titolo_cani');
        $this->addField('cta_cani');

        //Sezione chart
        $this->addField('titolo_chart');
        $this->addField('descrizione_chart');

        // Sezione aziende
        $this->addField('titolo_azienda');
        $this->addField('descrizione_azienda');
        $this->addField('cta_azienda');
        $this->addField('immagine_azienda');
    }

    public function getProgettiAttribute()
    {
        $this->repeater_progetti[0]['immagine'] = $this->immagine_tutti_progetti;
        $this->repeater_progetti[0]['titolo'] = $this->titolo_tutti_progetti;
        $this->repeater_progetti[0]['cta'] = $this->cta_tutti_progetti;
        $this->repeater_progetti[1]['immagine'] = $this->immagine_sociale_ghana;
        $this->repeater_progetti[1]['titolo'] = $this->titolo_sociale_ghana;
        $this->repeater_progetti[1]['cta'] = $this->cta_sociale_ghana;
        $this->repeater_progetti[2]['immagine'] = $this->immagine_sociale_nigeria;
        $this->repeater_progetti[2]['titolo'] = $this->titolo_sociale_nigeria;
        $this->repeater_progetti[2]['cta'] = $this->cta_sociale_nigeria;
        $this->repeater_progetti[3]['immagine'] = $this->immagine_antibracconaggio;
        $this->repeater_progetti[3]['titolo'] = $this->titolo_antibracconaggio;
        $this->repeater_progetti[3]['cta'] = $this->cta_antibracconaggio;
        $this->repeater_progetti[4]['immagine'] = $this->immagine_cani;
        $this->repeater_progetti[4]['titolo'] = $this->titolo_cani;
        $this->repeater_progetti[4]['cta'] = $this->cta_cani;

        return $this->repeater_progetti;
    }
}