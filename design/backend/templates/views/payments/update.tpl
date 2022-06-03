{if $payment}
    {assign var="id" value=$payment.payment_id}
{else}
    {assign var="id" value="0"}
{/if}

{$storefront_owner_id = false}
{if $payment.storefront_owner_id}
    {$storefront_owner_id = $payment.storefront_owner_id}
{/if}

{assign var="allow_save" value=$payment|fn_allow_save_object:"payments"}

<div id="content_group{$id}">

<form action="{""|fn_url}" method="post" name="payments_form_{$id}" enctype="multipart/form-data" class=" form-horizontal{if !$allow_save} cm-hide-inputs{/if}">
<input type="hidden" name="payment_id" value="{$id}" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="tab_details_{$id}" class="cm-js active"><a>{__("general")}</a></li>
        <li id="tab_conf_{$id}" class="cm-js cm-ajax {if !$payment.processor_id}hidden{/if}"><a {if $payment.processor_id}href="{"payments.processor?payment_id=`$id`"|fn_url}"{/if}>{__("configure")}</a></li>
        {if fn_allowed_for("MULTIVENDOR:ULTIMATE") || $is_sharing_enabled}
            <li id="tab_storefronts_{$id}" class="cm-js"><a>{__("storefronts")}</a></li>
        {/if}
        {hook name="payments:tabs_list"}
        {/hook}
    </ul>
</div>

<div class="cm-tabs-content" id="tabs_content_{$id}">
    <div id="content_tab_details_{$id}">
    <fieldset  data-ca-form-group="main">
        <div class="control-group" data-ca-form-group="name">
            <label for="elm_payment_name_{$id}" class="cm-required control-label">{__("name")}:</label>
            <div class="controls">
                <input id="elm_payment_name_{$id}" type="text" name="payment_data[payment]" value="{$payment.payment}">
            </div>
        </div>

        <div data-ca-form-group="company">
        {if "ULTIMATE"|fn_allowed_for && $allow_save}
                {include file="views/companies/components/company_field.tpl"
                    name="payment_data[company_id]"
                    id="payment_data_`$smarty.request.payment_id`"
                    selected=$payment.company_id
                }
        {/if}
        </div>

        <div class="control-group" data-ca-form-group="processor">
            <label class="control-label" for="elm_payment_processor_{$id}">{__("processor")}:</label>
            <div class="controls">
                <select id="elm_payment_processor_{$id}" class="cm-object-picker" name="payment_data[processor_id]" onchange="fn_switch_processor({$id}, this.value);">
                    <option value="0">{__("offline")}</option>
                    {hook name="payments:processors_optgroups"}
                        {foreach $payment_processors as $category_name => $payment_procs}
                            {if $payment_procs}
                            <optgroup label="{__($category_name)}">
                                {foreach $payment_procs as $processor}
                                    <option value="{$processor.processor_id}" {if $payment.processor_id == $processor.processor_id}selected="selected"{/if} {if $processor.processor_status == "D"}disabled="disabled"{/if}>{$processor.processor}</option>
                                {/foreach}
                            </optgroup>
                            {/if}
                        {/foreach}
                    {/hook}
                </select>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                {if fn_check_permissions("addons", "manage", "admin")}
                    <div class="well well-small help-block">
                        {__("tools_addons_additional_payment_methods", ["[url]" => "addons.manage?type=not_installed"|fn_url])}
                    </div>
                {/if}
                <p id="elm_processor_description_{$id}" class="muted description {if !$payment_processors[$payment.processor_id].description}hidden{/if}">
                    {$payment_processors[$payment.processor_id].description nofilter}
                </p>
            </div>
        </div>

        <div class="control-group" data-ca-form-group="tpl">
            <label class="control-label" for="elm_payment_tpl_{$id}">{__("template")}:</label>
            <div class="controls">
                <select id="elm_payment_tpl_{$id}" name="payment_data[template]" {if $payment.processor_id}disabled="disabled"{/if}>
                    <option value="views/orders/components/payments/empty.tpl">{__("none")}</option>
                    {foreach $templates as $template => $full_path}
                        {if not $full_path|strpos:"empty.tpl"}
                            <option value="{$full_path}" {if $payment.template == $full_path}selected="selected"{/if}>{$template}</option>
                        {/if}
                    {/foreach}
                </select>
                <p class="muted description">{__("tt_views_payments_update_template")}</p>
            </div>
        </div>

        {if !"ULTIMATE:FREE"|fn_allowed_for}
            <div class="control-group" data-ca-form-group="usergroup">
                <label class="control-label">{__("usergroups")}:</label>
                <div class="controls">
                    {include file="common/select_usergroups.tpl" id="elm_payment_usergroup_`$id`" name="payment_data[usergroup_ids]" usergroups=$usergroups usergroup_ids=$payment.usergroup_ids list_mode=false}
                </div>
            </div>
        {/if}

        <div class="control-group" data-ca-form-group="description">
            <label class="control-label" for="elm_payment_description_{$id}">{__("description")}:</label>
            <div class="controls">
                <input id="elm_payment_description_{$id}" type="text" name="payment_data[description]" value="{$payment.description}">
            </div>
        </div>

        {hook name="payments:update"}
        {/hook}

        <div data-ca-form-group="update_divider"></div>

        <div class="control-group" data-ca-form-group="surcharge">
            <label class="control-label" for="elm_payment_surcharge_{$id}">{__("surcharge")}:</label>
                <div class="controls">
                    <input id="elm_payment_surcharge_{$id}" type="text" name="payment_data[p_surcharge]" class="input-mini" value="{$payment.p_surcharge}" size="4"> % + <input type="text" name="payment_data[a_surcharge]" value="{$payment.a_surcharge}" class="input-mini" size="4"> {$currencies.$primary_currency.symbol nofilter}</div>
        </div>

        <div class="control-group" data-ca-form-group="surcharge_title">
            <label class="control-label" for="elm_payment_surcharge_title_{$id}">{__("surcharge_title")}:</label>
            <div class="controls">
                <input id="elm_payment_surcharge_title_{$id}" type="text" name="payment_data[surcharge_title]" value="{$payment.surcharge_title}">
                <p class="muted description">{__("tt_views_payments_update_surcharge_title")}</p>
            </div>
        </div>

        <div class="control-group" data-ca-form-group="taxes">
        <label class="control-label">{__("taxes")}:</label>
            <div class="controls">
                {foreach from=$taxes item="tax"}
                    <label for="elm_payment_taxes_{$tax.tax_id}" class="checkbox">
                        <input type="checkbox" name="payment_data[tax_ids][{$tax.tax_id}]" id="elm_payment_taxes_{$tax.tax_id}" {if $tax.tax_id|in_array:$payment.tax_ids}checked="checked"{/if} value="{$tax.tax_id}">
                        {$tax.tax}
                    </label>
                {foreachelse}
                    <div class="text-type-value">&mdash;</div>
                {/foreach}
                {if fn_allowed_for("MULTIVENDOR")}<p class="muted description">{__("tt_views_payments_update_taxes")}</p>{/if}
            </div>
        </div>

        <div class="control-group" data-ca-form-group="instructions">
            <label class="control-label" for="elm_payment_instructions_{$id}">{__("payment_instructions")}:</label>
            <div class="controls">
                <textarea id="elm_payment_instructions_{$id}" name="payment_data[instructions]" cols="55" rows="8" class="cm-wysiwyg input-textarea-long">{$payment.instructions}</textarea>
            </div>
            
        </div>

        <div data-ca-form-group="status">
        {if !$id}
            {include file="common/select_status.tpl" input_name="payment_data[status]" id="elm_payment_status_`$id`" obj_id=$id obj=$payment}
        {/if}
        </div>

        {include file="views/localizations/components/select.tpl" data_name="payment_data[localization]" id="elm_payment_localization_`$id`" data_from=$payment.localization}

        <div class="control-group" data-ca-form-group="icon">
            <label class="control-label">{__("icon")}:</label>
            <div class="controls">{include file="common/attach_images.tpl" image_name="payment_image" image_key=$id image_object_type="payment" image_pair=$payment.icon no_detailed="Y" hide_titles="Y" image_object_id=$id}</div>
        </div>

        {hook name="payments:properties"}
        {/hook}
    </fieldset>
    <!--content_tab_details_{$id}--></div>

    {if fn_allowed_for("MULTIVENDOR:ULTIMATE")|| $is_sharing_enabled}
        <div class="hidden" id="content_tab_storefronts_{$id}">
            {$add_storefront_text = __("add_storefronts")}
            {include file="pickers/storefronts/picker.tpl"
                multiple=true
                input_name="payment_data[storefront_ids]"
                item_ids=$payment.storefront_ids
                data_id="storefront_ids"
                but_meta="pull-right"
                no_item_text=__("all_storefronts")
                but_text=$add_storefront_text
                view_only=($is_sharing_enabled && $runtime.company_id)
            }
        <!--content_tab_storefronts_{$id}--></div>
    {/if}

    {hook name="payments:tabs_extra_content"}
        <div id="content_tab_conf_{$id}">
            {hook name="payments:tabs_content"}
            {/hook}
            <!--content_tab_conf_{$id}-->
        </div>
    {/hook}
</div>

{if !$hide_for_vendor}
    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_name="dispatch[payments.update]" cancel_action="close" save=$id cancel_meta="bulkedit-unchanged"}
    </div>
{/if}

</form>
<!--content_group{$id}--></div>
