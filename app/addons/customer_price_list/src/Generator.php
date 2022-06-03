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
use Tygh\Addons\CustomerPriceList\Provider\GroupedCatalogProviderInterface;
use XLSXWriter;

/**
 * Class Generator
 *
 * @package Tygh\Addons\CustomerPriceList
 */
class Generator
{
    /**
     * @var \XLSXWriter
     */
    protected $xlsx_writer;

    /**
     * @var array<string, array>
     */
    protected $fields_schema = [];

    /**
     * @var array<int, string>
     */
    protected $field_ids = [];

    /**
     * @var string
     */
    protected $sheet = 'Sheet1';

    /**
     * Generator constructor.
     *
     * @param \XLSXWriter $xlsx_writer
     * @param array       $fields_schema
     */
    public function __construct(XLSXWriter $xlsx_writer, array $field_ids, array $fields_schema)
    {
        $this->xlsx_writer = $xlsx_writer;
        $this->field_ids = $field_ids;
        $this->fields_schema = $fields_schema;
    }

    /**
     * Generates price list
     *
     * @param \Tygh\Addons\CustomerPriceList\Provider\CatalogProviderInterface $provider
     * @param string                                                           $file_path
     *
     * @return void
     */
    public function generate(CatalogProviderInterface $provider, $file_path)
    {
        $this->generateHeader();
        $this->generateBody($provider);

        $this->xlsx_writer->writeToFile($file_path);
    }

    /**
     * Generates price list header
     *
     * @return void
     */
    protected function generateHeader()
    {
        $header = [];

        foreach ($this->field_ids as $field_id) {
            if (!isset($this->fields_schema[$field_id])) {
                continue;
            }

            $field_schema = $this->fields_schema[$field_id];

            if (isset($field_schema['type']) && is_callable($field_schema['type'])) {
                $header[$field_schema['title']] = call_user_func($field_schema['type']);
            } else {
                $header[$field_schema['title']] = 'string';
            }
        }

        $this->xlsx_writer->writeSheetHeader($this->sheet, $header);
    }

    /**
     * Generates price list body
     *
     * @param \Tygh\Addons\CustomerPriceList\Provider\CatalogProviderInterface $provider
     *
     * @return void
     */
    protected function generateBody(CatalogProviderInterface $provider)
    {
        $last_group = null;

        foreach ($provider->getProduct() as $product) {
            if ($provider instanceof GroupedCatalogProviderInterface) {
                $group = $provider->getGroup();
            } else {
                $group = null;
            }

            if ($group && $last_group !== $group) {
                $this->xlsx_writer->writeSheetRow($this->sheet, [$group]);
                $last_group = $group;
            }

            $values = $this->getFieldsValues($product);

            $this->xlsx_writer->writeSheetRow($this->sheet, $values);
        }
    }

    /**
     * Gets fields values
     *
     * @param array<string, mixed> $product
     *
     * @return array<int, string>
     */
    protected function getFieldsValues(array $product)
    {
        $values = [];

        foreach ($this->field_ids as $field_id) {
            if (!isset($this->fields_schema[$field_id])) {
                continue;
            }

            $field_schema = $this->fields_schema[$field_id];

            if (isset($field_schema['formatter']) && is_callable($field_schema['formatter'])) {
                $value = call_user_func($field_schema['formatter'], $product);
            } else {
                $value = isset($product[$field_id]) ? $product[$field_id] : '';
            }

            $values[] = $value;
        }

        return $values;
    }
}
