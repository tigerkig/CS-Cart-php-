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

use Tygh\Addons\ProductReviews\ProductReview\ImagesService;
use Tygh\Addons\ProductReviews\ServiceProvider as ProductReviewsProvider;
use Tygh\Addons\ProductVariations\ServiceProvider;
use Tygh\Addons\ProductVariations\ServiceProvider as ProductVariationsServiceProvider;
use Tygh\Addons\MasterProducts\ServiceProvider as MasterProductsServiceProvider;
use Tygh\Common\OperationResult;
use Tygh\Enum\Addons\ProductReviews\ProductReviewsMessageTypes;
use Tygh\Enum\Addons\ProductReviews\ProductReviewsProductFilterProductFieldTypes;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\ReceiverSearchMethods;
use Tygh\Enum\UserTypes;
use Tygh\Enum\YesNo;
use Tygh\Notifications\Receivers\SearchCondition;
use Tygh\Providers\StorefrontProvider;
use Tygh\Registry;
use Tygh\Storefront\Storefront;

defined('BOOTSTRAP') or die('Access denied');

/**
 * The "delete_product_post" hook handler.
 *
 * Actions performed:
 *     - Removes reviews after deleting a product.
 *
 * @param int  $product_id      Product identifier
 * @param bool $product_deleted Whether the product has been removed
 *
 * @see fn_delete_product()
 *
 * @return void
 */
function fn_product_reviews_delete_product_post($product_id, $product_deleted)
{
    if (!$product_deleted && !$product_id) {
        return;
    }

    $service = ProductReviewsProvider::getService();
    $service->deleteProductReviewsByProductId($product_id);
}

/**
 * Gets available rating values with titles
 *
 * @return array<string> Rating values list
 */
function fn_product_reviews_get_reviews_ratings()
{
    return [
        5 => __('product_reviews.excellent'),
        4 => __('product_reviews.very_good'),
        3 => __('product_reviews.average'),
        2 => __('product_reviews.fair'),
        1 => __('product_reviews.poor')
    ];
}

/**
 * The "get_products" hook handler.
 *
 * Actions performed:
 *     - Adds a average_rating field and sorting by average_rating.
 *
 * @param array<string|int>           $params    Product search params
 * @param array<string>               $fields    List of fields for retrieving
 * @param array<string|array<string>> $sortings  Sorting fields
 * @param string                      $condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
 * @param string                      $join      String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
 *
 * @see fn_get_products()
 *
 * @return void
 */
function fn_product_reviews_get_products(array &$params, array &$fields, array &$sortings, &$condition, &$join)
{
    $storefront_id = fn_product_reviews_get_storefront_id_by_setting();

    $fields[] = '?:product_review_prepared_data.average_rating average_rating';
    $fields[] = '?:product_review_prepared_data.reviews_count product_reviews_count';

    $join .= db_quote(
        ' LEFT JOIN ?:product_review_prepared_data'
        . ' ON ?:product_review_prepared_data.product_id = products.product_id'
            . ' AND ?:product_review_prepared_data.storefront_id = ?i',
        $storefront_id
    );

    if (!empty($params['average_rating_n_and_more'])) {
        $condition .= db_quote(' AND average_rating >= ?i', $params['average_rating_n_and_more']);
    }

    if (empty($params['rating'])) {
        return;
    }

    $params['sort_by'] = 'rating';
    $params['sort_order'] = 'desc';
    $sortings['rating'] = ['average_rating', 'product_reviews_count'];
}

/**
 * The "get_product_data" hook handler.
 *
 * Actions performed:
 *     - Adds average rating to the field list by join.
 *
 * @param int    $product_id Product identifier
 * @param string $field_list List of fields for retrieving
 * @param string $join       String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
 *
 * @see fn_get_product_data()
 *
 * @return void
 */
function fn_product_reviews_get_product_data($product_id, &$field_list, &$join)
{
    $storefront_id = fn_product_reviews_get_storefront_id_by_setting();

    $field_list .= ', ?:product_review_prepared_data.average_rating average_rating';
    $field_list .= ', ?:product_review_prepared_data.reviews_count product_reviews_count';
    $join .= db_quote(
        ' LEFT JOIN ?:product_review_prepared_data'
        . ' ON ?:product_review_prepared_data.product_id = ?:products.product_id'
            . ' AND ?:product_review_prepared_data.storefront_id = ?i',
        $storefront_id
    );
}

/**
 * The "tools_change_status" hook handler.
 *
 * Actions performed:
 *     - Recalculates the average product rating.
 *
 * @param array{table: string, id: int} $params Parameters that control status changes (table, id_name, id)
 * @param bool                          $result The result of the DB request for status change
 *
 * @see fn_tools_update_status()
 *
 * @return void
 */
function fn_product_reviews_tools_change_status(array $params, $result)
{
    if (
        !$result
        || $params['table'] !== 'product_reviews'
        || empty($params['id'])
    ) {
        return;
    }

    $service = ProductReviewsProvider::getService();
    $product_id = $service->getProductIdByProductReviewId($params['id']);
    $service->actualizeProductPreparedData($product_id);
}

/**
 * Updates notification receiver search conditions
 *
 * @return void
 */
function fn_product_reviews_addon_install()
{
    list($root_admins,) = fn_get_users(
        [
            'is_root'   => YesNo::YES,
            'user_type' => UserTypes::ADMIN,
        ],
        Tygh::$app['session']['auth']
    );

    foreach ($root_admins as $root_admin) {
        if (!$root_admin['company_id']) {
            fn_update_notification_receiver_search_conditions(
                'group',
                'product_reviews.product_reviews',
                UserTypes::ADMIN,
                [
                    new SearchCondition(ReceiverSearchMethods::USER_ID, $root_admin['user_id']),
                ]
            );
            break;
        }
    }

    if (!fn_allowed_for('MULTIVENDOR')) {
        return;
    }

    fn_update_notification_receiver_search_conditions(
        'group',
        'product_reviews.product_reviews',
        UserTypes::VENDOR,
        [
            new SearchCondition(ReceiverSearchMethods::VENDOR_OWNER, ReceiverSearchMethods::VENDOR_OWNER),
        ]
    );
}

/**
 * Updates notification receiver search conditions and delete image pairs
 *
 * @return void
 */
function fn_product_reviews_addon_uninstall()
{
    fn_update_notification_receiver_search_conditions(
        'group',
        'product_reviews.product_reviews',
        UserTypes::ADMIN,
        []
    );

    fn_update_notification_receiver_search_conditions(
        'group',
        'product_reviews.product_reviews',
        UserTypes::VENDOR,
        []
    );

    $review_ids = db_get_fields('SELECT product_review_id FROM ?:product_reviews');
    $images_service = new ImagesService();

    foreach ($review_ids as $review_id) {
        $images_service->deleteImagePairsByProductReviewId($review_id);
    }
}

/**
 * @param int                   $product_review_id Product review identifier
 * @param array<string, string> $auth              Array with authorization data
 *
 * @psalm-suppress MoreSpecificReturnType, InvalidReturnStatement
 *
 * @return array{review_fields?: string, product_data?: array<string|int>, product_review_data?: array{advantages: string, city: string, comment: string, country: string, country_code: string, disadvantages: string, helpfulness: int, ip_address?: string, is_buyer: string, name: string, product_id: int, rating_value: int, reply: string, reply_company: null|string, reply_company_id: int|null, reply_timestamp: int, reply_user_id: int, product_review_id: int, product_review_timestamp: int, status: string, storefront_id: int, user_id: int, vote_down: int, vote_up: int}, user_data?: array<string|int>}
 */
function fn_product_reviews_get_data_for_notification($product_review_id, array $auth = [])
{
    $product_reviews_repository = ProductReviewsProvider::getProductReviewRepository();
    $product_review_data = $product_reviews_repository->findById($product_review_id);

    if (!$product_review_data || empty($product_review_data['product_review_id'])) {
        return [];
    }

    $product_data = fn_get_product_data($product_review_data['product']['product_id'], $auth);
    $user_data = fn_get_user_info($product_review_data['user_data']['user_id']);

    return [
        'product_review_data'  => $product_review_data,
        'product_data'         => $product_data,
        'user_data'            => $user_data,
        'review_fields'        => (string) Registry::ifGet('addons.product_reviews.review_fields', 'advanced'),
    ];
}

/**
 * The "get_product_filter_fields" hook handler.
 *
 * Actions performed:
 *  - Adds a new field to the list of product fields available for filtering.
 *
 * @param array<string|bool|array<callable|string|int>> $filters Filter product fields list
 *
 * @param-out non-empty-array<array-key, bool|non-empty-array<array-key, array{average_rating_n_and_more: int}|callable|int|string>|string> $filters
 *
 * @see fn_get_product_filter_fields()
 *
 * @return void
 */
function fn_product_reviews_get_product_filter_fields(&$filters)
{
    $filters[ProductReviewsProductFilterProductFieldTypes::AVERAGE_RATING] = [
        'db_field'       => 'average_rating',
        'table'          => 'product_review_prepared_data',
        'description'    => 'product_reviews.rating_4_and_up',
        'condition_type' => 'C',
        'map'            => [
            'average_rating_n_and_more' => 4,
        ],
    ];
}

/**
 * @param string $new_status New status code
 *
 * @return void
 */
function fn_settings_actions_addons_product_reviews($new_status)
{
    if (
        $new_status !== ObjectStatuses::ACTIVE
        || !fn_product_reviews_is_can_copy_old_reviews()
    ) {
        return;
    }

    fn_set_notification(
        NotificationSeverity::WARNING,
        __('warning'),
        __('product_reviews.copy_old_reviews_warning', ['[url]' => fn_url('addons.update&addon=product_reviews&selected_section=settings')])
    );
}

/**
 * @return bool
 */
function fn_product_reviews_is_can_copy_old_reviews()
{
    if (!Registry::ifGet('addons.discussion', false)) {
        return false;
    }

    $has_product_discussions = (bool) db_get_field(
        'SELECT 1 FROM ?:discussion discussion'
        . ' INNER JOIN ?:discussion_posts posts'
            . ' ON discussion.thread_id = posts.thread_id'
        . ' WHERE discussion.object_type = ?s'
            . ' AND discussion.type = ?s'
        . ' LIMIT 1',
        'P', // product
        'B'  // comment and rating
    );

    $has_copied_old_reviews = (bool) db_get_field(
        'SELECT 1 FROM ?:product_reviews'
        . ' WHERE product_review_timestamp < ('
            . ' SELECT install_datetime FROM ?:addons WHERE addon = ?s'
        . ' ) LIMIT 1',
        'product_reviews'
    );

    if (!$has_product_discussions || $has_copied_old_reviews) {
        return false;
    }

    return true;
}

/**
 * @return string|void
 */
function fn_product_reviews_copy_old_reviews_notice()
{
    if (!fn_product_reviews_is_can_copy_old_reviews()) {
        return;
    }

    $dispatch = 'product_reviews.copy_from_discussion';
    if (fn_allowed_for('ULTIMATE')) {
        $dispatch .= '&switch_company_id=' . fn_get_default_company_id();
    }

    $url = fn_url($dispatch);

    return __('product_reviews.copy_old_reviews_notice', ['[url]' => $url]);
}

/**
 * Gets product review statuses descriptions.
 *
 * @param array<string|int> $params    Params
 * @param string            $lang_code Two letter language code
 *
 * @return array<string>
 */
function fn_product_reviews_get_statuses_descriptions($params = [], $lang_code = CART_LANGUAGE)
{
    return [
        ObjectStatuses::DISABLED => __('product_reviews.not_approved', $params, $lang_code),
        ObjectStatuses::ACTIVE   => __('product_reviews.approved', $params, $lang_code),
    ];
}

/**
 * The "seo_get_schema_org_markup_items_post" hook handler.
 *
 * Actions performed:
 *     - Adds aggregate rating for the Product markup item.
 *     - Adds reviews for the Product markup item.
 *
 * @param array  $product_data Product data
 * @param bool   $show_price   Whether product price is shown
 * @param string $currency     Currency code to display price with
 * @param array  $markup_items Schema.org markup items for the product
 *
 * @return void
 *
 * @see \fn_seo_get_schema_org_markup_items()
 *
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 */
function fn_product_reviews_seo_get_schema_org_markup_items_post(array $product_data, $show_price, $currency, array &$markup_items)
{
    if (
        empty($markup_items['product'])
        || empty($product_data['product_reviews'])
        || empty($product_data['product_reviews_rating_stats'])
        || empty($product_data['average_rating'])
    ) {
        return;
    }

    $product_item = $markup_items['product'];

    $product_item['aggregateRating'] = [
        '@type'       => 'http://schema.org/AggregateRating',
        'reviewCount' => (int) $product_data['product_reviews_rating_stats']['total'],
        'ratingValue' => (float) $product_data['average_rating'],
    ];

    foreach ($product_data['product_reviews'] as $post) {
        $product_item['review'][] = [
            '@type'        => 'http://schema.org/Review',
            'author'       => [
                '@type' => 'http://schema.org/Person',
                'name'  => $post['user_data']['name'],
            ],
            'reviewRating' => [
                '@type'       => 'http://schema.org/Rating',
                'ratingValue' => (float) $post['rating_value'],
                'bestRating'  => 5,
            ],
        ];
    }

    $markup_items['product'] = $product_item;
}

/**
 * The "seo_dispatch_before_display_before_cache" hook handler.
 *
 * Actions performed:
 * - Adds review tables to cache conditions.
 *
 * @param array<string, mixed> $product    Product data
 * @param string               $key        Cache key
 * @param array<string>        $conditions Cache tables
 *
 * @return void
 *
 * @see \fn_seo_dispatch_before_display
 *
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
 */
function fn_product_reviews_seo_dispatch_before_display_before_cache(array $product, $key, array &$conditions)
{
    $conditions[] = 'product_reviews';
    $conditions[] = 'product_review_prepared_data';
}

/**
 * The "storefront_rest_api_gather_additional_products_data_pre" hook handler.
 *
 * Actions performed:
 * - Loads sellers data for products when requested via API.
 *
 * @param int                                                                     $storefront_id Storefront identifier
 * @param array{properties?: array{addons?: array<string|int|array<string|int>>}} $storefront    Storefront information
 *
 * @param-out array{
 *  properties: array{
 *   addons: array{
 *    product_reviews: array{
 *     is_enabled: bool,
 *     review_fields: string[],
 *     location_fields: string[]
 *    }
 *   }
 *  }
 * } $storefront
 *
 * @return void
 */
function fn_product_reviews_storefront_rest_api_get_storefront($storefront_id, array &$storefront)
{
    $product_reviews = [
        'is_enabled'      => Registry::get('addons.product_reviews.status') === ObjectStatuses::ACTIVE,
        'review_fields'   => ProductReviewsMessageTypes::getTypes(Registry::get('addons.product_reviews.review_fields')),
        'location_fields' => Registry::get('addons.product_reviews.review_ask_for_customer_location') === 'none'
            ? []
            : [Registry::get('addons.product_reviews.review_ask_for_customer_location')],
    ];

    if (
        isset($storefront['properties']['addons'])
        && is_array($storefront['properties']['addons'])
    ) {
        $storefront['properties']['addons']['product_reviews'] = $product_reviews;
    } else {
        $storefront['properties']['addons'] = [
            'product_reviews' => $product_reviews,
        ];
    }
}

/**
 * The "storefront_rest_api_gather_additional_products_data_pre" hook handler.
 *
 * Actions performed:
 * - Loads sellers data for products when requested via API.
 *
 * @param array<int, array<string, string|int|bool>> $products           Products
 * @param array<string, string>                      $params             Request parameters
 * @param array<string, string>                      $data_gather_params Product data gather parameters
 *
 * @return void
 *
 * @param-out array<int, array<string, string|int|bool|array<string, string>>> $products Products
 *
 * @see \fn_storefront_rest_api_gather_additional_products_data()
 */
function fn_product_reviews_storefront_rest_api_gather_additional_products_data_pre(
    array &$products,
    array $params,
    array $data_gather_params
) {
    if (count($products) !== 1) {
        return;
    }

    $_params = [
        'status'         => ObjectStatuses::ACTIVE,
        'page'           => isset($params['page']) ? (int) $params['page'] : 1,
        'items_per_page' => isset($params['items_per_page']) ? (int) $params['items_per_page'] : 0,
        'with_images'    => isset($params['with_images']) ? (bool) $params['with_images'] : false,
        'only_buyers'    => isset($params['only_buyers']) ? $params['only_buyers'] : '',
        'sort_order'     => isset($params['sort_order']) ? $params['sort_order'] : '',
        'sort_by'        => isset($params['sort_by']) ? $params['sort_by'] : 'desc',
    ];

    $_params['storefront_id'] = fn_product_reviews_get_storefront_id_by_setting();

    $product_reviews_repository = ProductReviewsProvider::getProductReviewRepository();
    $service = ProductReviewsProvider::getService();

    foreach ($products as $product_id => &$product) {
        $_params['product_id'] = $product_id;
        list($product_reviews, ) = $product_reviews_repository->find($_params);

        $product['product_reviews'] = $product_reviews;
        $first_review = reset($product_reviews);
        $product['product_reviews_rating_stats'] = $service->getProductRatingStats(
            $first_review ? $first_review['product']['product_id'] : 0,
            $_params['storefront_id']
        );
    }
}

/**
 * The "product_reviews_actualize_product_prepared_data_pre" hook handler.
 *
 * Actions performed:
 * - Replaces the child product_id with the parent.
 *
 * @param int   $product_id     Product identifier
 * @param int[] $storefront_ids Storefront identifiers
 *
 * @return void
 */
function fn_product_variations_product_reviews_actualize_product_prepared_data_pre(
    &$product_id,
    array $storefront_ids
) {
    if (empty($product_id)) {
        return;
    }

    $product_id_map = ServiceProvider::getProductIdMap();

    if (!$product_id_map->isChildProduct($product_id)) {
        return;
    }

    $product_id = $product_id_map->getParentProductId($product_id) ?: $product_id;
}

/**
 * The "product_reviews_actualize_product_prepared_data_post" hook handler.
 *
 * Actions performed:
 * - Synchronizes data in "product_review_prepared_data" table.
 *
 * @param int   $product_id     Product identifier
 * @param int[] $storefront_ids Storefront identifiers
 *
 * @return void
 */
function fn_product_variations_product_reviews_actualize_product_prepared_data_post(
    $product_id,
    array $storefront_ids
) {
    if (empty($product_id)) {
        return;
    }

    $product_id_map = ServiceProvider::getProductIdMap();

    if ($product_id_map->isChildProduct($product_id)) {
        $product_id = $product_id_map->getParentProductId($product_id) ?: $product_id;
    }

    $sync_service = ProductVariationsServiceProvider::getSyncService();
    $sync_service->onTableChanged('product_review_prepared_data', $product_id);
}

/**
 * @return int
 */
function fn_product_reviews_get_storefront_id_by_setting()
{
    return YesNo::toBool(Registry::ifGet('addons.product_reviews.split_reviews_by_storefronts', YesNo::NO))
        ? StorefrontProvider::getStorefront()->storefront_id
        : 0;
}

/**
 * The "storefront_repository_delete_post" hook handler.
 *
 * Actions performed:
 * - Deletes prepared data for the deleted storefront.
 *
 * @param \Tygh\Storefront\Storefront  $storefront       Storefront for remove
 * @param \Tygh\Common\OperationResult $operation_result Result of the storefront removal process
 *
 * @return void
 */
function fn_product_reviews_storefront_repository_delete_post(Storefront $storefront, OperationResult $operation_result)
{
    if ($operation_result->isFailure()) {
        return;
    }

    db_query('DELETE FROM ?:product_review_prepared_data WHERE storefront_id = ?i', $storefront->storefront_id);
}


/**
 * The "product_reviews_actualize_product_prepared_data_pre" hook handler.
 *
 * Actions performed:
 * - Replaces the vendor product_id with the master.
 *
 * @param int   $product_id     Product identifier
 * @param int[] $storefront_ids Storefront identifiers
 *
 * @return void
 */
function fn_master_products_product_reviews_actualize_product_prepared_data_pre(
    &$product_id,
    array $storefront_ids
) {
    $product_repository = MasterProductsServiceProvider::getProductRepository();

    $master_product_id = $product_repository->findMasterProductId($product_id);

    if (empty($master_product_id)) {
        return;
    }

    $product_id = $master_product_id;
}

/**
 * The "product_reviews_actualize_product_prepared_data_post" hook handler.
 *
 * Actions performed:
 * - Synchronizes data in "product_review_prepared_data" table.
 *
 * @param int   $product_id     Product identifier
 * @param int[] $storefront_ids Storefront identifiers
 *
 * @return void
 */
function fn_master_products_product_reviews_actualize_product_prepared_data_post(
    $product_id,
    array $storefront_ids
) {
    if (empty($product_id)) {
        return;
    }

    $sync_service = MasterProductsServiceProvider::getService();
    $sync_service->onTableChanged('product_review_prepared_data', $product_id);
}
