(function (_, $) {
  $(function () {
    var stored_colors = {};
    $(".js-mobile-app-input").on("change", function (event) {
      var url = fn_url('addons.update.rebuild?addon=mobile_app');
      stored_colors[this.dataset.target] = this.value;
      $.ceAjax('request', url, {
        method: "get",
        data: {
          colors: stored_colors
        },
        result_ids: "colors_variables"
      });
    });
  });
})(Tygh, Tygh.$);