<div class="control-group setting-wide">
    {include file="common/widget_copy.tpl"
        widget_copy_title=__("customer_price_list.attention")
        widget_copy_text=__("customer_price_list.run_price_list_updating_by_cron")
        widget_copy_code_text = fn_get_console_command("php /path/to/cart/", $config.admin_index, [
            "dispatch" => "customer_price_list.runner",
            "company_id" => $runtime.company_id,
            "p"
        ])
    }

    {include file="common/widget_copy.tpl"
        widget_copy_title=__("tip")
        widget_copy_text=__("customer_price_list.check_requirements")
        widget_copy_code_text = fn_get_console_command("php /path/to/cart/", $config.admin_index, [
            "dispatch" => "customer_price_list.check"
        ])
    }
</div>