{$_addon = $smarty.request.addon}
{script src="js/tygh/fileuploader_scripts.js"}
{script src="js/tygh/backend/addons/update.js"}
{include file="views/profiles/components/profiles_scripts.tpl" states=1|fn_get_all_states}

{capture name="mainbox"}
<div id="content_group{$_addon}">

        {capture name="tabsbox"}

            {* General tab *}
            {include file="views/addons/components/detailed_page/tabs/addon_general.tpl"}

            {* Settings tab *}
            {include file="views/addons/components/detailed_page/tabs/addon_settings.tpl"}

            {* Information tab *}
            {include file="views/addons/components/detailed_page/tabs/addon_information.tpl"}

            {* Update tab *}
            {include file="views/addons/components/detailed_page/tabs/addon_update.tpl"}

            {* Subscription tab *}
            {include file="views/addons/components/detailed_page/tabs/addon_subscription.tpl"}

            {* Reviews tab *}
            {include file="views/addons/components/detailed_page/tabs/addon_reviews.tpl"}

        {/capture}

        {include file="common/tabsbox.tpl"
            content=$smarty.capture.tabsbox
            group_name=$runtime.controller
            active_tab=$smarty.request.selected_section
            track=true
        }

<!--content_group{$_addon}--></div>
{/capture}

{include file="common/mainbox.tpl"
    title=$addon.name
    content=$smarty.capture.mainbox
    sidebar=({include file="views/addons/components/detailed_page/sidebar/detailed_page_sidebar.tpl"})
    buttons=({include file="views/addons/components/detailed_page/header/addon_header_buttons.tpl"})
    select_storefront=true
    show_all_storefront=$show_all_storefront
    storefront_switcher_param_name="storefront_id"
}
