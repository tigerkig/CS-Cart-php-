(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    var $addToCartCheckboxes = context.find('[data-ca-bulk-add-to-cart="checkbox"]'),
        $panel = context.find('[data-ca-bulk-add-to-cart="panel"]'),
        $addToCartButton = $panel.find('[data-ca-bulk-add-to-cart="button"]'),
        $textBtnPanel = $addToCartButton.find('[data-ca-bulk-add-to-cart="textBtnPanel"]');

    if (!$addToCartCheckboxes.length) {
      return;
    }

    $panel.on('bulk_add_to_cart:update', function () {
      var addToCartCheckedCheckboxesLength = $addToCartCheckboxes.filter(':checked').length;

      if (addToCartCheckedCheckboxesLength > 0) {
        $panel.addClass($panel.data('caBulkAddToCartShowClass'));
        $textBtnPanel.text(addToCartCheckedCheckboxesLength);
      } else {
        $panel.removeClass($panel.data('caBulkAddToCartShowClass'));
      }
    });
    $addToCartCheckboxes.on('click', function () {
      $panel.trigger('bulk_add_to_cart:update');
    });
    $addToCartButton.on('click', function () {
      var $addToCartCheckedCheckboxes = $addToCartCheckboxes.filter(':checked'),
          data = {};

      if (!$addToCartCheckedCheckboxes.length) {
        return false;
      }

      $addToCartCheckedCheckboxes.each(function () {
        var $checkbox = $(this),
            $form = $checkbox.closest('form'),
            $inputs = $checkbox.parent().find('[data-ca-bulk-add-to-cart="input"]');
        data[$checkbox.data('caBulkAddToCartName')] = $checkbox.val();
        data['notification_products_simple'] = $addToCartButton.data('caBulkAddToCartNotificationProductsSimple');
        $inputs.each(function () {
          var $elem = $(this),
              $alterElem = $form.find('[name="' + $elem.data('caBulkAddToCartName') + '"]');

          if ($alterElem.length) {
            data[$elem.data('caBulkAddToCartName')] = $alterElem.val();
          } else {
            data[$elem.data('caBulkAddToCartName')] = $elem.val();
          }
        });
      });
      $.ceAjax('request', $addToCartButton.data('caBulkAddToCartUrl'), {
        method: 'post',
        full_render: true,
        result_ids: 'cart_status*',
        data: data,
        callback: function callback() {
          $addToCartCheckedCheckboxes.prop('checked', false);
          $panel.trigger('bulk_add_to_cart:update');
        }
      });
    });
  });
})(Tygh, Tygh.$);