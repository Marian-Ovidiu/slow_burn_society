<?php
namespace Controllers;

use Core\Bases\BaseController;
use Models\GalleriaFields;
use Models\Grazie;

class PageController extends BaseController
{
    public function galleria()
    {
        $this->addJs('highlight', 'highlight.js', [], true);
        $this->addVarJs('highlight', 'highlights', GalleriaFields::get()->highlights);
        $this->render('galleria', ['galleria' => GalleriaFields::get()]);
    }
        
    public function grazie()
    {
        $fields = Grazie::get();
        $this->render('grazie', ['fields' => $fields]);
    }
}
