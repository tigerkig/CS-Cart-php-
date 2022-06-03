{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="rma_properties_form" id="rma_properties_form">
<input type="hidden" name="property_type" value="{$smarty.request.property_type|default:$smarty.const.RMA_REASON}" />
<input type="hidden" name="redirect_url" value="{$config.current_url}">

{if $properties}
    {capture name="rma_properties_table"}
        <div class="table-responsive-wrapper longtap-selection">
            <table width="100%" class="table table-middle table--relative table-responsive">
            <thead
                    data-ca-bulkedit-default-object="true"
                    data-ca-bulkedit-component="defaultObject"
            >
            <tr>
                <th width="1%" class="center mobile-hide">
                    {include file="common/check_items.tpl"}

                    <input type="checkbox"
                           class="bulkedit-toggler hide"
                           data-ca-bulkedit-disable="[data-ca-bulkedit-default-object=true]"
                           data-ca-bulkedit-enable="[data-ca-bulkedit-expanded-object=true]"
                    />
                </th>
                <th width="7%">{__("position")}</th>
                <th width="35%">{if $smarty.request.property_type == "R"}{__("reason")}{else}{__("action")}{/if}</th>
                {if $smarty.request.property_type != $smarty.const.RMA_REASON}
                <th width="30%" class="center">{__("update_totals_and_inventory")}</th>
                {/if}
                <th width="10%">&nbsp;</th>
                <th width="10%" class="right">{__("status")}</th>
            </tr>
            </thead>
            {foreach $properties as $property}
            <tr class="cm-row-status-{$property.status|lower} cm-longtap-target"
                data-ca-longtap-action="setCheckBox"
                data-ca-longtap-target="input.cm-item"
                data-ca-id="{$property.property_id}"
            >
                <td width="1%" class="center mobile-hide">
                    <input type="checkbox" name="property_ids[]" value="{$property.property_id}" class="cm-item cm-item-status-{$property.status|lower} hide" />
                </td>
                <td width="7%" data-th="{__("position")}">
                    <input type="text" name="property_data[{$property.property_id}][position]" size="7" value="{$property.position}" class="input-hidden input-micro" />
                </td>
                <td width="35%" data-th="{if $smarty.request.property_type == "R"}{__("reason")}{else}{__("action")}{/if}">
                    <input type="text" name="property_data[{$property.property_id}][property]" size="35" value="{$property.property}" class="input-hidden input-xlarge" />
                </td>
                {if $smarty.request.property_type != $smarty.const.RMA_REASON}
                <td width="30%" class="center" data-th="{__("update_totals_and_inventory")}">
                    <input type="checkbox" value="{$property.update_totals_and_inventory}" {if $property.update_totals_and_inventory == "Y"}checked="checked"{/if} disabled="disabled" />
                </td>
                {/if}
                <td width="10%" class="nowrap right" data-th="{__("tools")}">
                    {capture name="tools_list"}
                    {if $smarty.request.property_type == $smarty.const.RMA_REASON}
                        {assign var="property_type" value=$smarty.request.property_type|default:$smarty.const.RMA_REASON}
                        <li>{btn type="list" class="cm-confirm" text=__("delete") href="rma.delete_property?property_id=`$property.property_id`&property_type=`$property_type`" method="POST"}</li>
                    {else}
                        <li class="disabled"><a class="undeleted-element cm-tooltip" title="{__("delete")}">{__("delete")}</a></li>
                    {/if}
                    {/capture}
                    <div class="hidden-tools">
                        {dropdown content=$smarty.capture.tools_list}
                    </div>
                </td>
                <td width="10%" class="right nowrap" data-th="{__("status")}">
                    {include file="common/select_popup.tpl" id=$property.property_id status=$property.status hidden="" object_id_name="property_id" table="rma_properties"}
                </td>
            </tr>
            {/foreach}
            </table>
        </div>
    {/capture}

    {include file="common/context_menu_wrapper.tpl"
        form="rma_properties_form"
        object="rma_properties"
        items=$smarty.capture.rma_properties_table
    }
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{capture name="buttons"}
    {capture name="tools_list"}
        <li>{btn type="list" text=__("rma_reasons") href="rma.properties?property_type=R"}</li>
        <li>{btn type="list" text=__("rma_actions") href="rma.properties?property_type=A"}</li>
        <li>{btn type="list" text=__("rma_request_statuses") href="statuses.manage?type=R"}</li>
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {if $smarty.request.property_type != $smarty.const.RMA_REASON}
        {$property_type_class = "btn-primary" }
    {/if}
    {if $properties}
        {include file="buttons/save.tpl" but_name="dispatch[rma.update_properties]" but_role="action" but_target_form="rma_properties_form" but_meta="cm-submit `$property_type_class`"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {if $smarty.request.property_type == $smarty.const.RMA_REASON}
        {capture name="add_new_picker"}
            <form action="{""|fn_url}" method="post" name="add_rma_properties_form" class="form-horizontal form-edit ">
                <input type="hidden" name="property_type" value="{$smarty.request.property_type|default:$smarty.const.RMA_REASON}" />

                <div class="tabs cm-j-tabs">
                    <ul class="nav nav-tabs">
                        <li id="tab_rma_new" class="cm-js active"><a>{__("general")}</a></li>
                    </ul>
                </div>

                <div class="cm-tabs-content" id="content_tab_rma_new">
                    <div class="control-group">
                        <label class="control-label cm-required" for="add_property_data">{if $smarty.request.property_type == $smarty.const.RMA_REASON}{__("reason")}{else}{__("action")}{/if}</label>
                        <div class="controls">
                            <input type="text" name="add_property_data[0][property]" id="add_property_data" size="35" value="" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="add_property_position">{__("position")}</label>
                        <div class="controls">
                            <input type="text" name="add_property_data[0][position]" id="add_property_position" size="7" value="" />
                        </div>
                    </div>
                    {include file="common/select_status.tpl" input_name="add_property_data[0][status]" id="add_property_data"}
                </div>

                <div class="buttons-container">
                    {include file="buttons/save_cancel.tpl" but_name="dispatch[rma.add_properties]" cancel_action="close"}
                </div>

            </form>
        {/capture}
        {include file="common/popupbox.tpl" id="add_new_reasons" text=__("new_reason") content=$smarty.capture.add_new_picker title=__("add_reason") act="general" icon="icon-plus"}
    {/if}
{/capture}

</form>

{/capture}
{if $smarty.request.property_type == $smarty.const.RMA_REASON}
    {include file="common/mainbox.tpl" title=__("rma_reasons") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_languages=true}
{else}
    {include file="common/mainbox.tpl" title=__("rma_actions") content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_languages=true}
{/if}