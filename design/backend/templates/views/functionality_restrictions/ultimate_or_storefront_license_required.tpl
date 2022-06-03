{if "ULTIMATE"|fn_allowed_for && $store_mode != "ultimate"}
    <div id="restriction_promo_dialog" class="restriction-promo">
        {$suffix = ""|fn_get_product_state_suffix}

        {__("max_storefronts_reached.`$suffix`", [
            "[product]" => $smarty.const.PRODUCT_NAME,
            "[ultimate_license_url]" => $config.resources.ultimate_license_url
        ])}
        <div class="restriction-promo__wrapper">

            <div class="restriction-features">
                <div class="restriction-feature restriction-feature_storefronts">
                    <h2>{__("ultimate_license", ["[product]" => $smarty.const.PRODUCT_NAME])}</h2>

                    {__("new_text_ultimate_license_required", [
                        "[product]" => $smarty.const.PRODUCT_NAME,
                        "[ultimate_license_url]" => $config.resources.ultimate_license_url
                    ])}

                </div>
            </div>

            <div class="center">
                <a class="restriction-update-btn" href="{$config.resources.ultimate_license_url}" target="_blank">{__("upgrade_license")}</a>
            </div>
        </div>

        <div class="restriction-promo__wrapper">

            <div class="restriction-features">
                <div class="restriction-feature restriction-feature_storefronts_plus">
                    <h2>{__("storefront_license")}</h2>

                    {__("text_storefront_license_required", [
                        "[product]" => $smarty.const.PRODUCT_NAME,
                        "[storefront_license_url]" => $config.resources.storefront_license_url
                    ])}

                </div>
            </div>

            <div class="center">
                <a class="restriction-update-btn" href="{$config.resources.storefront_license_url}" target="_blank">
                    {__("buy_new_storefront_license", [
                        "[product]" => ""
                    ])}
                </a>
            </div>
        </div>
    </div>
{/if}