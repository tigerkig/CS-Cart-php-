{if $smarty.const.ACCOUNT_TYPE === "admin"}
    <div class="help-center hidden" data-ca-help-center="main">
        <div class="help-center__header">
            <div class="help-center__title">{__("help_center.help")}</div>
            <i class="help-center__close icon-remove"></i>
        </div>

        <div class="help-center__content">
        </div>

        <div class="help-center__footer">
        </div>
    </div>

    <script type="text/template" id="help_center_block" data-no-defer="true" data-no-execute="§">
        <div class="help-center__block {literal}${data.type_block}{/literal}">
            <div class="help-center__block-header">
                <div class="help-center__block-title">{literal}${data.name}{/literal}</div>
                {literal}
                    ${data.all_items_name
                    ? `
                        <a class="help-center__block-all-items" target="_blank" href="${data.all_items_url}">${data.all_items_name}</a>
                        <a class="help-center__block-all-items help-center__block-all-items--short" target="_blank" href="${data.all_items_url}">${data.all_items_name_short}</a>
                    ` : ``}
                {/literal}
            </div>
            <div class="help-center__block-content">
                <div class="help-center__block-items">

                </div>
                {literal}
                    ${data.is_lines_more_limit
                        ? `<a class="help-center__block-link help-center__block-link--show-more" href="#">${data.see_all_n_results}</a>`
                        : ``
                    }
                {/literal}
            </div>
        </div>
    </script>

    <script type="text/template" id="help_center_block_link" data-no-defer="true" data-no-execute="§">
        <div class="help-center__block-link {literal}${data.link_limit_class ? data.link_limit_class : ``}{/literal}">
            <a class="{literal}${data.image_url || data.icon ? `help-center__block-link--with-image` : ``}{/literal}" href="{literal}${data.url}{/literal}" target="_blank">
                <div class="help-center__block-link-image-container">
                    {literal}
                        ${data.image_url ? `<img class="help-center__block-link-image" src="${data.image_url}"/>` : ``}
                    {/literal}

                    <div class="help-center__block-link-image-container--time-indicator">
                        {literal}
                            ${data.time ? data.time : ``}
                        {/literal}
                    </div>
                </div>

                {literal}
                    ${data.icon ? `<i class="help-center__block-link-icon ${data.icon}"></i>` : ``}
                {/literal}

                {literal}${data.text}{/literal}
                <div class="help-center__block-link--label">
                    {literal}
                        ${data.data ? data.data : ``}
                    {/literal}
                </div>
            </a>
        </div>
    </script>
{/if}
