<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Custom;

use JasperClient\Client\InputControlOption;

class InputControlOptionListXlsExport extends AbstractInputControlOptionList {


    public function __construct($state) {
       $this->createList($state);
    }

    protected function createList($state) {
        $this->list[] = new InputControlOption ('N', 'No', true);
        $this->list[] = new InputControlOption ('Y', 'Yes', false);
    }


}