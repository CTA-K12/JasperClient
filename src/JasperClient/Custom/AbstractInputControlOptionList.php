<?php

/**
 *  @author David Cramblett
 */

namespace JasperClient\Custom;

abstract class AbstractInputControlOptionList {

    protected $list;

    public function getList() {
        return $this->list;
    }

    protected function createList() {
        throw new Exception(
            'Method createList() should be overwritten by custom child class'
        );
    }
}