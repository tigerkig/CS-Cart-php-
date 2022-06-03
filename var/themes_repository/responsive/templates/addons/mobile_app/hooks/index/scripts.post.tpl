{script src="js/addons/mobile_app/lib/jquery.smartbanner.js"}

{$banner_title = __("mobile_app.we_also_have_a_mobile_app")}
{$banner_icon_url = $logos.favicon.image.image_path|fn_query_remove:"t"}
{$banner_button_text = __("mobile_app.view_demo")}
{if $mobile_app_settings.banner_icon_url}
    {$banner_icon_url = $mobile_app_settings.banner_icon_url}
    {$banner_button_text = __("mobile_app.open")}
{/if}

<script>
    (function (_, $) {
        $.smartbanner({
            daysHidden: 15,
            daysReminder: 90,
            appStoreLanguage: '{$smarty.const.CART_LANGUAGE}',
            title: '{$banner_title|escape:"javascript"}',
            author: '',
            button: '{$banner_button_text|escape:"javascript"}',
            icon: '{$banner_icon_url|escape:"javascript"}'
        });
    })(Tygh, Tygh.$);
</script>
