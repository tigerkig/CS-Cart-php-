<div class="span16 mockup__mockups-container">
    <div class="span4 mockup">
        <div class="mockup__container">
            <div class="mockup__status-bar">

                <img src="{$images_dir}/addons/mobile_app/status_bar_example.png">

            </div>

            {include file="addons/mobile_app/components/atoms/navbar.tpl" is_button=true}

            <div 
                class="mockup__body body mockup__category screenBackgroundColor__background"
                style="min-height: 100%; max-height: 100%;"
            >

            </div>

        </div>
    </div>

    <div class="span8">
        {include file="common/subheader.tpl" title=__("mobile_app.section.navbar")}

        {include file="addons/mobile_app/components/inputs.tpl" input_name="navbar" inputs=$config_data.app_appearance.colors.navbar}
    </div>
</div>