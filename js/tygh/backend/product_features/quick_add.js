(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    var $blockElem = $('[data-ca-features-create-elem="block"]', context);

    if (!$blockElem.length) {
      return;
    }

    $blockElem.each(function () {
      initQuickAddForm($(this));
    });
  });

  function initQuickAddForm($block) {
    var $blockVariants = $block.find($block.data('caFeaturesCreateVariantsSelector')),
        $variantsData = $block.find($block.data('caFeaturesCreateVariantsDataSelector')),
        requestForm = $block.data('caFeaturesCreateRequestForm'),
        blockVariantsSelected = 0; // Create hidden input for submit form when creating variants

    $blockVariants.on('ce:object_picker:object_selected', function (event, objectPicker, selected, event2) {
      var $input = $('<input>').attr({
        type: 'hidden',
        name: 'feature_data[variants][' + blockVariantsSelected++ + '][variant]',
        value: selected.text,
        form: requestForm
      });
      $input.appendTo($variantsData);
      selected.data.$input = $input;
    }); // Delete hidden input for submit form when deleting variants

    $blockVariants.on('ce:object_picker:object_unselected', function (event, objectPicker, selected, event2) {
      if (selected.data.$input) {
        selected.data.$input.remove();
      }
    });
  }
})(Tygh, Tygh.$);