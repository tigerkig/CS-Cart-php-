<div class="sidebar-row addons-addon-name-search">
    <form action="{""|fn_url}" name="addons_search_form" method="get" class="{$form_meta} form--no-margin">
        <div class="controls">
            <input type="text"
                name="q"
                id="elm_addon"
                value="{$search.q}"
                autofocus
                class="input-full input--no-margin"
                placeholder="{__("search")}"
            />
            <button type="button" class="hidden addons-addon-name-search__remove" id="elm_addon_clear" title="{__("remove")}">
                <i class="icon icon-remove"></i>
            </button>
        </div>
    </form>
    <div class="muted description">
        {__("addons.search_description")}
    </div>
</div>