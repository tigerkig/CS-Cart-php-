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

namespace Tygh\Addons\ClientTiers;

use Tygh\Addons\ClientTiers\Enum\Logging;
use Tygh\Addons\InstallerInterface;
use Tygh\Core\ApplicationInterface;
use Tygh\Enum\UsergroupStatuses;
use Tygh\Enum\UsergroupTypes;
use Tygh\Languages\Languages;
use Tygh\Enum\YesNo;
use Tygh\Settings;

class Installer implements InstallerInterface
{
    /**
     * @var \Tygh\Core\ApplicationInterface
     */
    protected $app;

    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;
    }

    /**
     * @inheritDoc
     */
    public static function factory(ApplicationInterface $app)
    {
        return new self($app);
    }

    public function onInstall()
    {
        $this->setDefaultMinSpendValuesForUsergroups();

        $new_usergroups = $this->createUsergroupsExamples();

        $this->createPromotionsExamples($new_usergroups);

        $this->addLoggingSettings();

        fn_set_notification('W', __('warning'), __('client_tiers.installation_message', ['[user_groups]' => fn_url('usergroups.manage'), '[promotions]' => fn_url('promotions.manage')]));
    }

    protected function setDefaultMinSpendValuesForUsergroups()
    {
        $customers_usergroups = fn_get_usergroups(['type' => UsergroupTypes::TYPE_CUSTOMER]);

        foreach ($customers_usergroups as $customers_usergroup) {
            db_query('INSERT INTO ?:usergroups_tiers (min_spend_value, usergroup_id) VALUES (0, ?i)', $customers_usergroup['usergroup_id']);
        }
    }

    protected function createUsergroupsExamples()
    {
        $bronze_usergroup_data = [
            'usergroup' => __('client_tiers.bronze_level_customers'),
            'type' => UsergroupTypes::TYPE_CUSTOMER,
            'status' => UsergroupStatuses::ACTIVE,
        ];
        $bronze_usergroup_id = fn_update_usergroup($bronze_usergroup_data, 0);

        db_query('INSERT INTO ?:usergroups_tiers (min_spend_value, usergroup_id) VALUES (1, ?i)', $bronze_usergroup_id);

        $silver_usergroup_data = [
            'usergroup' => __('client_tiers.silver_level_customers'),
            'type' => UsergroupTypes::TYPE_CUSTOMER,
            'status' => UsergroupStatuses::ACTIVE,
        ];
        $silver_usergroup_id = fn_update_usergroup($silver_usergroup_data, 0);

        db_query('INSERT INTO ?:usergroups_tiers (min_spend_value, usergroup_id) VALUES (1000, ?i)', $silver_usergroup_id);

        $gold_usergroup_data = [
            'usergroup' => __('client_tiers.gold_level_customers'),
            'type' => UsergroupTypes::TYPE_CUSTOMER,
            'status' => UsergroupStatuses::ACTIVE,
        ];
        $gold_usergroup_id = fn_update_usergroup($gold_usergroup_data, 0);

        db_query('INSERT INTO ?:usergroups_tiers (min_spend_value, usergroup_id) VALUES (10000, ?i)', $gold_usergroup_id);

        return [
            'bronze' => $bronze_usergroup_id,
            'silver' => $silver_usergroup_id,
            'gold' => $gold_usergroup_id,
        ];
    }

    protected function createPromotionsExamples(array $new_usergroups)
    {
        $promotion_bronze = [
            'zone' => 'catalog',
            'name' => __('client_tiers.bronze_promotion'),
            'detailed_description' => __('client_tiers.bronze_promotion.detailed_description'),
            'short_description' => __('client_tiers.bronze_promotion.short_description'),
            'from_date' => 0,
            'to_date' => 0,
            'priority' => 0,
            'status' => 'A',
            'stop'  => YesNo::YES,
            'conditions' => [
                'set'   => 'all',
                'set_value' => 1,
                'conditions'    => [
                    '1' => [
                        'operator'  => 'eq',
                        'condition' => 'usergroup',
                        'value'     => $new_usergroups['bronze'],
                    ]
                ],
            ],
            'bonuses'   => [
                '1' => [
                    'bonus' => 'product_discount',
                    'discount_bonus'    => 'by_percentage',
                    'discount_value'    =>  3,
                ],
            ],
        ];

        if (fn_allowed_for('ULTIMATE')) {
            $promotion_bronze['company_id'] = fn_get_default_company_id();
        }

        $promotion_bronze_id = fn_update_promotion($promotion_bronze, 0);

        $promotion_silver = [
            'zone' => 'catalog',
            'name' => __('client_tiers.silver_promotion'),
            'detailed_description' => __('client_tiers.silver_promotion.detailed_description'),
            'short_description' => __('client_tiers.silver_promotion.short_description'),
            'from_date' => 0,
            'to_date' => 0,
            'priority' => 0,
            'status' => 'A',
            'stop'  => YesNo::YES,
            'conditions' => [
                'set'   => 'all',
                'set_value' => 1,
                'conditions'    => [
                    '1' => [
                        'operator'  => 'eq',
                        'condition' => 'usergroup',
                        'value'     => $new_usergroups['silver'],
                    ]
                ],
            ],
            'bonuses'   => [
                '1' => [
                    'bonus' => 'product_discount',
                    'discount_bonus'    => 'by_percentage',
                    'discount_value'    =>  7,
                ],
            ],
        ];

        if (fn_allowed_for('ULTIMATE')) {
            $promotion_silver['company_id'] = fn_get_default_company_id();
        }

        $promotion_silver_id = fn_update_promotion($promotion_silver, 0);

        $promotion_gold = [
            'zone' => 'catalog',
            'name' => __('client_tiers.gold_promotion'),
            'detailed_description' => __('client_tiers.gold_promotion.detailed_description'),
            'short_description' => __('client_tiers.gold_promotion.short_description'),
            'from_date' => 0,
            'to_date' => 0,
            'priority' => 0,
            'status' => 'A',
            'stop'  => YesNo::YES,
            'conditions' => [
                'set'   => 'all',
                'set_value' => 1,
                'conditions'    => [
                    '1' => [
                        'operator'  => 'eq',
                        'condition' => 'usergroup',
                        'value'     => $new_usergroups['gold'],
                    ]
                ],
            ],
            'bonuses'   => [
                '1' => [
                    'bonus' => 'product_discount',
                    'discount_bonus'    => 'by_percentage',
                    'discount_value'    =>  10,
                ],
            ],
        ];

        if (fn_allowed_for('ULTIMATE')) {
            $promotion_gold['company_id'] = fn_get_default_company_id();
        }

        $promotion_gold_id = fn_update_promotion($promotion_gold, 0);
    }

    protected function addLoggingSettings()
    {
        $settings_name = 'log_type_' . Logging::LOG_TYPE_CLIENT_TIERS;
        $settings = Settings::instance()->getSettingDataByName($settings_name);

        $logging_section = Settings::instance()->getSectionByName('Logging');
        $lang_codes = array_keys(Languages::getAll());

        if ($settings) {
            return;
        }

        $settings = [
            'name'           => $settings_name,
            'section_id'     => $logging_section['section_id'],
            'section_tab_id' => 0,
            'type'           => 'N',
            'position'       => 10,
            'is_global'      => 'N',
            'edition_type'   => 'ROOT',
        ];

        foreach ($lang_codes as $lang_code) {
            $descriptions[] = [
                'object_type'   => Settings::SETTING_DESCRIPTION,
                'lang_code'     => $lang_code,
                'value'         => __('log_type_client_tiers'),
            ];
        }

        $settings_id = Settings::instance()->update($settings, null, $descriptions, true);
        foreach (Logging::getActions() as $position => $variant) {
            $variant_id = Settings::instance()->updateVariant(
                [
                    'object_id' => $settings_id,
                    'name'      => $variant,
                    'position'  => $position,
                ]
            );

            foreach ($lang_codes as $lang_code) {
                $description = [
                    'object_id' => $variant_id,
                    'object_type' => Settings::VARIANT_DESCRIPTION,
                    'lang_code'   => $lang_code,
                    'value'       => __('log_action_' . $variant),
                ];
                Settings::instance()->updateDescription($description);
            }
        }

        Settings::instance()->updateValue($settings_name, [Logging::ACTION_FAILURE], 'Logging');
    }

    public function onBeforeInstall()
    {

    }

    public function onUninstall()
    {
        $setting = Settings::instance()->getSettingDataByName('log_type_' . Logging::LOG_TYPE_CLIENT_TIERS);
        if (!$setting) {
            return;
        }

        Settings::instance()->removeById($setting['object_id']);
    }

}
