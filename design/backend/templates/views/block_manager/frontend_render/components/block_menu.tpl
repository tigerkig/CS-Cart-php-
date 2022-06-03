{$is_block_enabled = $block.status === "A"}
<div class="bm-block-manager__menu-wrapper" data-ca-block-manager-menu-wrapper>
    <div class="bm-block-manager__menu" data-ca-block-manager-menu>
        <div class="bm-block-manager__handler">
            <i class="ty-icon-handler bm-block-manager__icon"></i>
        </div>
        <a href="{fn_url("block_manager.manage&selected_location={$parent_grid.location_id}&object_id={$block.snapping_id}&type=snapping", "A")}"
           class="bm-block-manager__btn bm-block-manager__properties"
           target="_blank"
        >
            <i class="ty-icon-cog bm-block-manager__icon"></i>
        </a>
        <button type="button"
                class="bm-block-manager__btn bm-block-manager__switch {if !$is_block_enabled}bm-block-manager__block--disabled{/if}"
                data-ca-block-manager-action="switch"
                data-ca-block-manager-switch="{if $is_block_enabled}false{else}true{/if}"
        >
            <i class="ty-icon-eye-open bm-block-manager__icon {if !$is_block_enabled}bm-block-manager__icon--hidden{/if}"
               data-ca-block-manager-switch-icon="show"
            ></i>
            <i class="ty-icon-eye-close bm-block-manager__icon {if $is_block_enabled}bm-block-manager__icon--hidden{/if}"
               data-ca-block-manager-switch-icon="hide"
            ></i>
        </button>
        <button type="button" class="bm-block-manager__btn bm-block-manager__up"
                data-ca-block-manager-action="move"
                data-ca-block-manager-move="up"
        >
            <i class="ty-icon-arrow-up bm-block-manager__icon"></i>
        </button>
        <button type="button"
                class="bm-block-manager__btn bm-block-manager__down"
                data-ca-block-manager-action="move"
                data-ca-block-manager-move="down"
        >
            <i class="ty-icon-arrow-down bm-block-manager__icon"></i>
        </button>
    </div>
    <div class="bm-block-manager__arrow-wrapper">
        <div class="bm-block-manager__arrow"></div>
    </div>
</div>
