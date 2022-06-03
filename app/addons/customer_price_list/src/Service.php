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

use Tygh\Addons\CustomerPriceList\Provider\CatalogProviderInterface;
use Tygh\Exceptions\DeveloperException;
use Tygh\Web\Session;

/**
 * Class Service
 *
 * @package Tygh\Addons\CustomerPriceList
 */
class Service
{
    /**
     * @var \Tygh\Addons\CustomerPriceList\Repository
     */
    protected $repository;

    /**
     * @var string
     */
    private $base_dir;

    /**
     * @var callable
     */
    protected $generator_factory;

    /**
     * @var \Tygh\Web\Session
     */
    protected $session;

    /**
     * @var array<string, mixed>
     */
    protected $current_auth;

    /**
     * @var callable
     */
    protected $provider_factory;

    /**
     * Service constructor.
     *
     * @param \Tygh\Addons\CustomerPriceList\Repository $repository
     * @param string                                    $base_dir
     * @param int                                       $usergroup_all_id
     * @param \Tygh\Web\Session                         $session
     * @param callable                                  $generator_factory
     * @param callable                                  $provider_factory
     */
    public function __construct(
        Repository $repository,
        $base_dir,
        Session $session,
        callable $generator_factory,
        callable $provider_factory
    ) {
        $this->repository = $repository;
        $this->base_dir = (string) $base_dir;
        $this->session = $session;
        $this->current_auth = (array) $session['auth'];

        $this->generator_factory = $generator_factory;
        $this->provider_factory = $provider_factory;
    }

    /**
     * Generates price list
     *
     * @param int $storefront_id
     * @param int $usergroup_id
     *
     * @return bool
     */
    public function generatePriceList($storefront_id, $usergroup_id)
    {
        $this->setAuth($usergroup_id);

        $generator = $this->getGenerator();
        $provider = $this->getProvider();

        fn_mkdir($this->getDir($storefront_id));

        $file_name = $this->getFileName($usergroup_id);
        $file_path = $this->getFilePath($storefront_id, $file_name);

        $generator->generate($provider, $file_path);

        $this->restoreAuth();

        if (file_exists($file_path)) {
            $this->repository->save([
                'storefront_id' => $storefront_id,
                'usergroup_id'  => $usergroup_id,
                'file'          => $file_name,
                'updated_at'    => time()
            ]);

            return true;
        }

        return false;
    }

    /**
     * Removes price list by storefront ID
     *
     * @param int $storefront_id
     */
    public function removePriceListByStorefrontId($storefront_id)
    {
        fn_rm($this->getDir($storefront_id));
        $this->repository->removeByStorefrontId($storefront_id);
    }

    /**
     * Removes price list by usergroup ID
     *
     * @param int $usergroup_id
     */
    public function removePriceListByUsergroupId($usergroup_id)
    {
        $list = $this->repository->getList([
            'usergroup_id' => $usergroup_id
        ]);

        foreach ($list as $item) {
            $this->removeFile($item['storefront_id'], $item['file']);
        }

        $this->repository->removeByUsergroupId($usergroup_id);
    }

    /**
     * Gets file path
     *
     * @param int    $storefront_id
     * @param string $file_name
     *
     * @return string
     */
    public function getFilePath($storefront_id, $file_name)
    {
        return sprintf('%s/%s', $this->getDir($storefront_id), $file_name);
    }

    /**
     * Gets file info by price list
     *
     * @param array $price_list
     *
     * @return array<int, string>
     */
    public function getFile(array $price_list)
    {
        $file_path = $this->getFilePath($price_list['storefront_id'], $price_list['file']);
        $file_name = strtolower(str_replace(' ', '_', sprintf('price_list_%s_%s_%s.xlsx',
            $price_list['storefront'],
            $price_list['usergroup'],
            date('Y_m_d')
        )));

        return [$file_path, $file_name];
    }

    /**
     * Remove file
     *
     * @param int    $storefront_id
     * @param string $file_name
     *
     * @return void
     */
    protected function removeFile($storefront_id, $file_name)
    {
        fn_rm($this->getFilePath($storefront_id, $file_name));
    }

    /**
     * Gets storefrint price list files dir
     *
     * @param int $storefront_id
     *
     * @return string
     */
    protected function getDir($storefront_id)
    {
        return sprintf('%s/%s', $this->base_dir, $storefront_id);
    }

    /**
     * Gets price list file name by usergroup ID
     *
     * @param int $usergroup_id
     *
     * @return string
     */
    protected function getFileName($usergroup_id)
    {
        return sprintf('usergroup_%s.xlsx', $usergroup_id);
    }

    /**
     * Initializes and sets to session custom `auth` by usergroup ID
     *
     * @param int $usergroup_id
     *
     * @return void
     */
    protected function setAuth($usergroup_id)
    {
        $auth = fn_fill_auth([]);
        $auth['usergroup_ids'][] = $usergroup_id;

        $this->session['auth'] = $auth;
    }

    /**
     * Restores `auth` on session to original auth
     *
     * @return void
     */
    protected function restoreAuth()
    {
        $this->session['auth'] = $this->current_auth;
    }

    /**
     * Gets generator instance
     *
     * @return \Tygh\Addons\CustomerPriceList\Generator
     */
    protected function getGenerator()
    {
        /**
         * @var \Tygh\Addons\CustomerPriceList\Generator $generator
         */
        $generator = call_user_func($this->generator_factory);

        if (!$generator instanceof Generator) {
            DeveloperException::throwException(
                'Generator must be instance of \Tygh\Addons\CustomerPriceList\Generator'
            );
        }

        return $generator;
    }

    /**
     * Gets catalog provider instance
     *
     * @return \Tygh\Addons\CustomerPriceList\Provider\CatalogProviderInterface
     */
    protected function getProvider()
    {
        /**
         * @var \Tygh\Addons\CustomerPriceList\Provider\CatalogProviderInterface $provider
         */
        $provider = call_user_func($this->provider_factory);

        if (!$provider instanceof CatalogProviderInterface) {
            DeveloperException::throwException(
                'Provider must be implement \Tygh\Addons\CustomerPriceList\Provider\CatalogProviderInterface'
            );
        }

        return $provider;
    }
}