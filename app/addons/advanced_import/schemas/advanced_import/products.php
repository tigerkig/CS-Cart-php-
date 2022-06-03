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

use Tygh\Addons\AdvancedImport\Readers\Xml;
use Tygh\Enum\Addons\AdvancedImport\ImportStrategies;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

include_once Registry::get('config.dir.addons') . 'advanced_import/schemas/advanced_import/products.functions.php';

$schema = array(
    'import_process_data' => array(
        'filter_out_null_values' => array(
            'function'    => 'fn_advanced_import_filter_out_null_values',
            'args'        => array('$object'),
            'import_only' => true,
        ),
        'test_import' => array(
            'function'    => 'fn_advanced_import_test_import',
            'args'        => array(
                '$pattern',
                '$options',
                '$processed_data',
                '$skip_record',
                '$stop_import',
            ),
            'import_only' => true,
        ),
    ),
    'export_fields' => array(
        'Advanced Import: Features' => array(
            'process_put'   => array('fn_advanced_import_set_product_features', '#key', '#this', '@features_delimiter'),
            'linked'        => false,
            'multilang'     => true,
            'import_only'   => true,
            'hidden'        => true,
            'return_result' => true,
            'return_field'  => 'product_features',
        ),
        'Images' => [
            'process_put' => [
                'fn_advanced_import_set_product_images',
                '#key',
                '#this',
                '@images_path',
                '@images_delimiter',
                '@remove_images',
                '@preset'
            ],
            'linked'      => false,
            'import_only' => true,
            'is_aggregatable' => true,
        ],
        'Detailed image' => array(
            'process_put' => array(
                'fn_advanced_import_import_detailed_image',
                '@images_path',
                '%Thumbnail%',
                '#this',
                '0',
                'M',
                '#key',
                'product',
                '@preset'
            ),
        ),
    ),
    'options'             => array(
        'images_delimiter' => array(
            'title'                     => 'advanced_import.images_delimiter',
            'description'               => 'advanced_import.images_delimiter.description',
            'type'                      => 'input',
            'default_value'             => '///',
            'option_data_post_modifier' => function ($option, $preset) {
                if (isset($preset['file']) && isset($preset['file_extension'])) {
                    $ext = $preset['file_extension'];
                    if (isset($preset['options']['fields_delimiter']) && $ext === 'xml') {
                        $option['selected_value'] = $preset['options']['fields_delimiter'];
                    }
                }

                return $option;
            },
        ),
        'fields_delimiter' => [
            'title'                     => 'advanced_import.fields_delimiter',
            'description'               => 'advanced_import.fields_delimiter.description',
            'type'                      => 'input',
            'default_value'             => ',',
        ],
        'target_node'      => array(
            'title'                     => 'advanced_import.target_node',
            'description'               => 'advanced_import.target_node.description',
            'type'                      => 'input',
            'default_value'             => implode(Xml::PATH_DELIMITER, array('yml_catalog', 'shop', 'offers', 'offer')),
            'position'                  => 5,
            'hidden'                    => true,
            'option_data_post_modifier' => function ($option, $preset) {
                if (isset($preset['file']) && isset($preset['file_extension'])) {
                    $ext = $preset['file_extension'];

                    if ($ext !== 'xml') {
                        $option['control_group_meta'] = 'hidden';
                    }
                }

                return $option;
            },
        ),
        'images_path'      => array(
            'option_data_post_modifier' => function ($option, $preset) {
                $option['display_value'] = '';

                $company_id = isset($preset['company_id']) ? (int) $preset['company_id'] : (int) fn_get_runtime_company_id();

                $companies_image_directories = fn_advanced_import_get_companies_import_images_directory();

                $option['companies_image_directories'] = $companies_image_directories;

                if (isset($companies_image_directories[$company_id])) {
                    $option['input_prefix'] = $companies_image_directories[$company_id]['relative_path'];

                    if (!empty($option['selected_value'])) {
                        $option['display_value'] = str_replace(
                            $companies_image_directories[$company_id]['exim_path'],
                            '',
                            $option['selected_value']
                        );
                    }
                }

                return $option;
            },
        ),
        'remove_images'    => array(
            'title'       => 'advanced_import.delete_additional_images',
            'description' => 'advanced_import.delete_additional_images_tooltip',
            'type'        => 'checkbox',
            'import_only' => true,
            'tab'         => 'settings',
            'section'     => 'additional',
            'position'    => 910,
        ),
        'test_import'      => array(
            'title'         => 'advanced_import.test_import',
            'description'   => 'advanced_import.test_import_tooltip',
            'type'          => 'checkbox',
            'import_only'   => true,
            'tab'           => 'settings',
            'section'       => 'general',
            'position'      => 780,
            'sampling_size' => 5,
        ),
        'import_strategy'  => array(
            'title'                     => 'advanced_import.import_strategy',
            'description'               => 'advanced_import.import_strategy_tooltip',
            'type'                      => 'select',
            'import_only'               => true,
            'tab'                       => 'settings',
            'section'                   => 'general',
            'position'                  => 790,
            'variants'                  => array(
                ImportStrategies::IMPORT_ALL      => 'advanced_import.import_all',
                ImportStrategies::UPDATE_EXISTING => 'update_existing_products_only',
                ImportStrategies::CREATE_NEW      => 'advanced_import.create_new_products_only',
            ),
            'default_value'             => ImportStrategies::IMPORT_ALL,
            'option_data_post_modifier' => 'fn_advanced_import_set_import_strategy_option_value',
        ),
        'files_path' => [
            'title' => 'downloadable_product_files_directory',
            'description' => 'text_files_directory',
            'type' => 'input',
            'default_value' => 'exim/backup/downloads/',
            'option_template' => 'addons/advanced_import/views/import_presets/components/option_fileeditor_open.tpl',
            'notes' => __('advanced_import.text_popup_file_editor_notice_full_link', [
                '[target]'    => 'files_path',
                '[link_text]' => __('file_editor'),
            ]),
            'position' => 700,
        ],
    ),
);

$schema['options']['test_import']['description_params'] = array(
    $schema['options']['test_import']['sampling_size'],
);

return $schema;
