(function (_, $) {
  /**
   * Toggles save buttons on variations tabs of product update page
   */
  $.ceEvent('on', 'ce.tab.show', function () {
    var isVariationsTabActive = $('#variations').hasClass('active');
    $('.cm-product-save-buttons').toggleClass('hidden', isVariationsTabActive);
    $('#tools_variations_btn').toggleClass('hidden', !isVariationsTabActive);
  });
  $.ceEvent('on', 'ce.commoninit', function (context) {
    var $variation_manage_form = context.find('.js-manage-variation-products-form');

    if (!$variation_manage_form.length) {
      return;
    }

    var feature_select_map = {};
    context.find('.js-product-variation-feature').each(function () {
      var $select = $(this);
      feature_select_map[$select.data('caFeatureId')] = $select;
    });
    $variation_manage_form.on('mouseenter touchstart', '.js-product-variation-feature-item', function () {
      var $select = $(this),
          val = $select.val(),
          feature_id = $select.data('caFeatureId');

      if (!feature_select_map[feature_id] || $select.hasClass('js-loaded')) {
        return;
      }

      $select.empty();
      feature_select_map[feature_id].find('option').clone().appendTo($select);
      $select.addClass('js-loaded').val(val);
    });
  });
})(Tygh, Tygh.$);