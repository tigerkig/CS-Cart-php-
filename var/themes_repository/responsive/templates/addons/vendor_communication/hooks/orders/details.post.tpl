{if $auth.user_id}
    {include
        file="addons/vendor_communication/views/vendor_communication/components/new_thread_form.tpl"
        object_type=$smarty.const.VC_OBJECT_TYPE_ORDER
        object_id=$order_info.order_id
        company_id=$order_info.company_id
        redirect_url="`$config.current_url`&selected_section=vendor_communication"
        no_ajax=true
        product=false
    }
{else}
    {include file="addons/vendor_communication/views/vendor_communication/components/login_form.tpl"}
{/if}
