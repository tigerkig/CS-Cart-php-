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

use Tygh\Enum\UserTypes;

return [
    'default_permission' => true,
    'controllers' => [
        'companies' => [
            'modes' => [
                'add' => [
                    'permissions' => false
                ],
            ],
        ],
        'localizations' => [
            'permissions' => false
        ],
        'storage' => [
            'modes' => [
                'index' => [
                    'permissions' => true,
                ],
                'clear_cache' => [
                    'permissions' => true,
                ],
                'clear_thumbnails' => [
                    'permissions' => true,
                ],
            ],
            'permissions' => false
        ],
        'upgrade_center' => [
            'permissions' => false,
            'condition'   => [
                'operator' => 'or',
                'function' => ['fn_check_change_storefront_permission'],
            ],

        ],
        'datakeeper' => [
            'permissions' => false,
            'condition'   => [
                'operator' => 'or',
                'function' => ['fn_check_change_storefront_permission'],
            ],
        ],
        'countries' => [
            'modes' => [
                'manage' => [
                    'permissions' => ['GET' => true, 'POST' => false],
                ],
            ],
            'permissions' => false,
        ],
        'taxes' => [
            'modes' => [
                'manage' => [
                    'permissions' => ['GET' => true, 'POST' => false],
                ],
                'update' => [
                    'permissions' => ['GET' => true, 'POST' => false],
                ],
            ],
            'permissions' => false,
        ],
        'shippings' => [
            'permissions' => true,
        ],
        'destinations' => [
            'modes' => [
                'manage' => [
                    'permissions' => ['GET' => true, 'POST' => false],
                ],
                'update' => [
                    'permissions' => ['GET' => true, 'POST' => false],
                ],
                'selector' => [
                    'permissions' => ['GET' => true, 'POST' => false],
                ]
            ],
            'permissions' => false,
        ],
        'statuses' => [
            'modes' => [
                'manage' => [
                    'permissions' => ['GET' => true, 'POST' => false],
                ],
                'update' => [
                    'permissions' => ['GET' => true, 'POST' => true],
                ],
            ],
            'permissions' => false,
        ],
        'states' => [
            'modes' => [
                'manage' => [
                    'permissions' => true,
                ],
                'update' => false,
            ],

            'permissions' => false,
        ],
        'profile_fields' => [
            'modes' => [
                'manage' => [
                    'permissions' => ['GET' => true, 'POST' => false],
                ],
                'update' => [
                    'permissions' => ['GET' => true, 'POST' => false],
                ],
                'picker' => [
                    'permissions' => true,
                ],
            ],
            'permissions' => false,
        ],
        'profiles' => [
            'modes' => [
                'manage' => [
                    'condition' => [
                        'user_type' => [
                            UserTypes::ADMIN => [
                                'operator' => 'and',
                                'function' => ['fn_check_permission_manage_profiles', UserTypes::ADMIN],
                            ],
                        ]
                    ],
                ],
            ],
        ],
        'usergroups' => [
            'modes' => [
                'manage' => [
                    'permissions' => ['GET' => true, 'POST' => false],
                ],
            ],
            'permissions' => false,
        ],
        'currencies' => [
            'modes' => [
                'delete' => [
                    'permissions' => false,
                ],
                'update' => [
                    'permissions' => ['GET' => true, 'POST' => false],
                ],
            ],
            'permissions' => true,
        ],
        'languages' => [
            'modes' => [
                'delete_language' => [
                    'permissions' => false,
                ],
                'm_delete' => [
                    'permissions' => false,
                ],
                'clone_language' => [
                    'permissions' => false,
                ],
                'install_from_po' => [
                    'permissions' => false,
                ],
                'install' => [
                    'permissions' => false,
                ],
                'update_status' => [
                    'permissions' => false,
                ],
                'update_translation' => [
                    'permissions' => false,
                ],
            ],
            'permissions' => true,
        ],
        'payments' => [
            'permissions' => true,
        ],
        'settings_wizard' => [
            'permissions' => false,
            'condition'   => [
                'operator' => 'or',
                'function' => ['fn_check_change_storefront_permission'],
            ],
        ],
        'robots' => [
            'permissions' => true,
        ],
        'addons' => [
            'modes' => [
                'uninstall' => [
                    'permissions' => false,
                ],
                'install' => [
                    'permissions' => false,
                ],
                'licensing' => [
                    'permissions' => false,
                ]
            ],
        ],
        'tools' => [
            'modes' => [
                'update_status' => [
                    'param_permissions' => [
                        'table' => [
                            'destinations' => false,
                            'countries'    => false,
                            'states'       => false,
                            'taxes'        => false,
                        ]
                    ]
                ],
                'cleanup_history' => [
                    'permissions' => true
                ],
                'view_changes' => [
                    'permissions' => false,
                    'condition'   => [
                        'operator' => 'or',
                        'function' => ['fn_check_change_storefront_permission'],
                    ],
                ],
                'update_position' => [
                    'param_permissions' => [
                        'table' => [
                            'statuses' => false,
                        ]
                    ]
                ]
            ]
        ],
        'settings' => [
            'modes' => [
                'change_store_mode' => [
                    'permissions' => 'upgrade_store',
                    'condition'   => [
                        'operator' => 'and',
                        'function' => ['fn_check_change_store_mode_permission'],
                    ],
                ]
            ],
            'permissions' => ['GET' => 'view_settings', 'POST' => 'update_settings'],
        ],
        'email_templates' => [
            'modes' => [
                'preview' => [
                    'permissions' => 'manage_email_templates',
                ],
                'send' => [
                    'permissions' => 'manage_email_templates'
                ]
            ],
            'permissions' => ['GET' => 'manage_email_templates', 'POST' => false],
        ],
        'internal_templates' => [
            'modes' => [
                'preview' => [
                    'permissions' => 'manage_internal_templates',
                ],
                'send' => [
                    'permissions' => 'manage_internal_templates'
                ]
            ],
            'permissions' => ['GET' => 'manage_internal_templates', 'POST' => false],
        ],
        'documents' => [
            'modes' => [
                'preview' => [
                    'permissions' => 'manage_document_templates',
                ],
            ],
            'permissions' => ['GET' => 'manage_document_templates', 'POST' => false],
        ],
        'snippets' => [
            'permissions' => ['GET' => true, 'POST' => false],
        ],
        'notification_settings' => [
            'modes' => [
                'preview' => [
                    'permissions' => 'manage_notification_settings',
                ],
                'send' => [
                    'permissions' => 'manage_notification_settings'
                ]
            ],
            'permissions' => ['GET' => 'manage_notification_settings', 'POST' => false],
        ],
    ],
    'addons' => [],
    'export' => [],
    'import' => [],

];
