{if $is_image && $show_hidpi_checkbox|default:true}
    <input type="hidden" name="is_high_res_{$var_name}" value="{$smarty.const.HIDPI_IS_HIGH_RES_FALSE}" id="is_high_res_{$id_var_name}_hidden" class="cm-image-field" />
    <label for="is_high_res_{$id_var_name}" class="hidpi-mark checkbox">
        <input type="checkbox" name="is_high_res_{$var_name}" value="{$smarty.const.HIDPI_IS_HIGH_RES_TRUE}" id="is_high_res_{$id_var_name}" {if $addons.hidpi.default_upload_high_res_image === "Y"}checked="checked"{/if} class="cm-image-field" />
        {__("hidpi.upload_high_res_image")} <i class="cm-tooltip icon-question-sign" title="{__("hidpi.upload_high_res_image.tooltip")}"></i>
    </label>
{/if}