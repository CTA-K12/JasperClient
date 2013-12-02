<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Custom;

use JasperClient\Client\InputControlOption;

class InputControlOptionListProgram extends AbstractInputControlOptionList {


    public function __construct($state) {
       $this->createList($state);
    }

    protected function createList($state) {

        $contractRepository = new \contractRepository();
        $contractRepository->findByUserId($_SESSION["userId"]);
        $contractData = $contractRepository->get_record();

        foreach( $contractData as $key => $contract ) {
            $selected = false;
            foreach($state->options->option as $key => $option) {
                if ( $option->value == $contract->get_name() &&
                     'true' == $option->selected ) {
                    $selected = true;
                }
            }
            $this->list[] = new InputControlOption (
                $contract->get_name(),
                $contract->get_acronym(),
                $selected
            );
        }
    }

}
