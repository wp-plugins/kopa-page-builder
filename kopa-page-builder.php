<?php
/* 
Plugin Name: Kopa Page Builder
Description: The visual page builder
Version: 1.1.0
Author: Kopa Theme
Author URI: http://kopatheme.com/
License: GNU General Public License v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Kopa Page Builder plugin, Copyright 2014 Kopatheme.com
Kopa Page Builder is distributed under the terms of the GNU GPL

Requires at least: 4.1
Tested up to: 4.2.3
Text Domain: kopa-page-builder
Domain Path: /languages/
*/

define('KPB_IS_DEV', false);
define('KPB_DIR', plugin_dir_url(__FILE__));
define('KPB_PATH', plugin_dir_path(__FILE__));

add_action('plugins_loaded', array('Kopa_Page_Builder', 'plugins_loaded'));	
add_action( 'after_setup_theme', array('Kopa_Page_Builder', 'after_setup_theme'), 20 );	

class Kopa_Page_Builder {

	function __construct(){		
		add_action('add_meta_boxes', array($this, 'register_meta_boxes'));				
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 20);

		#LIGHTBOX		
		add_action('admin_footer', array($this, 'print_ajax_security'));

		#AJAX
		add_action('wp_ajax_kpb_get_widget_form', array($this, 'get_widget_form'));
		add_action('wp_ajax_kpb_save_widget', array($this, 'save_widget'));
		add_action('wp_ajax_kpb_save_grid', array($this, 'save_grid'));		
		add_action('wp_ajax_kpb_delete_widget', array($this, 'delete_widget'));		
		add_action('wp_ajax_kpb_save_customize', array($this, 'save_customize'));		
		add_action('wp_ajax_kpb_save_layout_customize', array($this, 'save_layout_customize'));						
		add_action('wp_ajax_kpb_load_lightbox_html', array($this, 'load_lightbox_html'));				
		
		#SHORTCODE
		add_shortcode('kpb_home_url', array($this, 'shortcode_home_url'));	

		#CONFIG
		add_filter( 'kopa_theme_options_settings', array($this, 'configuration'), 20, 1);	
	}

	public static function after_setup_theme(){
		if (!class_exists('Kopa_Framework'))
			return; 		
		else	
			new Kopa_Page_Builder();							
	}

	public function configuration($options){
		$options[] = array(
			'title'   => __('Page builder', 'kopa-page-builder'),
			'type'    => 'title',		
			'id'      => 'page-builder'		
		);	
			$options[] = array(
				'title'   => NULL,
				'type' 	  => 'checkbox',				
				'id' 	  => 'is-enable-page-builder-use-sticky-toolbar',
				'default' => 1,
				'label'   => __( 'Use sticky toolbar ?', 'kopa-page-builder'),
			);

		return $options;
	}

	public static function is_page(){
		global $pagenow, $post;
		$is_page = false;

		if( in_array( $pagenow, array('post.php', 'post-new.php')) ){				
			if($post->post_type == 'page'){
				$is_page = true;				
			}
		}	

		return $is_page;
	}

	public static function get_meta_key_current_layout(){
		return apply_filters('kopa_page_builder_get_meta_key_current_layout','kopa_page_builder_current_layout');
	}

	public static function get_meta_key_layout_customize(){
		return apply_filters('kopa_page_builder_get_meta_key_layout_customize','kopa_page_builder_layout_customize');
	}

	public static function get_meta_key_grid(){
		return apply_filters('kopa_page_builder_get_meta_key_grid','kopa_page_builder_data');
	}

	public static function get_meta_key_wrapper(){
		return apply_filters('kopa_page_builder_get_meta_key_wrapper','kopa_page_builder_wrapper');
	}

	public static function get_meta_key_widget_customize(){
		return apply_filters('kopa_page_builder_get_meta_key_widget_customize','kopa_page_builder_widget_customize');
	}

	public static function get_current_layout($post_id){
    	return get_post_meta($post_id, self::get_meta_key_current_layout(), true);		
    }

	public static function get_current_layout_data($post_id, $current_layout = null){
		$current_layout = $current_layout ? $current_layout : self::get_current_layout($post_id);
		$meta_key = sprintf('%s-%s', self::get_meta_key_grid(), $current_layout);		

		return get_post_meta($post_id, $meta_key, true);
	}

	public static function get_current_wrapper_data($post_id, $current_layout, $current_section){
		if(empty($current_layout) || empty($current_section))
			return false;
		
		$meta_key = sprintf('%s-%s-%s', self::get_meta_key_wrapper(), $current_layout, $current_section);	
		return get_post_meta($post_id, $meta_key, true);
	}

	public static function get_layout_customize_data($post_id, $layout_slug){
		if(empty($layout_slug))
			return false;

		$meta_key = sprintf('%s-%s', self::get_meta_key_layout_customize(), $layout_slug);
		return get_post_meta($post_id, $meta_key, true);	
	}

	public function get_control($param_args){
		?>
		<div class="kpb-control kpb-clearfix">
			<div class="kpb-row">
				<div class="kpb-col-3">
					<?php echo esc_attr($param_args['title']);?>
				</div>

				<div class="kpb-col-9">
					<?php
					switch ($param_args['type']) {
						case 'color':
							$this->get_field_color($param_args);
							break;
						case 'image':
							$this->get_field_image($param_args);
							break;
						case 'select':
							$this->get_field_select($param_args);
							break;		
						case 'text':
							$this->get_field_text($param_args);
							break;																				
						case 'number':
							$this->get_field_number($param_args);
							break;	
						case 'checkbox':
							$this->get_field_checkbox($param_args);	
							break;
						case 'radio':
							$this->get_field_radio($param_args);
							break;
                        case 'radio_image':
                            $this->get_field_radio_image($param_args);
                            break;
						case 'textarea':
							$this->get_field_textarea($param_args);
							break;
						case 'icon':
							$this->get_field_icon($param_args);
							break;											
					}
					if(isset($param_args['help']) && !empty($param_args['help'])){
						?>
						<div class="kpb-ui-help-text"><?php echo stripcslashes($param_args['help']); ?></div>
						<?php
					}
					?>					
				</div>
			</div>
		</div>
		<?php
	}

	public static function plugins_loaded(){
		load_plugin_textdomain('kopa-page-builder', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}

	public function admin_enqueue_scripts($hook){
		if (self::is_page()) {
			$prefix = 'kopa_page_builder_';
			$affix = KPB_IS_DEV ? '' : '.min';			
			wp_enqueue_style( 'wp-color-picker');			
			wp_enqueue_style( 'jquery-magnific-popup', plugins_url("css/magnific-popup{$affix}.css", __FILE__), NULL, NULL);			
			wp_enqueue_style( $prefix . 'style', plugins_url("css/style{$affix}.css", __FILE__), NULL, NULL);

			wp_enqueue_script( 'jquery-form' );
            wp_enqueue_script( 'json2' );            
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'jquery-magnific-popup', plugins_url("js/jquery.magnific-popup{$affix}.js", __FILE__), array('jquery'), NULL, TRUE);			
			wp_enqueue_script( 'jquery-waypoints', plugins_url("js/waypoints{$affix}.js", __FILE__), array('jquery'), NULL, TRUE);
			wp_enqueue_script( 'jquery-waypoints-sticky', plugins_url("js/waypoints-sticky{$affix}.js", __FILE__), array('jquery'), NULL, TRUE);						
			wp_enqueue_script( $prefix . 'script', plugins_url("js/script{$affix}.js", __FILE__), array('jquery'), NULL, TRUE);

            wp_enqueue_script('kopa_media_uploader');

			wp_localize_script($prefix . 'script', 'KPB', array(   
				'is_sticky_toolbar' => get_theme_mod('is-enable-page-builder-use-sticky-toolbar', 1),             
                'ajax' => admin_url('admin-ajax.php'),
                'i18n' => array(     
					'media_center'                       => __('Media center', 'kopa-page-builder'),
					'choose_image'                       => __('Choose image', 'kopa-page-builder'),
					'loading'                            => __('Loading...', 'kopa-page-builder'),
					'save'                               => __('Save', 'kopa-page-builder'),
					'saving'                             => __('Saving...', 'kopa-page-builder'),
					'hide_preview'                       => __('Hide preview', 'kopa-page-builder'),
					'show_preview'                       => __('Show preview', 'kopa-page-builder'),
					'are_you_sure_to_remove_this_widget' => __('Are you sure to remove this widget ?', 'kopa-page-builder'),     	               
                )
            ));
		}
	}

	public function register_meta_boxes(){
		add_meta_box(
			'kopa_page_builder_meta_boxes',
			__( 'Page Builder', 'kopa-page-builder'),
			array($this, 'get_meta_boxes'),
			'page'
		);	
	}	

	public function get_meta_boxes(){
		global $post;
		
		wp_nonce_field( 'kopa_page_builder_meta_boxes', 'kopa_page_builder_meta_boxes_security' );
		
		$_layouts = apply_filters('kopa_page_builder_get_layouts', array());
		$_areas = apply_filters('kopa_page_builder_get_areas', array());
		$_section_fields = apply_filters('kopa_page_builder_get_section_fields', array());


		$current_layout = self::get_current_layout($post->ID);		
		?>
		<p id="kpb-metabox-loading-text"><img src="<?php echo KPB_DIR . '/images/loading.gif'; ?>" width="16px" height="16px">&nbsp;<?php _e('Loading..', 'kopa-page-builder'); ?></p>
		<section id="kpb-wrapper" style="display: none;">
			<header id="kpb-wrapper-header" class="kpb-clearfix">												
				<select id="kpb-select-layout" name="kpb-select-layout" class="kpb-pull-left" onchange="Kopa_Page_Builder.change_layout(event, jQuery(this));" autocomplete=off>					
					<?php
					$_is_first_layout = true;					
					foreach ($_layouts as $slug => $layout) {
						$_selected = $current_layout ? (($current_layout == $slug) ? 'selected="selected"' : '') : ($_is_first_layout ? 'selected="selected"' : '');
						printf('<option value="%s" %s>%s</option>', $slug, $_selected, $layout['title']);
						$_is_first_layout = false;
					}
					?>
				</select>											

				<a id="kpb-button-save-layouts" href="#" onclick="Kopa_Page_Builder.save_layout(event, jQuery(this));" class="button button-primary button-large kpb-pull-right" data-status='1'><?php _e('Save', 'kopa-page-builder'); ?></a>		

				<a id="kpb-button-hide-preview" href="#" onclick="Kopa_Page_Builder.hide_preview(event, jQuery(this));" class="button button-link button-large kpb-pull-right"><?php _e('Hide preview', 'kopa-page-builder'); ?></a>

				<a id="kpb-button-customize-layout" href="#" onclick="Kopa_Page_Builder.open_customize_layout(event, jQuery(this));" class="button button-link button-large kpb-pull-right"><?php _e('Customize', 'kopa-page-builder'); ?></a>
			</header>

			<?php 
			$_is_first_layout = true;
			$_layout_index = 0;
			foreach ($_layouts as $layout_slug => $layout): 						
				$current_layout_data = self::get_current_layout_data($post->ID, $layout_slug);
				
				$_classes = array('kpb-layout', 'kpb-row');				
				$_classes[] = $current_layout ? (($current_layout == $layout_slug) ? 'kpb-active' : 'kpb-hidden') : ($_is_first_layout ? 'kpb-active' : 'kpb-hidden');				
			?>

			<div id="<?php echo "kpb-layout-{$layout_slug}"; ?>" class="<?php echo implode(' ', $_classes); ?>" data-layout="<?php echo $layout_slug; ?>">
				<?php if(isset($layout['section']) && !empty($layout['section'])): ?>
					<div class="kpb-col-left kpb-col-8">
						<?php 
						if( $sections = isset($layout['section']) && !empty($layout['section']) ? $layout['section'] : false):							

							$_is_first_section = true;
							foreach ($sections as $section_slug => $section) :
								$_section_classes = ($_is_first_section) ? 'kpb-section kpb-first' : 'kpb-section';								
							?>
								<aside id="<?php echo "kpb-section-{$section_slug}-for-layout{$layout_slug}"; ?>" data-section="<?php echo $section_slug; ?>" class="<?php echo $_section_classes; ?>">
									<header class="kpb-section-header kpb-clearfix">
										<label class="kpb-pull-left"><?php echo esc_attr($section['title']); ?></label>
										
										<?php if(!empty($_section_fields)): ?>
											<a href="#"	
											onclick="Kopa_Page_Builder.open_customize(event, jQuery(this), '<?php echo $layout_slug;?>', '<?php echo $section_slug; ?>');" 
											class="kpb-button-customize kpb-pull-right" ></a>		
										<?php endif; ?>
									</header>
									<div class="kpb-section-placeholder">
										<div class="kpb-row">										
											<?php 
											
											if( $areas = isset($section['area']) && !empty($section['area']) ? $section['area'] : false):							
												
												$_section_grid = $section['grid'];


												foreach ($areas as $area_index => $area):
													if(is_array($area)):
														?>
														<div class="<?php printf('kpb-col-%d', (int)$_section_grid[$area_index]); ?>">														
																<?php
																$sub_grids = $area['grid'];
																$sub_areas = $area['area'];

																foreach($sub_areas as $sub_area_index => $sub_area){
																	$sub_area_class = (0 == $sub_area_index) ? 'kpb-row-sub-area-first' : '';
																	?>
																	<div class="kpb-row kpb-clearfix kpb-row-sub-area <?php echo $sub_area_class;?>">
																		<?php
																		foreach($sub_area as $child_area_area => $child_area):
																			$_area_name    = $_areas[$child_area];
																			$_area_classes =  array('kpb-area');
																			?>
																			<div class="<?php printf('kpb-col-%d', (int)$sub_grids[$sub_area_index][$child_area_area]); ?>">
																				<div id="<?php echo "kpb-area-{$child_area}-for-section{$section_slug}"; ?>" data-area="<?php echo $child_area; ?>" class="<?php echo implode(' ', $_area_classes); ?>">
																					<header class="kpb-area-header kpb-clearfix">
																						<label><?php echo esc_attr($_area_name); ?></label>
																						<br/>
																						<a href="#"	
																						onclick="Kopa_Page_Builder.open_list_widgets(event, jQuery(this));" 
																						class="kpb-button-add-widget"><?php _e('Add widget', 'kopa-page-builder'); ?></a>		
																					</header>

																					<div class="kpb-area-placeholder">
																						<?php																														
																							if( isset($current_layout_data[$section_slug][$child_area]) &&  
																								!empty($current_layout_data[$section_slug][$child_area])):											

																								$widgets = $current_layout_data[$section_slug][$child_area];
																			
																								foreach ($widgets as $widget_id => $widget):	
																									$widget_data = get_post_meta($post->ID, $widget_id,true);

																									$widget_title = isset($widget_data['widget']['title']) && !empty($widget_data['widget']['title']) ? $widget['name'] . ' : ' . $widget_data['widget']['title'] : $widget['name'];
																																										
																									?>
																									<aside id="<?php echo esc_attr($widget_id); ?>" class="kpb-widget" data-class="<?php echo esc_attr($widget['class_name']); ?>" data-name="<?php echo esc_attr($widget['name']); ?>">
																					                	<div class="kpb-widget-inner kpb-clearfix">							                
																					                		<label class=""><?php echo esc_attr($widget_title); ?></label>	
																					                		<br/>	
																					                		<div class="kpb-widget-action kpb-clearfix">				                		
																					                			<a href="#" onclick="Kopa_Page_Builder.edit_widget(event, jQuery(this), '<?php echo esc_attr($widget_id); ?>');" class="kpb-button-edit kpb-pull-left"><?php _e('Edit', 'kopa-page-builder'); ?></a>															                		
																						                		<a href="#" onclick="Kopa_Page_Builder.delete_widget(event, jQuery(this), '<?php echo esc_attr($widget_id); ?>');" class="kpb-button-delete kpb-pull-left"><?php _e('Delete', 'kopa-page-builder'); ?></a>															                		
																					                		</div>
																										</div>                    
																					                </aside>
																			                		<?php 												                		
																		                		endforeach;
														
																			                endif; 						                
																			                ?>
																					</div>

																				</div>
																			</div>
																			<?php
																		endforeach;
																		?>
																	</div>																															
																	<?php
																}
																?>														
														</div>
														<?php
													else:
														$_area_name = $_areas[$area];
														$_area_classes =  array('kpb-area');
														?>
														<div class="<?php printf('kpb-col-%d', (int)$_section_grid[$area_index]); ?>">
															<div id="<?php echo "kpb-area-{$area}-for-section{$section_slug}"; ?>" data-area="<?php echo $area; ?>" class="<?php echo implode(' ', $_area_classes); ?>">
																<header class="kpb-area-header kpb-clearfix">
																	<label><?php echo esc_attr($_area_name); ?></label>
																	<br/>
																	<a href="#"	
																	onclick="Kopa_Page_Builder.open_list_widgets(event, jQuery(this));" 
																	class="kpb-button-add-widget"><?php _e('Add widget', 'kopa-page-builder'); ?></a>		
																</header>

																<div class="kpb-area-placeholder">
																	<?php																														
																		if( isset($current_layout_data[$section_slug][$area]) &&  
																			!empty($current_layout_data[$section_slug][$area])):											

																			$widgets = $current_layout_data[$section_slug][$area];
														
																			foreach ($widgets as $widget_id => $widget):	
																				$widget_data = get_post_meta($post->ID, $widget_id,true);

																				$widget_title = isset($widget_data['widget']['title']) && !empty($widget_data['widget']['title']) ? $widget['name'] . ' : ' . $widget_data['widget']['title'] : $widget['name'];
																																					
																				?>
																				<aside id="<?php echo esc_attr($widget_id); ?>" class="kpb-widget" data-class="<?php echo esc_attr($widget['class_name']); ?>" data-name="<?php echo esc_attr($widget['name']); ?>">
																                	<div class="kpb-widget-inner kpb-clearfix">							                
																                		<label class=""><?php echo esc_attr($widget_title); ?></label>	
																                		<br/>	
																                		<div class="kpb-widget-action kpb-clearfix">				                		
																                			<a href="#" onclick="Kopa_Page_Builder.edit_widget(event, jQuery(this), '<?php echo esc_attr($widget_id); ?>');" class="kpb-button-edit kpb-pull-left"><?php _e('Edit', 'kopa-page-builder'); ?></a>															                		
																	                		<a href="#" onclick="Kopa_Page_Builder.delete_widget(event, jQuery(this), '<?php echo esc_attr($widget_id); ?>');" class="kpb-button-delete kpb-pull-left"><?php _e('Delete', 'kopa-page-builder'); ?></a>															                		
																                		</div>
																					</div>                    
																                </aside>
														                		<?php 												                		
													                		endforeach;
									
														                endif; 						                
														                ?>
																</div>

															</div>
														</div>
													<?php
													endif;
												endforeach;
											endif;
											?>
										</div>
									</div>
								</aside>
							<?php					
							$_is_first_section = false;		
							endforeach;
						endif;
						?>
					</div>
				<?php endif; ?>

				<?php if(isset($layout['preview']) && !empty($layout['preview'])): ?>
					<div class="kpb-col-right kpb-col-4">
						<span class="kpb-preview-images"><img src="<?php echo $layout['preview']; ?>"></span>					
					</div>
				<?php endif; ?>
			</div>

			<?php 
				$_is_first_layout = false;
				$_layout_index++;
			endforeach;			
			?>
		</section>
		<?php
	}

	public function print_ajax_security(){
		wp_nonce_field("kpb_get_widget_form", "kpb_get_widget_form_security", FALSE);
        wp_nonce_field("kpb_delete_widget", "kpb_delete_widget_security", FALSE);
        wp_nonce_field("kpb_save_grid", "kpb_save_grid_security", FALSE);
        wp_nonce_field("kpb_load_lightbox_html", "kpb_load_lightbox_html_security", FALSE);       	
	}

	public function get_widget_form(){
		check_ajax_referer('kpb_get_widget_form', 'security');

        if ($_POST['class_name']) {
            $class_name = $_POST['class_name'];
            $instance = array();
            $customize_data = array();

            if (isset($_POST['widget_id'])) {
                $widget_id = $_POST['widget_id'];

                $post_id = (int) $_POST['post_id'];
                $data = get_post_meta($post_id, $widget_id, true);

                $instance = $data['widget'];
                $customize_data = isset($data['customize']) ? $data['customize'] : array();
            }

            $widget = new $class_name;
            $widget->id_base = rand(0, 9999);
            $widget->number = rand(0, 9999);            

            if(isset($widget->kpb_is_private)){
            	$widget->kpb_is_private = false;
            }
            

            $customize_key = self::get_meta_key_widget_customize();
            
            $customize_fields = apply_filters('kopa_page_builder_get_customize_fields', array());                      

            if(!empty($customize_fields)){
            	?>
            	<section class="kpb-widget-customize kpb-wrapper-configuration">
            		<div class="kpb-wrapper-configuration-toggle">
						<nav>
							<ul class="kpb-clearfix">
								<li class="kpb-tab-title kpb-tab-title-first kpb-tab-title-active">
									<a href="<?php echo "#kpb-tab-widget-{$widget->id_base}"; ?>" onclick="Kopa_Page_Builder.change_customize_tab(event, jQuery(this));"><?php _e('Widget', 'kopa-page-builder'); ?></a>
								</li>

								<?php									
								foreach ($customize_fields  as $tab_slug => $tab):									
									$tab_id = $tab_slug . '-tab-' . $widget->id_base;
									?>
									<li class="kpb-tab-title">
										<a href="<?php echo "#{$tab_id}"; ?>"  onclick="Kopa_Page_Builder.change_customize_tab(event, jQuery(this));"><?php echo esc_attr($tab['title']); ?></a>
									</li>
									<?php	
								endforeach;
								?>
							</ul>
						</nav>
					
						<div id="<?php echo "kpb-tab-widget-{$widget->id_base}"; ?>" class="kpb-tab-content">
							<?php $widget->form($instance); ?>
						</div>

						<?php	
						foreach ($customize_fields  as $tab_slug => $tab):		
							$tab_id = $tab_slug . '-tab-' . $widget->id_base;
							?>
							<div id="<?php echo $tab_id; ?>" class="kpb-tab-content" style="display:none;">
								<?php 
								foreach ($tab['params'] as $param_key => $param_args):

									$param_args['name'] = sprintf('%s[%s][%s]', $customize_key, $tab_slug, $param_key);												
									$param_args['value'] = isset($customize_data[$tab_slug][$param_key]) ? $customize_data[$tab_slug][$param_key] : (isset($param_args['default']) ? $param_args['default'] : null);														
									
									$this->get_control($param_args);

								endforeach;
								?>
							</div>
							<?php							
						endforeach;
						?>	
            	</section>            
            	<?php
            }else{
            	$widget->form($instance);
            }
        }

        exit();
	}

	public function load_lightbox_html(){
		check_ajax_referer('kpb_load_lightbox_html', 'security');

		if(isset($_POST['post_id'])){
			$post_id = $_POST['post_id'];

			global $wp_widget_factory;				
			?>

			<div id="kpb-loading-overlay">
				<span><?php _e('Progressing..', 'kopa-page-builder'); ?></span>
			</div>

			<div id="kpb-widgets-lightbox" style="display: none;">
				<section id="kpb-widgets">	
					<header id="kpb-widgets-header" class="kpb-clearfix">
						<label class="kpb-pull-left"><?php _e('Avaiable Widgets', 'kopa-page-builder'); ?></label>
						<a href="#" onclick="Kopa_Page_Builder.close_list_widgets(event);" class="button button-link button-delete kpb-pull-right"><?php _e('Close', 'kopa-page-builder'); ?></a>
					</header>	
					<div class="kpb-widgets-inner">	
			            <?php
			            $widgets = $wp_widget_factory->widgets;
			           
			            $widgets = apply_filters('kpb_get_widgets_list', $widgets);

			            $widgets_inprocess = array();
			            $blocks = array();

			            foreach ($widgets as $class_name => $widget_info){
			            	if (isset($widget_info->kpb_group) && !empty($widget_info->kpb_group)){
			            		$group_slug = $widget_info->kpb_group;
			            	}else{
			            		if(strpos(strtolower($widget_info->name), 'bbpress')){
			            			$group_slug = 'bbpress';
			            		}else if (strpos(strtolower($widget_info->name), 'commerce')){
			            			$group_slug = 'product';
			            		}else{
			            			$group_slug = 'widgets';
			            		}			            		
			            	}
			            	 
							
							if(!isset($blocks[$group_slug])){
								$blocks[$group_slug]['title'] = $this->str_beautify($group_slug);							
							}

							$blocks[$group_slug]['items'][$class_name] = $widget_info;
			            }

			            ksort($blocks);

			            ?>
			            <div class="kpb-wrapper-configuration">
				            <div class="kpb-wrapper-configuration-toggle">
					            <nav id="kpb-nav-list-blocks">
									<ul class="kpb-clearfix">
										<?php	
										$_is_first_tab = true;									
										foreach($blocks as $block_slug => $block_info):
											$classes = $_is_first_tab ? 'kpb-tab-title kpb-tab-title-first kpb-tab-title-active' : 'kpb-tab-title';
											$tab_id = $this->str_uglify("kpb-list-{$block_slug}-blocks");
											?>
											<li class="<?php echo $classes;?>">
												<a href="<?php echo "#{$tab_id}"; ?>"><?php echo esc_attr($block_info['title']); ?></a>
											</li>
											<?php	
											$_is_first_tab = false;
										endforeach;
										?>
									</ul>
								</nav>

				            	<?php
					            $index_block = 0;
					            $_is_first_tab = true;

					            foreach($blocks as $block_slug => $block_info):
					            	$blocks_classes = (0 == $index_block) ? 'kpb-tab-content kpb-list-blocks kpb-list-blocks-first kpb-clearfix' : 'kpb-tab-content kpb-list-blocks kpb-clearfix';
					            	$display = $_is_first_tab ? 'block' : 'none';										
									$tab_id = $this->str_uglify("kpb-list-{$block_slug}-blocks");										
					            	?>
					            	<div id="<?php echo $tab_id; ?>" class="<?php echo $blocks_classes; ?>" style="display: <?php echo $display;?>">					            		
						            	<?php
						            		$index_global = 1;
					            			$index_single = 1;

							            	$widgets = $block_info['items'];
							            	
							            	ksort($widgets);

								            foreach ($widgets as $class_name => $widget_info):
								                if (1 == $index_single || ($index_single % 5 == 0)) {
								                	if(1 == $index_global){
								                		echo '<div class="kpb-row kpb-first">';	
								                	}else{
								                		echo '<div class="kpb-row">';
								                	}	                    
								                }
								                ?>
								                <aside class="kpb-widget kpb-col-3">
								                	<div class="kpb-widget-inner">
									                	<header class="kpb-clearfix">
									                		<label class="kpb-pull-left"><?php echo $widget_info->name; ?></label>
									                		<a href="#" onclick="Kopa_Page_Builder.add_widget(event, jQuery(this), '<?php echo $class_name; ?>', '<?php echo $widget_info->name; ?>');" class="kpb-button-use kpb-pull-right"><?php _e('use', 'kopa-page-builder'); ?></a>
									                	</header>   

														<div class="kpb-widget-description">						                             
									                        <span><?php echo $widget_info->widget_options['description']; ?></span>
														</div>
													</div>                    
								                </aside>
								                <?php
								                if (($index_single % 4 == 0) || ($index_global == count($widgets))) {
								                    echo '</div>';
								                    $index_single = 1;
								                } else {
								                    $index_single++;
								                }

								                $index_global++;
								            endforeach;
							            ?>
						        	</div>
					            	<?php
					            	$_is_first_tab = false;
					            	$index_block++;
					            endforeach;
					            ?>
					        </div>
					    </div>
		        	</div>
				</section>
			</div>

			<div id="kpb-widget-lightbox" style="display: none;">
	            <section id="kpb-widget">            	
		            <form id="kpb-form-widget" name="kpb-form-widget"  method="POST" autocomplete="off" onsubmit="Kopa_Page_Builder.save_widget(event, jQuery(this));" action="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=kpb_save_widget'), 'kpb_save_widget', 'security')); ?>">
		             	<header id="kpb-widget-header" class="kpb-clearfix">
							<label id="kpb-widget-title" class="kpb-pull-left"><?php _e('Widget Name', 'kopa-page-builder'); ?></label>
							<a href="#" onclick="Kopa_Page_Builder.close_widget(event);" class="button button-link button-delete kpb-pull-right"><?php _e('Close', 'kopa-page-builder'); ?></a>
						</header>

		             	<div class="kpb-form-inner">
		             		<center class="kpb-loading"><?php _e('Loading...', 'kopa-page-builder'); ?></center>
		             	</div>	             	

		                <input type="hidden" name="kpb-widget-class-name" value="" autocomplete=off>
		                <input type="hidden" name="kpb-widget-name" value="" autocomplete=off>	                
		                <input type="hidden" name="kpb-widget-id" value="" autocomplete=off>             
		                <input type="hidden" name="kpb-widget-action" value="add" autocomplete=off>                                         
		                <input type="hidden" name="kpb-post-id" value="<?php echo (int)$post_id; ?>" autocomplete=off>
		            
		                <footer id="kpb-widget-footer" class="kpb-clearfix">
			            	<button type="submit" class="button button-primary kpb-pull-right"><?php _e('Save', 'kopa-page-builder'); ?></button>	            	
			            </footer>  

		            </form>

	            </section>
	        </div>

	        <?php     
	        $_layouts = apply_filters('kopa_page_builder_get_layouts', array());
			$_areas = apply_filters('kopa_page_builder_get_areas', array());
			$_section_fields = apply_filters('kopa_page_builder_get_section_fields', array());	
			$meta_key = self::get_meta_key_wrapper();
			
			foreach ($_layouts as $layout_slug => $layout) :
				if( $sections = isset($layout['section']) && !empty($layout['section']) ? $layout['section'] : false):							
					foreach ($sections as $section_slug => $section):	
					$data = self::get_current_wrapper_data($post_id, $layout_slug, $section_slug);
					?>
					<div id="<?php echo "kpb-customize-lightbox-{$layout_slug}-{$section_slug}"; ?>" class="kpb-customize-lightbox" style="display: none;">	        	
			        	<section class="kpb-customize">
			        		<form name="<?php echo "kpb-form-customize-layout-{$layout_slug}-section-{$section_slug}" ?>"  method="POST" autocomplete="off" onsubmit="Kopa_Page_Builder.save_customize(event, jQuery(this), <?php echo (int) $post_id;?>);" action="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=kpb_save_customize'), 'kpb_save_customize', 'security')); ?>">
			        			<input type="hidden" name="layout" value="<?php echo $layout_slug; ?>" autocomplete="off">
			        			<input type="hidden" name="section" value="<?php echo $section_slug; ?>" autocomplete="off">

			        			<header class="kpb-customize-header kpb-clearfix">
									<label class="kpb-section-title kpb-pull-left"><?php echo $section['title']; ?></label>

									<a href="#" onclick="Kopa_Page_Builder.close_customize(event);" class="button button-link button-delete kpb-pull-right"><?php _e('Close', 'kopa-page-builder'); ?></a>
									<button type="submit" class="button button-primary kpb-pull-right"><?php _e('Save', 'kopa-page-builder'); ?></button>																
								</header>

								<div class="kpb-form-inner kpb-clearfix">
									<div id="<?php echo $section_slug; ?>" class="kpb-wrapper-configuration">									
										<div class="kpb-wrapper-configuration-toggle">
											<nav>
												<ul class="kpb-clearfix">
													<?php	
													$_is_first_tab = true;									
													foreach ($_section_fields  as $fields_slug => $fields):
														$classes = $_is_first_tab ? 'kpb-tab-title kpb-tab-title-first kpb-tab-title-active' : 'kpb-tab-title';
														$tab_id = $section_slug . '-field-' . $fields_slug;
														?>
														<li class="<?php echo $classes;?>">
															<a href="<?php echo "#{$tab_id}"; ?>"><?php echo esc_attr($fields['title']); ?></a>
														</li>
														<?php	
														$_is_first_tab = false;
													endforeach;
													?>
												</ul>
											</nav>

											<?php	
											$_is_first_tab = true;									
											foreach ($_section_fields  as $fields_slug => $fields):
												$display = $_is_first_tab ? 'block' : 'none';										
												$tab_id = $section_slug . '-field-' . $fields_slug;
												?>
												<div id="<?php echo $tab_id; ?>" class="kpb-tab-content" style="display:<?php echo $display;?>;">
													<?php 
													foreach ($fields['params'] as $param_key => $param_args):

														$param_args['name'] = sprintf('%s-%s-%s[%s][%s]', $meta_key, $layout_slug, $section_slug, $fields_slug, $param_key);												
														$param_args['value'] = isset($data[$fields_slug][$param_key]) ? $data[$fields_slug][$param_key] : (isset($param_args['default']) ? $param_args['default'] : null);														
														
														$this->get_control($param_args);

													endforeach;
													?>
												</div>
												<?php
												$_is_first_tab = false;
											endforeach;
											?>		
										</div>
									</div>									
								</div>
			        		</form>
			        	</section>
			        </div>
					<?php
					endforeach;
				endif;

				if( isset($layout['customize']) && !empty($layout['customize'])){
					$data = self::get_layout_customize_data($post_id, $layout_slug);				
					?>
					<div id="<?php echo "kpb-layout-customize-lightbox-{$layout_slug}"; ?>" class="kpb-customize-lightbox" style="display: none;">
			        	<section class="kpb-customize">
			        		<form name="<?php echo "kpb-form-customize-layout-{$layout_slug}" ?>"  method="POST" autocomplete="off" onsubmit="Kopa_Page_Builder.save_layout_customize(event, jQuery(this), <?php echo (int) $post_id;?>);" action="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=kpb_save_layout_customize'), 'kpb_save_layout_customize', 'security')); ?>">
			        			<input type="hidden" name="layout" value="<?php echo $layout_slug; ?>" autocomplete="off">

			        			<header class="kpb-customize-header kpb-clearfix">
									<label class="kpb-section-title kpb-pull-left"><?php echo $layout['title']; ?></label>
									<a href="#" onclick="Kopa_Page_Builder.close_layout_customize(event);" class="button button-link button-delete kpb-pull-right"><?php _e('Close', 'kopa-page-builder'); ?></a>
									<button type="submit" class="button button-primary kpb-pull-right"><?php _e('Save', 'kopa-page-builder'); ?></button>																
								</header>

								<div class="kpb-form-inner kpb-clearfix">
									<div class="kpb-wrapper-configuration">									
										<div class="kpb-wrapper-configuration-toggle">
											<nav>
												<ul class="kpb-clearfix">
													<?php	
													$_is_first_tab = true;																				
													foreach ($layout['customize']  as $tab_slug => $tab):
														$classes = $_is_first_tab ? 'kpb-tab-title kpb-tab-title-first kpb-tab-title-active' : 'kpb-tab-title';
														$tab_id = 'kpb-layout-customize-' . $layout_slug . '-tab-' . $tab_slug;
														?>
														<li class="<?php echo $classes;?>">
															<a href="<?php echo "#{$tab_id}"; ?>"><?php echo esc_attr($tab['title']); ?></a>
														</li>
														<?php	
														$_is_first_tab = false;
													endforeach;
													?>
												</ul>
											</nav>

											<?php	
											$_is_first_tab = true;									
											foreach ($layout['customize']  as $tab_slug => $tab):
												$display = $_is_first_tab ? 'block' : 'none';										
												$tab_id = 'kpb-layout-customize-' . $layout_slug . '-tab-' . $tab_slug;
												?>
												<div id="<?php echo $tab_id; ?>" class="kpb-tab-content" style="display:<?php echo $display;?>;">
													<?php 
													foreach ($tab['params'] as $param_key => $param_args):

														$param_args['name'] = sprintf('%s-%s[%s][%s]', self::get_meta_key_layout_customize(), $layout_slug, $tab_slug, $param_key);												
														$param_args['value'] = isset($data[$tab_slug][$param_key]) ? $data[$tab_slug][$param_key] : (isset($param_args['default']) ? $param_args['default'] : null);														
														
														$this->get_control($param_args);

													endforeach;
													?>
												</div>
												<?php
												$_is_first_tab = false;
											endforeach;
											?>		
										</div>
									</div>									
								</div>
			        		</form>
			        	</section>
			        </div>
					<?php
				}				
			endforeach;	
		}

		exit();
	}

	public function save_widget() {
		check_ajax_referer('kpb_save_widget', 'security');

		if (!empty($_POST)) {
			$data = $_POST;

			$post_id = 0;
            $widget_id = '';
            $option['widget'] = array();
            $option['class_name'] = array();

            $customize_key = self::get_meta_key_widget_customize();

            foreach ($data as $key => $value) {
                if ('widget' == substr($key, 0, 6)) {
                    $option['widget'] = reset($value);
                } else if ('kpb-widget-class-name' == $key) {
                    $option['class_name'] = $value;
                } else if ('kpb-widget-id' == $key) {
                    $widget_id = $value;
                } else if ('kpb-post-id' == $key) {
                    $post_id = (int) $value;
                } else if ('kpb-widget-name' == $key){
                	$option['name'] = $value;
                } else if($customize_key == $key){
                	$option['customize'] = $value;
                }
            }

            //VALIDATE DATA
            $obj = new $option['class_name'];
            $option['widget'] = $obj->update($option['widget'], array());

            update_post_meta($post_id, $widget_id, $option);

            $widget_title = (isset($option['widget']['title']) && !empty($option['widget']['title']))  ? $option['name'] . ' : ' . $option['widget']['title'] : $option['name'];

            ob_start();            
            if ('add' == $_POST['kpb-widget-action']):
                ?>
            	<aside id="<?php echo esc_attr($widget_id); ?>" class="kpb-widget" data-class="<?php echo esc_attr($option['class_name']); ?>" data-name="<?php echo esc_attr($option['name']); ?>">
                	<div class="kpb-widget-inner kpb-clearfix">							                
                		<label class=""><?php echo $widget_title; ?></label>	
                		<div  class="kpb-widget-action kpb-clearfix">				                			                		
	                		<a href="#" onclick="Kopa_Page_Builder.edit_widget(event, jQuery(this), '<?php echo esc_attr($widget_id); ?>');" class="kpb-button-edit kpb-pull-left"><?php _e('Edit', 'kopa-page-builder'); ?></a>
	                		<a href="#" onclick="Kopa_Page_Builder.delete_widget(event, jQuery(this), '<?php echo esc_attr($widget_id); ?>');" class="kpb-button-delete kpb-pull-left"><?php _e('Delete', 'kopa-page-builder'); ?></a>
						</div>	                		
					</div>                    
                </aside>                
                <?php
            else:
                echo $widget_title;
            endif;

            $html = ob_get_clean();            

            echo $html;
		}

		exit();
	}

	public function delete_widget(){
		check_ajax_referer('kpb_delete_grid', 'security');

		 if (isset($_POST['widget_id']) && isset($_POST['post_id'])) {
		 	$post_id = (int) $_POST['post_id'];
		 	$widget_id = $_POST['widget_id'];

            delete_post_meta($post_id, $widget_id);
        }

		exit();
	}

	public function save_grid(){
		check_ajax_referer('kpb_save_grid', 'security');

		$post_id = $_POST['post_id'];

        if (!empty($_POST['data'])) {
            $data = $_POST['data'];            

            $layouts = isset($data['layouts']) && !empty($data['layouts']) ? $data['layouts'] : false;
            if($layouts){
            	$current_layout = $data['current_layout'];
	            
	            foreach ($layouts as $layout_index => $layout) {            	
	            	
	            	
	            	$sections = isset($layout['sections']) && !empty($layout['sections']) ? $layout['sections'] : false;

	            	if($sections){            	
	            		$_sections = array();
	            		foreach ($sections as $section_index => $section) {            			            		
	            			$areas = isset($section['areas']) && !empty($section['areas']) ? $section['areas'] : false;

	            			if($areas){

	            				$_areas = array();

	            				foreach ($areas as $area_index => $area) {		            			
			            			$widgets = isset($area['widgets']) && !empty($area['widgets']) ? $area['widgets'] : false;
			            			if($widgets){		            				
			            				$_widgets = array();
			            				foreach ($widgets as $widget_index => $widget) {			            	
				            				$_widgets[$widget['id']] = array(												
	                                            'name' => $widget['name'],
	                                            'class_name' => $widget['class_name']
				            				);
			            				}
			            				$_areas[$area['name']] = $_widgets;	  
			            			}		            			          			
			            		}
			            		$_sections[$section['name']] = $_areas;
	            			}            			

	            		}
	            		
	            		$_meta_key = sprintf('%s-%s', self::get_meta_key_grid(), $layout['name']);
	            		update_post_meta($post_id, $_meta_key, $_sections);
					}				

	            }
	            update_post_meta($post_id, self::get_meta_key_current_layout(), $current_layout);	            
        	}
        } else {
            delete_post_meta($post_id, self::get_meta_key_grid());
        }

		exit();		
	}

	public function save_customize(){
		check_ajax_referer('kpb_save_customize', 'security');

		$post_id = (int) $_POST['post_id']; 
		$layout = $_POST['layout'];
		$section = $_POST['section'];

		$meta_key = sprintf('%s-%s-%s', self::get_meta_key_wrapper(), $layout, $section);	
		$meta_value = array();

		$data = $_POST[$meta_key];		

		$_section_fields = apply_filters('kopa_page_builder_get_section_fields', array());

		foreach ($_section_fields as $tab_slug => $tab) {

			foreach ($tab['params'] as $param_key => $param_args){
				$_value = isset($data[$tab_slug][$param_key]) ? $data[$tab_slug][$param_key] : (isset($param_args['default']) ? $param_args['default'] : null);				
				$meta_value[$tab_slug][$param_key] = $this->validate_data_meta_boxes($param_args, $_value);
			}

		}
		
		update_post_meta($post_id, $meta_key, $meta_value);	

		exit();		
	}	

	public function save_layout_customize(){
		check_ajax_referer('kpb_save_layout_customize', 'security');

		$post_id = (int) $_POST['post_id']; 
		$layout_slug = $_POST['layout'];

		$meta_key = sprintf('%s-%s', self::get_meta_key_layout_customize(), $layout_slug);	
		$meta_value = array();

		$data = $_POST[$meta_key];

		$layouts = apply_filters('kopa_page_builder_get_layouts', array());
		$current_layout = $layouts[$layout_slug];

		foreach ($current_layout['customize'] as $tab_slug => $tab) {
			foreach ($tab['params'] as $param_key => $param_args){
				$_value = isset($data[$tab_slug][$param_key]) ? $data[$tab_slug][$param_key] : (isset($param_args['default']) ? $param_args['default'] : null);				
				$meta_value[$tab_slug][$param_key] = $this->validate_data_meta_boxes($param_args, $_value);
			}
		}

		update_post_meta($post_id, $meta_key, $meta_value);	

		exit();
	}

	public function str_beautify($string) {
        return ucwords(str_replace('_', ' ', $string));
    }

    public function str_uglify($string) {
        $string = preg_replace("/[^a-zA-Z0-9\s]/", '', $string);
        return strtolower(str_replace(' ', '_', $string));
    }

    public function get_field_text($params){
    	?>
    	<input name="<?php echo esc_attr($params['name']); ?>" value="<?php echo esc_attr($params['value']); ?>" type="text" class="kpb-ui-text" autocomplete="off">    	
    	<?php
    }

    public function get_field_number($params){
    	?>
    	<input name="<?php echo esc_attr($params['name']); ?>" value="<?php echo esc_attr($params['value']); ?>" type="text" class="kpb-ui-number" autocomplete="off">    	
    	<?php if($params['affix']): ?>
    		<i><?php echo $params['affix']; ?></i>
    	<?php endif;?>
    	<?php
    }

    public function get_field_color($params){
    	?>
    	<input name="<?php echo esc_attr($params['name']); ?>" value="<?php echo esc_attr($params['value']); ?>" type="text" class="kpb-ui-color" data-default-color="<?php echo isset($params['default']) ? $params['default'] : ''; ?>" autocomplete="off">
    	<?php
    }

    public function get_field_checkbox($params){
    	$params['value'] = isset($params['value']) ? isset($params['value']) : isset($params['default']) ? $params['value'] : 'false';
    	?>
    	<input name="<?php echo esc_attr($params['name']); ?>" 
    		<?php checked( $params['value'], 'true'); ?>
	    	value="true" 
	    	type="checkbox" 
	    	class="kpb-ui-checbox" 	    	
	    	autocomplete="off">
    	<?php
    }

    public function get_field_radio($params){
    	foreach($params['options'] as $value => $title):
			$checked = !empty($params['value']) && ($params['value'] == $value) ? 'checked="checked"' : '';
			$id = wp_generate_password(4, false, false) . '-' . $value;
			?>
			<label for="<?php echo esc_attr($id);?>">
				<span><?php echo esc_attr($title); ?></span>
				<input id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($params['name']); ?>" value="<?php echo esc_attr($value); ?>" type="radio" class="kpb-ui-radio" <?php echo $checked; ?> autocomplete="off">    	
			</label>			
			<?php
		endforeach;
    }

    public function get_field_radio_image($params){
        foreach($params['options'] as $value => $title):
            $checked = !empty($params['value']) && ($params['value'] == $value) ? 'checked="checked"' : '';
            $id = wp_generate_password(4, false, false) . '-' . $value;
            ?>
            <div class="radio-image-wrapper">
                <label for="<?php echo esc_attr($id);?>">
                    <span><?php echo $title; ?></span>
                    <input id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($params['name']); ?>" value="<?php echo esc_attr($value); ?>" type="radio" class="kpb-ui-radio" <?php echo $checked; ?> autocomplete="off">
                </label>
            </div>
        <?php
        endforeach;
    }

    public function get_field_select($params){
    	?>
		<select name="<?php echo esc_attr($params['name']); ?>" class="kpb-ui-select" autocomplete=off>
			<?php 
			foreach($params['options'] as $value => $title):
				$selected = !empty($params['value']) && ($params['value'] == $value) ? 'selected="selected"' : '';
				?>
				<option value="<?php echo $value ?>" <?php echo esc_attr($selected); ?>><?php echo esc_attr($title); ?></option>
				<?php
			endforeach;
			?>
		</select>
    	<?php
    }

    public function get_field_image($params){
		$preview       =  KPB_DIR . 'images/placehold.png';
		$image         = !empty($params['value']) ? do_shortcode($params['value']) : '';
		$image_reset   = (isset($params['default']) && !empty($params['default'])) ? do_shortcode($params['default']) : '';
		$image_preview = $image ? $image : $preview;
    	?>
    	<div class="kpb-ui-image-outer">
	    	<div class="kpb-clearfix">
		    	<input name="<?php echo esc_attr($params['name']); ?>" value="<?php echo esc_url($image); ?>" type="text" class="kpb-ui-image kpb-pull-left" autocomplete="off">
		    	<a href="#" class="kpb-ui-image-button-upload button button-secondary kpb-pull-left"><?php _e('Upload', 'kopa-page-builder'); ?></a>
		    	<a href="#" class="kpb-ui-image-button-reset button button-link button-delete kpb-pull-left" data-preview="<?php echo esc_url($preview); ?>" data-reset="<?php echo esc_url($image_reset); ?>"><?php _e('Reset', 'kopa-page-builder'); ?></a>
	    	</div>    
	    	<br/>
	    	<img src="<?php echo $image_preview;?>" class="kpb-ui-image-preview" data-preview="<?php echo esc_url($preview); ?>">    	
    	</div>
    	<?php
    }

    public function get_field_textarea($params){
    	$class = isset($params['class']) && !empty($params['class']) ? $params['class'] : '';
    	$rows = isset($params['rows']) && !empty($params['rows']) ? (int) $params['rows'] : 3;    	
    	?>
    	<textarea name="<?php echo esc_attr($params['name']); ?>" class="kpb-ui-textarea <?php echo $class;?>" rows="<?php echo $rows; ?>" autocomplete="off"><?php echo htmlspecialchars_decode(stripslashes($params['value'])); ?></textarea>
    	<?php
    }

    public function get_field_icon($params){
		$params['options'] = array('' => __('-- select icon --', 'kopa-page-builder'));
		$icons = $this->get_icons();
		foreach ($icons as $icon) {
			$params['options'][$icon] = str_replace('fa fa-', '', $icon);
		}

		$this->get_field_select($params);
    }

    public function validate_data_meta_boxes($param_args, $value){
		switch ($param_args['type']) {
			case 'color':
				$value = esc_attr($value);
				break;
			case 'image':
				if (!empty($value)) {
                    $value = str_replace(home_url(), '[kpb_home_url]', $value);
                }
				break;
			case 'select':
				$value = esc_attr($value);
				break;		
			case 'text':
				$value = esc_attr($value);
				break;																				
			case 'number':
				if(trim($value) != ''){
					$value = floatval($value);
				}				
				break;
			case 'textarea':
				$value = htmlspecialchars_decode(stripslashes($value));
				break;															
		}

		return $value;
	}

    public function shortcode_home_url() {    
    	return get_site_url();
    }

    public function get_icons(){
    	$icons = array(
	        'fa fa-rub',
	        'fa fa-ruble',
	        'fa fa-rouble',
	        'fa fa-pagelines',
	        'fa fa-stack-exchange',
	        'fa fa-arrow-circle-o-right',
	        'fa fa-arrow-circle-o-left',
	        'fa fa-caret-square-o-left',
	        'fa fa-toggle-left',
	        'fa fa-dot-circle-o',
	        'fa fa-wheelchair',
	        'fa fa-vimeo-square',
	        'fa fa-try',
	        'fa fa-turkish-lira',
	        'fa fa-plus-square-o',
	        'fa fa-adjust',
	        'fa fa-anchor',
	        'fa fa-archive',
	        'fa fa-arrows',
	        'fa fa-arrows-h',
	        'fa fa-arrows-v',
	        'fa fa-asterisk',
	        'fa fa-ban',
	        'fa fa-bar-chart-o',
	        'fa fa-barcode',
	        'fa fa-bars',
	        'fa fa-beer',
	        'fa fa-bell',
	        'fa fa-bell-o',
	        'fa fa-bolt',
	        'fa fa-book',
	        'fa fa-bookmark',
	        'fa fa-bookmark-o',
	        'fa fa-briefcase',
	        'fa fa-bug',
	        'fa fa-building-o',
	        'fa fa-bullhorn',
	        'fa fa-bullseye',
	        'fa fa-calendar',
	        'fa fa-calendar-o',
	        'fa fa-camera',
	        'fa fa-camera-retro',
	        'fa fa-caret-square-o-down',
	        'fa fa-caret-square-o-left',
	        'fa fa-caret-square-o-right',
	        'fa fa-caret-square-o-up',
	        'fa fa-certificate',
	        'fa fa-check',
	        'fa fa-check-circle',
	        'fa fa-check-circle-o',
	        'fa fa-check-square',
	        'fa fa-check-square-o',
	        'fa fa-circle',
	        'fa fa-circle-o',
	        'fa fa-clock-o',
	        'fa fa-cloud',
	        'fa fa-cloud-download',
	        'fa fa-cloud-upload',
	        'fa fa-code',
	        'fa fa-code-fork',
	        'fa fa-coffee',
	        'fa fa-cog',
	        'fa fa-cogs',
	        'fa fa-comment',
	        'fa fa-comment-o',
	        'fa fa-comments',
	        'fa fa-comments-o',
	        'fa fa-compass',
	        'fa fa-credit-card',
	        'fa fa-crop',
	        'fa fa-crosshairs',
	        'fa fa-cutlery',
	        'fa fa-dashboard',
	        'fa fa-desktop',
	        'fa fa-dot-circle-o',
	        'fa fa-download',
	        'fa fa-edit',
	        'fa fa-ellipsis-h',
	        'fa fa-ellipsis-v',
	        'fa fa-envelope',
	        'fa fa-envelope-o',
	        'fa fa-eraser',
	        'fa fa-exchange',
	        'fa fa-exclamation',
	        'fa fa-exclamation-circle',
	        'fa fa-exclamation-triangle',
	        'fa fa-external-link',
	        'fa fa-external-link-square',
	        'fa fa-eye',
	        'fa fa-eye-slash',
	        'fa fa-female',
	        'fa fa-fighter-jet',
	        'fa fa-film',
	        'fa fa-filter',
	        'fa fa-fire',
	        'fa fa-fire-extinguisher',
	        'fa fa-flag',
	        'fa fa-flag-checkered',
	        'fa fa-flag-o',
	        'fa fa-flash',
	        'fa fa-flask',
	        'fa fa-folder',
	        'fa fa-folder-o',
	        'fa fa-folder-open',
	        'fa fa-folder-open-o',
	        'fa fa-frown-o',
	        'fa fa-gamepad',
	        'fa fa-gavel',
	        'fa fa-gear',
	        'fa fa-gears',
	        'fa fa-gift',
	        'fa fa-glass',
	        'fa fa-globe',
	        'fa fa-group',
	        'fa fa-hdd-o',
	        'fa fa-headphones',
	        'fa fa-heart',
	        'fa fa-heart-o',
	        'fa fa-home',
	        'fa fa-inbox',
	        'fa fa-info',
	        'fa fa-info-circle',
	        'fa fa-key',
	        'fa fa-keyboard-o',
	        'fa fa-laptop',
	        'fa fa-leaf',
	        'fa fa-legal',
	        'fa fa-lemon-o',
	        'fa fa-level-down',
	        'fa fa-level-up',
	        'fa fa-lightbulb-o',
	        'fa fa-location-arrow',
	        'fa fa-lock',
	        'fa fa-magic',
	        'fa fa-magnet',
	        'fa fa-mail-forward',
	        'fa fa-mail-reply',
	        'fa fa-mail-reply-all',
	        'fa fa-male',
	        'fa fa-map-marker',
	        'fa fa-meh-o',
	        'fa fa-microphone',
	        'fa fa-microphone-slash',
	        'fa fa-minus',
	        'fa fa-minus-circle',
	        'fa fa-minus-square',
	        'fa fa-minus-square-o',
	        'fa fa-mobile',
	        'fa fa-mobile-phone',
	        'fa fa-money',
	        'fa fa-moon-o',
	        'fa fa-music',
	        'fa fa-pencil',
	        'fa fa-pencil-square',
	        'fa fa-pencil-square-o',
	        'fa fa-phone',
	        'fa fa-phone-square',
	        'fa fa-picture-o',
	        'fa fa-plane',
	        'fa fa-plus',
	        'fa fa-plus-circle',
	        'fa fa-plus-square',
	        'fa fa-plus-square-o',
	        'fa fa-power-off',
	        'fa fa-print',
	        'fa fa-puzzle-piece',
	        'fa fa-qrcode',
	        'fa fa-question',
	        'fa fa-question-circle',
	        'fa fa-quote-left',
	        'fa fa-quote-right',
	        'fa fa-random',
	        'fa fa-refresh',
	        'fa fa-reply',
	        'fa fa-reply-all',
	        'fa fa-retweet',
	        'fa fa-road',
	        'fa fa-rocket',
	        'fa fa-rss',
	        'fa fa-rss-square',
	        'fa fa-search',
	        'fa fa-search-minus',
	        'fa fa-search-plus',
	        'fa fa-share',
	        'fa fa-share-square',
	        'fa fa-share-square-o',
	        'fa fa-shield',
	        'fa fa-shopping-cart',
	        'fa fa-sign-in',
	        'fa fa-sign-out',
	        'fa fa-signal',
	        'fa fa-sitemap',
	        'fa fa-smile-o',
	        'fa fa-sort',
	        'fa fa-sort-alpha-asc',
	        'fa fa-sort-alpha-desc',
	        'fa fa-sort-amount-asc',
	        'fa fa-sort-amount-desc',
	        'fa fa-sort-asc',
	        'fa fa-sort-desc',
	        'fa fa-sort-down',
	        'fa fa-sort-numeric-asc',
	        'fa fa-sort-numeric-desc',
	        'fa fa-sort-up',
	        'fa fa-spinner',
	        'fa fa-square',
	        'fa fa-square-o',
	        'fa fa-star',
	        'fa fa-star-half',
	        'fa fa-star-half-empty',
	        'fa fa-star-half-full',
	        'fa fa-star-half-o',
	        'fa fa-star-o',
	        'fa fa-subscript',
	        'fa fa-suitcase',
	        'fa fa-sun-o',
	        'fa fa-superscript',
	        'fa fa-tablet',
	        'fa fa-tachometer',
	        'fa fa-tag',
	        'fa fa-tags',
	        'fa fa-tasks',
	        'fa fa-terminal',
	        'fa fa-thumb-tack',
	        'fa fa-thumbs-down',
	        'fa fa-thumbs-o-down',
	        'fa fa-thumbs-o-up',
	        'fa fa-thumbs-up',
	        'fa fa-ticket',
	        'fa fa-times',
	        'fa fa-times-circle',
	        'fa fa-times-circle-o',
	        'fa fa-tint',
	        'fa fa-toggle-down',
	        'fa fa-toggle-left',
	        'fa fa-toggle-right',
	        'fa fa-toggle-up',
	        'fa fa-trash-o',
	        'fa fa-trophy',
	        'fa fa-truck',
	        'fa fa-umbrella',
	        'fa fa-unlock',
	        'fa fa-unlock-alt',
	        'fa fa-unsorted',
	        'fa fa-upload',
	        'fa fa-user',
	        'fa fa-users',
	        'fa fa-video-camera',
	        'fa fa-volume-down',
	        'fa fa-volume-off',
	        'fa fa-volume-up',
	        'fa fa-warning',
	        'fa fa-wheelchair',
	        'fa fa-wrench',
	        'fa fa-check-square',
	        'fa fa-check-square-o',
	        'fa fa-circle',
	        'fa fa-circle-o',
	        'fa fa-dot-circle-o',
	        'fa fa-minus-square',
	        'fa fa-minus-square-o',
	        'fa fa-plus-square',
	        'fa fa-plus-square-o',
	        'fa fa-square',
	        'fa fa-square-o',
	        'fa fa-bitcoin',
	        'fa fa-btc',
	        'fa fa-cny',
	        'fa fa-dollar',
	        'fa fa-eur',
	        'fa fa-euro',
	        'fa fa-gbp',
	        'fa fa-inr',
	        'fa fa-jpy',
	        'fa fa-krw',
	        'fa fa-money',
	        'fa fa-rmb',
	        'fa fa-rouble',
	        'fa fa-rub',
	        'fa fa-ruble',
	        'fa fa-rupee',
	        'fa fa-try',
	        'fa fa-turkish-lira',
	        'fa fa-usd',
	        'fa fa-won',
	        'fa fa-yen',
	        'fa fa-align-center',
	        'fa fa-align-justify',
	        'fa fa-align-left',
	        'fa fa-align-right',
	        'fa fa-bold',
	        'fa fa-chain',
	        'fa fa-chain-broken',
	        'fa fa-clipboard',
	        'fa fa-columns',
	        'fa fa-copy',
	        'fa fa-cut',
	        'fa fa-dedent',
	        'fa fa-eraser',
	        'fa fa-file',
	        'fa fa-file-o',
	        'fa fa-file-text',
	        'fa fa-file-text-o',
	        'fa fa-files-o',
	        'fa fa-floppy-o',
	        'fa fa-font',
	        'fa fa-indent',
	        'fa fa-italic',
	        'fa fa-link',
	        'fa fa-list',
	        'fa fa-list-alt',
	        'fa fa-list-ol',
	        'fa fa-list-ul',
	        'fa fa-outdent',
	        'fa fa-paperclip',
	        'fa fa-paste',
	        'fa fa-repeat',
	        'fa fa-rotate-left',
	        'fa fa-rotate-right',
	        'fa fa-save',
	        'fa fa-scissors',
	        'fa fa-strikethrough',
	        'fa fa-table',
	        'fa fa-text-height',
	        'fa fa-text-width',
	        'fa fa-th',
	        'fa fa-th-large',
	        'fa fa-th-list',
	        'fa fa-underline',
	        'fa fa-undo',
	        'fa fa-unlink',
	        'fa fa-angle-double-down',
	        'fa fa-angle-double-left',
	        'fa fa-angle-double-right',
	        'fa fa-angle-double-up',
	        'fa fa-angle-down',
	        'fa fa-angle-left',
	        'fa fa-angle-right',
	        'fa fa-angle-up',
	        'fa fa-arrow-circle-down',
	        'fa fa-arrow-circle-left',
	        'fa fa-arrow-circle-o-down',
	        'fa fa-arrow-circle-o-left',
	        'fa fa-arrow-circle-o-right',
	        'fa fa-arrow-circle-o-up',
	        'fa fa-arrow-circle-right',
	        'fa fa-arrow-circle-up',
	        'fa fa-arrow-down',
	        'fa fa-arrow-left',
	        'fa fa-arrow-right',
	        'fa fa-arrow-up',
	        'fa fa-arrows',
	        'fa fa-arrows-alt',
	        'fa fa-arrows-h',
	        'fa fa-arrows-v',
	        'fa fa-caret-down',
	        'fa fa-caret-left',
	        'fa fa-caret-right',
	        'fa fa-caret-square-o-down',
	        'fa fa-caret-square-o-left',
	        'fa fa-caret-square-o-right',
	        'fa fa-caret-square-o-up',
	        'fa fa-caret-up',
	        'fa fa-chevron-circle-down',
	        'fa fa-chevron-circle-left',
	        'fa fa-chevron-circle-right',
	        'fa fa-chevron-circle-up',
	        'fa fa-chevron-down',
	        'fa fa-chevron-left',
	        'fa fa-chevron-right',
	        'fa fa-chevron-up',
	        'fa fa-hand-o-down',
	        'fa fa-hand-o-left',
	        'fa fa-hand-o-right',
	        'fa fa-hand-o-up',
	        'fa fa-long-arrow-down',
	        'fa fa-long-arrow-left',
	        'fa fa-long-arrow-right',
	        'fa fa-long-arrow-up',
	        'fa fa-toggle-down',
	        'fa fa-toggle-left',
	        'fa fa-toggle-right',
	        'fa fa-toggle-up',
	        'fa fa-arrows-alt',
	        'fa fa-backward',
	        'fa fa-compress',
	        'fa fa-eject',
	        'fa fa-expand',
	        'fa fa-fast-backward',
	        'fa fa-fast-forward',
	        'fa fa-forward',
	        'fa fa-pause',
	        'fa fa-play',
	        'fa fa-play-circle',
	        'fa fa-play-circle-o',
	        'fa fa-step-backward',
	        'fa fa-step-forward',
	        'fa fa-stop',
	        'fa fa-youtube-play',
	        'fa fa-adn',
	        'fa fa-android',
	        'fa fa-apple',
	        'fa fa-bitbucket',
	        'fa fa-bitbucket-square',
	        'fa fa-bitcoin',
	        'fa fa-btc',
	        'fa fa-css3',
	        'fa fa-dribbble',
	        'fa fa-dropbox',
	        'fa fa-facebook',
	        'fa fa-facebook-square',
	        'fa fa-flickr',
	        'fa fa-foursquare',
	        'fa fa-github',
	        'fa fa-github-alt',
	        'fa fa-github-square',
	        'fa fa-gittip',
	        'fa fa-google-plus',
	        'fa fa-google-plus-square',
	        'fa fa-html5',
	        'fa fa-instagram',
	        'fa fa-linkedin',
	        'fa fa-linkedin-square',
	        'fa fa-linux',
	        'fa fa-maxcdn',
	        'fa fa-pagelines',
	        'fa fa-pinterest',
	        'fa fa-pinterest-square',
	        'fa fa-renren',
	        'fa fa-skype',
	        'fa fa-stack-exchange',
	        'fa fa-stack-overflow',
	        'fa fa-trello',
	        'fa fa-tumblr',
	        'fa fa-tumblr-square',
	        'fa fa-twitter',
	        'fa fa-twitter-square',
	        'fa fa-vimeo-square',
	        'fa fa-vk',
	        'fa fa-weibo',
	        'fa fa-windows',
	        'fa fa-xing',
	        'fa fa-xing-square',
	        'fa fa-youtube',
	        'fa fa-youtube-play',
	        'fa fa-youtube-square',
	        'fa fa-ambulance',
	        'fa fa-h-square',
	        'fa fa-hospital-o',
	        'fa fa-medkit',
	        'fa fa-plus-square',
	        'fa fa-stethoscope',
	        'fa fa-user-md',
	        'fa fa-wheelchair'
	    );

	    return apply_filters('kpb_get_icons', $icons);
	}

}