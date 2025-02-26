<?php

namespace App\Http\Helpers;

class ResponseModel {

    var $message;
    var $status;
    var $data;

    public function __construct($message, $status, $data) {
        $this->message = $message;
        $this->status = $status;
        $this->data = $data;
    }
}

class Messages {
    var $success = 'Successful';
    var $fail = 'Fail';
}