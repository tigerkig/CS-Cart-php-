import $ from "jquery";

export const api = {
    sendRequest: function (mode, action, data) {
        $.ceAjax('request', fn_url('block_manager' + '.' + mode + (action ? '.' + action : '')), {
            data: data,
            method: 'post'
        });
    },
};
