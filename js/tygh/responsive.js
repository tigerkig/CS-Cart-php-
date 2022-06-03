(function (_, $) {
  var breakpoints = {
    tablet: 767,
    phone: 479
  }; // ui module

  var ui = function () {
    return {
      winWidth: function winWidth() {
        return $(window).width();
      },
      responsiveScroll: function responsiveScroll() {
        this.needScrollInited = this.needScrollInited || false;

        if (this.needScrollInited) {
          return;
        }

        this.needScrollInited = true;
        $.ceEvent('on', 'ce.needScroll', function (opt) {
          opt.timeout = 310;
        });
      },
      responsiveTabs: function responsiveTabs() {
        if (ui.winWidth() <= breakpoints.phone + 1) {
          var accordionOptions = {
            animate: $(_.body).data('caAccordionAnimateDelay') || 300,
            heightStyle: "content",
            activate: function activate(event, ui) {
              var selectedItem = $(ui.newHeader);
              if (!selectedItem.length) return;
              var tabId = selectedItem.prop('id');
              var isActiveScrollToElm = ui.newPanel.data('caAccordionIsActiveScrollToElm');

              if (isActiveScrollToElm) {
                $.scrollToElm(selectedItem);
              }

              selectedItem.addClass('active');

              if (tabId) {
                $.ceEvent('trigger', 'ce.tab.show', [tabId, $(this)]);
              }
            }
          }; // conver tabs to accordion

          $('.cm-j-tabs:not(.cm-j-tabs-disable-convertation)').each(function (index) {
            var accordion = $('<div class="ty-accordion cm-accordion" id="accordion_id_' + index + '">');
            var tabsContent = $(this).nextAll('.cm-tabs-content:not(.cm-j-content-disable-convertation)').first();
            var self = this; // hide tabs

            $(this).hide();
            tabsContent.hide();

            if (!$('#accordion_id_' + index).length) {
              $(this).find('>ul>li').each(function (indexTab) {
                var id = $(this).attr('id');

                if ($(this).hasClass('active')) {
                  accordionOptions.active = indexTab;
                }

                var content = $('> #content_' + id, tabsContent).show(); // rename tab id

                $(this).attr('id', 'hidden_tab_' + id);
                accordion.append('<h3 id="' + id + '">' + $(this).text() + '</h3>');
                $(content).appendTo(accordion);
              });
              $(self).before(accordion);
            }
          });
          $('.cm-accordion').ceAccordion('reinit', accordionOptions);
          var active = _.anchor;

          if (typeof active !== 'undefined' && $(active).length > 0) {
            $(active).click();
          }
        } else {
          $('.cm-accordion').accordion('destroy');
          $('.cm-accordion > div').each(function (index) {
            var $tabsContent = $(this).parent().nextAll('.cm-tabs-content:not(.cm-j-content-disable-convertation)').first();
            $(this).hide();
            $(this).appendTo($tabsContent);
          });
          $('.cm-accordion').remove(); // remove prefix

          $('.cm-j-tabs>ul>li').each(function () {
            var $tabs = $(this).closest('.cm-j-tabs');
            var $tabsContent = $tabs.nextAll('.cm-tabs-content:not(.cm-j-content-disable-convertation)').first();
            var id = $(this).attr('id').replace('hidden_tab_', '');
            $(this).attr('id', id);
            var $content = $tabsContent.find('#content_' + id);
            $content.css('display', '');
          });
          $('.cm-j-tabs, .cm-tabs-content').show();
        }
      },
      responsiveMenu: function responsiveMenu(elms) {
        var whichEvent = 'ontouch' in document.documentElement ? "touch" : "click"; // FIXME Windows IE 8 doesn't have touch event

        if (_.isTouch && window.navigator.msPointerEnabled) {
          whichEvent = 'click';
        }

        if (elms) {
          $(elms).each(function () {
            _addResponsiveMenuOpenEvent(whichEvent, this);
          });
        } else {
          _addResponsiveMenuOpenEvent(whichEvent);
        }

        ;

        if (_.isTouch == false && ui.winWidth() >= breakpoints.tablet) {
          $('.cm-responsive-menu').on('mouseover mouseout', function (e) {
            ui.detectMenuWidth(e);
          });
        }

        function _addResponsiveMenuOpenEvent(whichEvent, elm) {
          var responsiveMenu = elm || document;

          if ($(responsiveMenu).find('.ty-menu__menu-btn').data('ca-responsive-menu') !== true) {
            $(responsiveMenu).find('.ty-menu__menu-btn').on(whichEvent, function (e) {
              var menu_elm = $('.cm-responsive-menu');
              $(this).parent(menu_elm).find('.ty-menu__item').toggle();
            });
            $(responsiveMenu).find('.cm-responsive-menu-toggle').on(whichEvent, function (e) {
              $(this).toggleClass('ty-menu__item-toggle-active');
              $('.icon-down-open', this).toggleClass('icon-up-open');
              $(this).parent().find('.cm-responsive-menu-submenu').first().toggleClass('ty-menu__items-show');
            });
            $(responsiveMenu).find('.ty-menu__menu-btn').data('ca-responsive-menu', true);
          }
        }
      },
      responsiveMenuLargeTouch: function responsiveMenuLargeTouch(e) {
        var elm = $(e.target);
        var menuWidth = $('.cm-responsive-menu').width();

        if (ui.winWidth() >= breakpoints.tablet && e.type == 'touchstart') {
          if (elm.is('.ty-menu__submenu-link')) {
            elm.click();
          }

          var menuItem = elm.hasClass('cm-menu-item-responsive') ? elm : elm.closest('.cm-menu-item-responsive');

          if (!menuItem.hasClass('is-hover-menu') && menuItem.find('.ty-menu__submenu-items').length > 0) {
            e.preventDefault();
            menuItem.siblings('.cm-menu-item-responsive').removeClass('is-hover-menu');
            menuItem.addClass('is-hover-menu');
          }

          var subMenu = $('.ty-menu__submenu-items');

          if (subMenu.is(':visible') && !elm.closest('.cm-menu-item-responsive').length) {
            $('.cm-menu-item-responsive').removeClass('is-hover-menu');
          }
        } else {
          $('.cm-menu-item-responsive').removeClass('is-hover-menu');
        }

        ui.detectMenuWidth(e);
      },
      detectMenuWidth: function detectMenuWidth(e) {
        var $self = $(e.target),
            $menuItem = $self.closest('.cm-menu-item-responsive'),
            $menuItemSubmenu = $('.cm-responsive-menu-submenu', $menuItem).first(),
            $menu = $self.parents('.cm-responsive-menu');
        var verticalMenuClassName = 'ty-menu-vertical',
            reverseDirectionClassName = 'ty-menu__submenu-reverse-direction',
            isHorizontal = !$menu.parent().hasClass(verticalMenuClassName);

        if (!isHorizontal || !$menuItemSubmenu.length || !$menuItem.length) {
          return false;
        }

        var menuWidth = $menu.outerWidth(),
            itemWidth = $menuItem.outerWidth(),
            menuItemSubmenuWidth = _getSubmenuOriginWidth($menuItemSubmenu);

        $('.' + reverseDirectionClassName).removeClass(reverseDirectionClassName); // disable toggled (always clear state)
        // apply only to second half of elements in menu

        if ($menuItem.index() / $menu.children().length > 0.5) {
          var _offset = Math.abs(_.language_direction == "rtl" ? $menuItem.offset().left + itemWidth - ($menu.offset().left + menuWidth) : $menuItem.offset().left - $menu.offset().left);

          $menuItemSubmenu.toggleClass(reverseDirectionClassName, menuWidth - itemWidth * 2 < menuItemSubmenuWidth + itemWidth || _offset + menuItemSubmenuWidth > menuWidth);
        }
        /**
         * Returns origins submenu width.
         * FIXME: using dirty hack.
         * @param {jQueryHTMLElement} $submenu 
         */


        function _getSubmenuOriginWidth($submenu) {
          $submenu.css('left', 0);

          var _width = $submenu.outerWidth() || 0; // remove inline style perfectly


          $submenu.get(0).style.left = '';
          return _width;
        }
      },
      responsiveTables: function responsiveTables(e) {
        var tables = $('.ty-table');

        if (ui.winWidth() <= breakpoints.tablet) {
          tables.each(function () {
            var thTexts = []; // if we have sub table detach it.

            var subTable = $(this).find('.ty-table');

            if (subTable.length) {
              var subTableStack = [];
              subTable.each(function (index) {
                $(this).parent().attr('data-ca-has-sub-table_' + index, 'true');
                subTableStack.push($(this).detach());
              });
            }

            $(this).find('th:not(.ty-table-disable-convertation)').each(function () {
              thTexts.push($(this).text());
            });
            $(this).find('tr:not(.ty-table__no-items)').each(function () {
              $(this).find('td:not(.ty-table-disable-convertation)').each(function (index) {
                var $elm = $(this);

                if ($elm.find('.ty-table__responsive-content').length == 0) {
                  $elm.wrapInner('<div class="ty-table__responsive-content"></div>');
                  $elm.prepend('<div class="ty-table__responsive-header">' + thTexts[index] + '</div>');
                }
              });
            });

            if (subTable.length) {
              subTable.each(function (index) {
                var subTableElm = $('[data-ca-has-sub-table_' + index + ']');
                subTableElm.prepend(subTableStack[index]);
                subTableElm.removeAttr('data-ca-has-sub-table_' + index);
              });
            }
          });
        }
      },
      resizeDialog: function resizeDialog() {
        var dlg = $('.ui-dialog');
        var $contentElem = $(dlg).find('.ui-dialog-content');

        if (ui.winWidth() > breakpoints.tablet) {
          $contentElem.data('caDialogAutoHeight', false);
          return;
        }

        $contentElem.data('caDialogAutoHeight', true);
        $('.ui-widget-overlay').css({
          'min-height': $(window).height()
        });
        $(dlg).css({
          'position': 'absolute',
          'width': $(window).width() - 20,
          'left': '10px',
          'top': '10px',
          'max-height': 'none',
          'height': 'auto',
          'margin-bottom': '10px'
        }); // calculate title width

        $(dlg).find('.ui-dialog-title').css({
          'width': $(window).width() - 80
        });
        $contentElem.css({
          'height': 'auto',
          'max-height': 'none'
        });
        $(dlg).find('.object-container').css({
          'height': 'auto'
        });
        $(dlg).find('.buttons-container').css({
          'position': 'relative',
          'top': 'auto',
          'left': '0px',
          'right': '0px',
          'bottom': '0px',
          'width': 'auto'
        });
        var w = $.getWindowSizes();
        $('.cm-notification-content.notification-content-extended').each(function (id, elm) {
          var notification = $(elm),
              notificationMaxHeight = w.view_height - 300;
          notification.find('.cm-notification-max-height').css({
            'max-height': notificationMaxHeight
          });
          notification.css('top', w.view_height / 2 - notification.height() / 2);
        });
      },
      responsiveDialog: function responsiveDialog() {
        $.ceEvent('on', 'ce.dialogshow', function () {
          if (ui.winWidth() <= breakpoints.tablet) {
            var currentScrollPosition = $(document).scrollTop();
            ui.resizeDialog();
            $('body,html').scrollTop(0);
            $.ceEvent('on', 'ce.dialogclose', function () {
              $('body,html').scrollTop(currentScrollPosition);
            });
          }
        });
      },
      responsiveFilters: function responsiveFilters(e) {
        var filtersContent = $('.cm-horizontal-filters-content');

        if (ui.winWidth() <= breakpoints.tablet) {
          filtersContent.removeClass('cm-popup-box');
        } else {
          filtersContent.addClass('cm-popup-box');
        }

        if (ui.winWidth() > breakpoints.tablet) {
          $('.ty-horizontal-filters-content-to-right').removeClass('ty-horizontal-filters-content-to-right');
          $('.ty-horizontal-product-filters-dropdown').click(function () {
            var hrFiltersWidth = $(".cm-horizontal-filters").width();
            var hrFiltersContent = $('.cm-horizontal-filters-content', this);
            setTimeout(function () {
              var position = hrFiltersContent.offset().left + hrFiltersContent.width();

              if (position > hrFiltersWidth) {
                hrFiltersContent.addClass("ty-horizontal-filters-content-to-right");
              }
            }, 1);
          });
        }
      },
      responsiveInlineTextLinksLargeTouch: function responsiveInlineTextLinksLargeTouch(e) {
        var elm = $(e.target);

        if (ui.winWidth() >= breakpoints.tablet && e.type == 'touchstart') {
          var linksItem = elm.hasClass('ty-text-links__item') ? elm : elm.closest('.ty-text-links__item');

          if (!linksItem.hasClass('is-hover-link') && linksItem.hasClass('ty-text-links__subitems')) {
            e.preventDefault();
            linksItem.siblings('.ty-text-links__item').removeClass('is-hover-link');
            linksItem.addClass('is-hover-link');
          }
        } else {
          $('.ty-text-links__item').removeClass('is-hover-link');
        }
      }
    };
  }(); // Init


  $(document).ready(function () {
    var responsiveTablesDebounced = $.debounce(ui.responsiveTables);
    var responsiveFiltersDebounced = $.debounce(ui.responsiveFilters);
    var resizeDialogDebounced = $.debounce(ui.resizeDialog);
    var responsiveMenuDebounced = $.debounce(ui.responsiveMenu);
    $(window).on('resize', function () {
      responsiveTablesDebounced();
      responsiveFiltersDebounced();
      resizeDialogDebounced();
    });

    if (window.addEventListener) {
      window.addEventListener('orientationchange', function () {
        resizeDialogDebounced();
        $.ceDialog('get_last').ceDialog('reload');
      }, false);
    }

    ui.responsiveDialog(); // responsive tables

    responsiveTablesDebounced(); // responsive filters

    responsiveFiltersDebounced();
    $.ceEvent('on', 'ce.ajaxdone', function (elms) {
      responsiveTablesDebounced();
      responsiveFiltersDebounced();
      resizeDialogDebounced();

      if (elms.length) {
        ui.responsiveMenu(elms);
      } else {
        responsiveMenuDebounced();
      }
    }); // Menu and Inline text links init

    responsiveMenuDebounced();
    $(document).on('touchstart', function (e) {
      var elm = $(e.target); // Menu

      if (elm.hasClass('cm-menu-item-responsive') || elm.closest('.cm-menu-item-responsive').length) {
        ui.responsiveMenuLargeTouch(e);
      } else {
        $('.is-hover-menu').removeClass('is-hover-menu');
      } // Inline text links


      if (elm.hasClass('ty-text-links__subitems') || elm.closest('.ty-text-links__subitems').length) {
        ui.responsiveInlineTextLinksLargeTouch(e);
      } else {
        $('.is-hover-link').removeClass('is-hover-link');
      }
    });
  }); // tabs

  $.ceEvent('on', 'ce.tab.init', function () {
    var responsiveTabsDebounced = $.debounce(ui.responsiveTabs);
    $(window).on('resize', function () {
      responsiveTabsDebounced();
    });
    responsiveTabsDebounced();
    ui.responsiveScroll();
  });
})(Tygh, Tygh.$);