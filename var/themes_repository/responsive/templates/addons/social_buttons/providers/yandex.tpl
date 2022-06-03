{if $addons.social_buttons.yandex_enable == "Y" && $provider_settings.yandex.data}

{$provider_settings.yandex.data nofilter}	
<script class="cm-ajax-force">	
    (function(_, $) {	
        $.ceEvent('one', 'ce.commoninit', function () {	
            $('.ya-share2').attr('id', 'ya-share2');	
             if (typeof (Ya) != 'undefined') {	
                var share = Ya.share2('ya-share2');	
            }	
        });	
    }(Tygh, Tygh.$));	
</script>

{/if}
