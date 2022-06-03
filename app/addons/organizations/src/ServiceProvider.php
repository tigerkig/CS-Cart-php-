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


namespace Tygh\Addons\Organizations;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Addons\Organizations\Organization\OrganizationRepository;
use Tygh\Addons\Organizations\Organization\OrganizationUserRepository;
use Tygh\Addons\Organizations\Tools\QueryFactory;
use Tygh\Registry;
use Tygh\Tygh;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $app['addons.organization.organization.organization_repository'] = function (Container $app) {
            return new OrganizationRepository(self::getQueryFactory(), self::getOrganizationUserRepository());
        };

        $app['addons.organization.organization.organization_user_repository'] = function (Container $app) {
            return new OrganizationUserRepository(self::getQueryFactory());
        };

        $app['addons.organization.tools.query_factory'] = function (Container $app) {
            return new QueryFactory($app['db']);
        };
    }

    /**
     * @return \Tygh\Addons\Organizations\Organization\OrganizationRepository
     */
    public static function getOrganizationRepository()
    {
        return Tygh::$app['addons.organization.organization.organization_repository'];
    }

    /**
     * @return \Tygh\Addons\Organizations\Organization\OrganizationUserRepository
     */
    public static function getOrganizationUserRepository()
    {
        return Tygh::$app['addons.organization.organization.organization_user_repository'];
    }

    /**
     * @return \Tygh\Addons\Organizations\Tools\QueryFactory
     */
    public static function getQueryFactory()
    {
        return Tygh::$app['addons.organization.tools.query_factory'];
    }

    /**
     * Checks if storefront is B2B
     *
     * @param int|null $storefront_id
     *
     * @return bool
     */
    public static function isStorefrontB2B($storefront_id = null)
    {
        if ($storefront_id === null) {
            /** @var \Tygh\Storefront\Storefront $storefront */
            $storefront = Tygh::$app['storefront'];
            $storefront_id = $storefront->storefront_id;
        }

        $b2b_storefront_ids = array_keys((array) Registry::ifGet('addons.organizations.b2b_storefront_ids', []));

        return in_array((int) $storefront_id, $b2b_storefront_ids, true);
    }

    /**
     * Checks if user is owner of organization
     *
     * @param int $user_id
     * @param int $organization_id
     *
     * @return bool
     */
    public static function isOrganizationOwner($user_id, $organization_id)
    {
        if (empty($organization_id) || empty($user_id)) {
            return false;
        }

        $organization_user_repository = ServiceProvider::getOrganizationUserRepository();

        $organization_user = $organization_user_repository->findByUserId($user_id);

        return $organization_user
            && $organization_user->isOwner()
            && $organization_user->getOrganizationId() === (int) $organization_id;
    }

    public static function actualizeCart($cart, $user_id, $organization_id)
    {
        $timestamps = db_get_fields(
            'SELECT timestamp FROM ?:user_session_products WHERE organization_id = ?i AND type = ?s ORDER BY timestamp ASC',
            $organization_id, 'C'
        );
        $hash = md5(implode(',', $timestamps));

        if (isset($cart['organization_cart_hash']) && $cart['organization_cart_hash'] === $hash) {
            return $cart;
        }

        unset($cart['products']);
        fn_clear_cart($cart);
        fn_extract_cart_content($cart, $user_id);
        $cart['organization_cart_hash'] = $hash;

        return $cart;
    }
}