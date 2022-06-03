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

defined('BOOTSTRAP') or die('Access denied');

$schema = [
    'about_store' => [
        'position' => 10,
        'title' => 'sw.about_store',
        'header' => 'sw.text_about_store_header',
        'sections' => [
            [
                'items' => [
                    [
                        'type' => 'setting',
                        'is_required' => true,
                        'name' => 'company_name',
                        'description' => 'sw.company_name',
                        'decoration_class' => 'sw_size_1',
                    ],

                ],
                'decoration_class' => 'control-icon sw_company_name',
            ],
            [
                'items' => [
                    [
                        'type' => 'setting',
                        'is_email' => true,
                        'name' => 'company_site_administrator',
                        'description' => 'sw.site_admin_email',
                        'decoration_class' => 'sw_size_2',
                    ],
                ],
                'decoration_class' => 'control-icon sw_site_admin_email',
            ],
            [
                'items' => [
                    [
                        'type' => 'setting',
                        'name' => 'company_address',
                        'description' => 'sw.address_text',
                        'decoration_class' => 'sw_size_1',
                    ],
                    [
                        'type' => 'setting',
                        'name' => 'company_city',
                        'description' => 'sw.city',
                        'decoration_class' => 'sw_size_1',
                    ],
                    [
                        'type' => 'setting',
                        'name' => 'company_country',
                        'description' => 'sw.country',
                        'decoration_class' => 'sw_size_1',
                    ],
                    [
                        'type' => 'setting',
                        'name' => 'company_state',
                        'description' => 'sw.state',
                        'decoration_class' => 'sw_size_1',
                    ],
                    [
                        'type' => 'setting',
                        'name' => 'company_zipcode',
                        'description' => 'sw.zipcode',
                        'decoration_class' => 'sw_size_3',
                    ],
                ],
                'decoration_class' => 'control-icon sw_address',
            ],
            [
                'items' => [
                    [
                        'type' => 'setting',
                        'name' => 'company_phone',
                        'section' => 'Company',
                        'description' => 'sw.phone',
                        'decoration_class' => 'sw_size_2',
                    ],
                    [
                        'type' => 'setting',
                        'name' => 'company_phone_2',
                        'description' => 'sw.phone_2',
                        'decoration_class' => 'sw_size_2',
                    ],
                    [
                        'type' => 'setting',
                        'name' => 'company_fax',
                        'description' => 'sw.fax',
                        'decoration_class' => 'sw_size_2',
                    ],
                ],
                'decoration_class' => 'control-icon sw_phone',
            ],
            [
                'items' => [
                    [
                        'type' => 'setting',
                        'name' => 'company_website',
                        'description' => 'sw.website',
                        'decoration_class' => 'sw_size_2',
                    ],
                ],
                'decoration_class' => 'control-icon sw_website',
            ],
            [
                'items' => [
                    [
                        'type' => 'setting',
                        'name' => 'tracking_code',
                        'section' => 'google_analytics',
                        'description' => 'sw.google_analytics.tracking_code',
                        'decoration_class' => 'sw_size_2',
                    ],
                ],
                'decoration_class' => 'control-icon sw_statistic',
            ],
            [
                'items' => [
                    [
                        'type' => 'setting',
                        'name' => 'company_start_year',
                        'description' => 'sw.company_start_year',
                        'decoration_class' => 'sw_size_2',
                    ],
                ],
                'decoration_class' => 'control-icon sw_company_start_year',
            ],
        ]
    ],
    'design' => [
        'position' => 40,
        'title'    => 'sw.design',
        'header'   => 'sw.text_design_header',
        'extra'    => 'views/setup_wizard/components/tabs/design.tpl',
    ],
    /*'shippings' => [
        'position' => 50,
        'title'    => 'sw.shippings',
        'header'   => 'sw.text_shippings_header',
        'extra'    => 'views/setup_wizard/components/tabs/shippings.tpl',
    ],*/
    'settings' => [
        'position' => 60,
        'title' => 'sw.settings',
        'header' => 'sw.text_settings_header',
        'sections' => [
            [
                'items' => [
                    [
                        'type' => 'setting',
                        'name' => 'timezone',
                        'description' => 'sw.timezone',
                        'decoration_class' => 'sw_size_1',
                    ],
                    [
                        'type' => 'setting',
                        'name' => 'order_start_id',
                        'description' => 'sw.order_start_id',
                        'decoration_class' => 'sw_size_3',
                    ],
                    [
                        'type' => 'setting',
                        'name' => 'min_order_amount',
                        'description' => 'sw.min_order_amount',
                        'decoration_class' => 'sw_size_3',
                    ],
                ],
                'decoration_class' => 'control-icon sw_settings_icon',
            ],
            [
                'items' => [
                    [
                        'type' => 'setting',
                        'name' => 'show_out_of_stock_products',
                        'description' => 'sw.show_out_of_stock_products',
                    ],
                    [
                        'type' => 'setting',
                        'name' => 'enable_quick_view',
                        'description' => 'sw.enable_quick_view',
                    ],
                ],
                'decoration_class' => 'control-icon sw_kiosk_icon',
            ],
            [
                'items' => [
                    [
                        'type' => 'setting',
                        'name' => 'inventory_tracking',
                        'description' => 'sw.enable_inventory_tracking',
                    ],
                    [
                        'type' => 'setting',
                        'name' => 'allow_negative_amount',
                        'description' => 'sw.allow_negative_amount',
                    ],
                ],
                'decoration_class' => 'control-icon sw_plus_one_icon',
            ],
        ],
        'extra' => 'views/setup_wizard/components/tabs/settings.tpl',
    ],
];

$schema['business_model'] = [
    'position' => 20,
    'title'    => 'sw.business_model',
    'header'   => 'sw.text_business_model_header',
    'extra'    => 'views/setup_wizard/components/tabs/business_model.tpl',
    'sections' => [
        [
            'items' => [
                [
                    'type'             => 'setting',
                    'name'             => 'collect_taxes_from_vendors',
                    'description'      => 'sw.collect_taxes_from_vendors',
                    'decoration_class' => 'sw_size_2',
                ]
            ],
        ]
    ]
];

$schema['vendors'] = [
    'position'              => 30,
    'title'                 => 'sw.vendors',
    'header'                => 'sw.text_vendors_header',
    'extra'                 => 'views/setup_wizard/components/tabs/vendors.tpl',
    'show_section_in_extra' => true,
    'show_submit_button'    => 'N',
    'sections'              => [
        [
            'header'        => 'sw.moderation',
            'items'         => [
                [
                    'type'             => 'setting',
                    'name'             => 'vendor_profile_updates_approval',
                    'section'          => 'vendor_data_premoderation',
                    'description'      => 'sw.vendor_profile_updates_approval',
                    'decoration_class' => 'sw_size_2',
                ],
                [
                    'type'             => 'setting',
                    'name'             => 'products_prior_approval',
                    'section'          => 'vendor_data_premoderation',
                    'description'      => 'sw.products_prior_approval',
                    'decoration_class' => 'sw_size_2',
                ],
                [
                    'type'             => 'setting',
                    'name'             => 'products_updates_approval',
                    'section'          => 'vendor_data_premoderation',
                    'description'      => 'sw.products_updates_approval',
                    'decoration_class' => 'sw_size_2',
                ]
            ],
        ],
        [
            'header' => 'sw.accounting',
            'items'  => [
                [
                    'type'             => 'setting',
                    'name'             => 'lowers_allowed_balance',
                    'section'          => 'vendor_debt_payout',
                    'description'      => 'sw.lowers_allowed_balance',
                    'decoration_class' => 'sw_size_2',
                ],
                [
                    'type'             => 'setting',
                    'name'             => 'grace_period_to_refill_balance',
                    'section'          => 'vendor_debt_payout',
                    'description'      => 'sw.grace_period_to_refill_balance',
                    'decoration_class' => 'sw_size_2',
                ],
            ],
        ]
    ]
];

return $schema;
