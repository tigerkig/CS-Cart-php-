{$selected = $selected|default:"home"}

<div class="mockup__bottom-tabs bottomTabsBackgroundColor">
    <span class="mockup__bottom-tabs-btn">
        <i class="icon-home {if $selected === "home"}bottomTabsSelectedIconColor{else}bottomTabsIconColor{/if}"></i>
        <div class="{if $selected === "home"}bottomTabsSelectedTextColor{else}bottomTabsTextColor{/if}">Home</div>
    </span>
    <span class="mockup__bottom-tabs-btn">
        <i class="fa fa-search fa-lg bottomTabsIconColor"></i>
        <div class="bottomTabsTextColor">Search</div>
    </span>
    <span class="mockup__bottom-tabs-btn">
        <span class="mockup__bottom-tabs-primary-badge bottomTabsPrimaryBadgeColor">2</span>
        <i class="icon-shopping-cart bottomTabsIconColor"></i>
        <div class="bottomTabsTextColor">Cart</div>
    </span>
    <span class="mockup__bottom-tabs-btn">
        <i class="icon-heart bottomTabsIconColor"></i>
        <div class="bottomTabsTextColor">Favorite</div>
    </span>
    <span class="mockup__bottom-tabs-btn">
        <i class="icon-user {if $selected === "profile"}bottomTabsSelectedIconColor{else}bottomTabsIconColor{/if}""></i>
        <div class="{if $selected === "profile"}bottomTabsSelectedTextColor{else}bottomTabsTextColor{/if}">Profile</div>
    </span>
</div>