(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    var $elems = $('.cm-gdpr-tooltip', context);

    if ($elems.length) {
      $elems.each(function () {
        var target_elem_id = $(this).data('ceGdprTargetElem'),
            $target_elem = $('#' + target_elem_id);

        if ($target_elem.length) {
          $(this).appendTo('body');
          $target_elem.data('ceTooltipPosition', 'center').data('ceTooltipClass', 'ty-gdpr-tooltip ty-gdpr-tooltip--light').ceTooltip({
            tip: '#gdpr_tooltip_' + target_elem_id,
            tipClass: 'ty-gdpr-tooltip ty-gdpr-tooltip--light',
            use_dynamic_plugin: !Modernizr.touchevents,
            onShow: function onShow() {
              var $tip = this.getTip();

              if ($tip.position().left < 0) {
                $tip.css({
                  left: '0px'
                });
              }
            }
          }).on('touchstart', function () {
            $(this).data('tooltip').show();
          });
          $('#gdpr_tooltip_' + target_elem_id).find('.cm-gdpr-tooltip--close').on('touchstart', function () {
            $target_elem.data('tooltip').hide();
          });
        }
      });
    }
  });
})(Tygh, Tygh.$);