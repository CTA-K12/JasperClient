<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Custom;

use JasperClient\Client\InputControlOption;

class InputControlOptionListProviderName extends AbstractInputControlOptionList {


    public function __construct($state) {
       $this->createList($state);
    }

    protected function createList($state) {

        $authDivisionRepository = new \authDivisionRepository();
        $authDivisionRepository->findByTypeProvider();
        $authDivisionData = $authDivisionRepository->get_record();

        foreach( $authDivisionData as $key => $authDivision ) {
            $selected = false;
            foreach($state->options->option as $key => $option) {
                if ( $option->value == $authDivision->get_name() &&
                     'true' == $option->selected ) {
                    $selected = true;
                }
            }
            $this->list[] = new InputControlOption (
                $authDivision->get_name(),
                $authDivision->get_name(),
                $selected
            );
        }
    }

}