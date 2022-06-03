<div class="span16 mockup__mockups-container">
    <div class="span4 mockup">
        <div class="mockup__container">
            <div class="mockup__status-bar">

                <img src="{$images_dir}/addons/mobile_app/status_bar_example.png">

            </div>

            {include file="addons/mobile_app/components/atoms/navbar.tpl" back_icon=true uppercase_title=false}

            <div 
                class="mockup__body body categoriesBackgroundColor__background mockup__category"
                style="min-height: calc(100% - 65px); max-height: calc(100% - 65px);"
            >
                
                <h3 class="mockup__main-heading categoriesHeaderColor">Categories</h3>

                <div class="mockup__category-container">
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/cars.png" 
                            class="mockup__category-preview"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">Car Electronics</p>
                    </div>
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/tv.png" 
                            class="mockup__category-preview"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">TV & Video</p>
                    </div>
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/cell.png" 
                            class="mockup__category-preview"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">Cell Phones</p>
                    </div>
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/mp3.png" 
                            class="mockup__category-preview"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">MP3 Players</p>
                    </div>
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/camera.png" 
                            class="mockup__category-preview"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">Cameras & Photo</p>
                    </div>
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <p class="mockup__category-name categoryBlockTextColor">Game consoles</p>
                    </div>
                </div>

            </div>


            {include file="addons/mobile_app/components/atoms/bottom_tabs.tpl"}
        </div>
    </div>

    <div class="span8">
        {include file="common/subheader.tpl" title=__("mobile_app.section.category")}

        {include file="addons/mobile_app/components/inputs.tpl" input_name="categories" inputs=$config_data.app_appearance.colors.categories}
    </div>
</div>