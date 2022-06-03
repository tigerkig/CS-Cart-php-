(function (_, $) {
  var isDisabledFields = $('#content_shipping_charges').hasClass('cm-hide-inputs');
  $(_.doc).on('click', '.shipping-rate-tools__add-table', function () {
    var data = $(this).data();
    fn_add_table_conditions($('#tables_rate_condition_' + data.destinationId), data);
    $(this).addClass('hidden');
    $(this).next().removeClass('hidden');
  });
  $(_.doc).on('click', '.shipping-rate-tools__remove-table', function () {
    if (confirm(_.tr('text_are_you_sure_to_proceed')) != false) {
      var data = $(this).data();
      $('#table_' + data.destinationId + '_' + data.type).remove();
      $(this).addClass('hidden');
      $(this).prev().removeClass('hidden');

      if ($('#shipping_rate_' + data.destinationId + ' .shipping-rate__table-condition').length === 0) {
        $('#shipping_rate_' + data.destinationId + ' .shipping-rate__add-conditions').removeClass('hidden');
        $('#shipping_rate_' + data.destinationId + ' .shipping-rate__not-empty-conditions-tool').addClass('hidden');
      } else {
        fn_set_range(data.destinationId);
      }
    }

    return false;
  });
  $(_.doc).on('click', '.shipping-rate__add-conditions', function () {
    var data = $(this).closest('.shipping-rate__button-list').data();
    var typesConditions = data.typesConditions.split(',');

    if ($('#shipping_rate_' + data.destinationId + ' .shipping-rate__table-condition').length === 0) {
      for (type in typesConditions) {
        if ($('#table_' + data.destinationId + '_' + typesConditions[type]).length === 0) {
          fn_add_table_conditions($('#tables_rate_condition_' + data.destinationId), {
            destinationId: data.destinationId,
            type: typesConditions[type]
          });
          $('#shipping_rate_' + data.destinationId + ' .shipping-rate-tools__add-table[data-type="' + typesConditions[type] + '"]').addClass('hidden');
          $('#shipping_rate_' + data.destinationId + ' .shipping-rate-tools__remove-table[data-type="' + typesConditions[type] + '"]').removeClass('hidden');
        }
      }

      $('#tables_rate_condition_' + data.destinationId).removeClass('hidden');
    } else {
      $('#tables_rate_condition_' + data.destinationId).toggleClass('hidden');
    }
  });
  $(_.doc).on('change', '.shipping-rate__input-append .cm-item', function () {
    if (this.checked) {
      $(this).closest('.btn-group').find('.text').text($(this).parent('li').text());
    } else {
      $(this).closest('.btn-group').find('.text').text('');
    }
  });
  $(_.doc).on('change', '.shipping-rate__surcharge-discount', function () {
    var data = $(this).data();
    fn_set_range(data.destinationId);
  });
  $(_.doc).on('click', '.shipping-rate__show-conditions', function () {
    var data = $(this).closest('.shipping-rate__button-list').data();
    $(this).addClass('hidden');
    $('#shipping_rate_' + data.destinationId + ' .shipping-rate__hide-conditions').removeClass('hidden');
    $('#tables_rate_condition_' + data.destinationId).removeClass('hidden');
  });
  $(_.doc).on('click', '.shipping-rate__hide-conditions', function () {
    var data = $(this).closest('.shipping-rate__button-list').data();
    $(this).addClass('hidden');
    $('#shipping_rate_' + data.destinationId + ' .shipping-rate__show-conditions').removeClass('hidden');
    $('#tables_rate_condition_' + data.destinationId).addClass('hidden');
  });
  $.ceEvent('on', 'ce.formcheckfailed_shippings_form', function ($form) {
    $('.shipping-rate').each(function () {
      var hasError = $(this).find('.control-group.error').length > 0;

      if (hasError) {
        $(this).find('.shipping-rate__hide-conditions').removeClass('hidden');
        $(this).find('.tables-rate-condition').removeClass('hidden');
        $(this).find('.shipping-rate__show-conditions').addClass('hidden');
        $(this).find('.shipping-rate__add-conditions').addClass('hidden');
      }
    });
  });
  $.ceEvent('on', 'ce.object_picker.selection_before_update', function (object) {
    var Utils = $.fn.select2.amd.require('select2/utils');

    $(object.options.externalContainerSelector).children().each(function () {
      var data = Utils.GetData($(this)[0], 'data');

      if (!data) {
        return;
      }

      fn_render_surcharge_conditions(data);
    });
    fn_init_autonumeric();
  });
  $.ceEvent('on', 'ce.object_picker.object_selected', function (object) {
    fn_init_autonumeric();
  });
  $.ceFormValidator('registerValidator', {
    class_name: 'shipping-rate-range-label',
    message: _.tr('rate_range_overlap_error_message'),
    func: function func(id) {
      return validate_overlap_shipping_rate_ranges(id);
    }
  });
  $.ceFormValidator('registerValidator', {
    class_name: 'shipping-rate-range-start-label',
    message: _.tr('rate_range_limit_error_message'),
    func: function func(id) {
      return validate_limits_shipping_rate_ranges(id);
    }
  });

  function fn_render_surcharge_conditions(template) {
    var destinationId = template.id;

    for (rate in template.data.rate_value) {
      var $block = $('#tables_rate_condition_' + destinationId);

      if ($block.length > 0 && $block.find('#table_' + destinationId + '_' + rate).length === 0) {
        fn_add_table_conditions($block, {
          type: rate,
          conditionName: "price",
          destinationId: destinationId
        }, template.data.rate_value[rate]);
        $block.prev('.shipping-rate__container').find('.shipping-rate-tools__add-table[data-type="' + rate + '"]').addClass('hidden');
        $block.prev('.shipping-rate__container').find('.shipping-rate-tools__remove-table[data-type="' + rate + '"]').removeClass('hidden');

        if (Object.keys(template.data.rate_value).length > 0) {
          fn_set_range(destinationId);
        }
      }
    }
  }

  function fn_set_range(destinationId) {
    var surchargeDiscountValues = [],
        range = '';
    $('#tables_rate_condition_' + destinationId + ' .shipping-rate__table-condition .shipping-rate__surcharge-discount').each(function () {
      var value = parseFloat($(this).autoNumeric('get'));

      if (!isNaN(value)) {
        surchargeDiscountValues.push({
          valueString: $(this).val(),
          value: value
        });
      }
    });

    if (surchargeDiscountValues.length > 0) {
      var minValue = surchargeDiscountValues.reduce(function (prev, curr) {
        return prev.value < curr.value ? prev : curr;
      });
      maxValue = surchargeDiscountValues.reduce(function (prev, curr) {
        return prev.value > curr.value ? prev : curr;
      });
      range = minValue.value === maxValue.value ? minValue.valueString : minValue.valueString + ' ... ' + maxValue.valueString;
      $('#shipping_rate_' + destinationId + ' .shipping-rate__range').text(range);
    }

    if (surchargeDiscountValues.length > 0) {
      $('#shipping_rate_' + destinationId + ' .shipping-rate__add-conditions').addClass('hidden');

      if ($('#tables_rate_condition_' + destinationId).hasClass('hidden')) {
        $('#shipping_rate_' + destinationId + ' .shipping-rate__show-conditions').removeClass('hidden');
      } else {
        $('#shipping_rate_' + destinationId + ' .shipping-rate__hide-conditions').removeClass('hidden');
      }
    } else {
      $('#shipping_rate_' + destinationId + ' .shipping-rate__add-conditions').removeClass('hidden');
      $('#shipping_rate_' + destinationId + ' .shipping-rate__show-conditions').addClass('hidden');
      $('#shipping_rate_' + destinationId + ' .shipping-rate__hide-conditions').addClass('hidden');
    }
  }

  function fn_add_table_conditions($block, data, rateValues) {
    var typeCondition = data.type,
        rateValuesKeys = rateValues ? Object.keys(rateValues) : [];
    var $newConditionTable = $('<table><thead><th>' + _.tr(typeCondition + '_condition_name') + '</th><th>' + _.tr('surcharge_discount_name') + '</th><th></th></thead><tbody></tbody></table>').attr({
      id: 'table_' + data.destinationId + '_' + typeCondition
    }).addClass('table table-middle table--relative shipping-rate__table-condition');

    if (rateValues && rateValuesKeys.length > 0) {
      rateValuesKeys.sort().forEach(function (key) {
        data.rateValue = rateValues[key];
        fn_add_tr_to_table_conditions(data, $newConditionTable, false);
      });
      delete data.rateValue;

      if (!isDisabledFields) {
        fn_add_tr_to_table_conditions(data, $newConditionTable);
      }
    } else {
      fn_add_tr_to_table_conditions(data, $newConditionTable);
    }

    $block.append($newConditionTable);
    fn_init_autonumeric();
  }

  function fn_add_tr_to_table_conditions(data, table) {
    var isLast = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

    var $templateTableTr = document.querySelector('#template_table_row'),
        $clone = $templateTableTr.content.cloneNode(true),
        index = table.find('tbody tr').length,
        unit = _.tr(data.type + '_unit');

    data.index = index;
    data.perUnit = _.tr('per') + unit;
    data.unit = unit;
    data.placeholderFrom = isLast ? _.tr('from') + ' (' + unit + ')' : '0';
    data.placeholderTo = isLast ? _.tr('to') + ' (' + unit + ')' : _.tr('shipping_and_up');
    data.currencySymbolPlacement = data.type === 'C' && !_.currencies_after ? 'p' : 's';

    if (isLast) {
      $($clone).find('.cm-delete-row').addClass('hidden');
    }

    table.find('tbody').append(fn_render_template(data, $($clone).find('tbody').html()));

    if (data.rateValue && data.rateValue.per_unit === 'Y') {
      var $elem = table.find('#shipping_rate_' + data.destinationId + '_per_unit_' + data.index);
      $elem.prop('checked', true);
      table.find('.shipping-rate_' + data.index + '_per-unit').find('.text').text(data.perUnit);
    }

    if (!isDisabledFields) {
      fn_init_autonumeric();

      if (isLast) {
        fn_add_event_to_tr_table_conditions(table, data);
      }
    }
  }

  function fn_render_template(data, template) {
    var templater = new Function('data', "return `".concat(template, "`;"));
    return templater(data);
  }

  function fn_init_autonumeric() {
    $('.cm-numeric').autoNumeric('init');
  }

  function fn_add_event_to_tr_table_conditions(tableCondition, data) {
    $('body').one('keyup', '#' + tableCondition.attr('id') + ' tr.table-rate__row:last-child() input.cm-numeric', function () {
      $(this).closest('tr').find('.cm-delete-row').removeClass('hidden');
      fn_add_tr_to_table_conditions(data, tableCondition);
    });
  }

  function validate_overlap_shipping_rate_ranges(id) {
    var $currentBlock = $('#' + id).closest('.control-group'),
        $parent = $currentBlock.closest('tr'),
        currentFromValue = parseFloat($currentBlock.find('.shipping-rate-start-range').autoNumeric('get')),
        currentToValue = parseFloat($currentBlock.find('.shipping-rate-end-range').autoNumeric('get')),
        isValid = true;

    if ($parent.hasClass('cm-delete-row')) {
      return isValid;
    } //do not check range overlap if the range is not correct


    if (!isNaN(currentFromValue) && !isNaN(currentToValue) && currentFromValue > currentToValue) {
      return true;
    }

    $parent.siblings('tr:not(.cm-delete-row)').each(function () {
      var fromValue = parseFloat($(this).find('.shipping-rate-start-range').autoNumeric('get')),
          toValue = parseFloat($(this).find('.shipping-rate-end-range').autoNumeric('get'));

      if (isNaN(fromValue) && (currentFromValue < toValue || currentToValue <= toValue)) {
        isValid = false;
        return false;
      }

      if (isNaN(toValue) && (currentFromValue >= fromValue || currentToValue > fromValue)) {
        isValid = false;
        return false;
      }

      if (currentFromValue > fromValue && currentFromValue < toValue || currentToValue > fromValue && currentToValue < toValue) {
        isValid = false;
        return false;
      }
    });
    return isValid;
  }

  function validate_limits_shipping_rate_ranges(id) {
    var $startField = $('#' + id),
        startFieldValue = parseFloat($startField.autoNumeric('get')),
        $parent = $startField.closest('.shipping-rate-range'),
        endFieldValue = parseFloat($parent.find('.shipping-rate-end-range').autoNumeric('get'));

    if ($parent.closest('tr').hasClass('cm-delete-row')) {
      return true;
    }

    if (!isNaN(startFieldValue) && !isNaN(endFieldValue) && startFieldValue !== 0 && startFieldValue >= endFieldValue) {
      return false;
    }

    return true;
  }
})(Tygh, Tygh.$);