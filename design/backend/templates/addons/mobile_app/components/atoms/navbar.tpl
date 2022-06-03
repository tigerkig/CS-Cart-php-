{$title = $title|default:"CS-Cart"}
{$uppercase_title = $uppercase_title|default:true}

<div class="mockup__navbar navBarBackgroundColor__background">
    {if $back_icon}
        <span class="mockup__navbar-left">
            <i class="fa fa-arrow-left navBarButtonColor navBarButtonFontSize"></i>
        </span>
    {/if}
    <span class="mockup__navbar-title navBarTextColor {if $uppercase_title}mockup__navbar-title--uppercase{/if}">
        {$title}
    </span>

    {if $is_button}
        <div class="mockup__navbar-btn-container">
            <span class="mockup__navbar-btn">
                <i class="icon-heart navBarButtonColor navBarButtonFontSize"></i>
            </span> 
            <span class="mockup__navbar-btn">
                <i class="icon-share-alt navBarButtonColor navBarButtonFontSize"></i>
            </span> 
        </div>
    {/if}
</div>