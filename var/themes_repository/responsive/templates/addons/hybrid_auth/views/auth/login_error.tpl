<script data-no-defer>

    var is_facebook_embedded_browser = /fbav/gi.test(window.navigator.userAgent),
        target_window                = is_facebook_embedded_browser ? window : opener;

    {if $redirect_url}
        var url = '{$redirect_url|escape:"javascript"}'.replace(/\&amp;/g,'&');
        target_window.location.href = url;
    {else}
        target_window.location.reload();
    {/if}

    close();

</script>
