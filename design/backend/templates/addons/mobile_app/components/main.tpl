<div class="span16 mockup__mockups-container">
    <div class="span4 mockup">
        <div class="mockup__container">
            <div class="mockup__status-bar">

                <img src="{$images_dir}/addons/mobile_app/status_bar_example.png">

            </div>

            {include file="addons/mobile_app/components/atoms/navbar.tpl" title="Simtech"}

            <div class="mockup__body body screenBackgroundColor__background mockup__category" style="min-height: calc(100% - 65px); max-height: calc(100% - 65px);">
                <div class="mockup__carousel-container">
                    <img src="{$images_dir}/addons/mobile_app/king.jpg" class="mockup__carousel-img"/>
                </div> 
             
                <div class="categoriesBackgroundColor__background" style="margin-left: -10px; margin-right: -10px; padding: 10px 10px;">
                    <div class="mockup__category-container">
                        <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                            <p class="mockup__category-name categoryBlockTextColor">Electronics</p>
                        </div>
                        <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                            <p class="mockup__category-name categoryBlockTextColor">Computers</p>
                        </div>
                        <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                            <p class="mockup__category-name categoryBlockTextColor">Sports & Outdoors</p>
                        </div>
                        <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                            <p class="mockup__category-name categoryBlockTextColor">Apparel</p>
                        </div>
                        <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                            <p class="mockup__category-name categoryBlockTextColor">Books</p>
                        </div>
                        <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                            <p class="mockup__category-name categoryBlockTextColor">Music</p>
                        </div>
                        <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                            <p class="mockup__category-name categoryBlockTextColor">Movies & TV</p>
                        </div>
                        <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                            <p class="mockup__category-name categoryBlockTextColor">Video Games</p>
                        </div>
                        <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                            <p class="mockup__category-name categoryBlockTextColor">Office Supplies</p>
                        </div>
                    </div>
                </div>

                <h4 class="mockup__second-heading categoriesHeaderColor">Hot deals</h4>
                <div class="mockup__carousel-container">
                    <div class="mockup__carousel-product productBorderColor__border">
                        <p class="mockup__carousel-product-badge productDiscountColor__background borderRadius">Discount 17%</p>
                        <img src="{$images_dir}/addons/mobile_app/nokia.jpg" class="mockup__carousel-product-preview"/>
                        <p class="mockup__carousel-product-describe">
                            <span class="mockup__carousel-product-name">Apple iPad with Retina</span>
                            <span class="mockup__carousel-product-cost">$499.00</span>
                        </p>
                    </div>
                </div>

                <h4 class="mockup__second-heading categoriesHeaderColor">Sale</h4>
                <div class="mockup__carousel-container">
                    <div class="mockup__carousel-product productBorderColor__border">
                        <p class="mockup__carousel-product-badge productDiscountColor__background borderRadius">Discount 17%</p>
                        <img src="{$images_dir}/addons/mobile_app/led.jpg" class="mockup__carousel-product-preview"/>
                        <p class="mockup__carousel-product-describe">
                            <span class="mockup__carousel-product-name">LED 8800 Series Smart TV</span>
                            <span class="mockup__carousel-product-cost">$499.00</span>
                        </p>
                    </div>
                </div>

            </div>

            {include file="addons/mobile_app/components/atoms/bottom_tabs.tpl"}
        </div>
    </div>

    <div class="span8">
        {include file="common/subheader.tpl" title=__("mobile_app.section.main")}

        {include file="addons/mobile_app/components/inputs.tpl" input_name="other" inputs=$config_data.app_appearance.colors.other}
    </div>
</div>