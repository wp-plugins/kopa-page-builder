var Kopa_Page_Builder, kpb_current_sidebar, kpb_current_widget, kpb_media, kpb_media_button_reset, kpb_media_button_upload;

jQuery(document).ready(function() {
  Kopa_Page_Builder.load_lightbox_html();
});

jQuery(window).load(function() {});

kpb_current_widget = {};

kpb_current_sidebar = {};

kpb_media = false;

kpb_media_button_upload = {};

kpb_media_button_reset = {};

Kopa_Page_Builder = {
  init_effect: function() {
    Kopa_Page_Builder.init_sortable();
    Kopa_Page_Builder.init_section_tab();
    Kopa_Page_Builder.sticky_header();
    Kopa_Page_Builder.open_media_center();
    jQuery('.kpb-ui-color').wpColorPicker();
  },
  load_lightbox_html: function() {
    jQuery.ajax({
      beforeSend: function(jqXHR) {},
      success: function(data, textStatus, jqXHR) {
        jQuery('body').append(data);
        jQuery('#kpb-metabox-loading-text').remove();
        jQuery('#kpb-wrapper').show();
        Kopa_Page_Builder.init_effect();
      },
      url: KPB.ajax,
      dataType: "html",
      type: 'POST',
      async: true,
      data: {
        action: 'kpb_load_lightbox_html',
        security: jQuery('#kpb_load_lightbox_html_security').val(),
        post_id: jQuery('#post_ID').val()
      }
    });
  },
  main_form_submit: function() {
    if (jQuery('form#post').length > 0) {
      jQuery('form#post').on('submit', function() {
        return jQuery('#kpb-button-save-layouts').click();
      });
    }
  },
  sticky_header: function(event) {
    if (KPB.is_sticky_toolbar === '1') {
      jQuery('#kpb-wrapper-header').waypoint('sticky', {
        direction: 'down',
        stuckClass: 'kpb-stuck',
        wrapper: '<div class="kpb-wrapper-header-sticky" />'
      });
    }
  },
  open_customize_layout: function(event, obj) {
    var lightbox;
    event.preventDefault();
    lightbox = '#kpb-layout-customize-lightbox-' + jQuery('#kpb-select-layout option:selected').val();
    jQuery.magnificPopup.open({
      callbacks: {
        open: function() {
          jQuery(lightbox).show();
        },
        close: function() {
          jQuery(lightbox).hide();
        }
      },
      modal: true,
      preloader: true,
      alignTop: true,
      items: {
        src: lightbox,
        type: 'inline'
      }
    });
  },
  save_layout_customize: function(event, obj, post_id) {
    event.preventDefault();
    Kopa_Page_Builder.show_overlay_loading();
    obj.ajaxSubmit({
      beforeSubmit: function(arr, $form, options) {},
      success: function(responseText, statusText, xhr, $form) {
        Kopa_Page_Builder.hide_overlay_loading();
      },
      data: {
        post_id: post_id
      }
    });
  },
  close_layout_customize: function(event) {
    event.preventDefault();
    Kopa_Page_Builder.close_widget(event);
  },
  open_customize: function(event, obj, layout, section) {
    var lightbox;
    event.preventDefault();
    lightbox = '#kpb-customize-lightbox-' + layout + '-' + section;
    jQuery.magnificPopup.open({
      callbacks: {
        open: function() {
          jQuery(lightbox).show();
        },
        close: function() {
          jQuery(lightbox).hide();
        }
      },
      modal: true,
      preloader: true,
      alignTop: true,
      items: {
        src: lightbox,
        type: 'inline'
      }
    });
  },
  save_customize: function(event, obj, post_id) {
    event.preventDefault();
    Kopa_Page_Builder.show_overlay_loading();
    obj.ajaxSubmit({
      beforeSubmit: function(arr, $form, options) {},
      success: function(responseText, statusText, xhr, $form) {
        Kopa_Page_Builder.hide_overlay_loading();
      },
      data: {
        post_id: post_id
      }
    });
  },
  close_customize: function(event) {
    event.preventDefault();
    Kopa_Page_Builder.close_widget(event);
  },
  open_list_widgets: function(event, obj) {
    var lightbox;
    event.preventDefault();
    kpb_current_sidebar = obj.parents('.kpb-area');
    lightbox = '#kpb-widgets-lightbox';
    jQuery.magnificPopup.open({
      callbacks: {
        open: function() {
          jQuery(lightbox).show();
        },
        close: function() {
          jQuery(lightbox).hide();
        }
      },
      modal: true,
      preloader: true,
      alignTop: true,
      items: {
        src: lightbox,
        type: 'inline'
      }
    });
  },
  close_list_widgets: function(event) {
    event.preventDefault();
    jQuery.magnificPopup.close();
  },
  add_widget: function(event, obj, class_name, widget_name) {
    var lightbox;
    event.preventDefault();
    Kopa_Page_Builder.close_list_widgets(event);
    lightbox = '#kpb-widget-lightbox';
    jQuery.magnificPopup.open({
      callbacks: {
        open: function() {
          jQuery(lightbox).show();
          jQuery('#kpb-widget-title').text(widget_name);
          jQuery('#kpb-widget input[name=kpb-widget-class-name]').val(class_name);
          jQuery('#kpb-widget input[name=kpb-widget-name]').val(widget_name);
          jQuery('#kpb-widget input[name=kpb-widget-action]').val('add');
          jQuery('#kpb-widget input[name=kpb-widget-id]').val(Kopa_Page_Builder.get_random_id('widget-'));
          jQuery.ajax({
            success: function(data, textStatus, jqXHR) {
              jQuery('#kpb-widget .kpb-form-inner').html(data);
              Kopa_Page_Builder.open_media_center();
              jQuery('.kpb-ui-color').wpColorPicker();
            },
            url: KPB.ajax,
            dataType: "html",
            type: 'POST',
            async: true,
            data: {
              action: 'kpb_get_widget_form',
              security: jQuery('#kpb_get_widget_form_security').val(),
              class_name: class_name
            }
          });
        },
        close: function() {
          jQuery(lightbox).hide();
        }
      },
      modal: true,
      preloader: true,
      alignTop: true,
      items: {
        src: lightbox,
        type: 'inline'
      }
    });
  },
  edit_widget: function(event, obj, widget_id) {
    var lightbox;
    event.preventDefault();
    kpb_current_widget = obj.parents('.kpb-widget');
    lightbox = '#kpb-widget-lightbox';
    jQuery.magnificPopup.open({
      callbacks: {
        open: function() {
          jQuery(lightbox).show();
          jQuery('#kpb-widget-title').text(kpb_current_widget.find('label').text());
          jQuery('#kpb-widget input[name=kpb-widget-class-name]').val(kpb_current_widget.attr('data-class'));
          jQuery('#kpb-widget input[name=kpb-widget-name]').val(kpb_current_widget.attr('data-name'));
          jQuery('#kpb-widget input[name=kpb-widget-action]').val('edit');
          jQuery('#kpb-widget input[name=kpb-widget-id]').val(widget_id);
          jQuery.ajax({
            success: function(data, textStatus, jqXHR) {
              jQuery('#kpb-widget .kpb-form-inner').html(data);
              Kopa_Page_Builder.open_media_center();
              jQuery('.kpb-ui-color').wpColorPicker();
            },
            url: KPB.ajax,
            dataType: "html",
            type: 'POST',
            async: true,
            data: {
              action: 'kpb_get_widget_form',
              security: jQuery('#kpb_get_widget_form_security').val(),
              widget_id: widget_id,
              class_name: kpb_current_widget.attr('data-class'),
              post_id: jQuery('#post_ID').val()
            }
          });
        },
        close: function() {
          jQuery(lightbox).hide();
        }
      },
      modal: true,
      preloader: true,
      alignTop: true,
      items: {
        src: lightbox,
        type: 'inline'
      },
      fixedBgPos: true
    });
  },
  delete_widget: function(event, obj, widget_id) {
    var answer;
    event.preventDefault();
    answer = confirm(KPB.i18n.are_you_sure_to_remove_this_widget);
    if (answer) {
      jQuery.ajax({
        beforeSend: function(jqXHR) {},
        success: function(data, textStatus, jqXHR) {
          obj.parents('.kpb-widget').remove();
          jQuery('#kpb-button-save-layouts').click();
        },
        url: KPB.ajax,
        dataType: "html",
        type: 'POST',
        async: true,
        data: {
          action: 'kpb_delete_widget',
          security: jQuery('#kpb_delete_widget_security').val(),
          widget_id: widget_id
        }
      });
    }
  },
  save_widget: function(event, obj) {
    event.preventDefault();
    Kopa_Page_Builder.show_overlay_loading();
    obj.ajaxSubmit({
      beforeSubmit: function(arr, $form, options) {},
      success: function(responseText, statusText, xhr, $form) {
        var lightbox;
        if (responseText) {
          lightbox = '#kpb-widget-lightbox';
          jQuery(lightbox).hide();
          jQuery.magnificPopup.close();
          if ('add' === jQuery('#kpb-widget input[name=kpb-widget-action]').val()) {
            kpb_current_sidebar.find('.kpb-area-placeholder').append(responseText);
          } else {
            if (kpb_current_widget) {
              kpb_current_widget.find('label').text(responseText);
            }
          }
          jQuery('#kpb-widget-title').text('');
          jQuery('#kpb-widget input[name=kpb-widget-class-name]').val('');
          jQuery('#kpb-widget input[name=kpb-widget-action]').val('add');
          jQuery('#kpb-widget input[name=kpb-widget-id]').val('');
          jQuery('#kpb-widget input[name=kpb-widget-name]').val('');
        }
        Kopa_Page_Builder.close_widget(event);
        jQuery('#kpb-button-save-layouts').click();
      }
    });
  },
  close_widget: function(event) {
    event.preventDefault();
    jQuery.magnificPopup.close();
    jQuery('#kpb-widget .kpb-form-inner').html('<center class="kpb-loading">' + KPB.i18n.loading + '</center>');
    Kopa_Page_Builder.hide_overlay_loading();
  },
  save_layout: function(event, obj) {
    var current_layout, data, layouts;
    event.preventDefault();
    current_layout = jQuery('#kpb-select-layout').find('option:selected').val();
    Kopa_Page_Builder.show_overlay_loading();
    data = {
      current_layout: jQuery('#kpb-select-layout').find('option:selected').val(),
      layouts: []
    };
    layouts = jQuery('.kpb-layout');
    if (layouts.length > 0) {
      layouts.each(function(l_index, l_element) {
        var layout_data, sections;
        current_layout = jQuery(l_element);
        layout_data = {
          name: current_layout.attr('data-layout'),
          sections: []
        };
        sections = current_layout.find('.kpb-section');
        if (sections.length > 0) {
          sections.each(function(s_index, s_element) {
            var areas, current_section, section_data;
            current_section = jQuery(s_element);
            section_data = {
              name: current_section.attr('data-section'),
              areas: []
            };
            areas = current_section.find('.kpb-area');
            if (areas.length > 0) {
              areas.each(function(a_index, a_element) {
                var area_data, current_area, widgets;
                current_area = jQuery(a_element);
                area_data = {
                  name: current_area.attr('data-area'),
                  widgets: []
                };
                widgets = current_area.find('.kpb-widget');
                if (widgets.length > 0) {
                  widgets.each(function(w_index, w_element) {
                    var current_widget, widget_data;
                    current_widget = jQuery(w_element);
                    widget_data = {
                      id: current_widget.attr('id'),
                      name: current_widget.attr('data-name'),
                      class_name: current_widget.attr('data-class')
                    };
                    area_data.widgets.push(widget_data);
                  });
                }
                section_data.areas.push(area_data);
              });
            }
            layout_data.sections.push(section_data);
          });
        }
        data.layouts.push(layout_data);
      });
    }
    jQuery.ajax({
      error: function(jqXHR, textStatus, errorThrown) {
        console.log(textStatus);
      },
      beforeSend: function(jqXHR) {
        obj.text(KPB.i18n.saving);
      },
      success: function(data, textStatus, jqXHR) {
        obj.text(KPB.i18n.save);
        Kopa_Page_Builder.hide_overlay_loading();
      },
      url: KPB.ajax,
      dataType: "html",
      type: 'POST',
      async: true,
      data: {
        action: 'kpb_save_grid',
        security: jQuery('#kpb_save_grid_security').val(),
        data: data,
        post_id: jQuery('#post_ID').val()
      }
    });
  },
  show_overlay_loading: function() {
    jQuery('#kpb-loading-overlay').stop().animate({
      display: 'block',
      right: '0px'
    }, 500);
  },
  hide_overlay_loading: function() {
    jQuery('#kpb-loading-overlay').animate({
      display: 'none',
      right: '-150px'
    }, 500);
  },
  hide_preview: function(event, obj) {
    event.preventDefault();
    if (obj.attr('data-status') === '0') {
      jQuery('.kpb-layout > .kpb-col-left').removeClass('kpb-col-12').addClass('kpb-col-8');
      jQuery('.kpb-layout > .kpb-col-right').show();
      obj.attr('data-status', '1');
      obj.text(KPB.i18n.hide_preview);
    } else {
      jQuery('.kpb-layout > .kpb-col-left').removeClass('kpb-col-8').addClass('kpb-col-12');
      jQuery('.kpb-layout > .kpb-col-right').hide();
      obj.attr('data-status', '0');
      obj.text(KPB.i18n.show_preview);
    }
  },
  get_random_id: function(prefix) {
    return prefix + Math.random().toString(36).substr(2);
  },
  change_layout: function(event, obj) {
    var new_layout, new_layout_id;
    event.preventDefault();
    new_layout = obj.find('option:selected').val();
    new_layout_id = '#kpb-layout-' + new_layout;
    if (!jQuery(new_layout_id).hasClass('kpb-active')) {
      jQuery('.kpb-layout.kpb-active').removeClass('kpb-active').addClass('kpb-hidden');
      jQuery(new_layout_id).removeClass('kpb-hidden').addClass('kpb-active');
    }
  },
  init_sortable: function() {
    jQuery('.kpb-area-placeholder').sortable({
      forcePlaceholderSize: true,
      connectWith: '.kpb-area-placeholder',
      placeholder: "kpb-widget-sortable-placeholder",
      start: function(e, ui) {
        return ui.placeholder.height(ui.helper.outerHeight() - 2);
      }
    }).disableSelection();
  },
  open_media_center: function() {
    jQuery('.kpb-ui-image-outer').on('click', '.kpb-ui-image-button-upload', function(event) {
      event.preventDefault();
      kpb_media_button_upload = jQuery(this);
      if (kpb_media) {
        kpb_media.open();
        return;
      }
      kpb_media = wp.media.frames.kpb_media = wp.media({
        title: KPB.i18n.media_center,
        button: {
          text: KPB.i18n.choose_image
        },
        library: {
          type: 'image'
        },
        multiple: false
      });
      kpb_media.on('select', function() {
        var attachment;
        attachment = kpb_media.state().get('selection').first().toJSON();
        kpb_media_button_upload.parents('.kpb-ui-image-outer').find('.kpb-ui-image').val(attachment.url);
        kpb_media_button_upload.parents('.kpb-ui-image-outer').find('.kpb-ui-image-preview').attr('src', attachment.url);
      });
      kpb_media.open();
    });
    jQuery('.kpb-ui-image-outer').on('click', '.kpb-ui-image-button-reset', function(event) {
      event.preventDefault();
      kpb_media_button_reset = jQuery(this);
      if (kpb_media_button_reset.attr('data-reset')) {
        kpb_media_button_reset.parents('.kpb-ui-image-outer').find('.kpb-ui-image').val(kpb_media_button_reset.attr('data-reset'));
        kpb_media_button_reset.parents('.kpb-ui-image-outer').find('.kpb-ui-image-preview').attr('src', kpb_media_button_reset.attr('data-reset'));
      } else {
        kpb_media_button_reset.parents('.kpb-ui-image-outer').find('.kpb-ui-image').val(kpb_media_button_reset.attr(''));
        kpb_media_button_reset.parents('.kpb-ui-image-outer').find('.kpb-ui-image-preview').attr('src', kpb_media_button_reset.attr('data-preview'));
      }
    });
  },
  init_section_tab: function() {
    var tabs;
    tabs = jQuery('.kpb-tab-title > a');
    if (tabs.length > 0) {
      tabs.each(function(index, element) {
        var tab;
        tab = jQuery(this);
        tab.click(function(event) {
          var parent, root;
          event.preventDefault();
          root = tab.parents('.kpb-wrapper-configuration-toggle');
          parent = tab.parent();
          if (!parent.hasClass('kpb-tab-title-active')) {
            root.find('.kpb-tab-title-active').removeClass('kpb-tab-title-active');
            root.find('.kpb-tab-content').slideUp(500);
            parent.addClass('kpb-tab-title-active');
            jQuery(tab.attr('href')).slideDown(500);
          }
        });
      });
    }
  },
  change_customize_tab: function(event, obj) {
    var parent, root;
    event.preventDefault();
    root = obj.parents('.kpb-wrapper-configuration-toggle');
    parent = obj.parent();
    if (!parent.hasClass('kpb-tab-title-active')) {
      root.find('.kpb-tab-title-active').removeClass('kpb-tab-title-active');
      root.find('.kpb-tab-content').slideUp(500);
      parent.addClass('kpb-tab-title-active');
      jQuery(obj.attr('href')).slideDown(500);
    }
  }
};
