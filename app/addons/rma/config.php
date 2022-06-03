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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

define('STATUSES_RETURN', 'R');
define('ORDER_DATA_RETURN', 'H');
define('ORDER_DATA_PRODUCTS_DELIVERY_DATE', 'V');

define('RMA_REASON', 'R');
define('RMA_ACTION', 'A');
/** @deprecated since 4.10.4 use \Tygh\Enum\Addons\Rma\ReturnStatuses::REQUESTED  */
define('RMA_DEFAULT_STATUS', 'R');
/** @deprecated since 4.10.4 use \Tygh\Enum\Addons\Rma\ReturnStatuses::APPROVED */
define('RETURN_PRODUCT_ACCEPTED', 'A');
/** @deprecated since 4.10.4 use \Tygh\Enum\Addons\Rma\ReturnStatuses::DECLINED */
define('RETURN_PRODUCT_DECLINED', 'D');
