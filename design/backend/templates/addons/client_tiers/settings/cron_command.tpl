<div class="control-group setting-wide">
    <label class="control-label">
        {__("client_tiers.update_tiers")}
    </label>
    <div class="controls">
        <p>
            <a href="{fn_url("client_tiers.recalculate?type={"\Tygh\Addons\ClientTiers\Enum\Calculation::MANUAL"|constant}")}" class="cm-ajax cm-post">
                {__("client_tiers.run_recalculation")}
            </a>
        </p>
        {include file="common/widget_copy.tpl"
            widget_copy_title=__("tip")
            widget_copy_text=__("client_tiers.run_tiers_updating_by_cron")
            widget_copy_code_text = fn_get_console_command("php /path/to/cart/", $config.admin_index, [
                "dispatch" => "client_tiers.recalculate",
                "p"
            ])
        }
    </div>
</div>