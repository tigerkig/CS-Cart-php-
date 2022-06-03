<div class="modal signin-modal">
    <form action="{""|fn_url}"
          method="post"
          name="recover_form"
          class=" cm-skip-check-items cm-check-changes"
    >
        <div class="modal-header">
            <h4>{__("recover_password")}</h4>
        </div>
        {if $action == "request"}
            <div class="modal-body">
                <p>{__("text_recover_password_notice")}</p>
                <label for="user_login">{__("email")}:</label>
                <input type="text"
                       name="user_email"
                       id="user_login"
                       size="20"
                       value=""
                />
            </div>
            <div class="modal-footer">
                {include file="buttons/button.tpl"
                         but_text=__("reset_password")
                         but_name="dispatch[auth.recover_password]"
                         but_role="button_main"
                }
            </div>
        {elseif $action == "recover"}
            <input type="hidden"
                   name="ekey"
                   value="{$ekey}"
            />
            <div class="modal-body">
                <p>{__("press_continue_to_recover_password")}</p>
            </div>
            <div class="modal-footer">
                {include file="buttons/button.tpl"
                         but_text=__("continue")
                         but_name="dispatch[auth.recover_password]"
                         but_role="button_main"
                }
            </div>
        {/if}
    </form>
</div>