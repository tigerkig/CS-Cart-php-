<div class="span16 mockup__mockups-container">
    <div class="span4 mockup">
        <div class="mockup__container">
            <div class="mockup__status-bar">

                <img src="{$images_dir}/addons/mobile_app/status_bar_example.png">

            </div>

            <div 
                class="mockup__body body mockup__category screenBackgroundColor__background"
                style="min-height: calc(100% - 65px); max-height: calc(100% - 65px);"
            >

            </div>

            {include file="addons/mobile_app/components/atoms/bottom_tabs.tpl"}
        </div>
    </div>

    <div class="span8">
        {include file="common/subheader.tpl" title=__("mobile_app.section.bottom_tabs")}

        {include file="addons/mobile_app/components/inputs.tpl" input_name="bottom_tabs" inputs=$config_data.app_appearance.colors.bottom_tabs}
    </div>
</div>