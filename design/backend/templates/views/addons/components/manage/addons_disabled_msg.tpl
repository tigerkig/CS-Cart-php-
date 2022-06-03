{if $settings.init_addons}
    <div class="alert alert-block addon-info-msg">
        <span>{__("tools_addons_disabled_msg")}</span>
        <form action="{""|fn_url}" method="post">
            <input type="hidden" name="dispatch" value="addons.tools">
            <button type="submit" class="btn btn-warning" name="init_addons" value="restore">
                {__("tools_re_enable_add_ons")}
            </button>
        </form>
    </div>
{/if}
