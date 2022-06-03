{hook name="addons:adv_buttons"}
    {include file="buttons/button.tpl"
        but_href="{$config.resources.marketplace_url}"|fn_url
        but_text=__("visit_marketplace")
        but_meta="btn btn-primary"
        but_role="action"
        but_target="_blank"
    }
{/hook}
