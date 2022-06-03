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


namespace Tygh\Addons\ProductVariations\HookHandlers;


use Tygh\Addons\ProductVariations\Product\Group\Group;
use Tygh\Addons\ProductVariations\Product\Group\GroupProduct;
use Tygh\Addons\ProductVariations\Service;
use Tygh\Addons\ProductVariations\ServiceProvider;
use Tygh\Application;
use Tygh\Common\OperationResult;

/**
 * This class describes hook handlers related to the Product reviews add-on
 *
 * @package Tygh\Addons\ProductVariations\HookHandlers
 */
class ProductReviewsHookHandler
{
    /**
     * @var Application $application
     */
    protected $application;

    /**
     * ProductReviewsHookHandler constructor.
     *
     * @param Application $application Application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * The "product_reviews_find_pre" hook handler.
     *
     * Actions performed:
     *  - Replaces product_id with parent_product_id.
     *
     * @param array{product_id?: int|int[]} $params Search and sort parameters
     *
     * @param-out array{product_id?: int|int[]} $params Search and sort parameters
     *
     * @return void
     *
     * @see \Tygh\Addons\ProductReviews\ProductReview\Repository::find()
     */
    public function onProductReviewsFindPre(&$params)
    {
        if (!isset($params['product_id'])) {
            return;
        }

        $product_id_map = ServiceProvider::getProductIdMap();

        $product_ids = [];

        foreach ((array) $params['product_id'] as $product_id) {
            if (!$product_id_map->isChildProduct($product_id)) {
                continue;
            }

            $parent_product_id = $product_id_map->getParentProductId($product_id);

            if (!$parent_product_id) {
                continue;
            }

            $product_ids[] = $parent_product_id;
        }

        if (empty($product_ids)) {
            return;
        }

        $params['product_id'] = is_array($params['product_id']) ? $product_ids : reset($product_ids);
    }

    /**
     * The "product_reviews_find_pre" hook handler.
     *
     * Actions performed:
     *  - Replaces product_id with parent_product_id.
     *
     * @param array<string, string|int|null> $product_review_data Product review data
     *
     * @return void
     *
     * @see \Tygh\Addons\ProductReviews\ProductReview\Repository::create()
     */
    public function onProductReviewsCreatePre(&$product_review_data)
    {
        if (!isset($product_review_data['product_id'])) {
            return;
        }

        $product_id_map = ServiceProvider::getProductIdMap();

        if (!$product_id_map->isChildProduct((int) $product_review_data['product_id'])) {
            return;
        }

        $product_review_data['product_id'] = $product_id_map->getParentProductId((int) $product_review_data['product_id']);
    }

    /**
     * The "variation_group_mark_product_as_main_post" hook handler.
     *
     * Actions performed:
     *  - Moves reviews from the old parent product to a new one.
     *    This is necessary because child variations can't have their own reviews.
     *
     * @param \Tygh\Addons\ProductVariations\Service                    $service            Instance of the service
     * @param \Tygh\Addons\ProductVariations\Product\Group\Group        $group              Instance of variation group
     * @param \Tygh\Addons\ProductVariations\Product\Group\GroupProduct $from_group_product Instance of the old parent product
     * @param \Tygh\Addons\ProductVariations\Product\Group\GroupProduct $to_group_product   Instance of the new parent product
     *
     * @return void
     *
     * @see \Tygh\Addons\ProductVariations\Service::saveGroup
     */
    public function onVariationGroupMarkProductAsMainPost(Service $service, Group $group, GroupProduct $from_group_product, GroupProduct $to_group_product)
    {
        $new_main_product_id = $to_group_product->getProductId();
        $old_main_product_id = $from_group_product->getProductId();

        $query = ServiceProvider::getQueryFactory()->createQuery(
            'product_reviews',
            ['product_id' => [$old_main_product_id, $new_main_product_id]],
            ['*']
        );

        $on_insert_list = [];

        foreach ($query->select() as $item) {
            if ($item['product_id'] === $old_main_product_id) {
                $item['product_id'] = $new_main_product_id;
            } elseif ($item['product_id'] === $new_main_product_id) {
                $item['product_id'] = $old_main_product_id;
            }

            $on_insert_list[] = $item;
        }

        if (!$on_insert_list) {
            return;
        }

        $query = ServiceProvider::getQueryFactory()->createQuery(
            'product_reviews',
            ['product_id' => [$old_main_product_id, $new_main_product_id]]
        );

        $query->delete();

        $query = ServiceProvider::getQueryFactory()->createQuery('product_reviews');
        $query->multipleInsert($on_insert_list);
    }

    /**
     * The "product_reviews_is_user_eligible_to_write_product_review" hook handler.
     *
     * Actions performed:
     *  - Checks if another variation is bought by chosen user
     *
     * @param int             $user_id           User identifier
     * @param int             $product_id        Product identifier
     * @param string|null     $ip                IP address by fn_ip_to_db
     * @param bool            $need_to_buy_first State of the review_after_purchase setting
     * @param bool            $review_ip_check   State of the review_ip_check setting
     * @param OperationResult $result            Operation result
     *
     * @return void
     *
     * @see \Tygh\Addons\ProductReviews\Service::isUserEligibleToWriteProductReview()
     */
    public function onProductReviewsIsUserEligibleToWriteReview($user_id, $product_id, $ip, $need_to_buy_first, $review_ip_check, OperationResult &$result)
    {
        $product_id_map = ServiceProvider::getProductIdMap();

        if (
            empty($user_id)
            || !$need_to_buy_first
            || $result->isSuccess()
            || !$product_id_map->isVariationProduct($product_id)
            || isset($result->getErrors()['product_reviews.review_already_posted_from_ip'])
        ) {
            return;
        }

        $product_ids = $product_id_map->getVariationSubGroupProductIds($product_id);

        $query = ServiceProvider::getQueryFactory()->createQuery(
            'orders',
            ['user_id' => $user_id],
            ['orders.order_id'],
            'orders'
        );
        $query->addInnerJoin('details', 'order_details', ['order_id' => 'order_id'], ['product_id' => $product_ids]);
        $query->setLimit(1);

        $result->setSuccess((bool) $query->column());
    }
}
