function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function (_, $) {
  var createMoment = function createMoment(input) {
    // Unix timestamp
    if (isFinite(input)) {
      return moment.unix(input);
    } // ISO 8601
    else {
        return moment(input, moment.ISO_8601).zone(input);
      }
  };

  var methods = {
    init: function init(params) {
      var $self = $(this);
      $.loadCss(['js/lib/daterangepicker/daterangepicker.css']);

      if (typeof moment == 'undefined') {
        $.getScript('js/lib/daterangepicker/moment.min.js', function () {
          $.getScript('js/lib/daterangepicker/daterangepicker.js', function () {
            return $self.ceDateRangePicker();
          });
        });
        return false;
      }

      if (!$self.length || $self.data('daterangepicker')) {
        return;
      }

      moment.locale(_.tr("default_lang"), {
        monthsShort: [_.tr("month_name_abr_1"), _.tr("month_name_abr_2"), _.tr("month_name_abr_3"), _.tr("month_name_abr_4"), _.tr("month_name_abr_5"), _.tr("month_name_abr_6"), _.tr("month_name_abr_7"), _.tr("month_name_abr_8"), _.tr("month_name_abr_9"), _.tr("month_name_abr_10"), _.tr("month_name_abr_11"), _.tr("month_name_abr_12")]
      });
      moment.locale(_.tr("default_lang"));
      var default_params = {
        ranges: $self.data('caShowRanges') === undefined || $self.data('caShowRanges') ? {} : $self.data('caShowRanges'),
        startDate: createMoment($self.data('caTimeFrom') || _.time_from),
        endDate: createMoment($self.data('caTimeTo') || _.time_to),
        locale: {
          applyLabel: _.tr("apply"),
          cancelLabel: _.tr("cancel"),
          clearLabel: _.tr("clear"),
          fromLabel: _.tr("from"),
          toLabel: _.tr("to"),
          customRangeLabel: _.tr("custom_range"),
          monthNames: [_.tr("month_name_abr_1"), _.tr("month_name_abr_2"), _.tr("month_name_abr_3"), _.tr("month_name_abr_4"), _.tr("month_name_abr_5"), _.tr("month_name_abr_6"), _.tr("month_name_abr_7"), _.tr("month_name_abr_8"), _.tr("month_name_abr_9"), _.tr("month_name_abr_10"), _.tr("month_name_abr_11"), _.tr("month_name_abr_12")],
          daysOfWeek: [_.tr("weekday_abr_0"), _.tr("weekday_abr_1"), _.tr("weekday_abr_2"), _.tr("weekday_abr_3"), _.tr("weekday_abr_4"), _.tr("weekday_abr_5"), _.tr("weekday_abr_6")]
        },
        format: $self.data('caDateFormat') || _.daterangepicker.customRangeFormat
      }; // but, if we had .admin-content and RTL enabled, place picker in this wrapper

      if ($('.admin-content').length && Tygh.language_direction == 'rtl') {
        default_params.parentEl = '.admin-content';
      }

      if ($self.data('minDate') || _.daterangepicker.minDate) {
        default_params.minDate = createMoment($self.data('minDate') || _.daterangepicker.minDate);
      }

      if ($self.data('maxDate') || _.daterangepicker.maxDate) {
        default_params.maxDate = createMoment($self.data('maxDate') || _.daterangepicker.maxDate);
      }

      default_params['ranges'][_.tr('today')] = [moment().startOf('day'), moment().endOf('day')];
      default_params['ranges'][_.tr('yesterday')] = [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')];
      default_params['ranges'][_.tr('this_month')] = [moment().startOf('month'), moment().endOf('month')];
      default_params['ranges'][_.tr('last_month')] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
      default_params['ranges'][_.tr('this_year')] = [moment().startOf('year').startOf('day'), moment().endOf('year').endOf('day')];
      default_params['ranges'][_.tr('last_year')] = [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')];
      var periods = {};
      periods[_.tr('today')] = 'D';
      periods[_.tr('yesterday')] = 'LD';
      periods[_.tr('this_month')] = 'M';
      periods[_.tr('last_month')] = 'LM';
      periods[_.tr('this_year')] = 'Y';
      periods[_.tr('last_year')] = 'LY';
      $.extend(default_params, params);
      return this.each(function () {
        var $el = $(this);
        $el.daterangepicker(default_params, function (start, end, label) {
          var query_params;
          start = moment(start).local().startOf('day');
          end = moment(end).local().endOf('day');
          var selected_from = parseInt(start.valueOf() / 1000, 10);
          var selected_to = parseInt(end.valueOf() / 1000, 10);

          if (($self.data('caUsePredefinedPeriods') || _.daterangepicker.usePredefinedPeriods) && periods[label] != undefined) {
            query_params = 'time_period=' + periods[label];
          } else {
            query_params = 'time_from=' + selected_from + '&time_to=' + selected_to;
          }

          $('span', $el).html(start.format($self.data('caDateFormat') || _.daterangepicker.displayedFormat) + ' — ' + end.format($self.data('caDateFormat') || _.daterangepicker.displayedFormat));

          if ($el.data('ca-target-url') && $el.data('ca-target-id')) {
            $.ceAjax('request', $.attachToUrl($el.data('ca-target-url'), query_params), {
              result_ids: $el.data('ca-target-id'),
              caching: false,
              force_exec: true
            });
          }

          if ($el.data('caEvent')) {
            $.ceEvent('trigger', $el.data('caEvent'), [$el, selected_from, selected_to, start, end]);
          }
        });
      });
    }
  };

  $.fn.ceDateRangePicker = function (method) {
    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } else if (_typeof(method) === 'object' || !method) {
      return methods.init.apply(this, arguments);
    } else {
      $.error('ty.tooltip: method ' + method + ' does not exist');
    }
  };

  $.ceEvent('on', 'ce.commoninit', function (context) {
    $dateRange = $('.cm-date-range', context);

    if (!$dateRange.length) {
      return;
    }

    $dateRange.ceDateRangePicker();
  });
})(Tygh, Tygh.$);