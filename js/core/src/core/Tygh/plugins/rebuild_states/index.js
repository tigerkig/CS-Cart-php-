import { Tygh } from "../..";
import $ from "jquery";

const _ = Tygh;

var options = {};
var init = false;

function _rebuildStates(section, elm) {
    elm = elm || $('.cm-state.cm-location-' + section).prop('id');
    var sbox = $('#' + elm).is('select') ? $('#' + elm) : $('#' + elm + '_d');
    var inp = $('#' + elm).is('input') ? $('#' + elm) : $('#' + elm + '_d');
    var default_state = inp.val();
    var cntr = $('.cm-country.cm-location-' + section).last();
    var cntr_disabled;
    var isFocusStates = sbox.is(":focus") || inp.is(":focus");

    if (cntr.length) {
        cntr_disabled = cntr.prop('disabled');
    } else {
        cntr_disabled = sbox.prop('disabled');
    }

    var country_code = (cntr.length) ? cntr.val() : options.default_country;

    sbox.prop('id', elm).prop('disabled', false).removeClass('hidden cm-skip-avail-switch');
    inp.prop('id', elm + '_d').prop('disabled', true).addClass('hidden cm-skip-avail-switch').val('');

    if (!inp.hasClass('disabled')) {
        sbox.removeClass('disabled');
    }
    let inpId = inp.attr('id');
    let sboxId = sbox.attr('id');
    if (options.states && options.states[country_code]) { // Populate selectbox with states
        sbox.find('option').each(function(i, option){
            var $option = $(option);
            if ($option.val() && $option.data('caRebuildStates') !== 'skip') {
                $option.remove();
            }
        });
        for (var i = 0; i < options.states[country_code].length; i++) {
            sbox.append('<option value="' + options.states[country_code][i]['code'] + '"' + (options.states[country_code][i]['code'] == default_state ? ' selected' : '') + '>' + options.states[country_code][i]['state'] + '</option>');
        }

        sbox.prop('id', elm).prop('disabled', false).removeClass('cm-skip-avail-switch');
        inp.prop('id', elm + '_d').prop('disabled', true).addClass('cm-skip-avail-switch');
        if (isFocusStates) {
            sbox.focus();
        }

        if (!inp.hasClass('disabled')) {
            sbox.removeClass('disabled');
        }
        $('label[for*=' + inpId + ']').addClass('cm-required');
        $('label[for*=' + sboxId + ']').addClass('cm-required');
    } else { // Disable states
        sbox.prop('id', elm + '_d').prop('disabled', true).addClass('hidden cm-skip-avail-switch');
        inp.prop('id', elm).prop('disabled', false).removeClass('hidden cm-skip-avail-switch').val(default_state);
        $('label[for=' + inpId + ']').removeClass('cm-required');
        $('label[for=' + sboxId + ']').removeClass('cm-required');
        
        if (isFocusStates) {
            inp.focus();
        }

        if (!sbox.hasClass('disabled')) {
            inp.removeClass('disabled');
        }
    }

    if (cntr_disabled == true) {
        sbox.prop('disabled', true);
        inp.prop('disabled', true);
    }

    $.ceEvent('trigger', 'ce.rebuild_states');
}

function _rebuildStatesInLocation() {
    var location_elm = $(this).prop('class').match(/cm-location-([^\s]+)/i);
    if (location_elm) {
        _rebuildStates(location_elm[1], $('.cm-state.cm-location-' + location_elm[1]).not(':disabled').last().prop('id'));
    }
}

export const methods = {
    init: function () {
        if ($(this).hasClass('cm-country')) {
            if (init == false) {
                $(_.doc).on('change', 'select.cm-country', _rebuildStatesInLocation);
                init = true;
            }
            $(this).trigger('change', {
                is_triggered_by_user: false
            });
        } else {
            _rebuildStatesInLocation.call(this);
        }
    }
}

/**
 * States field builder
 * @param {JQueryStatic} $ 
 */
export const ceRebuildStatesInit = function ($) {
    $.fn.ceRebuildStates = function (method) {
        var args = arguments;

        return $(this).each(function (i, elm) {
            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(args, 1));
            } else if (typeof method === 'object' || !method) {
                return methods.init.apply(this, args);
            } else {
                $.error('ty.rebuildstates: method ' + method + ' does not exist');
            }
        });
    };

    $.ceRebuildStates = function (action, params) {
        params = params || {};
        if (action == 'init') {
            options = params;
        }
    }
}
