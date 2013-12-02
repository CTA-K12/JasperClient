<?php

/**
 * @author David Cramblett
 */

namespace JasperClient\Custom;

use JasperClient\Client\InputControlOption;

class InputControlOptionListMONTH extends AbstractInputControlOptionList {


    public function __construct($state) {
       $this->createList($state);
    }

    protected function createList($state) {

        $month = array(
                       'January'   => 1,
                       'February'  => 2,
                       'March'     => 3,
                       'April'     => 4,
                       'May'       => 5,
                       'June'      => 6,
                       'July'      => 7,
                       'August'    => 8,
                       'September' => 9,
                       'October'   => 10,
                       'November'  => 11,
                       'December'  => 12
                      );

        foreach( $month as $k => $v ) {
            $this->list[] = new InputControlOption (
                $v,
                $k,
                false
            );
        }
    }

}
