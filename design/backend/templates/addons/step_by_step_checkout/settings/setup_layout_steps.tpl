{if $is_backed_up}
    <div class="well">
        {__("step_by_step_checkout.check_layout_back_ups", [
            "[file_path]" => {$file_path}
        ])}
    </div>
{/if}
<p>
    {__("step_by_step_checkout.setup_layout_instruction")}
</p>
