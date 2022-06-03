{capture name="tools_list"}
    {hook name="addons:action_buttons"}
        {if !$runtime.company_id && !"RESTRICTED_ADMIN"|defined}
            <li>
                {include file="common/popupbox.tpl"
                    id="upload_addon"
                    text=__("upload_addon")
                    title=__("upload_addon")
                    content=({include file="views/addons/components/upload_addon.tpl"})
                    act="edit"
                    link_class="cm-dialog-auto-size"
                    link_text=__("manual_installation")
                }
            </li>
        {/if}
        <li>
            {btn type="text"
                method="POST"
                text=__("tools_addons_disable_all")
                href="addons.tools?init_addons=none"
            }
        </li>
        <li>
            {btn type="text"
                method="POST"
                text=__("tools_addons_disable_third_party")
                href="addons.tools?init_addons=core"
            }
        </li>
    {/hook}
{/capture}
{dropdown content=$smarty.capture.tools_list}