{script src="js/addons/vendor_communication/backend/bulk_edit.js"}

{$show_subject_image_column = false}

{capture name="mainbox"}

    {assign var="c_icon" value="<i class=\"icon-`$search.sort_order_rev`\"></i>"}
    {assign var="c_dummy" value="<i class=\"icon-dummy\"></i>"}

    {include file="common/pagination.tpl"
        save_current_page=true
        save_current_url=true
        div_id=$smarty.request.content_id
    }

    {assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
    {assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}
    {assign var="show_vendor_col" value=$auth.user_type == "A" && !$runtime.company_id}



    {capture name="tabsbox"}
        <form action="{""|fn_url}" method="post" name="threads_list_form" id="threads_list_form" class="{if $runtime.company_id}cm-hide-inputs{/if}" id="threads_list_form">
            {if $threads}
                {foreach $threads as $thread}
                    {if $thread.object_type === $smarty.const.VC_OBJECT_TYPE_PRODUCT
                        || $thread.object_type === $smarty.const.VC_OBJECT_TYPE_COMPANY
                    }
                        {$show_subject_image_column = true}
                    {/if}
                {/foreach}

                <input type="hidden" name="communication_type" value="{$smarty.request.communication_type}"/>
                <input type="hidden" name="redirect_url" value="{$config.current_url}">
                {capture name="threads_list_table"}
                    <div class="table-responsive-wrapper longtap-selection">
                        <table width="100%" class="table table-middle table--relative table-responsive">
                            <thead
                                    data-ca-bulkedit-default-object="true"
                                    data-ca-bulkedit-component="defaultObject"
                            >
                            <tr>
                                {if !$runtime.company_id && $auth.user_type === "UserTypes::ADMIN"|enum}
                                    <th class="left" width="4%">
                                        {include file="common/check_items.tpl"}

                                        <input type="checkbox"
                                               class="bulkedit-toggler hide"
                                               data-ca-bulkedit-disable="[data-ca-bulkedit-default-object=true]"
                                               data-ca-bulkedit-enable="[data-ca-bulkedit-expanded-object=true]"
                                        />
                                    </th>
                                {/if}
                                <th width="2%" class="status-label">&nbsp;</th>
                                {if $show_subject_image_column}
                                    <th width="7%">&nbsp;</th>
                                {/if}
                                <th width="14%">
                                    <a class="cm-ajax"
                                       href="{"`$c_url`&sort_by=thread&sort_order=`$search.sort_order_rev`"|fn_url}"
                                       data-ca-target-id={$rev}>
                                        {__("id")}
                                        {if $search.sort_by == "thread"}
                                            {$c_icon nofilter}{else}{$c_dummy nofilter}
                                        {/if}
                                    </a>
                                </th>
                                <th width="30%">{__("message")} / {__("subject")}</th>
                                {if $search.communication_type == "Addons\\VendorCommunication\\CommunicationTypes::VENDOR_TO_CUSTOMER"|enum}
                                    <th width="19%">
                                        <a class="cm-ajax"
                                           href="{"`$c_url`&sort_by=name&sort_order=`$search.sort_order_rev`"|fn_url}"
                                           data-ca-target-id={$rev}>
                                            {__("customer")}
                                            {if $search.sort_by == "name"}
                                                {$c_icon nofilter}
                                            {else}
                                                {$c_dummy nofilter}
                                            {/if}
                                        </a>
                                    </th>
                                {/if}
                                {hook name="vendor_communication:manage_header"}{/hook}
                                <th width="8%">&nbsp;</th>
                                <th width="15%">
                                    <a class="cm-ajax"
                                       href="{"`$c_url`&sort_by=last_updated&sort_order=`$search.sort_order_rev`"|fn_url}"
                                       data-ca-target-id={$rev}>
                                        {__("date")}
                                        {if $search.sort_by == "last_updated"}
                                            {$c_icon nofilter}{else}{$c_dummy nofilter}
                                        {/if}
                                    </a>
                                </th>
                            </tr>
                            </thead>
                            {foreach $threads as $thread}
                                {$thread_href = "vendor_communication.view?thread_id=`$thread.thread_id`&communication_type=`$search.communication_type`"|fn_url}

                                {$has_new_message = $auth.user_id != $thread.last_message_user_id && $thread.user_status == $smarty.const.VC_THREAD_STATUS_HAS_NEW_MESSAGE}
                                <tr class="cm-longtap-target"
                                    data-ca-longtap-action="setCheckBox"
                                    data-ca-longtap-target="input.cm-item"
                                    data-ca-id="{$thread.thread_id}"
                                >
                                    {if !$runtime.compnay_id && $auth.user_type == "UserTypes::ADMIN"|enum}
                                        <td class="left mobile-hide">
                                            <input type="checkbox" name="thread_ids[]" value="{$thread.thread_id}" class="cm-item hide" />
                                        </td>
                                    {/if}
                                    <td>
                                        {if $has_new_message}
                                            <span class="status-new__label"></span>
                                        {/if}
                                    </td>
                                    {if $show_subject_image_column}
                                        <td class="{if $has_new_message}status-new__text{/if}" data-th="&nbsp;">
                                            {include file="addons/vendor_communication/views/vendor_communication/components/subject_image.tpl"
                                                thread=$thread
                                            }
                                        </td>
                                    {/if}
                                    <td class="{if $has_new_message}status-new__text{/if}" data-th="{__("id")}">
                                        <a href="{$thread_href}">
                                            <bdi>{__("vendor_communication.thread", ["[thread_id]" => $thread.thread_id])}</bdi>
                                        </a>
                                        {include file="views/companies/components/company_name.tpl" object=$thread}
                                    </td>
                                    <td class="{if $has_new_message}status-new__text{/if}" data-th="{__("message")} / {__("subject")}">
                                        <a href="{$thread_href}" class="no-link vendor-communication__message" title="{$thread.last_message}">
                                            <strong>
                                                {if $thread.last_message_user_id == $auth.user_id}
                                                    {__("vendor_communication.you")}:
                                                {elseif $thread.last_message_user_type === "UserTypes::ADMIN"|enum}
                                                    {__("vendor_communication.admin")}:
                                                {elseif $thread.last_message_user_type === "UserTypes::VENDOR"|enum}
                                                    {$thread.company}:
                                                {else}
                                                    {__("customer")}:
                                                {/if}
                                            </strong>
                                            {$thread.last_message|truncate:200:"...":true}
                                        </a>
                                        <div>
                                            {include file="addons/vendor_communication/views/vendor_communication/components/subject.tpl"
                                                thread=$thread
                                            }
                                        </div>
                                    </td>
                                    {if $search.communication_type == "Addons\\VendorCommunication\\CommunicationTypes::VENDOR_TO_CUSTOMER"|enum}
                                        <td class="{if $has_new_message}status-new__text{/if}" data-th="{__("customer")}">
                                            {if $auth.user_type == "A"}
                                                {if $thread.customer_email}<a href="mailto:{$thread.customer_email|escape:url}">@</a>{/if}
                                                <a href="{"profiles.update&user_id={$thread.user_id}"|fn_url}">
                                                    {$thread.firstname} {$thread.lastname}
                                                </a>
                                            {else}
                                                {$thread.firstname} {$thread.lastname}
                                            {/if}
                                        </td>
                                    {/if}
                                    {hook name="vendor_communication:manage_data"}{/hook}
                                    <td class="right">
                                        {capture name="tools_list"}
                                            {capture name="tools_delete"}
                                                <li>
                                                    {btn
                                                        type="list"
                                                        text=__("delete")
                                                        class="cm-confirm"
                                                        href="vendor_communication.delete_thread?thread_id=`$thread.thread_id`&communication_type=`$search.communication_type`"
                                                        method="POST"
                                                    }
                                                </li>
                                            {/capture}
                                            {if $auth.user_type == "A"}
                                                {$smarty.capture.tools_delete nofilter}
                                            {/if}
                                        {/capture}
                                        <div class="hidden-tools">
                                            {dropdown content=$smarty.capture.tools_list}
                                        </div>
                                    </td>
                                    <td class="nowrap {if $has_new_message}status-new__text{/if}" data-th="{__("date")}">
                                        <a href="{$thread_href}"  class="no-link">
                                            {$thread.last_updated|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
                                        </a>
                                    </td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
            {/capture}

            {include file="common/context_menu_wrapper.tpl"
                form="threads_list_form"
                object="vendor_communication_threads"
                items=$smarty.capture.threads_list_table
                is_check_all_shown=true
                communication_type=$communication_type
            }
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}

        </form>
        {include file="common/pagination.tpl" div_id=$smarty.request.content_id}
    {/capture}
    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$communication_type track=true}

{/capture}

{capture name="adv_buttons"}
    {assign var="_title" value=__("vendor_communication.message_center")}
{/capture}

{capture name="adv_buttons"}
    {if $search.communication_type == "Addons\\VendorCommunication\\CommunicationTypes::VENDOR_TO_ADMIN"|enum}
        {include
            file="addons/vendor_communication/views/vendor_communication/components/new_thread_button.tpl"
            but_icon="icon-plus"
            but_role="text"
            but_meta="btn btn-primary cm-dialog-opener"
        }
    {/if}
{/capture}

{capture name="sidebar"}
    {hook name="vendor_communication:manage_sidebar"}
        {include
            file="addons/vendor_communication/views/vendor_communication/components/thread_search_form.tpl"
            dispatch="vendor_communication.threads"
            period=$search.period
        }
    {/hook}
{/capture}

{include
    file="common/mainbox.tpl"
    title=$_title
    content=$smarty.capture.mainbox
    sidebar=$smarty.capture.sidebar
    adv_buttons=$smarty.capture.adv_buttons
    content_id="manage_threads"
}
