jQuery(document).ready ->
	Kopa_Page_Builder.load_lightbox_html()
	return

jQuery(window).load ->
	
	return

kpb_current_widget = {}
kpb_current_sidebar = {}
kpb_media = false
kpb_media_button_upload = {}
kpb_media_button_reset = {}

Kopa_Page_Builder =	

	init_effect: () ->
		Kopa_Page_Builder.init_sortable()	
		Kopa_Page_Builder.init_section_tab()	
		Kopa_Page_Builder.sticky_header()		
		Kopa_Page_Builder.open_media_center()
		jQuery('.kpb-ui-color').wpColorPicker()
		return

	load_lightbox_html: () ->
		#if jQuery('#kpb-select-layout').val() != 'disable'		
		jQuery.ajax
			beforeSend: (jqXHR) ->					
				return
			success: (data, textStatus, jqXHR) ->			
				jQuery('body').append(data)					  
				jQuery('#kpb-metabox-loading-text').remove()
				jQuery('#kpb-wrapper').show()
				Kopa_Page_Builder.init_effect()				
				return
			url: KPB.ajax
			dataType: "html"
			type: 'POST'
			async: true
			data:
				action: 'kpb_load_lightbox_html'
				security: jQuery('#kpb_load_lightbox_html_security').val()							
				post_id:jQuery('#post_ID').val()					
		return

	main_form_submit: () ->
		if jQuery('form#post').length > 0
			jQuery('form#post').on 'submit', () ->
				jQuery('#kpb-button-save-layouts').click()
		return

	sticky_header: (event) ->
		if KPB.is_sticky_toolbar is '1'
			jQuery('#kpb-wrapper-header').waypoint 'sticky', 
				direction: 'down'
				stuckClass: 'kpb-stuck'
				wrapper: '<div class="kpb-wrapper-header-sticky" />'			

		return

	open_customize_layout: (event, obj) ->
		event.preventDefault()		
		lightbox = '#kpb-layout-customize-lightbox-' +  jQuery('#kpb-select-layout option:selected').val()

		jQuery.magnificPopup.open
			callbacks:
				open: () ->
					jQuery(lightbox).show()
					return
				close: () ->
					jQuery(lightbox).hide()
					return
			modal: true
			preloader: true
			alignTop: true
			items:
		 		src: lightbox
		 		type: 'inline'

		return
	
	save_layout_customize: (event, obj, post_id) ->
		event.preventDefault()
		Kopa_Page_Builder.show_overlay_loading()

		obj.ajaxSubmit
			beforeSubmit: (arr, $form, options) ->				
				return
			success: (responseText, statusText, xhr, $form) ->								
				Kopa_Page_Builder.hide_overlay_loading()
				return		
			data:
				post_id: post_id	
		return

	close_layout_customize: (event) ->
		event.preventDefault()
		Kopa_Page_Builder.close_widget(event)
		return

	open_customize: (event, obj, layout, section) ->
		event.preventDefault()
				
		lightbox = '#kpb-customize-lightbox-' + layout + '-' + section;		

		jQuery.magnificPopup.open
			callbacks:
				open: () ->
					jQuery(lightbox).show()
					return
				close: () ->
					jQuery(lightbox).hide()
					return
			modal: true
			preloader: true
			alignTop: true
			items:
		 		src: lightbox
		 		type: 'inline'
		return

	save_customize: (event, obj, post_id) ->
		event.preventDefault()
		Kopa_Page_Builder.show_overlay_loading()

		obj.ajaxSubmit
			beforeSubmit: (arr, $form, options) ->				
				return
			success: (responseText, statusText, xhr, $form) ->								
				Kopa_Page_Builder.hide_overlay_loading()
				return		
			data:
				post_id: post_id	
		return

	close_customize: (event) ->
		event.preventDefault()
		Kopa_Page_Builder.close_widget(event)
		return

	open_list_widgets: (event, obj) ->
		event.preventDefault()
		kpb_current_sidebar = obj.parents '.kpb-area'
		lightbox = '#kpb-widgets-lightbox'
		jQuery.magnificPopup.open
			callbacks:
				open: () ->
					jQuery(lightbox).show()
					return
				close: () ->
					jQuery(lightbox).hide()
					return
			modal: true
			preloader: true
			alignTop: true
			items:
		 		src: lightbox
		 		type: 'inline'
		return

	close_list_widgets: (event) ->
		event.preventDefault()
		jQuery.magnificPopup.close()
		return

	add_widget: (event, obj, class_name, widget_name) ->
		event.preventDefault()

		Kopa_Page_Builder.close_list_widgets event

		lightbox = '#kpb-widget-lightbox'
		jQuery.magnificPopup.open
			callbacks:
				open: () ->
					jQuery(lightbox).show()
					jQuery('#kpb-widget-title').text widget_name									
					jQuery('#kpb-widget input[name=kpb-widget-class-name]').val class_name					
					jQuery('#kpb-widget input[name=kpb-widget-name]').val widget_name
					jQuery('#kpb-widget input[name=kpb-widget-action]').val 'add'
					jQuery('#kpb-widget input[name=kpb-widget-id]').val Kopa_Page_Builder.get_random_id 'widget-'

					jQuery.ajax
						success: (data, textStatus, jqXHR) ->
							jQuery('#kpb-widget .kpb-form-inner').html data
							Kopa_Page_Builder.open_media_center()
							jQuery('.kpb-ui-color').wpColorPicker()						
							return
						url: KPB.ajax
						dataType: "html"
						type: 'POST'
						async: true
						data:
							action: 'kpb_get_widget_form'
							security: jQuery('#kpb_get_widget_form_security').val()
							class_name: class_name
					return
				close: () ->
					jQuery(lightbox).hide()
					return
			modal: true
			preloader: true
			alignTop: true
			items:
		 		src: lightbox
		 		type: 'inline'
		return
	
	edit_widget: (event, obj, widget_id) ->
		event.preventDefault()
		kpb_current_widget = obj.parents '.kpb-widget'
		lightbox = '#kpb-widget-lightbox'

		jQuery.magnificPopup.open
			callbacks:
				open: () ->
					jQuery(lightbox).show()
					jQuery('#kpb-widget-title').text kpb_current_widget.find('label').text()							
					jQuery('#kpb-widget input[name=kpb-widget-class-name]').val kpb_current_widget.attr 'data-class'					
					jQuery('#kpb-widget input[name=kpb-widget-name]').val kpb_current_widget.attr 'data-name'
					jQuery('#kpb-widget input[name=kpb-widget-action]').val 'edit'
					jQuery('#kpb-widget input[name=kpb-widget-id]').val widget_id

					jQuery.ajax
						success: (data, textStatus, jqXHR) ->
							jQuery('#kpb-widget .kpb-form-inner').html data
							Kopa_Page_Builder.open_media_center()
							jQuery('.kpb-ui-color').wpColorPicker()
							return
						url: KPB.ajax
						dataType: "html"
						type: 'POST'
						async: true
						data:
							action: 'kpb_get_widget_form'
							security: jQuery('#kpb_get_widget_form_security').val()							
							widget_id: widget_id
							class_name: kpb_current_widget.attr 'data-class'
							post_id: jQuery('#post_ID').val()						

					return
				close: () ->
					jQuery(lightbox).hide()
					return
			modal: true
			preloader: true
			alignTop: true
			items:
		 		src: lightbox
		 		type: 'inline'
		 	fixedBgPos: true		 		

		return
	
	delete_widget: (event, obj, widget_id) ->
		event.preventDefault()
		answer = confirm KPB.i18n.are_you_sure_to_remove_this_widget
		if answer
			jQuery.ajax
				beforeSend: (jqXHR) ->					
					return
				success: (data, textStatus, jqXHR) ->			
					obj.parents('.kpb-widget').remove()
					jQuery('#kpb-button-save-layouts').click()
					return
				url: KPB.ajax
				dataType: "html"
				type: 'POST'
				async: true
				data:
					action: 'kpb_delete_widget'
					security: jQuery('#kpb_delete_widget_security').val()							
					widget_id: widget_id					
		return

	save_widget: (event, obj) ->
		event.preventDefault()
		Kopa_Page_Builder.show_overlay_loading()

		obj.ajaxSubmit
			beforeSubmit: (arr, $form, options) ->				
				return
			success: (responseText, statusText, xhr, $form) ->
				if responseText
					lightbox = '#kpb-widget-lightbox'
					jQuery(lightbox).hide()
					jQuery.magnificPopup.close()

					if 'add' == jQuery('#kpb-widget input[name=kpb-widget-action]').val()
						kpb_current_sidebar.find('.kpb-area-placeholder').append responseText
					else
						if kpb_current_widget
							kpb_current_widget.find('label').text responseText

					jQuery('#kpb-widget-title').text ''
					jQuery('#kpb-widget input[name=kpb-widget-class-name]').val ''				
					jQuery('#kpb-widget input[name=kpb-widget-action]').val 'add'
					jQuery('#kpb-widget input[name=kpb-widget-id]').val ''					
					jQuery('#kpb-widget input[name=kpb-widget-name]').val ''										

				Kopa_Page_Builder.close_widget(event)
				jQuery('#kpb-button-save-layouts').click()

				return
		return

	close_widget: (event) ->
		event.preventDefault()
		jQuery.magnificPopup.close()
		jQuery('#kpb-widget .kpb-form-inner').html '<center class="kpb-loading">' + KPB.i18n.loading + '</center>'
		Kopa_Page_Builder.hide_overlay_loading()
		return

	save_layout: (event, obj) ->
		event.preventDefault()

		current_layout = jQuery('#kpb-select-layout').find('option:selected').val()		

		Kopa_Page_Builder.show_overlay_loading()	
		data =
			current_layout: jQuery('#kpb-select-layout').find('option:selected').val()
			layouts: []

		#LAYOUT
		layouts = jQuery '.kpb-layout'

		if layouts.length > 0
			layouts.each (l_index, l_element) ->
				current_layout = jQuery l_element
				layout_data =
					name: current_layout.attr 'data-layout'
					sections: []

				#SECTION 
				sections = current_layout.find '.kpb-section'

				if sections.length > 0
					sections.each (s_index, s_element) ->
						current_section = jQuery s_element
						section_data =
							name: current_section.attr 'data-section'
							areas: []

						#AREA
						areas = current_section.find '.kpb-area'

						if areas.length > 0
							areas.each (a_index, a_element) ->
								current_area = jQuery a_element
								area_data =
									name: current_area.attr 'data-area'
									widgets: []

								#WIDGET
								widgets = current_area.find '.kpb-widget'

								if widgets.length > 0
									widgets.each (w_index, w_element) ->
										current_widget = jQuery w_element										
										widget_data =
											id: current_widget.attr 'id'											
											name: current_widget.attr 'data-name'
											class_name: current_widget.attr 'data-class'

										area_data.widgets.push widget_data
										return

								section_data.areas.push area_data
								return

						layout_data.sections.push section_data
						return

				data.layouts.push layout_data						
				return		

		jQuery.ajax
			error: (jqXHR, textStatus, errorThrown) ->
				console.log textStatus
				return
			beforeSend: (jqXHR) ->				
				obj.text KPB.i18n.saving
				return 
			success: (data, textStatus, jqXHR) ->
				obj.text KPB.i18n.save
				Kopa_Page_Builder.hide_overlay_loading()
				return
			url: KPB.ajax
			dataType: "html"
			type: 'POST'
			async: true
			data:
				action: 'kpb_save_grid'
				security: jQuery('#kpb_save_grid_security').val()
				data: data
				post_id: jQuery('#post_ID').val()
		return

	show_overlay_loading: () ->
		#jQuery('#kpb-loading-overlay').show()	
		jQuery('#kpb-loading-overlay').stop().animate
			display: 'block'
			right: '0px'
			,500
		return

	hide_overlay_loading: () ->
		#jQuery('#kpb-loading-overlay').hide()
		jQuery('#kpb-loading-overlay').animate
			display: 'none'
			right: '-150px'
			,500
		return

	hide_preview: (event, obj)->
		event.preventDefault()
		
		if obj.attr('data-status') is '0'	
			jQuery('.kpb-layout > .kpb-col-left').removeClass('kpb-col-12').addClass('kpb-col-8')
			jQuery('.kpb-layout > .kpb-col-right').show()
			obj.attr 'data-status', '1'
			obj.text KPB.i18n.hide_preview
		else			
			jQuery('.kpb-layout > .kpb-col-left').removeClass('kpb-col-8').addClass('kpb-col-12')
			jQuery('.kpb-layout > .kpb-col-right').hide()
			obj.attr 'data-status', '0'
			obj.text KPB.i18n.show_preview
		return

	get_random_id: (prefix) ->
        prefix + Math.random().toString(36).substr(2)
	
	change_layout: (event, obj) ->
		event.preventDefault()
		
		new_layout = obj.find('option:selected').val()
		new_layout_id = '#kpb-layout-' + new_layout;
		
		if !jQuery(new_layout_id).hasClass('kpb-active')
			jQuery('.kpb-layout.kpb-active').removeClass('kpb-active').addClass('kpb-hidden')
			jQuery(new_layout_id).removeClass('kpb-hidden').addClass('kpb-active')
		
		return

	init_sortable: () ->
		jQuery('.kpb-area-placeholder').sortable			
			forcePlaceholderSize: true
			connectWith: '.kpb-area-placeholder'
			placeholder: "kpb-widget-sortable-placeholder"
			start: (e, ui) ->
				ui.placeholder.height ui.helper.outerHeight() - 2
		.disableSelection()
		return

	open_media_center: () ->		
		jQuery('.kpb-ui-image-outer').on 'click', '.kpb-ui-image-button-upload', (event)->
			event.preventDefault()

			kpb_media_button_upload = jQuery this
					
			if (kpb_media)
				kpb_media.open()
				return

			kpb_media = wp.media.frames.kpb_media = wp.media
				title: KPB.i18n.media_center
				button:
					text: KPB.i18n.choose_image         
				library:
					type: 'image'
				multiple: false		            

			kpb_media.on 'select', () ->
				attachment = kpb_media.state().get('selection').first().toJSON()
				kpb_media_button_upload.parents('.kpb-ui-image-outer').find('.kpb-ui-image').val attachment.url
				kpb_media_button_upload.parents('.kpb-ui-image-outer').find('.kpb-ui-image-preview').attr 'src', attachment.url
				return				

			kpb_media.open()

			return	

		jQuery('.kpb-ui-image-outer').on 'click', '.kpb-ui-image-button-reset', (event)->
			event.preventDefault()
			kpb_media_button_reset = jQuery this

			if kpb_media_button_reset.attr('data-reset')
				kpb_media_button_reset.parents('.kpb-ui-image-outer').find('.kpb-ui-image').val kpb_media_button_reset.attr 'data-reset'
				kpb_media_button_reset.parents('.kpb-ui-image-outer').find('.kpb-ui-image-preview').attr 'src', kpb_media_button_reset.attr 'data-reset'
			else
				kpb_media_button_reset.parents('.kpb-ui-image-outer').find('.kpb-ui-image').val kpb_media_button_reset.attr ''
				kpb_media_button_reset.parents('.kpb-ui-image-outer').find('.kpb-ui-image-preview').attr 'src', kpb_media_button_reset.attr 'data-preview'				

			return

		return

	init_section_tab: () ->
		tabs = jQuery('.kpb-tab-title > a')
		if tabs.length > 0
			tabs.each (index, element) ->
				tab = jQuery this
				tab.click (event) ->
					event.preventDefault()

					root = tab.parents('.kpb-wrapper-configuration-toggle')
					parent = tab.parent()
					
					if !parent.hasClass('kpb-tab-title-active')
						root.find('.kpb-tab-title-active').removeClass('kpb-tab-title-active')
						root.find('.kpb-tab-content').slideUp 500

						parent.addClass('kpb-tab-title-active')
						jQuery(tab.attr 'href').slideDown 500

					return
				return
		return

	change_customize_tab: (event, obj) ->
		event.preventDefault()
		
		root = obj.parents('.kpb-wrapper-configuration-toggle')
		parent = obj.parent()

		if !parent.hasClass('kpb-tab-title-active')
			root.find('.kpb-tab-title-active').removeClass('kpb-tab-title-active')
			root.find('.kpb-tab-content').slideUp 500

			parent.addClass('kpb-tab-title-active')
			jQuery(obj.attr 'href').slideDown 500

		return	