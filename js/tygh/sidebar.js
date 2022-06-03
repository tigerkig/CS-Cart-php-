function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/*
 * Sidebar
 *
 */
(function ($) {
  var sidebars = [];
  var methods = {
    init: function init() {
      var $self = $(this);
      $self.find('.sidebar-toggle').on('click', function () {
        methods._toggle($self);
      });

      methods._resize($self);

      sidebars.push($self);
    },
    resize: function resize() {
      return methods._resize(this);
    },
    toggle: function toggle() {
      $(this).toggleClass('sidebar-open');
    },
    open: function open() {
      if (!methods._is_open(this)) {
        $(this).addClass('sidebar-open');
      }
    },
    close: function close() {
      if (methods._is_open(this)) {
        $(this).removeClass('sidebar-open');
      }
    },
    is_open: function is_open() {
      return methods._is_open(this);
    },
    _toggle: function _toggle(elem) {
      $(elem).toggleClass('sidebar-open');
    },
    _resize: function _resize(elem) {
      $(elem).css({
        "top": $('#actions_panel').height() + 'px'
      });
    },
    _is_open: function _is_open(elem) {
      return $(elem).hasClass('sidebar-open');
    }
  };

  $.fn.ceSidebar = function (method) {
    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } else if (_typeof(method) === 'object' || !method) {
      return methods.init.apply(this, arguments);
    } else {
      $.error('ty.sidebar: method ' + method + ' does not exist');
    }
  };

  $(window).on('resize', function (e) {
    for (var i in sidebars) {
      methods._resize(sidebars[i]);
    }
  });
})(Tygh.$);