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

namespace Tygh\Addons\CustomerPriceList;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Tygh\Addons\CustomerPriceList\Provider\CatalogProvider;
use Tygh\Addons\CustomerPriceList\Provider\GroupedCatalogProvider;
use Tygh\Application;
use Tygh\Enum\YesNo;
use Tygh\Registry;
use Tygh\Tygh;
use XLSXWriter;

/**
 * Class ServiceProvider
 *
 * @package Tygh\Addons\CustomerPriceList
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var null|string
     */
    protected static $php_binary_path = null;

    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $app['addons.customer_price_list.fields_schema'] = function () {
            return fn_get_schema('customer_price_list', 'fields');
        };

        $app['addons.customer_price_list.generator'] = function (Application $app) {
            /** @var \Composer\Autoload\ClassLoader $class_loader */
            $class_loader = $app['class_loader'];
            $class_loader->addClassMap([
                'XLSXWriter' => Registry::get('config.dir.addons') . 'customer_price_list/lib/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php'
            ]);

            return new Generator(
                new XLSXWriter(),
                self::getFieldIds(),
                self::getFieldsSchema()
            );
        };

        $app['addons.customer_price_list.repository'] = function (Application $app) {
            return new Repository($app['db'], USERGROUP_ALL, __('all'));
        };

        $app['addons.customer_price_list.service'] = function (Application $app) {
            return new Service(
                self::getRepository(),
                self::getBaseDir(),
                $app['session'],
                function () {
                    return self::getGenerator();
                },
                function () {
                    return self::createCatalogProvider();
                }
            );
        };
    }

    /**
     * @return array<int, string>
     */
    public static function getFieldIds()
    {
        return array_keys(Registry::get('addons.customer_price_list.price_list_fields'));
    }

    /**
     * @return array<string, array>
     */
    public static function getFieldsSchema()
    {
        return Tygh::$app['addons.customer_price_list.fields_schema'];
    }

    /**
     * @return string
     */
    public static function getSortByField()
    {
        return (string) Registry::get('addons.customer_price_list.price_list_sorting');
    }

    /**
     * @return \Tygh\Addons\CustomerPriceList\Generator
     */
    public static function getGenerator()
    {
        return Tygh::$app['addons.customer_price_list.generator'];
    }

    /**
     * @return \Tygh\Addons\CustomerPriceList\Repository
     */
    public static function getRepository()
    {
        return Tygh::$app['addons.customer_price_list.repository'];
    }

    /**
     * @return \Tygh\Addons\CustomerPriceList\Service
     */
    public static function getService()
    {
        return Tygh::$app['addons.customer_price_list.service'];
    }

    /**
     * @return bool
     */
    public static function isNeedToGroupByCategory()
    {
        return YesNo::toBool(Registry::get('addons.customer_price_list.price_list_group_by_category'));
    }

    /**
     * @return string
     */
    public static function getBaseDir()
    {
        return sprintf('%s/customer_price_list', fn_get_files_dir_path(0));
    }

    /**
     * @param array $params
     *
     * @return \Tygh\Addons\CustomerPriceList\Provider\CatalogProviderInterface
     */
    public static function createCatalogProvider(array $params = [])
    {
        if (self::isNeedToGroupByCategory()) {
            return new GroupedCatalogProvider(self::getFieldIds(), self::getSortByField(), $params, DEFAULT_LANGUAGE);
        } else {
            return new CatalogProvider(self::getFieldIds(), self::getSortByField(), $params, DEFAULT_LANGUAGE);
        }
    }

    /**
     * @param array $args
     *
     * @return \Symfony\Component\Process\Process
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    public static function createAdminProcess(array $args)
    {
        array_unshift($args, self::getPhpBinaryPath(), DIR_ROOT . '/' . Registry::get('config.admin_index'));

        return new Process($args);
    }

    /**
     * @param array $args
     *
     * @return \Symfony\Component\Process\Process
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    public static function createCustomerProcess(array $args)
    {
        array_unshift($args, self::getPhpBinaryPath(), DIR_ROOT . '/' . Registry::get('config.customer_index'));

        return new Process($args);
    }

    /**
     * @return string
     */
    public static function getPhpBinaryPath()
    {
        if (self::$php_binary_path) {
            return self::$php_binary_path;
        }

        $php_binary_path = Registry::ifGet('config.customer_price_list.php_binary_path', false);

        if ($php_binary_path === false) {
            $php_binary_finder = new PhpExecutableFinder();
            $php_binary_path = $php_binary_finder->find();
        }

        if ($php_binary_path === false) {
            $php_binary_path = 'php';
        }

        return self::$php_binary_path = $php_binary_path;
    }
}