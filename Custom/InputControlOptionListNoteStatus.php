<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Custom;

use JasperClient\Client\InputControlOption;

class InputControlOptionListNoteStatus extends AbstractInputControlOptionList {


    public function __construct($state) {
       $this->createList($state);
    }

    protected function createList($state) {

        $statusTypeRepository = new \statusTypeRepository();
        $statusTypeRepository->findAll();
        $statusTypeData = $statusTypeRepository->get_record();

        foreach($statusTypeData as $key => $statusType) {
            $selected = false;
            foreach($state->options->option as $key => $option) {
                if ( $option->value == $statusType->get_name() &&
                     'true' == $option->selected ) {
                    $selected = true;
                }
            }
            $this->list[] = new InputControlOption (
                $statusType->get_name(),
                $statusType->get_description(),
                $selected
            );
        }
    }

}