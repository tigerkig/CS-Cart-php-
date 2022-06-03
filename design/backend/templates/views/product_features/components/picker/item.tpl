<div class="object-picker__results-label object-picker__features-results-label object-picker__results-label--new">
    {if $icon|default:true}
        <div class="object-picker__results-label-icon-wrapper object-picker__features-results-label-icon-wrapper object-picker__results-label-icon-wrapper--new">
            <i class="object-picker__results-label-icon object-picker__features-results-label-icon object-picker__results-label-icon--new {$icon|default:"icon-plus-sign"}"></i>
        </div>
    {/if}
    {if $title_pre}
        <div class="object-picker__results-label-prefix object-picker__features-results-label-prefix object-picker__results-label-prefix object-picker__features-results-label-prefix--new">
            {$title_pre}
        </div>
    {/if}
    <div class="object-picker__results-label-body object-picker__features-results-label-body object-picker__results-label-body--new">
        {literal}${data.name}{/literal}
    </div>
    {if $title_post}
        <div class="object-picker__results-label-suffix object-picker__features-results-label-suffix object-picker__results-label-suffix--new">
            {$title_post}
        </div>
    {/if}
</div>