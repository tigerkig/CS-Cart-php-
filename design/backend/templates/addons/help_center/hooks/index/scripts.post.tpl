{if $smarty.const.ACCOUNT_TYPE === "admin"}
    <script>
        (function (_, $) {
            $.extend(_, {
                help_center_server_url: '{$help_center_server_url}',
            });

            _.tr({
                all: '{__("all")|escape:"javascript"}',
                see_all_n_results: '{__("help_center.see_all_n_results")|escape:"javascript"}'
            });
        }(Tygh, Tygh.$));
    </script>
    {script src="js/addons/help_center/func.js"}
{/if}
