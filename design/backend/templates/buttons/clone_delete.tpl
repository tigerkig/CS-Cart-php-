{if $href_clone}
    <a class="btn clone-item"
       title="{__("remove")}"
       href="{$href_clone|fn_url}"
    >
        <i class="icon-trash"></i>
    </a>
{/if}

{if $href_delete}
    <a {if $id}id="rm_{$id}"{/if}
       class="btn delete-item {if !$no_confirm}cm-confirm{/if}{if $microformats} {$microformats}{/if}"
       title="{__("remove")}"
       {if $href_delete}href="{$href_delete|fn_url}"{/if}
       {if $delete_target_id}data-ca-target-id="{$delete_target_id}"{/if}
    >
        <i class="icon-trash"></i>
    </a>
{/if}

{if !$href_delete && !$href_clone}
    <button type="button"
            class="btn delete-item {if !$no_confirm}cm-confirm{/if}{if $microformats} {$microformats}{/if}"
            title="{__("remove")}"
            {if $delete_target_id}data-ca-target-id="{$delete_target_id}"{/if}
    >
        <i class="icon-trash"></i>
    </button>
{/if}
