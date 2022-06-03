/* editior-description:text_redactor2 */
(function (_, $) {
  // FIXME: when jQuery UI will be updated from 1.11.1 version, remove the code below.
  $.widget("ui.dialog", $.ui.dialog, {
    /*! jQuery UI - v1.10.2 - 2013-12-12
     *  http://bugs.jqueryui.com/ticket/9087#comment:27 - bugfix
     *  http://bugs.jqueryui.com/ticket/4727#comment:23 - bugfix
     *  allowInteraction fix to accommodate windowed editors
     */
    _allowInteraction: function _allowInteraction(event) {
      if (this._super(event)) {
        return true;
      } // address interaction issues with general iframes with the dialog


      if (event.target.ownerDocument != this.document[0]) {
        return true;
      } // address interaction issues with dialog window


      if ($(event.target).closest(".ui-draggable").length) {
        return true;
      } // address interaction issues with iframe based drop downs in IE


      if ($(event.target).closest(".cke").length) {
        return true;
      }
    },

    /*! jQuery UI - v1.10.2 - 2013-10-28
     *  http://dev.ckeditor.com/ticket/10269 - bugfix
     *  moveToTop fix to accommodate windowed editors
     */
    _moveToTop: function _moveToTop(event, silent) {
      if (!event || !this.options.modal) {
        this._super(event, silent);
      }
    }
  });
  var methods = {
    _getEditor: function _getEditor(elm) {
      var obj = $('#' + elm.prop('id'));

      if (obj.data('redactor')) {
        return obj;
      }

      return false;
    }
  };
  $.ceEditor('handlers', {
    editorName: 'redactor2',
    params: null,
    elms: [],
    run: function run(elm, params) {
      var support_langs = ['ar', 'de', 'en', 'es', 'fa', 'fi', 'fr', 'he', 'hu', 'it', 'ja', 'ko', 'nl', 'pl', 'pt_br', 'ru', 'sv', 'tr', 'zh_cn', 'zh_tw'];
      var lang_map = {
        'pt': 'pt_br',
        'zh': 'zh_tw'
      };
      var lang_code = fn_get_listed_lang(support_langs);

      if (lang_code in lang_map) {
        lang_code = lang_map[lang_code];
      }

      var isBlockManagerEnabled = elm.data('caIsBlockManagerEnabled');

      if (typeof $.fn.redactor == 'undefined') {
        $.ceEditor('state', 'loading');
        $.loadCss(['js/lib/redactor2/redactor.min.css']);
        $.loadCss(['js/lib/redactor2/plugins/alignment/alignment.css']); // Load elFinder

        $.loadCss(['js/lib/elfinder/css/elfinder.min.css']);
        $.loadCss(['js/lib/elfinder/css/theme.css']);
        $.getScript('js/lib/elfinder/js/elfinder.min.js');
        var pluginsQueue = ['js/lib/redactor2/plugins/fontcolor/fontcolor.js', 'js/lib/redactor2/plugins/table/table.js', 'js/lib/redactor2/plugins/imageupload/imageupload.js', 'js/lib/redactor2/plugins/source/source.js', 'js/lib/redactor2/plugins/alignment/alignment.js', 'js/lib/redactor2/plugins/video/video.js'];

        if (isBlockManagerEnabled) {
          pluginsQueue.push('js/tygh/wysiwyg_plugins/block_manager/redactor2.js');
        }

        if (lang_code !== 'en') {
          pluginsQueue.push('js/lib/redactor2/lang/' + lang_code + '.js');
        }

        var pluginsLoadedCount = 0; // load redactor with all the plugins

        $.getScript('js/lib/redactor2/redactor.min.js', function () {
          for (var i in pluginsQueue) {
            $.getScript(pluginsQueue[i], function () {
              pluginsLoadedCount++; // initiate only on full load

              if (pluginsLoadedCount === pluginsQueue.length) {
                callback();
              }
            });
          }
        });

        var callback = function callback() {
          $.ceEditor('state', 'loaded');
          elm.ceEditor('run', params);
        };

        return true;
      }

      if (!this.params) {
        this.params = {
          lang: lang_code,
          removeComments: false,
          replaceTags: false,
          overrideStyles: true
        };
        this.params.direction = _.language_direction;
      }

      if (typeof params !== 'undefined' && params[this.editorName]) {
        $.extend(this.params, params[this.editorName]);
      }

      this.params.callbacks = {
        init: function init() {
          $('.redactor-toolbar-tooltip').each(function () {
            $(this).css('z-index', 50001);
          });
          $('.redactor-box').addClass('redactor2-box');
        },
        modalOpened: function modalOpened() {
          $('#redactor-modal-overlay, #redactor-modal-box, #redactor-modal, .redactor-dropdown').each(function () {
            $(this).css('z-index', 50001, 'important');
          });
        },
        dropdownShow: function dropdownShow() {
          $('#redactor-modal-overlay, #redactor-modal-box, #redactor-modal, .redactor-dropdown').each(function () {
            $(this).css('z-index', 50001, 'important');
          });
        },
        changeCallback: function changeCallback(html) {
          elm.ceEditor('changed', html);
        }
      };
      this.params.plugins = ['alignment', 'fontcolor', 'table', 'source'];
      this.params.buttons = ['source', 'format', 'bold', 'italic', 'deleted', 'lists', 'video', 'table', 'link', 'alignment', 'horizontalrule'];

      if (_.area === 'A' || _.live_editor_mode === true) {
        this.params.plugins.push('imageupload', 'video');

        if (isBlockManagerEnabled) {
          this.params.plugins.push('blockManager');
        }
      }

      this.params.imageResizable = true;
      this.params.imageCaption = false;
      this.params.imagePosition = true;
      this.params.keepStyleAttr = ['*']; // Launch Redactor

      elm.redactor(this.params);
      elm.get(0).defaultValue = elm.get(0).value;
      var $parent = elm.parent();
      $parent.find('textarea:not([name])').addClass("cm-skip-check-item");

      if (elm.prop('disabled')) {
        elm.ceEditor('disable', true);
      }

      this.elms.push(elm.get(0));
      return true;
    },
    destroy: function destroy(elm) {
      var ed = methods._getEditor(elm);

      if (ed) {
        ed.redactor('core.destroy');
      }
    },
    recover: function recover(elm) {
      if ($.inArray(elm.get(0), this.elms) !== -1) {
        $.ceEditor('run', elm);
      }
    },
    val: function val(elm, value) {
      var ed = methods._getEditor(elm);

      if (!ed) {
        return false;
      }

      if (typeof value == 'undefined') {
        return ed.redactor('code.get');
      } else {
        ed.redactor('code.set', value);
      }

      return true;
    },
    updateTextFields: function updateTextFields(elm) {
      return true;
    },
    insert: function insert(elm, text) {
      var ed = methods._getEditor(elm);

      if (ed) {
        ed.redactor('selection.restore');
        ed.redactor('insert.text', text);
      }
    },
    disable: function disable(elm, value) {
      var ed = methods._getEditor(elm);

      if (ed) {
        var obj = ed.redactor('core.getBox');

        if (value == true) {
          if (!$(obj).parent().hasClass('disable-overlay-wrap')) {
            $(obj).wrap("<div class='disable-overlay-wrap wysiwyg-overlay'></div>");
            $(obj).before("<div id='" + elm.prop('id') + "_overlay' class='disable-overlay'></div>");
            elm.prop('disabled', true);
          }
        } else {
          $(obj).unwrap();
          $('#' + elm.prop('id') + '_overlay').remove();
          elm.prop('disabled', false);
        }
      }
    }
  });
})(Tygh, Tygh.$);