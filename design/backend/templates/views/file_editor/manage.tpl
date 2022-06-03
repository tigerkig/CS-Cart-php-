{capture name="mainbox"}
    {$container_id = $smarty.request.container_id|default:"elfinder"}

    <script>
    (function(_, $) {
        $.getScript('js/lib/elfinder/js/elfinder.min.js')
            .done(function () {
                $.loadCss(['js/lib/elfinder/css/elfinder.min.css']);
                $.loadCss(['js/lib/elfinder/css/theme.css']);

                {if $smarty.const.CART_LANGUAGE != 'en'}
		    $.getScript("js/lib/elfinder/js/i18n/elfinder.{$smarty.const.CART_LANGUAGE}.js")
			.then(null, function() { return $.getScript("js/lib/elfinder/js/i18n/elfinder.LANG.js"); })
			.done(fn_init_elfinder);
                {else}
                    fn_init_elfinder();
                {/if}
            });

        function fn_init_elfinder() {
            var w = $.getWindowSizes();
            var options = $.extend(_.fileManagerOptions, {
                url: fn_url('elf_connector.manage?start_path={$smarty.request.path}&security_hash=' + _.security_hash),
                height: w.view_height - 190
            });
            $('#{$container_id}').elfinder(options);
        }
    }(Tygh, Tygh.$))
    </script>

    <div id={$container_id}></div>

{/capture}

{if $smarty.request.in_popup}
    {$smarty.capture.mainbox nofilter}
{else}
    {include file="common/mainbox.tpl" content=$smarty.capture.mainbox title=__("file_editor") buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar sidebar_position="left"}
{/if}
