<?php

namespace Models;

use Core\Bases\BaseGroupAcf;

class GalleriaFields extends BaseGroupAcf
{

    public $titolo;
    public $frase_base;
    public $parola_1;
    public $parola_2;
    public $parola_3;
    public $descrizione;
    public $immagine_1;
    public $immagine_2;
    public $immagine_3;
    public $immagine_4;
    public $immagine_5;
    public $immagine_6;
    public $immagine_7;
    public $immagine_8;
    public $immagine_9;
    public $immagine_10;
    public $immagine_11;
    public $immagine_12;
    public $testo_1;
    public $testo_2;
    public $testo_3;
    public $testo_4;
    public $testo_5;
    public $testo_6;
    public $testo_7;
    public $testo_8;
    public $testo_9;
    public $testo_10;
    public $testo_11;
    public $testo_12;
    public $descrizione_1;
    public $descrizione_2;
    public $descrizione_3;
    public function __construct($postId = null) {
        parent::__construct('group_6735fa35e43e1', $postId ?: get_the_ID());
        $this->defineAttributes();
    }

    public function defineAttributes()
    {
        //Slider
        $this->addField('titolo');
        $this->addField('frase_base');
        $this->addField('parola_1');
        $this->addField('parola_2');
        $this->addField('parola_3');
        $this->addField('descrizione');
        $this->addField('immagine_1');
        $this->addField('immagine_2');
        $this->addField('immagine_3');
        $this->addField('immagine_4');
        $this->addField('immagine_5');
        $this->addField('immagine_6');
        $this->addField('immagine_7');
        $this->addField('immagine_8');
        $this->addField('immagine_9');
        $this->addField('immagine_10');
        $this->addField('immagine_11');
        $this->addField('immagine_12');
        $this->addField('testo_1');
        $this->addField('testo_2');
        $this->addField('testo_3');
        $this->addField('testo_4');
        $this->addField('testo_5');
        $this->addField('testo_6');
        $this->addField('testo_7');
        $this->addField('testo_8');
        $this->addField('testo_9');
        $this->addField('testo_10');
        $this->addField('testo_11');
        $this->addField('testo_12');
        $this->addField('descrizione_1');
        $this->addField('descrizione_2');
        $this->addField('descrizione_3');
    }

    public function getDataAttribute()
    {
        $data = [
            [
                [
                    'immagine' => $this->immagine_1,
                    'testo' => $this->testo_1,
                    'descrizione' => null
                ],
                [
                    'immagine' => $this->immagine_2,
                    'testo' => $this->testo_2,
                    'descrizione' => null
                ],
                [
                    'immagine' => $this->immagine_3,
                    'testo' => $this->testo_3,
                    'descrizione' => null
                ],
                [
                    'immagine' => $this->immagine_4,
                    'testo' => $this->testo_4,
                    'descrizione' => $this->descrizione_1
                ],
            ],
            [
                [
                    'immagine' => $this->immagine_5,
                    'testo' => $this->testo_5,
                    'descrizione' => null
                ],
                [
                    'immagine' => $this->immagine_6,
                    'testo' => $this->testo_6,
                    'descrizione' => null
                ],
                [
                    'immagine' => $this->immagine_7,
                    'testo' => $this->testo_7,
                    'descrizione' => null
                ],
                [
                    'immagine' => $this->immagine_8,
                    'testo' => $this->testo_8,
                    'descrizione' => $this->descrizione_2
                ],
            ],
            [
                [
                    'immagine' => $this->immagine_9,
                    'testo' => $this->testo_9,
                    'descrizione' => null
                ],
                [
                    'immagine' => $this->immagine_10,
                    'testo' => $this->testo_10,
                    'descrizione' => null
                ],
                [
                    'immagine' => $this->immagine_11,
                    'testo' => $this->testo_11,
                    'descrizione' => null
                ],
                [
                    'immagine' => $this->immagine_12,
                    'testo' => $this->testo_12,
                    'descrizione' => $this->descrizione_3
                ]
            ]
        ];

        return $data;
    }

    public function getHighlightsAttribute()
    {
         return [
           $this->parola_1,
           $this->parola_2,
           $this->parola_3,
         ];
    }
}