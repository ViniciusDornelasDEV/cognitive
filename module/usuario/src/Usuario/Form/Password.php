<?php

namespace User\Form;

use Application\Form\Base as BaseForm;

class Password extends BaseForm {

    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
    public function __construct($name = null) {
        parent::__construct($name);

        $this->addHiddenInput('reset_token', true);
        $this->_addPassword('password', false, 'Please enter a password with at least 7 characters');
        $this->_addPassword('repeat_password', false, 'Repeat Your Password', 'password');

        $this->addSubmit();
    }

}
