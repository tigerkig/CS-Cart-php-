<?php


$schema['conditions']['organizations'] = [
    'operators' => ['in', 'nin'],
    'type' => 'picker',
    'picker_props' => [
        'picker' => 'addons/organizations/views/organizations/components/picker/proxy.tpl',
        'params' => [
            'multiple' => true,
            'meta' => 'select2-wrapper--width-auto'
        ],
    ],
    'field' => '@auth.organization_id',
    'zones' => ['catalog', 'cart']
];

return $schema;
