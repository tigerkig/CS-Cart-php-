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

use Tygh\Enum\ProductOptionTypes;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_wishlist_fill_user_fields(&$exclude)
{
    $exclude[] = 'wishlist';
}

//
//
//
function fn_wishlist_get_gift_certificate_info(&$_certificate, &$certificate, &$type)
{
    if ($type == 'W' && is_numeric($certificate)) {
        $_certificate = fn_array_merge(Tygh::$app['session']['wishlist']['gift_certificates'][$certificate], array('gift_cert_wishlist_id' => $certificate));
    }
}

function fn_wishlist_user_init(&$auth, &$user_info, &$first_init)
{
    $user_id = $auth['user_id'];
    $user_type = 'R';
    if (empty($user_id) && fn_get_session_data('cu_id')) {
        $user_id = fn_get_session_data('cu_id');
        $user_type = 'U';
    }

    fn_extract_cart_content(Tygh::$app['session']['wishlist'], $user_id, 'W', $user_type);
}

function fn_wishlist_init_user_session_data(&$sess_data, &$user_id)
{
    $is_acting_on_behalf_of_user = !empty($sess_data['auth']['act_as_user'])
        && !empty($sess_data['auth']['area'])
        && $sess_data['auth']['area'] == 'C';
    if (AREA == 'C' || $is_acting_on_behalf_of_user) {
        if (empty(Tygh::$app['session']['wishlist'])) {
            Tygh::$app['session']['wishlist'] = array(
                'products' => array()
            );
        }
        fn_extract_cart_content($sess_data['wishlist'], $user_id, 'W');
        fn_save_cart_content(Tygh::$app['session']['wishlist'], $user_id, 'W');
    }

    return true;
}

function fn_wishlist_sucess_user_login($udata, $auth)
{
    if (AREA == 'C') {
        if ($cu_id = fn_get_session_data('cu_id')) {
            fn_clear_cart($cart);
            fn_save_cart_content($cart, $cu_id, 'W', 'U');
        }
    }
}

function fn_wishlist_pre_add_to_cart(&$product_data, &$cart, &$auth, &$update)
{
    $wishlist = & Tygh::$app['session']['wishlist'];

    if (!empty($wishlist['products'])) {
    foreach ($wishlist['products'] as $key => $product) {
        if (!empty($product['extra']['custom_files'])) {
        foreach ($product['extra']['custom_files'] as $option_id => $files) {
            if (!empty($files)) {
            foreach ($files as $file) {
                $product_data['custom_files']['uploaded'][] = array(
                    'product_id' => $key,
                    'option_id' => $option_id,
                    'path' => $file['path'],
                    'name' => $file['name'],
                );
            }
            }
        }
        }
    }
    }
}

//
// Add possibility to retrieve the wishlist products form user_sessions_products
//
// @param array $type_restrictions allowed types
// @return no return value
//
function fn_wishlist_get_carts(&$type_restrictions)
{
    if (is_array($type_restrictions)) {
        $type_restrictions[] = 'W';
    }
}

function fn_wishlist_get_additional_information(&$product, &$products_data)
{
    $_product = reset($products_data['product_data']);
    if (isset($product['product_id']) && isset($_product['product_id']) && $product['product_id'] == $_product['product_id'] && isset($_product['object_id'])) {
        $product['product_id'] = $product['object_id'] = $_product['object_id'];
    }
}

/**
 * Gets wishlist items count
 *
 * @return int wishlist items count
 */
function fn_wishlist_get_count()
{
    $wishlist = array();
    $result = 0;

    if (!empty(Tygh::$app['session']['wishlist'])) {
        $wishlist = & Tygh::$app['session']['wishlist'];
        $result = !empty($wishlist['products']) ? count($wishlist['products']) : 0;
    }

    /**
     * Changes wishlist items count
     *
     * @param array $wishlist wishlist data
     * @param int   $result   wishlist items count
     */
    fn_set_hook('wishlist_get_count_post', $wishlist, $result);

    return empty($result) ? -1 : $result;
}

/**
 * The "save_cart_content_pre" hook handler.
 *
 * Actions performed:
 *  - Gets user data info from session and adds them into records with wishlist type
 *
 * @see fn_save_cart_content
 */
function fn_wishlist_save_cart_content_pre(&$cart, $user_id, $type, $user_type)
{
    if (!empty($user_id)) {
        return;
    }

    if ($type == 'W') {
        if (empty($cart['user_data']) && !empty(Tygh::$app['session']['cart']['user_data'])) {
            $cart['user_data'] = Tygh::$app['session']['cart']['user_data'];
        }
    } elseif (!empty(Tygh::$app['session']['wishlist']) && !empty($cart['user_data'])) {
        Tygh::$app['session']['wishlist']['user_data'] = $cart['user_data'];
        fn_save_cart_content(Tygh::$app['session']['wishlist'], $user_id, 'W', $user_type);
    }
}

/**
 * Gathers information for products in wish list.
 *
 * @param array  $products       Products in wishlist
 * @param array  $extra_products Products don't directly added into wish list, but provided externally
 * @param array  $auth           Authentication data
 * @param string $lang_code      Two-letter language code for descriptions
 *
 * @return array[] Products and extra products with loaded data
 *
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingTraversableTypeHintSpecification
 */
function fn_wishlist_gather_product_data(array $products, array $extra_products, array $auth, $lang_code = CART_LANGUAGE)
{
    foreach ($products as $k => $v) {
        $_options = [];
        $extra = $v['extra'];
        if (!empty($v['product_options'])) {
            $_options = $v['product_options'];
        }
        $products[$k] = fn_get_product_data(
            $v['product_id'],
            $auth,
            $lang_code,
            '',
            true,
            true,
            true,
            false,
            false,
            true,
            false,
            true
        );

        if (empty($products[$k])) {
            unset($products[$k]);
            continue;
        }

        $products[$k]['extra'] = empty($products[$k]['extra'])
            ? []
            : $products[$k]['extra'];
        $products[$k]['extra'] = array_merge($products[$k]['extra'], $extra);

        if (isset($products[$k]['extra']['product_options']) || $_options) {
            $products[$k]['selected_options'] = empty($products[$k]['extra']['product_options'])
                ? $_options
                : $products[$k]['extra']['product_options'];
        }

        if (!empty($products[$k]['selected_options'])) {
            $options = fn_get_selected_product_options($v['product_id'], $v['product_options'], $lang_code);
            foreach ($products[$k]['selected_options'] as $option_id => $variant_id) {
                foreach ($options as $option) {
                    if (
                        (int) $option['option_id'] === (int) $option_id
                        && !ProductOptionTypes::isSelectable($option['option_type'])
                        && empty($variant_id)
                    ) {
                        $products[$k]['changed_option'] = $option_id;
                        break 2;
                    }
                }
            }

            if (isset($products[$k]['selected_options'])) {
                $products[$k]['combination'] = fn_get_options_combination($products[$k]['selected_options']);
            }
        }
        $products[$k]['display_subtotal'] = $products[$k]['price'] * $v['amount'];
        $products[$k]['display_amount'] = $v['amount'];
        $products[$k]['cart_id'] = $k;

        if (!empty($products[$k]['extra']['parent'])) {
            $extra_products[$k] = $products[$k];
            unset($products[$k]);
            continue;
        }
    }

    fn_gather_additional_products_data(
        $products,
        ['get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => true],
        $lang_code
    );

    return [$products, $extra_products];
}

/**
 * Adds product to wishlist.
 *
 * @param array $product_data Product to add data
 * @param array $wishlist     Wishlist data storage
 * @param array $auth         User session data
 *
 * @return array<int>|false Wishlist IDs for the added products, false otherwise
 *
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
 */
function fn_add_product_to_wishlist(array $product_data, array &$wishlist, array &$auth)
{
    // Check if products have custom images
    list($product_data, $wishlist) = fn_add_product_options_files($product_data, $wishlist, $auth, false, 'wishlist');

    fn_set_hook('pre_add_to_wishlist', $product_data, $wishlist, $auth);

    if (empty($product_data) || !is_array($product_data)) {
        return false;
    }

    $wishlist_ids = [];
    foreach ($product_data as $product_id => $data) {
        if (empty($data['amount'])) {
            $data['amount'] = 1;
        }
        if (!empty($data['product_id'])) {
            $product_id = $data['product_id'];
        }

        if (empty($data['extra'])) {
            $data['extra'] = [];
        }

        // Add one product
        if (!isset($data['product_options'])) {
            $data['product_options'] = fn_get_default_product_options($product_id);
        }

        // Generate wishlist id
        $data['extra']['product_options'] = $data['product_options'];
        $_id = fn_generate_cart_id($product_id, $data['extra']);

        $_data = db_get_row('SELECT is_edp, options_type, tracking FROM ?:products WHERE product_id = ?i', $product_id);
        $_data = fn_normalize_product_overridable_fields($_data);

        $data['is_edp'] = $_data['is_edp'];
        $data['options_type'] = $_data['options_type'];
        $data['tracking'] = $_data['tracking'];

        $wishlist_ids[] = $_id;
        $wishlist['products'][$_id]['product_id'] = $product_id;
        $wishlist['products'][$_id]['product_options'] = $data['product_options'];
        $wishlist['products'][$_id]['extra'] = $data['extra'];
        $wishlist['products'][$_id]['amount'] = $data['amount'];
    }

    return $wishlist_ids;
}

/**
 * Deletes product from the wishlist.
 *
 * @param array $wishlist    Wishlist data storage
 * @param int   $wishlist_id ID of the product to delete from wishlist
 *
 * @return bool Always true
 *
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
 */
function fn_delete_wishlist_product(array &$wishlist, $wishlist_id)
{
    fn_set_hook('delete_wishlist_product', $wishlist, $wishlist_id);

    if (!empty($wishlist_id)) {
        unset($wishlist['products'][$wishlist_id]);
    }

    return true;
}
