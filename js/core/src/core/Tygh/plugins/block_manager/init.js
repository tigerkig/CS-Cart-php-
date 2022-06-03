import { Tygh } from '../..';
import { params } from './params';
import { actions } from './actions';
import { sortable } from './sortable';
import $ from "jquery";

let isInit;

export const init = {
    init: function () {
        if (isInit) {
            return;
        }

        sortable._sortable();

        $(params.block_selector).each(function () {
            actions._setMenuPosition($(this));
        });

        $(Tygh.doc).on('click', params.action_selector, function (e) {
            params._self = $(this);
            var jelm = params._self.parents(params.menu_selector).parent().parent();

            params._hover_element = jelm;
            var action = params._self.data('caBlockManagerAction');

            return actions._executeAction(action);
        });

        $(Tygh.doc).on('block_manager:animation_complete', function (event) {
            actions._setMenuPosition($(event.target));
        });

        isInit = true;
    }
};
