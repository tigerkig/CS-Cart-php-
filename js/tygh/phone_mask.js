(function (_, $) {
  var mask_list;
  var is_custom_format;
  $.ceEvent('on', 'ce.commoninit', function (context) {
    is_custom_format = !!_.call_phone_mask;
    var $phone_elems = context.find('.cm-mask-phone'),
        phone_validation_mode = _.phone_validation_mode || 'international_format',
        is_international_format = phone_validation_mode === 'international_format',
        is_any_digits = phone_validation_mode === 'any_digits';

    if (!$phone_elems.length || is_international_format && !window.localStorage) {
      return;
    }

    if (is_international_format || is_custom_format) {
      loadPhoneMasks().then(function (phone_masks) {
        _.phone_masks_list = phone_masks; // backward compatibility

        _.call_requests_phone_masks_list = _.phone_masks_list;
        mask_list = $.masksSort(_.phone_masks_list, ['#'], /[0-9]|#/, "mask");
        var mask_opts = {
          inputmask: {
            definitions: {
              '#': {
                validator: "[0-9]",
                cardinality: 1
              }
            },
            showMaskOnHover: false,
            autoUnmask: false,
            onKeyDown: function onKeyDown() {
              $(this).trigger('_input');
            }
          },
          match: /[0-9]/,
          replace: '#',
          list: mask_list,
          listKey: "mask"
        };
        $phone_elems.each(function (index, elm) {
          if (is_custom_format && $(elm).data('enableCustomMask')) {
            $(elm).inputmask({
              mask: _.call_phone_mask,
              showMaskOnHover: false,
              autoUnmask: false,
              onKeyDown: function onKeyDown() {
                $(this).trigger('_input');
              }
            });
          } else {
            $(elm).inputmasks(mask_opts);
          }

          $(elm).addClass('js-mask-phone-inited');

          if ($(elm).val()) {
            $(elm).oneFirst('keypress keydown', function () {
              if (!validatePhone($(elm))) {
                $(elm).trigger('paste');
              }
            });
            $(elm).prop('defaultValue', $(elm).val());
          }
        });
      });
      $.ceFormValidator('registerValidator', {
        class_name: 'cm-mask-phone-label',
        message: _.tr('error_validator_phone_mask'),
        func: function func(id) {
          return validatePhone($('#' + id));
        }
      });
    } else if (is_any_digits) {
      $.ceFormValidator('registerValidator', {
        class_name: 'cm-mask-phone-label',
        message: _.tr('error_validator_phone'),
        func: function func(elm_id, elm, lbl) {
          return $.is.blank(elm.val()) || $.is.phone(elm.val());
        }
      });
    }
  });

  function validatePhone($input) {
    if ($.is.blank($input.val()) || !$input.hasClass('js-mask-phone-inited')) {
      return true;
    }

    var mask_is_valid = false;

    if (is_custom_format && $input.data('enableCustomMask')) {
      mask_is_valid = _toRegExp(_.call_phone_mask).test($input.val());
    } else {
      mask_list.forEach(function (mask) {
        mask_is_valid = mask_is_valid || _toRegExp(mask.mask).test($input.val());
      });
    }

    return mask_is_valid && $input.inputmask("isComplete");

    function _toRegExp(mask) {
      var _convertedMask = mask.str_replace('#', '.').str_replace('+', '\\+').str_replace('(', '\\(').str_replace(')', '\\)').str_replace('9', '[0-9]').str_replace('\\[0-9]', '9');

      return new RegExp(_convertedMask);
    }
  }

  function loadPhoneMasks() {
    var oldHashOfAvailableCountries = window.localStorage.getItem('availableCountriesHash'),
        newHashOfAvailableCountries = _.hash_of_available_countries,
        rawPhoneMasks = window.localStorage.getItem('phoneMasks'),
        phoneMasks,
        d = $.Deferred();

    if (rawPhoneMasks) {
      phoneMasks = JSON.parse(rawPhoneMasks);
    }

    if (!phoneMasks || newHashOfAvailableCountries !== undefined && oldHashOfAvailableCountries !== newHashOfAvailableCountries) {
      $.ceAjax('request', fn_url('phone_masks.get_masks'), {
        method: 'get',
        caching: false,
        data: {},
        callback: function callback(response) {
          if (!response || !response.phone_mask_codes) {
            return;
          }

          $.ceEvent('trigger', 'ce.phone_masks.masks_loaded', [response]);
          phoneMasks = Object.keys(response.phone_mask_codes).map(function (key) {
            return response.phone_mask_codes[key];
          });
          window.localStorage.setItem('phoneMasks', JSON.stringify(phoneMasks));
          d.resolve(phoneMasks);
        },
        repeat_on_error: false,
        hidden: true,
        pre_processing: function pre_processing(response) {
          if (response.force_redirection) {
            delete response.force_redirection;
          }

          return false;
        },
        error_callback: function error_callback() {
          d.reject();
        }
      });
      window.localStorage.setItem('availableCountriesHash', newHashOfAvailableCountries);
    } else {
      d.resolve(phoneMasks);
    }

    return d.promise();
  }
})(Tygh, Tygh.$);