<script async src="https://www.googletagmanager.com/gtag/js?id={$addons.google_analytics.tracking_code}"></script>
<script>
    // Global site tag (gtag.js) - Google Analytics
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());
    gtag('config', '{$addons.google_analytics.tracking_code}');
</script>

<script>
    (function(_, $) {
        // Setting up sending pageviews in Google analytics when changing the page dynamically(ajax)
        $.ceEvent('on', 'ce.history_load', function(url) {
            if (typeof(gtag) !== 'undefined') {

                // disabling page tracking by default
                gtag('config', '{$addons.google_analytics.tracking_code}', { send_page_view: false });

                // send pageview for google analytics
                gtag('event', 'page_view', {
                    page_path: url.replace('!', ''),
                    send_to: '{$addons.google_analytics.tracking_code}'
                });
            }
        });
    }(Tygh, Tygh.$));
</script>
