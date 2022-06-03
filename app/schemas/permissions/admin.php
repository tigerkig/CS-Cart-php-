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

use Tygh\Registry;

$schema =  [
    'orders' => [
        'modes' => [
            'update_status' => [
                'permissions' => 'change_order_status'
            ],
            'delete_orders' => [
                'permissions' => 'delete_orders'
            ],
            'delete' => [
                'permissions' => 'delete_orders'
            ],
            'm_delete' => [
                'permissions' => 'delete_orders'
            ],
            'bulk_print' => [
                'permissions' => 'view_orders'
            ],
            'remove_cc_info' => [
                'permissions' => 'update_order_details'
            ],
            'update_details' => [
                'permissions' => 'update_order_details'
            ],
            'assign_manager' => [
                'permissions' => 'update_order_details'
            ],
            'export_range' => [
                'permissions' => 'exim_access'
            ],
            'm_update' => [
                'permissions' => 'change_order_status'
            ],
        ],
        'permissions' => 'view_orders'
    ],
    'taxes' => [
        'modes' => [
            'delete' => [
                'permissions' => 'manage_taxes'
            ],
        ],
        'permissions' => ['GET' => 'view_taxes', 'POST' => 'manage_taxes'],
    ],
    'sitemap' => [
        'permissions' => 'manage_sitemap',
    ],
    'datakeeper' => [
        'permissions' => 'backup_restore',
    ],
    'product_options' => [
        'modes' => [
            'delete' => [
                'permissions' => 'manage_catalog'
            ],
            'm_delete' => [
                'permissions' => 'manage_catalog'
            ]
        ],
        'permissions' => ['GET' => 'view_catalog', 'POST' => 'manage_catalog'],
    ],
    'tabs' => [
        'modes' => [
            'delete' => [
                'permissions' => 'manage_catalog'
            ],
            'update_status' => [
                'permissions' => 'manage_catalog'
            ],
            'update' => [
                'permissions' => 'manage_catalog'
            ],
            'add' => [
                'permissions' => 'manage_catalog'
            ],
            'manage' => [
                'permissions' => 'view_catalog'
            ],
            'picker' => [
                'permissions' => 'view_catalog'
            ],
        ],
        'permissions' => ['GET' => 'view_catalog', 'POST' => 'manage_catalog'],
    ],
    'products' => [
        'modes' => [
            'delete' => [
                'permissions' => 'manage_catalog'
            ],
            'clone' => [
                'permissions' => 'manage_catalog'
            ],
            'add' => [
                'permissions' => 'manage_catalog'
            ],
            'manage' => [
                'permissions' => 'view_catalog'
            ],
            'picker' => [
                'permissions' => 'view_catalog'
            ],
            'options' => [
                'permissions' => 'update_order_details'
            ],
            'm_update_categories' => [
                'permissions' => 'manage_catalog'
            ],
            'm_update_prices' => [
                'permissions' => 'manage_catalog'
            ],
            'm_activate' => [
                'permissions' => 'manage_catalog'
            ],
            'm_disable' => [
                'permissions' => 'manage_catalog'
            ],
            'm_hide' => [
                'permissions' => 'manage_catalog'
            ],
            'm_clone' => [
                'permissions' => 'manage_catalog'
            ],
            'export_range' => [
                'permissions' => 'exim_access'
            ],
            'm_delete' => [
                'permissions' => 'manage_catalog'
            ],
            'global_update' => [
                'permissions' => 'manage_catalog'
            ],
            'm_add' => [
                'permissions' => 'manage_catalog'
            ],
            'export_found' => [
                'permissions' => 'exim_access'
            ],
            'm_delete_subscr' => [
                'permissions' => 'manage_catalog'
            ],
        ],
        'permissions' => ['GET' => 'view_catalog', 'POST' => 'manage_catalog'],
    ],
    'product_filters' => [
        'modes' => [
            'delete'            => [
                'permissions' => 'manage_catalog'
            ],
            'm_delete'          => [
                'permissions' => 'manage_catalog'
            ],
            'm_update_statuses' => [
                'permissions' => 'manage_catalog'
            ],
        ],
        'permissions' => ['GET' => 'view_catalog', 'POST' => 'manage_catalog'],
    ],
    'shippings' => [
        'modes' => [
            'delete_shipping' => [
                'permissions' => 'manage_shipping'
            ],
            'add' => [
                'permissions' => 'manage_shipping'
            ],
            'test' => [
                'permissions' => 'view_shipping',
            ],
        ],
        'permissions' => ['GET' => 'view_shipping', 'POST' => 'manage_shipping'],
    ],
    'usergroups' => [
        'modes' => [
            'update_status' => [
                'permissions' => 'manage_usergroups'
            ],
            'delete' => [
                'permissions' => 'manage_usergroups'
            ],
            'update' => [
                'permissions' => 'manage_usergroups',
                'condition'   => [
                    'operator' => 'and',
                    'function' => ['fn_check_permission_manage_usergroups'],
                ],
            ],
        ],
        'permissions' => ['GET' => 'view_usergroups', 'POST' => 'manage_usergroups'],
    ],
    'customization' => [
        'modes' => [
            'update_mode' => [
                'param_permissions' => [
                    'type' => [
                        'live_editor'   => 'manage_translation',
                        'design'        => 'manage_design',
                        'theme_editor'  => 'manage_design',
                        'block_manager' => 'edit_blocks',
                    ],
                ],
            ],
        ],
    ],
    'profiles' => [
        'modes' => [
            'delete' => [
                'permissions' => 'manage_users'
            ],
            'delete_profile' => [
                'permissions' => 'manage_users'
            ],
            'm_delete' => [
                'permissions' => 'manage_users'
            ],
            'add' => [
                'permissions' => 'manage_users'
            ],
            'update' => [
                'permissions' => ['GET' => 'view_users', 'POST' => 'manage_users'],
                'condition'   => [
                    'operator' => 'or',
                    'function' => ['fn_check_permission_manage_own_profile'],
                ],
            ],
            'update_status' => [
                'permissions' => 'manage_users'
            ],
            'm_activate' => [
                'permissions' => 'manage_users',
            ],
            'm_disable' => [
                'permissions' => 'manage_users',
            ],
            'manage' => [
                'permissions' => 'view_users'
            ],
            'export_range' => [
                'permissions' => 'exim_access'
            ],
            'act_as_user' => [
                'permissions' => 'manage_users',
                'condition'   => [
                    'operator' => 'or',
                    'function' => ['fn_check_permission_act_as_user'],
                ]
            ],
            'login_as_vendor' => [
                'permissions' => 'manage_users',
                'condition'   => [
                    'operator' => 'or',
                    'function' => ['fn_check_permission_act_as_user'],
                ]
            ]
        ],
    ],
    'cart' => [
        'modes' => [
            'convert_to_order' => [
                'permissions' => 'create_order',
            ],
            'cart_list' => [
                'permissions' => 'view_orders',
            ],
            'delete' => [
                'permissions' => 'delete_orders',
            ],
            'm_delete' => [
                'permissions' => 'delete_orders',
            ],
        ],
        'permissions' => ['GET' => 'view_users', 'POST' => 'manage_users'],
    ],
    'pages' => [
        'modes' => [
            'delete' => [
                'permissions' => 'manage_pages'
            ],
            'add'    => [
                'permissions' => 'manage_pages'
            ],
        ],
        'permissions' => ['GET' => 'view_pages', 'POST' => 'manage_pages'],
    ],
    'profile_fields' => [
        'permissions' => ['GET' => 'view_users', 'POST' => 'manage_users'],
    ],
    'logs' => [
        'modes' => [
            'clean' => [
                'permissions' => 'delete_logs'
            ]
        ],
        'permissions' => 'view_logs',
    ],
    'categories' => [
        'modes' => [
            'delete' => [
                'permissions' => 'manage_catalog'
            ],
            'm_add' => [
                'permissions' => 'manage_catalog'
            ],
            'm_delete' => [
                'permissions' => 'manage_catalog'
            ],
            'm_activate' => [
                'permissions' => 'manage_catalog'
            ],
            'm_disable' => [
                'permissions' => 'manage_catalog'
            ],
            'm_hide' => [
                'permissions' => 'manage_catalog'
            ],
        ],
        'permissions' => ['GET' => 'view_catalog', 'POST' => 'manage_catalog'],
    ],
    'settings' => [
        'modes' => [
            'change_store_mode' => [
                'permissions' => 'upgrade_store'
            ]
        ],
        'permissions' => ['GET' => 'view_settings', 'POST' => 'update_settings'],
    ],
    'settings_wizard' => [
        'permissions' => 'update_settings',
    ],
    'robots' => [
        'permissions' => 'update_settings',
    ],
    'upgrade_center' => [
        'permissions' => 'upgrade_store',
    ],
    'payments' => [
        'modes' => [
            'delete'            => [
                'permissions' => 'manage_payments'
            ],
            'm_delete'          => [
                'permissions' => 'manage_payments'
            ],
            'm_update_statuses' => [
                'permissions' => 'manage_payments'
            ],
        ],
        'permissions' => ['GET' => 'view_payments', 'POST' => 'manage_payments'],
    ],
    'currencies' => [
        'modes' => [
            'delete' => [
                'permissions' => 'manage_currencies'
            ]
        ],
        'permissions' => ['GET' => 'view_currencies', 'POST' => 'manage_currencies'],
    ],
    'destinations' => [
        'modes' => [
            'delete' => [
                'permissions' => 'manage_locations'
            ],
            'selector' => [
                'permissions' => 'view_shipping'
            ],
        ],
        'permissions' => ['GET' => 'view_locations', 'POST' => 'manage_locations'],
    ],
    'localizations' => [
        'modes' => [
            'delete' => [
                'permissions' => 'manage_locations'
            ]
        ],
        'permissions' => ['GET' => 'view_locations', 'POST' => 'manage_locations'],
    ],
    'exim' => [
        'modes' => [
            'export' => [
                'param_permissions' => [
                    'section' => [
                        'features'     => 'view_catalog',
                        'orders'       => 'view_orders',
                        'products'     => 'view_catalog',
                        'translations' => 'view_languages',
                        'users'        => 'view_users',
                        'states'       => 'view_locations',
                    ],
                ]
            ],
            'import' => [
                'param_permissions' => [
                    'section' => [
                        'features'     => 'manage_catalog',
                        'orders'       => 'edit_order',
                        'products'     => 'manage_catalog',
                        'translations' => 'manage_languages',
                        'users'        => 'manage_users',
                        'states'       => 'manage_locations',
                    ],
                ]
            ]
        ],

        'permissions' => 'exim_access',
    ],
    'languages' => [
        'modes' => [
            'delete_variable'    => [
                'permissions' => 'manage_languages'
            ],
            'delete_language'    => [
                'permissions' => 'manage_languages'
            ],
            'm_delete_variables' => [
                'permissions' => 'manage_languages'
            ],
        ],
        'permissions' => ['GET' => 'view_languages', 'POST' => 'manage_languages'],
    ],
    'product_features' => [
        'modes' => [
            'delete'            => [
                'permissions' => 'manage_catalog'
            ],
            'm_delete'          => [
                'permissions' => 'manage_catalog'
            ],
            'm_update_statuses' => [
                'permissions' => 'manage_catalog'
            ],
            'm_set_display'     => [
                'permissions' => 'manage_catalog'
            ],
        ],
        'permissions' => ['GET' => 'view_catalog', 'POST' => 'manage_catalog'],
    ],
    'static_data' => [
        'modes' => [
            'delete'            => [
                'permissions' => 'manage_static_data'
            ],
            'm_delete'          => [
                'permissions' => 'manage_static_data'
            ],
            'm_update_statuses' => [
                'permissions' => 'manage_static_data'
            ],
        ],
        'permissions' => ['GET' => 'view_static_data', 'POST' => 'manage_static_data'],
    ],
    'statuses' => [
        'permissions' => 'manage_order_statuses',
    ],
    'sales_reports' => [
        'modes' => [
            'view' => [
                'permissions' => 'view_reports'
            ],
            'set_report_view' => [
                'permissions' => 'view_reports'
            ],
        ],
        'permissions' => 'manage_reports',
    ],
    'addons' => [
        'permissions' => 'update_settings',
    ],
    'states' => [
        'modes' => [
            'delete' => [
                'permissions' => 'manage_locations'
            ]
        ],
        'permissions' => ['GET' => 'view_locations', 'POST' => 'manage_locations'],
    ],
    'countries' => [
        'permissions' => ['GET' => 'view_locations', 'POST' => 'manage_locations'],
    ],
    'order_management' => [
        'modes' => [
            'edit' => [
                'param_permissions' => [
                    'copy' => [
                        '1' => 'create_order'
                    ],
                ],
                'permissions' => 'edit_order'
            ],
            'new' => [
                'permissions' => 'create_order'
            ],
            'add' => [
                'permissions' => 'create_order'
            ],
            'options' => [
                'permissions' => 'update_order_details'
            ]
        ],
        'permissions' => 'edit_order',
        'condition'   => [
            'operator' => 'or',
            'function' => ['fn_check_current_user_access', 'create_order'],
        ]
    ],
    'file_editor' => [
        'permissions' => 'edit_files',
    ],
    'block_manager' => [
        'permissions' => 'edit_blocks',
    ],
    'menus' => [
        'modes' => [
            'delete' => [
                'permissions' => 'edit_blocks'
            ],
        ],
        'permissions' => 'edit_blocks',
    ],
    'promotions' => [
        'permissions' => 'manage_promotions',
    ],
    'shipments' => [
        'modes' => [
            'manage' => [
                'permissions' => 'view_orders',
            ],
            'delete' => [
                'permissions' => 'update_order_details',
            ],
            'picker' => [
                'permissions' => 'update_order_details',
            ],
            'add' => [
                'permissions' => 'update_order_details',
            ]
        ],
        'permissions' => 'view_orders',
    ],
    'tools' => [
        'modes' => [
            'update_position' => [
                'param_permissions' => [
                    'table' => [
                        'product_tabs'           => 'manage_catalog',
                        'template_table_columns' => 'manage_document_templates',
                        'statuses'               => 'manage_order_statuses',
                        'payments'               => 'manage_payments',
                        'currencies'             => 'manage_currencies',
                    ]
                ]
            ],
            'view_changes' => [
                'permissions' => 'view_file_changes',
            ],
            'update_status' => [
                'param_permissions' => [
                    'table' => [
                        'categories'             => 'manage_catalog',
                        'states'                 => 'manage_locations',
                        'usergroups'             => 'manage_usergroups',
                        'currencies'             => 'manage_currencies',
                        'blocks'                 => 'edit_blocks',
                        'pages'                  => 'manage_pages',
                        'taxes'                  => 'manage_taxes',
                        'promotions'             => 'manage_promotions',
                        'static_data'            => 'manage_static_data',
                        'statistics_reports'     => 'manage_reports',
                        'countries'              => 'manage_locations',
                        'shippings'              => 'manage_shipping',
                        'languages'              => 'manage_languages',
                        'sitemap_sections'       => 'manage_sitemap',
                        'localizations'          => 'manage_locations',
                        'products'               => 'manage_catalog',
                        'destinations'           => 'manage_locations',
                        'product_options'        => 'manage_catalog',
                        'product_features'       => 'manage_catalog',
                        'payments'               => 'manage_payments',
                        'product_filters'        => 'manage_catalog',
                        'product_files'          => 'manage_catalog',
                        'orders'                 => 'change_order_status',
                        'template_emails'        => 'manage_email_templates',
                        'template_table_columns' => 'manage_document_templates'
                    ]
                ]
            ],
        ]
    ],
    'storage' => [
        'permissions' => 'manage_storage',
    ],
    'themes' => [
        'permissions' => 'manage_themes',
    ],
    'email_templates' => [
        'permissions' => 'manage_email_templates',
    ],
    'internal_templates' => [
        'permissions' => 'manage_internal_templates',
    ],
    'documents' => [
        'permissions' => 'manage_document_templates',
    ],
    'templates' => [
        'permissions' => 'edit_files'
    ],
    'storefronts' => [
        'modes' => [
            'manage' => [
                'permissions' => 'view_stores'
            ],
            'add' => [
                'permissions' => 'manage_stores'
            ],
            'update' => [
                'permissions' => [
                    'GET' => 'view_stores', 'POST' => 'manage_stores'
                ],
            ],
            'update_status' => [
                'permissions' => 'manage_stores'
            ],
            'm_delete' => [
                'permissions' => 'manage_stores'
            ],
            'm_open' => [
                'permissions' => 'manage_stores'
            ],
            'm_close' => [
                'permissions' => 'manage_stores'
            ],
        ],
    ],
    'notification_settings' => [
        'permissions' => 'manage_notification_settings',
    ],
    'sync_data' => [
        'modes' => [
            'manage' => [
                'permissions' => true,
                'condition'   => [
                    'operator' => 'and',
                    'function' => ['fn_check_permission_sync_data'],
                ],
            ]
        ]
    ]
];

$schema['root']['sync_data'] = $schema['sync_data'];

if (Registry::get('config.tweaks.disable_localizations') == true || fn_allowed_for('ULTIMATE:FREE')) {
    $schema['localizations'] = $schema['root']['localizations'] = array(
        'permissions' => 'none',
    );
}

return $schema;
