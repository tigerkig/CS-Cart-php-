<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

$_scheme = [
    'menus' => [
        'vendor_only' => true,
        'use_company' => true,
        'page_title'  => 'menus',
    ],
    'tabs' => [
        'vendor_only' => true,
        'use_company' => true,
        'page_title'  => 'product_tabs',
    ],
    'block_manager' => [
        'vendor_only' => true,
        'use_company' => true,
        'page_title'  => 'layouts',
    ],
    'sitemap' => [
        'vendor_only' => true,
        'use_company' => true,
        'page_title'  => 'sitemap',
    ],
    'themes' => [
        'vendor_only' => true,
        'use_company' => true,
        'page_title'  => 'themes',
    ],
    'customization' => [
        'vendor_only' => true,
        'use_company' => true,
        'page_title'  => 'customization',
    ],
    'order_management' => [
        'vendor_only' => true,
        'use_company' => true,
        'page_title'  => 'order_management',
    ],
    'static_data' => [
        'vendor_only' => [
            'display_condition' => [
                'section' => 'A',
            ],
        ],
        'use_company' => [
            'condition' => [
                [
                    'field' => 'section',
                    'value' => 'A'
                ],
            ],
        ],
        'page_title' => 'static_data',
    ],
    'companies' => [
        'modes' => [
            'manage' => [
                'permissions' => 'view_stores'
            ],
            'add' => [
                'permissions' => 'manage_stores'
            ],
            'update' => [
                'permissions' => [
                    'GET'  => 'view_stores',
                    'POST' => 'manage_stores'
                ],
            ],
            'delete' =>  [
                'permissions' => 'manage_stores',
            ],
            'switch_storefront_status' => [
                'permissions' => 'manage_stores',
            ],
        ],
        'page_title' => 'companies',
    ],
    'storefronts' => [
        'modes' => [
            'picker' => [
                'permissions' => [
                    'GET' => 'view_stores',
                ],
                'condition' => [
                    'operator' => 'or',
                    'function' => ['fn_is_admin_account_type']
                ],
            ],
        ],
    ],
    'products' => [
        'modes' => [
            'update' => [
                'use_company' => true,
            ],
            'add' => [
                'use_company' => true,
            ],
        ],
        'page_title' => 'products',
    ],
    'product_options' => [
        'modes' => [
            'update' => [
                'use_company' => true,
            ],
        ],
    ],
    'product_features' => [
        'modes' => [
            'update' => [
                'use_company' => true,
            ],
            'get_variants' => [
                'use_company' => true,
            ],
        ],
    ],
    'categories' => [
        'modes' => [
            'update' => [
                'use_company' => true,
            ],
            'add' => [
                'use_company' => true,
            ],
        ],
        'page_title' => 'categories',
    ],
    'pages' => [
        'modes' => [
            'update' => [
                'use_company' => true,
            ],
            'add' => [
                'use_company' => true,
            ],
        ],
        'page_title' => 'pages',
    ],
    'payments' => [
        'modes' => [
            'add' => [
                'use_company' => true,
            ],
        ],
        'page_title' => 'payments',
    ],
    'currencies' => [
        'modes' => [
            'update' => [
                'auto_sharing' => [
                    'object_id'   => 'currency_data.currency_id',
                    'object_type' => 'currencies'
                ],
            ],
        ],
        'page_title' => 'currencies',
    ],
    'languages' => [
        'modes' => [
            'update' => [
                'auto_sharing' => [
                    'object_id'   => 'language_data.lang_id',
                    'object_type' => 'languages'
                ],
            ],
        ],
        'page_title' => 'languages',
    ],
    'profile_fields' => [
        'modes' => [
            'update' => [
                'auto_sharing' => [
                    'object_id'   => 'field_id',
                    'object_type' => 'profile_fields'
                ],
            ],
        ],
        'page_title' => 'profile_fields',
    ],
    'file_editor' => [
        'page_title'  => 'file_editor',
        'use_company' => true,
    ],
    'exim' => [
        'use_company' => true,
    ],
    'sync_data' => [
        'vendor_only' => true,
        'use_company' => true,
    ]
];

$schema = array_merge_recursive($schema, $_scheme);

return $schema;
