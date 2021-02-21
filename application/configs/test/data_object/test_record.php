<?php

use Dvelum\Data\Record\DefaultValue\CurrentDateTimeString;
use Dvelum\Validator\Email;

return [
    'fields' => [
        'int_field' => [
            'type' => 'int'
        ],
        'float_field' => [
            'type' => 'float'
        ],
        'bool_field' => [
            'type' => 'bool'
        ],
        'string_field' => [
            'type' => 'string'
        ],
        'string_field_date' => [
            'type' => 'string',
            'defaultValueAdapter' =>CurrentDateTimeString::class
        ],
        'string_feld_required' => [
            'type' => 'string',
            'required' => true
        ],
        'string_field_limit' => [
            'type' => 'string',
            'required' => true,
            'maxLength' => 5
        ],
        'string_field_email' => [
            'type' => 'string',
            'required' => true,
            'validator' => Email::class
        ],
        'int_field_limit'=>[
            'type' => 'int',
            'minValue'=>1,
            'maxValue' =>10
        ],
        'json_field' => [
            'type' => 'json'
        ],
        'string_default' => [
            'type' => 'string',
            'default' => 'default'
        ],
    ]
];