{script src="js/lib/inputmask/jquery.inputmask.min.js"}
{script src="js/lib/creditcardvalidator/jquery.creditCardValidator.js"}
{script src="js/tygh/sagepay_browser_settings.js"}

{if $card_id}
    {assign var="id_suffix" value="`$card_id`"}
{else}
    {assign var="id_suffix" value=""}
{/if}

<div class="litecheckout__item">
<div class="clearfix">
    <div class="ty-credit-card cm-cc_form_{$id_suffix}">
            <div class="ty-credit-card__control-group ty-control-group">
                <label for="credit_card_number_{$id_suffix}" class="ty-control-group__title cm-cc-number cc-number_{$id_suffix} cm-required">{__("card_number")}</label>
                <input size="35" type="text" id="credit_card_number_{$id_suffix}" name="payment_info[card_number]" value="" class="ty-credit-card__input cm-autocomplete-off ty-inputmask-bdi" />
                <ul class="ty-cc-icons cm-cc-icons cc-icons_{$id_suffix}">
                    <li class="ty-cc-icons__item cc-default cm-cc-default"><span class="ty-cc-icons__icon default">&nbsp;</span></li>
                    <li class="ty-cc-icons__item cm-cc-visa"><span class="ty-cc-icons__icon visa">&nbsp;</span></li>
                    <li class="ty-cc-icons__item cm-cc-visa_electron"><span class="ty-cc-icons__icon visa-electron">&nbsp;</span></li>
                    <li class="ty-cc-icons__item cm-cc-mastercard"><span class="ty-cc-icons__icon mastercard">&nbsp;</span></li>
                    <li class="ty-cc-icons__item cm-cc-maestro"><span class="ty-cc-icons__icon maestro">&nbsp;</span></li>
                    <li class="ty-cc-icons__item cm-cc-amex"><span class="ty-cc-icons__icon american-express">&nbsp;</span></li>
                    <li class="ty-cc-icons__item cm-cc-discover"><span class="ty-cc-icons__icon discover">&nbsp;</span></li>
                </ul>
            </div>

            <div class="ty-credit-card__control-group ty-control-group">
                <label for="credit_card_month_{$id_suffix}" class="ty-control-group__title cm-cc-date cc-date_{$id_suffix} cm-cc-exp-month cm-required">{__("valid_thru")}</label>
                <label for="credit_card_year_{$id_suffix}" class="cm-required cm-cc-date cm-cc-exp-year cc-year_{$id_suffix} hidden"></label>
                <input type="number" id="credit_card_month_{$id_suffix}" name="payment_info[expiry_month]" value="" size="2" maxlength="2" class="ty-credit-card__input-short ty-inputmask-bdi" />&nbsp;&nbsp;/&nbsp;&nbsp;<input type="number" id="credit_card_year_{$id_suffix}"  name="payment_info[expiry_year]" value="" size="2" maxlength="2" class="ty-credit-card__input-short ty-inputmask-bdi" />&nbsp;
            </div>

            <div class="ty-credit-card__control-group ty-control-group">
                <label for="credit_card_name_{$id_suffix}" class="ty-control-group__title cm-required">{__("cardholder_name")}</label>
                <input size="35" type="text" id="credit_card_name_{$id_suffix}" name="payment_info[cardholder_name]" value="" class="cm-cc-name ty-credit-card__input ty-uppercase" />
            </div>
    </div>

    <div class="ty-control-group ty-credit-card__cvv-field cvv-field">
        <label for="credit_card_cvv2_{$id_suffix}" class="ty-control-group__title cm-required cm-cc-cvv2  cc-cvv2_{$id_suffix} cm-autocomplete-off">{__("cvv2")}</label>
        <input type="number" id="credit_card_cvv2_{$id_suffix}" name="payment_info[cvv2]" value="" size="4" maxlength="4" class="ty-credit-card__cvv-field-input ty-inputmask-bdi" />

        <div class="ty-cvv2-about">
            <span class="ty-cvv2-about__title">{__("what_is_cvv2")}</span>
            <div class="ty-cvv2-about__note">

                <div class="ty-cvv2-about__info mb30 clearfix">
                    <div class="ty-cvv2-about__image">
                        <img src="{$images_dir}/visa_cvv.png" alt="" />
                    </div>
                    <div class="ty-cvv2-about__description">
                        <h5 class="ty-cvv2-about__description-title">{__("visa_card_discover")}</h5>
                        <p>{__("credit_card_info")}</p>
                    </div>
                </div>
                <div class="ty-cvv2-about__info clearfix">
                    <div class="ty-cvv2-about__image">
                        <img src="{$images_dir}/express_cvv.png" alt="" />
                    </div>
                    <div class="ty-cvv2-about__description">
                        <h5 class="ty-cvv2-about__description-title">{__("american_express")}</h5>
                        <p>{__("american_express_info")}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <input type="hidden" id="browser_user_agent_{$id_suffix}" name="browser_settings[user_agent]" value="">
    <input type="hidden" id="browser_language_{$id_suffix}" name="browser_settings[language]" value="">
    <input type="hidden" id="browser_color_depth_{$id_suffix}" name="browser_settings[color_depth]" value="">
    <input type="hidden" id="browser_screen_height_{$id_suffix}" name="browser_settings[screen_height]" value="">
    <input type="hidden" id="browser_screen_width_{$id_suffix}" name="browser_settings[screen_width]" value="">
    <input type="hidden" id="browser_tz_{$id_suffix}" name="browser_settings[timezone]" value="">
    <input type="hidden" id="browser_java_enabled_{$id_suffix}" name="browser_settings[java_enabled]" value="">
    <input type="hidden" id="browser_js_enabled_{$id_suffix}" name="browser_settings[js_enabled]" value="">

</div>
</div>

<script>

(function(_, $) {

    var isChromeOnOldAndroid = function() {
        var ua = navigator.userAgent;
        return (/Android/.test(ua) && /Chrome/.test(ua));
    };

    var ccFormId = '{$id_suffix}';

    $.ceEvent('on', 'ce.commoninit', function() {

        var icons           = $('.cc-icons_' + ccFormId + ' li');

        var ccNumber        = $(".cc-number_" + ccFormId);
        var ccNumberInput   = $("#" + ccNumber.attr("for"));

        var ccCv2           = $(".cc-cvv2_" + ccFormId);
        var ccCv2Input      = $("#" + ccCv2.attr("for"));

        var ccMonth         = $(".cc-date_" + ccFormId);
        var ccMonthInput    = $("#" + ccMonth.attr("for"));

        var ccYear          = $(".cc-year_" + ccFormId);
        var ccYearInput     = $("#" + ccYear.attr("for"));

        if(_.isTouch === false && jQuery.isEmptyObject(ccNumberInput.data("_inputmask")) == true) {

            if (!isChromeOnOldAndroid()) {
                ccNumberInput.inputmask("9999 9999 9999 9[9][9][9]", {
                    placeholder: ''
                });
            }

            $.ceFormValidator('registerValidator', {
                class_name: 'cc-number_' + ccFormId,
                message: '',
                func: function(id) {
                    return isChromeOnOldAndroid() || ccNumberInput.inputmask("isComplete");
                }
            });

            if (!isChromeOnOldAndroid()) {
                ccCv2Input.inputmask("999[9]", {
                    placeholder: ''
                });
            }

            $.ceFormValidator('registerValidator', {
                class_name: 'cc-cvv2_' + ccFormId,
                message: '{__("error_validator_ccv")|escape:javascript}',
                func: function(id) {
                    return isChromeOnOldAndroid() || ccNumberInput.inputmask("isComplete");
                }
            });

            if (!isChromeOnOldAndroid()) {
                ccMonthInput.inputmask("99", {
                    placeholder: ''
                });

                ccYearInput.inputmask("99", {
                    placeholder: ''
                });
            }

            $.ceFormValidator('registerValidator', {
                class_name: 'cc-date_' + ccFormId,
                message: '',
                func: function(id) {
                    return isChromeOnOldAndroid() || (ccYearInput.inputmask("isComplete") && ccMonthInput.inputmask("isComplete"));
                }
            });
        }

        if (ccNumber.length && ccNumberInput.length) {
            ccNumberInput.validateCreditCard(function (result) {
                icons.removeClass('active');
                if (result.card_type) {
                    icons.filter(' .cm-cc-' + result.card_type.name).addClass('active');
                    if (['visa_electron', 'maestro', 'laser'].indexOf(result.card_type.name) != -1) {
                        ccCv2.removeClass("cm-required");
                    } else {
                        ccCv2.addClass("cm-required");
                    }
                }
            });
        }
    });
})(Tygh, Tygh.$);
</script>
