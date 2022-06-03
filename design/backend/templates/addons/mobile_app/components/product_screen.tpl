<div class="span16 mockup__mockups-container">
    <div class="span4 mockup">
        <div class="mockup__container">
            <div class="mockup__status-bar">

                <img src="{$images_dir}/addons/mobile_app/status_bar_example.png">

            </div>

            {include file="addons/mobile_app/components/atoms/navbar.tpl" 
                is_button=true
                uppercase_title=false
                back_icon=true
                title="Mac OS X Lion: The Missing Manual"
            }

            <div class="mockup__body body screenBackgroundColor__background" style="min-height: calc(100% - 65px); max-height: calc(100% - 65px);">
                <div class="mockup__product-preview">
                    <img src="{$images_dir}/addons/mobile_app/product_preview.gif">
                </div>

                <div class="mockup__product-describes">
                    <p class="mockup__product-title darkColor">Mac OS X Lion: The Missing Manual</p>
                    <p class="mockup__product-rate">
                        <i class="fa fa-star fa-lg ratingStarsColor"></i>
                        <i class="fa fa-star fa-lg ratingStarsColor"></i>
                        <i class="fa fa-star fa-lg ratingStarsColor"></i>
                        <i class="fa fa-star fa-lg ratingStarsColor"></i>
                        <i class="fa fa-star-half fa-lg ratingStarsColor"></i>
                        <span style="color: #808080">1 reviews</span>
                    </p>
                    <p class="mockup__product-price darkColor">$34.99</p>
                    <p class="mockup__product-desc" style="color: #808080">For a company that promised to "put a pause on new features," Apple sure has been busy-there"s barely a feature left untouched in Mac OS X 10.6 "Snow Leopard."</p>
                    <div class="mockup__product-quantity">
                        <button class="mockup__product-quantity-btn" disabled>
                            <i class="icon-minus"></i>
                        </button>
                        <span class="mockup__product-quantity-text">1</span>
                        <button class="mockup__product-quantity-btn" disabled>
                            <i class="icon-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="mockup__product-tabs tabs">
                    <ul class="tabs__container grayColor__background SectionRow__border">
                        <li class="tabs__el">Reviews (1)</li>
                    </ul>

                    <div class="tabs__content tabs__content--review">
                        <p>
                            <span class="darkColor"><b>David</b></span>
                            <span style="float: right;">
                                <i class="fa fa-star fa-lg ratingStarsColor"></i>
                                <i class="fa fa-star fa-lg ratingStarsColor"></i>
                                <i class="fa fa-star fa-lg ratingStarsColor"></i>
                                <i class="fa fa-star fa-lg ratingStarsColor"></i>
                                <i class="fa fa-star-half fa-lg ratingStarsColor"></i>
                            </span>
                        </p>

                        <p class="discussionMessageColor">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Suscipit officiis voluptatum totam repudiandae eligendi iusto magnam cum mollitia corrupti esse, molestiae, cupiditate autem asperiores obcaecati est soluta commodi earum quia.</p>
                    </div>
                </div>

                <div class="mockup__product-tabs">
                    <ul class="tabs__container grayColor__background SectionRow__border">
                        <li class="tabs__el">Features</li>
                    </ul>

                    <div class="tabs__content tabs__content--features">
                        <p style="margin: 0;">
                            <span style="color: #595959"><b>Brand</b></span>
                            <span style="color: #595959; float: right;">Samsung </span>
                        </p>
                    </div>
                </div>

                <div class="mockup__product-tabs">
                    <ul class="tabs__container grayColor__background SectionRow__border">
                        <li class="tabs__el">Vendor</li>
                    </ul>

                    <div class="tabs__content tabs__content--vendors">
                        <p style="position: relative; color: #595959">
                            <span><b>Simtech</b><br /><span style="font-size: 11px;">245 items</span></span>
                            <span style="position: absolute; right: 0; top: 0;" class="primaryColor">Details</span>
                        </p>
                        <p style="color: #595959">The company that makes the best shopping cart software in the world</p>
                    </div>
                </div>

                <div class="mockup__product-link-to-store">
                    <span class="primaryColor">Go to store</span>
                </div>

                <br>
                <br>
                <br>
            </div>

            <div class="mockup__product-add-to-cart">
                <button class="mockup__product-add-to-cart--action primaryColorText primaryColor__background" disabled>Add to cart</button>
            </div>
            
            {include file="addons/mobile_app/components/atoms/bottom_tabs.tpl"}
        </div>
    </div>

    <div class="span8">
        {include file="common/subheader.tpl" title=__("mobile_app.section.product_screen")}

        {include file="addons/mobile_app/components/inputs.tpl" input_name="product_screen" inputs=$config_data.app_appearance.colors.product_screen}
    </div>
</div>