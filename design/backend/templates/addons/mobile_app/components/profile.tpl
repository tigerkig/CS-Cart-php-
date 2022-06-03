<div class="span16 mockup__mockups-container">
    <div class="span4 mockup">
        <div class="mockup__container">
            <div class="mockup__status-bar">
                <img src="{$images_dir}/addons/mobile_app/status_bar_example.png">
            </div>

            {include file="addons/mobile_app/components/atoms/navbar.tpl" title="Profile"}

            <div 
                class="mockup__body body screenBackgroundColor__background"
                style="min-height: calc(100% - 65px); max-height: calc(100% - 65px);"
            >
                <div class="mockup__profile">
                    <div class="mockup__profile-tabs">
                        <ul class="tabs__container grayColor__background">
                            <li class="tabs__el">Settings</li>
                        </ul>

                        <div class="tabs__content tabs__content--settings">
                            <div class="mockup__profile-item menuItemsBorderColor">
                                <span class="mockup__profile-item-text menuTextColor">Language</span>
                                <span class="mockup__profile-item-value menuIconsColor">
                                    RU
                                    <i class="icon icon-angle-right"></i>
                                </span>
                            </div>

                            <div class="mockup__profile-item menuItemsBorderColor">
                                <span class="mockup__profile-item-text menuTextColor">Currency</span>
                                <span class="mockup__profile-item-value menuIconsColor">
                                    $
                                    <i class="icon icon-angle-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mockup__profile-tabs">
                        <ul class="tabs__container grayColor__background">
                            <li class="tabs__el">Buyer</li>
                        </ul>
                        <div class="tabs__content tabs__content--buyer">
                            <div class="mockup__profile-item menuItemsBorderColor">
                                <span class="mockup__profile-item-text">
                                    <i class="icon-user menuIconsColor"></i>
                                    <span class="menuTextColor">Profile</span>
                                </span>
                                <span class="mockup__profile-item-value">
                                    <i class="icon icon-angle-right menuIconsColor"></i>
                                </span>
                            </div>

                            <div class="mockup__profile-item menuItemsBorderColor">
                                <span class="mockup__profile-item-text">
                                    <i class="icon-list menuIconsColor"></i>
                                    <span class="menuTextColor">Orders</span>
                                </span>
                                <span class="mockup__profile-item-value">
                                    <i class="icon icon-angle-right menuIconsColor"></i>
                                </span>
                            </div>

                            <div class="mockup__profile-item menuItemsBorderColor">
                                <span class="mockup__profile-item-text">
                                    <i class="icon-signout menuIconsColor"></i>
                                    <span class="menuTextColor">Logout</span>
                                </span>
                                <span class="mockup__profile-item-value">
                                    <i class="icon icon-angle-right menuIconsColor"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mockup__profile-tabs">
                        <ul class="tabs__container grayColor__background">
                            <li class="tabs__el">Pages</li>
                        </ul>

                        <div class="tabs__content tabs__content--pages">
                            <div class="mockup__profile-item menuItemsBorderColor">
                                <span class="mockup__profile-item-text menuTextColor">Contacts</span>
                                <span class="mockup__profile-item-value">
                                    <i class="icon icon-angle-right menuIconsColor"></i>
                                </span>
                            </div>

                            <div class="mockup__profile-item menuItemsBorderColor">
                                <span class="mockup__profile-item-text menuTextColor">Returns and Exchanges</span>
                                <span class="mockup__profile-item-value">
                                    <i class="icon icon-angle-right menuIconsColor"></i>
                                </span>
                            </div>

                            <div class="mockup__profile-item menuItemsBorderColor">
                                <span class="mockup__profile-item-text menuTextColor">Payment and shipping</span>
                                <span class="mockup__profile-item-value">
                                    <i class="icon icon-angle-right menuIconsColor"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {include file="addons/mobile_app/components/atoms/bottom_tabs.tpl" selected="profile"}
        </div>
    </div>

    <div class="span8">
        {include file="common/subheader.tpl" title=__("mobile_app.section.profile")}

        {include file="addons/mobile_app/components/inputs.tpl" input_name="profile" inputs=$config_data.app_appearance.colors.profile}
    </div>
</div>
