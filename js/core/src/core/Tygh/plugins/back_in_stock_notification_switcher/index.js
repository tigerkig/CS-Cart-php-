import { Tygh } from "../..";
import $ from "jquery";

const _ = Tygh;

export const methods = {
    init: function () {
        const $self = $(this);

        $self.on('change', function() {
            const authUserId = $self.data('caAuthUserId'),
                  objPrefix  = $self.data('caProductObjectPrefix'),
                  objId      = $self.data('caProductId'),
                  isChecked  = $self.prop('checked');
    
            if (authUserId || !isChecked) {
                const isValidForm = $(`[name="product_form_${objPrefix}${objId}"]`).ceFormValidator('checkFields', true, false, true);
                
                if (isValidForm) {
                    const checked = isChecked ? 'Y' : 'N',
                          email   = $(`#product_notify_email_${objPrefix}${objId}`).length ? '&email=' + $(`#product_notify_email_${objPrefix}${objId}`).val() : '';
    
                    $.ceAjax('request', fn_url(`products.product_notifications?enable=${checked}&product_id=${objId}${email}`), {
                        caching: false,
                    });
                } else {
                    $self.prop('checked', !isChecked);
                }
            }
        });
    },
};

/**
 * BackInStockNotificationSwitcher
 * @param {JQueryStatic} $ 
 */
export const ceBackInStockNotificationSwitcherInit = function ($) {
    $.fn.ceBackInStockNotificationSwitcher = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('ty.backinstocknotificationswitcher: method ' + method + ' does not exist');
        }
    };
}