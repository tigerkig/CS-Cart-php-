{script src="js/tygh/backend/categories.js"}

{if $language_direction == "rtl"}
    {$direction = "right"}
{else}
    {$direction = "left"}
{/if}
{$page_title_seo_length = 60}
{$description_seo_length = 145}

{capture name="mainbox"}

    {capture name="tabsbox"}
        {** /Item menu section **}

        {assign var="categories_company_id" value=$product_data.company_id}
        {assign var="allow_save" value=$product_data|fn_allow_save_object:"product"}

        {if "ULTIMATE"|fn_allowed_for}
            {assign var="categories_company_id" value=""}
            {if $runtime.company_id && $product_data.shared_product == "Y" && $product_data.company_id != $runtime.company_id}
                {assign var="no_hide_input_if_shared_product" value="cm-no-hide-input"}
                {assign var="is_shared_product" value=true}
            {/if}

            {if !$runtime.company_id && $product_data.shared_product == "Y"}
                {assign var="show_update_for_all" value=true}
            {/if}
        {/if}

        {if $product_data.product_id}
            {assign var="id" value=$product_data.product_id}
        {else}
            {assign var="id" value=0}
        {/if}

        {$is_form_readonly = fn_check_form_permissions("") || ($id && $runtime.company_id && (fn_allowed_for("MULTIVENDOR") || $product_data.shared_product == "Y") && $product_data.company_id != $runtime.company_id)}

        <form id="form" action="{""|fn_url}" method="post" name="product_update_form" class="form-horizontal form-edit  cm-disable-empty-files {if $is_form_readonly}cm-hide-inputs{/if}" enctype="multipart/form-data"> {* product update form *}
            <input type="hidden" name="fake" value="1" />
            <input type="hidden" class="{$no_hide_input_if_shared_product}" name="selected_section" id="selected_section" value="{$smarty.request.selected_section}" />
            <input type="hidden" class="{$no_hide_input_if_shared_product}" name="product_id" value="{$id}" />

            {** Product description section **}

            <div class="product-manage {if $selected_section !== "detailed"}hidden{/if}" id="content_detailed"> {* content detailed *}

                {** General info section **}
                {include file="common/subheader.tpl" title=__("information") target="#acc_information"}

                <div id="acc_information" class="collapse in collapse-visible">

                    {hook name="products:update_product_name"}
                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label for="product_description_product" class="control-label cm-required">{__("name")}</label>
                        <div class="controls">
                            <input class="input-large" form="form" type="text" name="product_data[product]" id="product_description_product" size="55" value="{$product_data.product}" />
                            {include file="buttons/update_for_all.tpl"
                            display=$show_update_for_all
                            object_id="product"
                            name="update_all_vendors[product]"
                            component="products.product"
                        }
                        </div>
                    </div>
                    {/hook}

                    {hook name="products:categories_section"}
                        {$result_ids = "product_categories"}

                        {if "MULTIVENDOR"|fn_allowed_for && $mode != "add"}
                             {$js_action = "fn_change_vendor_for_product();"}
                        {/if}

                        {hook name="companies:product_details_fields"}

                        {if "ULTIMATE"|fn_allowed_for}
                            {assign var="companies_tooltip" value=__("text_ult_product_store_field_tooltip")}
                        {/if}

                        {include file="views/companies/components/company_field.tpl"
                            name="product_data[company_id]"
                            id="product_data_company_id"
                            selected=$product_data.company_id
                            tooltip=$companies_tooltip
                        }

                        {/hook}

                        <input type="hidden" value="{$result_ids}" name="result_ids">

                        <div class="control-group {$no_hide_input_if_shared_product}" id="product_categories">
                            {math equation="rand()" assign="rnd"}
                            {if $smarty.request.category_id}
                                {assign var="request_category_id" value=","|explode:$smarty.request.category_id}
                            {else}
                                {assign var="request_category_id" value=""}
                            {/if}
                            <label for="product_categories_add_{$rnd}" class="control-label cm-required control-label--product-categories">{__("categories")}</label>
                            <div class="controls">
                                <input type="hidden" name="product_data[add_new_category][]" value=""/>
                                {include file="views/categories/components/picker/picker.tpl"
                                    input_name="product_data[category_ids][]"
                                    simple_class="cm-field-container"
                                    multiple=true
                                    id="product_categories_add_{$rnd}"
                                    tabindex=$tabindex
                                    item_ids=$product_data.category_ids
                                    meta="input-large object-categories-add object-categories-add--multiple"
                                    show_advanced=true
                                    allow_add=fn_check_permissions("categories", "update", "admin", "POST")
                                    allow_sorting=true
                                    result_class="object-picker__result--inline"
                                    selection_class="object-picker__selection--product-categories"
                                    required=true
                                    close_on_select=false
                                    allow_multiple_created_objects=true
                                    created_object_holder_selector="[name='product_data[add_new_category][]']"
                                }
                                <p class="muted description">{__("tt_views_products_update_categories")}</p>
                        </div>
                    <!--product_categories--></div>
                    {/hook}

                    {hook name="products:product_update_price"}
                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label for="elm_price_price" class="control-label cm-required">{__("price")} ({$currencies.$primary_currency.symbol nofilter}):</label>
                        <div class="controls">
                            <input type="text" name="product_data[price]" id="elm_price_price" size="10" value="{$product_data.price|default:"0.00"|fn_format_price:$primary_currency:null:false}" class="input-long cm-numeric" data-a-sep/>
                            {include file="buttons/update_for_all.tpl"
                                display=$show_update_for_all
                                object_id="price"
                                name="update_all_vendors[price]"
                                component="products.price"
                            }
                            </div>
                        </div>
                    {/hook}

                    {hook name="products:update_product_full_description"}
                    <div class="control-group cm-no-hide-input">
                        <label class="control-label" for="elm_product_full_descr">{__("full_description")}:</label>
                        <div class="controls">
                            {include file="buttons/update_for_all.tpl"
                                display=$show_update_for_all
                                object_id="full_description"
                                name="update_all_vendors[full_description]"
                                component="products.full_description"
                            }
                            <textarea id="elm_product_full_descr"
                                      name="product_data[full_description]"
                                      cols="55"
                                      rows="8"
                                      class="cm-wysiwyg input-large"
                                      data-ca-is-block-manager-enabled="{fn_check_view_permissions("block_manager.block_selection", "GET")|intval}"
                            >{$product_data.full_description}</textarea>

                            {if $view_uri}
                                {include
                                    file="buttons/button.tpl"
                                    but_href="customization.update_mode?type=live_editor&status=enable&frontend_url={$view_uri|urlencode}{if "ULTIMATE"|fn_allowed_for}&switch_company_id={$product_data.company_id}{/if}"
                                    but_text=__("edit_content_on_site")
                                    but_role="action"
                                    but_meta="btn-small btn-live-edit cm-post"
                                    but_target="_blank"}
                            {/if}
                            </div>
                        </div>
                    {/hook}
                    {** /General info section **}

                    {hook name="products:update_product_status"}
                    {include file = "views/products/components/status_on_update.tpl"
                        input_name = "product_data[status]"
                        id = "elm_product_status"
                        obj = $product_data
                        hidden = true
                    }
                    {/hook}

                    {hook name="products:update_detailed_images"}
                    <div class="control-group">
                        <label class="control-label">{__("images")}:</label>
                        <div class="controls">
                            {include
                                file="common/form_file_uploader.tpl"
                                existing_pairs=(($product_data.main_pair) ? [$product_data.main_pair] : []) + $product_data.image_pairs|default:[]
                                file_name="file"
                                image_pair_types=['N' => 'product_add_additional_image', 'M' => 'product_main_image', 'A' => 'product_additional_image']
                                allow_update_files=!$is_shared_product && $allow_update_files|default:true
                            }
                        </div>
                    </div>
                    {/hook}

                </div>

                {hook name="products:update_product_options_settings"}
                    {capture name="select_options_type"}
                        {component
                            name="product.overridable_field_input"
                            type="SettingTypes::SELECTBOX"|enum
                            value=$product_data.options_type_raw
                            field_name="options_type"
                            variants=[
                                "ProductOptionsApplyOrder::SIMULTANEOUS"|enum => __("simultaneous"),
                                "ProductOptionsApplyOrder::SEQUENTIAL"|enum   => __("sequential")
                            ]
                            disable_inputs=$disable_selectors
                            company_id=$product_data.company_id|default:null
                        }{/component}
                    {/capture}
                    {capture name="select_exceptions_type"}
                        {component
                            name="product.overridable_field_input"
                            type="SettingTypes::SELECTBOX"|enum
                            value=$product_data.exceptions_type_raw
                            field_name="exceptions_type"
                            variants=[
                                "ProductOptionsExceptionsTypes::FORBIDDEN"|enum => __("forbidden"),
                                "ProductOptionsExceptionsTypes::ALLOWED"|enum   => __("allowed")
                            ]
                            disable_inputs=$disable_selectors
                            company_id=$product_data.company_id|default:null
                        }{/component}
                    {/capture}
                    {if $smarty.capture.select_options_type|trim && $smarty.capture.select_exceptions_type|trim}
                        <hr>
                        {include file="common/subheader.tpl" title=__("options_settings") target="#acc_options"}

                        <div id="acc_options" class="collapse in">
                            {hook name="products:update_product_options_type"}
                                 {if $smarty.capture.select_options_type|trim}
                                    <div class="control-group {$promo_class}">
                                        <label class="control-label" for="elm_options_type">{__("options_type")}:</label>
                                        <div class="controls">
                                            {$smarty.capture.select_options_type nofilter}
                                        </div>
                                    </div>
                                {/if}
                            {/hook}
                            {hook name="products:update_product_exceptions_type"}
                                {if $smarty.capture.select_exceptions_type|trim}
                                    <div class="control-group {$promo_class}">
                                        <label class="control-label" for="elm_exceptions_type">{__("exceptions_type")}:</label>
                                        <div class="controls">
                                            {$smarty.capture.select_exceptions_type nofilter}
                                        </div>
                                    </div>
                                {/if}
                            {/hook}
                        </div>
                    {/if}
                {/hook}

                <hr>

                {include file="common/subheader.tpl" title=__("pricing_inventory") target="#acc_pricing_inventory"}
                <div class="collapse in" id="acc_pricing_inventory">
                    {hook name="products:update_product_code"}
                    <div class="control-group">
                        <label class="control-label" for="elm_product_code">{__("sku")}:</label>
                        <div class="controls">
                            <input type="text" name="product_data[product_code]" id="elm_product_code" size="20" maxlength={"ProductFieldsLength::PRODUCT_CODE"|enum}  value="{$product_data.product_code}" class="input-large" />
                            </div>
                        </div>
                    {/hook}

                    {hook name="products:update_product_list_price"}
                    <div class="control-group">
                        <label class="control-label" for="elm_list_price">{__("list_price")} ({$currencies.$primary_currency.symbol nofilter}) :</label>
                        <div class="controls">
                            <input type="text" name="product_data[list_price]" id="elm_list_price" size="10" value="{$product_data.list_price|default:"0.00"|fn_format_price:$primary_currency:null:false}" class="input-long cm-numeric" data-a-sep />
                            <p class="muted description">{__("tt_views_products_update_list_price")}</p>
                        </div>
                    </div>
                    {/hook}

                    <div id="product_amount">
                    {hook name="products:update_product_amount"}
                    <div class="control-group">
                        <label class="control-label" for="elm_in_stock">{__("in_stock")}:</label>
                        <div class="controls">
                            <input type="text" name="product_data[amount]" id="elm_in_stock" size="10" value="{$product_data.amount|default:"1"}" class="input-small" />
                        </div>
                    </div>
                    {/hook}
                    <!--product_amount--></div>

                    {hook name="products:update_product_zero_price_action"}
                        {component
                            name="product.overridable_field_input"
                            type="SettingTypes::SELECTBOX"|enum
                            value=$product_data.zero_price_action_raw
                            field_name="zero_price_action"
                            variants=[
                                "ProductZeroPriceActions::NOT_ALLOW_ADD_TO_CART"|enum => __("zpa_refuse"),
                                "ProductZeroPriceActions::ALLOW_ADD_TO_CART"|enum => __("zpa_permit"),
                                "ProductZeroPriceActions::ASK_TO_ENTER_PRICE"|enum => __("zpa_ask_price")
                            ]
                            disable_inputs=$disable_selectors
                            company_id=$product_data.company_id|default:null
                        }
                            <div class="control-group">
                                <label class="control-label" for="elm_zero_price_action">{__("zero_price_action")}:</label>
                                <div class="controls">
                                    #INPUT#
                                </div>
                            </div>
                        {/component}
                    {/hook}

                    {hook name="products:update_product_tracking"}
                        {component
                            name="product.overridable_field_input"
                            type="SettingTypes::SELECTBOX"|enum
                            value=$product_data.tracking_raw
                            field_name="tracking"
                            variants=[
                                "ProductTracking::TRACK"|enum => __("yes"),
                                "ProductTracking::DO_NOT_TRACK"|enum => __("no")
                            ]
                            disable_inputs=$disable_selectors || $settings.General.inventory_tracking === "YesNo::NO"|enum
                            company_id=$product_data.company_id|default:null
                        }
                            <div class="control-group">
                                <label class="control-label" for="elm_product_tracking">{__("track_inventory")}:</label>
                                <div class="controls">
                                    #INPUT#
                                    <p class="muted description">{__("track_inventory_tooltip")}</p>
                                </div>
                            </div>
                        {/component}
                    {/hook}

                    {hook name="products:update_product_min_qty"}
                        {component
                            name="product.overridable_field_input"
                            type="SettingTypes::INPUT"|enum
                            value=$product_data.min_qty_raw
                            field_name="min_qty"
                            disable_inputs=$disable_selectors
                            company_id=$product_data.company_id|default:null
                            custom_input_styles="cm-numeric"
                        }
                            <div class="control-group">
                                <label class="control-label" for="elm_min_qty">{__("min_order_qty")}:</label>
                                <div class="controls">
                                    #INPUT#
                                </div>
                            </div>
                        {/component}
                    {/hook}

                    {hook name="products:update_product_max_qty"}
                        {component
                            name="product.overridable_field_input"
                            type="SettingTypes::INPUT"|enum
                            value=$product_data.max_qty_raw
                            field_name="max_qty"
                            disable_inputs=$disable_selectors
                            company_id=$product_data.company_id|default:null
                            custom_input_styles="cm-numeric"
                        }
                            <div class="control-group">
                                <label class="control-label" for="elm_max_qty">{__("max_order_qty")}:</label>
                                <div class="controls">
                                    #INPUT#
                                </div>
                            </div>
                        {/component}
                    {/hook}

                    {hook name="products:update_product_qty_step"}
                        {component
                            name="product.overridable_field_input"
                            type="SettingTypes::INPUT"|enum
                            value=$product_data.qty_step_raw
                            field_name="qty_step"
                            disable_inputs=$disable_selectors
                            company_id=$product_data.company_id|default:null
                            custom_input_styles="cm-numeric"
                        }
                            <div class="control-group">
                                <label class="control-label" for="elm_qty_step">{__("quantity_step")}:</label>
                                <div class="controls">
                                    #INPUT#
                                </div>
                            </div>
                        {/component}
                    {/hook}

                    {hook name="products:update_product_list_qty_count"}
                        {component
                            name="product.overridable_field_input"
                            type="SettingTypes::INPUT"|enum
                            value=$product_data.list_qty_count_raw
                            field_name="list_qty_count"
                            disable_inputs=$disable_selectors
                            company_id=$product_data.company_id|default:null
                            custom_input_styles="cm-numeric"
                        }
                            <div class="control-group">
                                <label class="control-label" for="elm_list_qty_count">{__("list_quantity_count")}:</label>
                                <div class="controls">
                                    #INPUT#
                                </div>
                            </div>
                        {/component}
                    {/hook}

                    {hook name="products:update_product_tax_ids"}
                    <div class="control-group">
                        <label class="control-label">{__("taxes")}:</label>
                        <div class="controls">
                            <input type="hidden" name="product_data[tax_ids]" value="" />
                            {foreach from=$taxes item="tax"}
                                <label class="checkbox inline" for="elm_taxes_{$tax.tax_id}">
                                    <input type="checkbox" name="product_data[tax_ids][{$tax.tax_id}]" id="elm_taxes_{$tax.tax_id}" {if $tax.tax_id|in_array:$product_data.tax_ids}checked="checked"{/if} value="{$tax.tax_id}" />
                                    {$tax.tax}</label>
                                {foreachelse}
                                &ndash;
                            {/foreach}
                        </div>
                    </div>
                    {/hook}
                </div>

                {hook name="products:update_product_availability"}
                <hr>
                {include file="common/subheader.tpl" title=__("availability") target="#acc_availability"}
                <div id="acc_availability" class="collapse in">
                    {hook name="products:update_product_usergroup_ids"}
                    {if !"ULTIMATE:FREE"|fn_allowed_for}
                        <div class="control-group">
                            <label class="control-label">{__("usergroups")}:</label>
                            <div class="controls">
                                {include file="common/select_usergroups.tpl" id="ug_id" name="product_data[usergroup_ids]" usergroups=["type"=>"C", "status"=>["A", "H"]]|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids=$product_data.usergroup_ids input_extra="" list_mode=false}
                            </div>
                        </div>
                    {/if}
                    {/hook}

                    {hook name="products:update_product_timestamp"}
                    <div class="control-group">
                        <label class="control-label" for="elm_date_holder">{__("creation_date")}:</label>
                        <div class="controls">
                            {include file="common/calendar.tpl" date_id="elm_date_holder" date_name="product_data[timestamp]" date_val=$product_data.timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
                        </div>
                    </div>
                    {/hook}

                    {hook name="products:update_product_avail_since"}
                    <div class="control-group">
                        <label class="control-label" for="elm_date_avail_holder">{__("available_since")}:</label>
                        <div class="controls">
                            {include file="common/calendar.tpl" date_id="elm_date_avail_holder" date_name="product_data[avail_since]" date_val=$product_data.avail_since|default:"" start_year=$settings.Company.company_start_year}
                        </div>
                    </div>
                    {/hook}

                    {hook name="products:update_product_out_of_stock_actions"}
                    <div class="control-group">
                        <label class="control-label" for="elm_out_of_stock_actions">{__("out_of_stock_actions")}:</label>
                        <div class="controls">
                            <select class="span3" name="product_data[out_of_stock_actions]" id="elm_out_of_stock_actions">
                                <option value="N" {if $product_data.out_of_stock_actions == "N"}selected="selected"{/if}>{__("none")}</option>
                                <option value="B" {if $product_data.out_of_stock_actions == "B"}selected="selected"{/if}>{__("buy_in_advance")}</option>
                                <option value="S" {if $product_data.out_of_stock_actions == "S"}selected="selected"{/if}>{__("sign_up_for_notification")}</option>
                            </select>
                            <p class="muted description">{__("tt_views_products_update_out_of_stock_actions")}</p>
                        </div>
                    </div>
                    {/hook}
                </div>
                {/hook}

                {capture name="product_extra"}
                    {hook name="products:update_product_details_layout"}
                        {component
                            name="product.layout_input"
                            id=$id|default:0
                            value=$product_data.details_layout_raw|default:"default"
                            company_id=$product_data.company_id
                        }
                            <div class="control-group">
                                <label class="control-label" for="elm_details_layout">{__("product_details_view")}:</label>
                                <div class="controls">
                                    #INPUT#
                                </div>
                            </div>
                        {/component}
                    {/hook}

                    {hook name="products:update_edp_section"}
                    {if $settings.General.enable_edp == "Y"}
                    <div class="control-group">
                        <label class="control-label" for="elm_product_is_edp">{__("downloadable")}:</label>
                        <div class="controls">
                            <label class="checkbox">
                                <input type="hidden" name="product_data[is_edp]" value="N" />
                                <input type="checkbox" name="product_data[is_edp]" id="elm_product_is_edp" value="Y" {if $product_data.is_edp == "Y"}checked="checked"{/if} onclick="Tygh.$('#edp_shipping').toggleBy(); Tygh.$('#edp_unlimited').toggleBy();"/>
                            </label>
                        </div>
                    </div>

                    <div class="control-group {if $product_data.is_edp != "Y"}hidden{/if}" id="edp_shipping">
                        <label class="control-label" for="elm_product_edp_shipping">{__("edp_enable_shipping")}:</label>
                        <div class="controls">
                            <label class="checkbox">
                                <input type="hidden" name="product_data[edp_shipping]" value="N" />
                                <input type="checkbox" name="product_data[edp_shipping]" id="elm_product_edp_shipping" value="Y"{if $product_data.edp_shipping == "Y"}checked="checked"{/if} />
                            </label>
                        </div>
                    </div>

                    <div class="control-group {if $product_data.is_edp != "Y"}hidden{/if}" id="edp_unlimited">
                        <label class="control-label" for="elm_product_edp_unlimited">{__("time_unlimited_download")}:</label>
                        <div class="controls">
                            <label class="checkbox">
                                <input type="hidden" name="product_data[unlimited_download]" value="N" />
                                <input type="checkbox" name="product_data[unlimited_download]" id="elm_product_edp_unlimited" value="Y" {if $product_data.unlimited_download == "Y"}checked="checked"{/if} />
                            </label>
                        </div>
                    </div>
                    {/if}
                    {/hook}

                    {include file="views/localizations/components/select.tpl" data_from=$product_data.localization data_name="product_data[localization]"}

                    {hook name="products:update_product_short_description"}
                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_short_descr">{__("short_description")}:</label>
                        <div class="controls">
                            <textarea id="elm_product_short_descr"
                                      name="product_data[short_description]"
                                      cols="55"
                                      rows="2"
                                      class="cm-wysiwyg input-large"
                            >{$product_data.short_description}</textarea>
                            {include file="buttons/update_for_all.tpl"
                                display=$show_update_for_all
                                object_id="short_description"
                                name="update_all_vendors[short_description]"
                                component="products.short_description"
                            }
                        </div>
                    </div>
                    {/hook}

                    {hook name="products:update_product_popularity"}
                    <div class="control-group">
                        <label class="control-label" for="elm_product_popularity">{__("popularity")}:</label>
                        <div class="controls">
                            <input type="text" {if $disable_edit_popularity}disabled="disabled"{/if} name="product_data[popularity]" id="elm_product_popularity" size="55" value="{$product_data.popularity|default:0}" class="input-long" />
                            <p class="muted description">{__("ttc_popularity")}</p>
                        </div>
                    </div>
                    {/hook}

                    {hook name="products:update_product_search_words"}
                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_search_words">{__("search_words")}:</label>
                        <div class="controls">
                            <textarea name="product_data[search_words]" id="elm_product_search_words" cols="55" rows="2" class="input-large">{$product_data.search_words}</textarea>
                            {include file="buttons/update_for_all.tpl"
                                display=$show_update_for_all
                                object_id="search_words"
                                name="update_all_vendors[search_words]"
                                component="products.search_words"
                            }
                            <p class="muted description">{__("ttc_search_words")}</p>
                        </div>
                    </div>
                    {/hook}

                    {hook name="products:update_product_promo_text"}
                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_promo_text">{__("promo_text")}:</label>
                        <div class="controls">
                            <textarea id="elm_product_promo_text" name="product_data[promo_text]" cols="55" rows="2" class="cm-wysiwyg input-large">{$product_data.promo_text}</textarea>
                            {include file="buttons/update_for_all.tpl"
                                display=$show_update_for_all
                                object_id="promo_text"
                                name="update_all_vendors[promo_text]"
                                component="products.promo_text"
                            }
                        </div>
                    </div>
                    {/hook}
                {/capture}

                {if $smarty.capture.product_extra|strip_tags|trim}
                    <hr>
                    {include file="common/subheader.tpl" title=__("extra") target="#acc_extra"}
                    <div id="acc_extra" class="collapse in">
                        {$smarty.capture.product_extra nofilter}
                </div>
                {/if}
                <!--content_detailed--></div> {* /content detailed *}

            {** /Product description section **}

            {hook name="products:update_product_seo_settings"}
            {** SEO settings section **}
            <div class="{if $selected_section !== "seo"}hidden{/if}" id="content_seo">

                {hook name="products:update_seo"}
                {include file="common/subheader.tpl" title=__("seo_meta_data") target="#acc_seo_meta"}
                <div id="acc_seo_meta" class="collapse in">
                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_page_title">{__("page_title")}:</label>
                        <div class="controls">
                            <input type="text"
                                name="product_data[page_title]"
                                id="elm_product_page_title"
                                size="55"
                                value="{$product_data.page_title}"
                                class="input-large"
                                data-ca-seo-length="{$page_title_seo_length}"
                            />
                            {include file="buttons/update_for_all.tpl"
                                display=$show_update_for_all
                                object_id="page_title"
                                name="update_all_vendors[page_title]"
                                component="products.page_title"
                            }
                        </div>
                    </div>

                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_meta_descr">{__("meta_description")}:</label>
                        <div class="controls">
                            <textarea name="product_data[meta_description]"
                                id="elm_product_meta_descr"
                                cols="55"
                                rows="2"
                                class="input-large"
                                data-ca-seo-length="{$description_seo_length}"
                            >{$product_data.meta_description}</textarea>
                            {include file="buttons/update_for_all.tpl"
                                display=$show_update_for_all
                                object_id="meta_description"
                                name="update_all_vendors[meta_description]"
                                component="products.meta_description"
                            }
                        </div>
                    </div>

                    <div class="control-group {$no_hide_input_if_shared_product}">
                        <label class="control-label" for="elm_product_meta_keywords">{__("meta_keywords")}:</label>
                        <div class="controls">
                            <textarea name="product_data[meta_keywords]" id="elm_product_meta_keywords" cols="55" rows="2" class="input-large">{$product_data.meta_keywords}</textarea>
                            {include file="buttons/update_for_all.tpl"
                                display=$show_update_for_all
                                object_id="meta_keywords"
                                name="update_all_vendors[meta_keywords]"
                                component="products.meta_keywords"
                            }
                        </div>
                    </div>
                </div>
                {/hook}
            </div>
            {** /SEO settings section **}
            {/hook}

            {hook name="products:update_product_shipping_settings"}
            {** Shipping settings section **}
            <div class="{if $selected_section !== "shippings"}hidden{/if}" id="content_shippings"> {* content shippings *}
                {include file="views/products/components/products_shipping_settings.tpl"}
            </div> {* /content shippings *}
            {** /Shipping settings section **}
            {/hook}

            {** Quantity discounts section **}
            {hook name="products:update_qty_discounts"}
            {include file="views/products/components/products_update_qty_discounts.tpl"}
            {/hook}
            {** /Quantity discounts section **}

            {hook name="products:update_product_features"}
            {** Product features section **}
            {include file="views/products/components/products_update_features.tpl" product_id=$product_data.product_id allow_save=$allow_save_feature}
            {** /Product features section **}
            {/hook}

            {hook name="products:update_addons_section"}
            <div class="{if $selected_section !== "addons"}hidden{/if}" id="content_addons">
                {hook name="products:detailed_content"}
                {/hook}
            </div>
            {/hook}

            {hook name="products:tabs_content"}
            {/hook}

            {** Form submit section **}
            {capture name="buttons"}
            {$allow_clone = true}
            {hook name="products:update_product_buttons"}
                {include file="common/view_tools.tpl" url="products.update?product_id="}

                {if $id}
                    {capture name="tools_list"}
                        {hook name="products:update_tools_list"}
                            {if $view_uri}
                                <li>{btn type="list" target="_blank" text=__("preview") href=$view_uri}</li>
                                <li class="divider"></li>
                            {/if}
                            {if $allow_clone}
                            <li>{btn type="list" text=__("clone") href="products.clone?product_id=`$id`" method="POST"}</li>
                            {/if}
                            {if $allow_save}
                                <li>{btn type="list" text=__("delete") class="cm-confirm" href="products.delete?product_id=`$id`" method="POST"}</li>
                            {/if}
                        {/hook}
                    {/capture}
                    {dropdown content=$smarty.capture.tools_list}
                {/if}
                <!-- the button goes here -->
                {include file="buttons/save_cancel.tpl" but_meta="cm-product-save-buttons" but_role="submit-link" but_name="dispatch[products.update]" but_target_form="product_update_form" save=$id}
                <!-- the button goes there -->
            {/hook}
            {/capture}
            {** /Form submit section **}

            {if "ULTIMATE"|fn_allowed_for}
                <input type="hidden" name="switch_company_id" class="{$no_hide_input_if_shared_product}" value="{$runtime.company_id}" />
            {/if}
            <input type="hidden" name="descr_sl" class="{$no_hide_input_if_shared_product}" value="{$smarty.const.DESCR_SL}" />
        </form> {* /product update form *}

        {hook name="products:tabs_extra"}{/hook}

        {if $id}
            {** Product options section **}
            <div class="cm-hide-save-button {if $selected_section !== "options"}hidden{/if}" id="content_options">
                {include file="views/products/components/products_update_options.tpl" enable_search=true}
            </div>
            {** /Product options section **}

            {** Products files section **}
            {if $settings.General.enable_edp == "Y"}
            <div class="cm-hide-save-button {if $selected_section !== "files"}hidden{/if}" id="content_files">
                {hook name="products:content_files"}
                {include file="views/products/components/products_update_files.tpl"}
                {/hook}
            </div>
            {/if}
            {** /Products files section **}

            {** Subscribers section **}
            <div class="cm-hide-save-button {if $selected_section !== "subscribers"}hidden{/if}" id="content_subscribers">
                {include file="views/products/components/product_subscribers.tpl" product_id=$id}
            </div>
            {** /Subscribers section **}
        {/if}

    {/capture}
    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name=$runtime.controller active_tab=$selected_section track=true}

{/capture}

{hook name="products:update_mainbox_params"}

{if $id}
    {$title = $product_data.product|strip_tags}
{else}
    {$title = __("new_product")}
{/if}

{/hook}

{include file="common/mainbox.tpl"
    title=$title
    content=$smarty.capture.mainbox
    select_languages=(bool) $id
    buttons=$smarty.capture.buttons
    adv_buttons=$smarty.capture.adv_buttons
}

{if "MULTIVENDOR"|fn_allowed_for}
<script>
  var fn_change_vendor_for_product = function(){
    $.ceAjax('request', Tygh.current_url, {
      data: {
        product_data: {
          company_id: $('[name="product_data[company_id]"]').val(),
          category_ids: $('[name="product_data[category_ids]"]').val()
        }
      },
      result_ids: 'product_amount,product_categories'
    });
  };
</script>
{/if}
