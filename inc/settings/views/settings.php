
<div class="wrap theme-options">

	<div class="icon32" id="icon-options-general"><br /></div>

	<h2><?php _e( 'Contextual Nav Menu Breadcrumb Settings', 'cnm' ); ?></h2>

	<form method="post" action="options.php">

		<?php settings_fields( 'contextual_nav_menu_breadcrumb_settings-group' );?>

		<div class="postbox metabox-holder">

			<h3 class="hndle"><?php _e( 'General Settings', 'cnm' );?></h3>

			<div class="inside">

				<div class="setting-panel">

					<p>
						
						<?php $breadcrumbs_show_on_home = get_option( 'contextual_nav_menu_breadcrumb_show_on_home' ); ?>

						<input <?php checked( $breadcrumbs_show_on_home, 1 ); ?> id="contextual_nav_menu_breadcrumb_show_on_home" name="contextual_nav_menu_breadcrumb_show_on_home" value="1" type="checkbox" />
						<label for="contextual_nav_menu_breadcrumb_show_on_home"><?php _e( 'Show on homepage', 'cnm' );?></label>
					
					</p>

				</div>

			</div>

		</div>

		<div class="postbox metabox-holder">
			
			<h3 class="hndle"><?php _e( 'Menu order', 'anmb' );?></h3>

			<div class="inside">

				<div class="setting-panel">

					<?php
						// Get menus
						$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

						for( $i = 0; $i < count( $menus ); $i++ ) {
							
							?>
							<p>
								
								<select class="menu_order_number" id="contextual_nav_menu_breadcrumb_menu_order_<?php print $i; ?>" name="contextual_nav_menu_breadcrumb_menu_order_<?php print $i; ?>">
									
									<option value="">---</option>
									
									<?php 
										
										$breadcrumbs_menu_order = get_option( 'contextual_nav_menu_breadcrumb_menu_order_' . $i );
										
										foreach( $menus as $menu ) {

											?><option <?php selected( $menu->term_id, $breadcrumbs_menu_order ); ?> value="<?php print $menu->term_id; ?>"><?php print $menu->name; ?></option><?php
										
										}

									?>

								</select>
								
								<?php
									
									global $polylang;
									
									if( isset( $polylang ) ) {

										?>

										<select class="menu_order_language" id="contextual_nav_menu_breadcrumb_menu_language_<?php print $i; ?>" name="contextual_nav_menu_breadcrumb_menu_language_<?php print $i; ?>">
											
											<option value="">---</option>

											<?php

												$languages = $polylang->model->get_languages_list();

												$breadcrumbs_menu_language = get_option( 'contextual_nav_menu_breadcrumb_menu_language_' . $i );
												
												foreach( $languages as $language ) {
													
													?><option <?php selected( $language->slug, $breadcrumbs_menu_language ); ?> value="<?php print $language->slug; ?>"><?php print $language->name; ?></option><?php
												
												}

											?>

										</select>

										<?php

									}

								?>

							</p>

							<?php
						}

					?>

					<?php $breadcrumbs_menu_order_numbers = get_option('contextual_nav_menu_breadcrumb_menu_order_list'); ?>

					<input id="contextual_nav_menu_breadcrumb_menu_order_list" name="contextual_nav_menu_breadcrumb_menu_order_list" value="<?php print $breadcrumbs_menu_order_numbers; ?>" type="hidden" />
				
				</div>

			</div>

		</div>
		
		<div class="postbox metabox-holder">

			<h3 class="hndle"><?php _e( 'Visual settings', 'cnm' );?></h3>
			
			<div class="inside">
				
				<div class="setting-panel">
					
					<?php

						$breadcrumbs_home_image = get_option( 'contextual_nav_menu_breadcrumb_home_image' );
						
						$breadcrumbs_home_image_src = '';
						
						if( !empty( $breadcrumbs_home_image ) ) {
							
							$breadcrumbs_home_image_src = wp_get_attachment_image_src( $breadcrumbs_home_image, 'full' );
							$breadcrumbs_home_image_src = $breadcrumbs_home_image_src[0];

						} else {
							
							$breadcrumbs_home_image_src = '';

						}

					?>

					<p>
						
						<input type="hidden" id="contextual_nav_menu_breadcrumb_home_image" name="contextual_nav_menu_breadcrumb_home_image" value="<?php print $breadcrumbs_home_image; ?>" />
						<input id="upload_image_button" class="button" type="button" value="<?php _e( 'Choose Home Image', 'cnm' ); ?>" /><br />
						<input id="delete_home_image" class="button" type="button" value="<?php _e( 'Delete Home Image', 'cnm' ); ?>" /></br />
						<img id="contextual_nav_menu_breadcrumb_home_image_preview" src="<?php print $breadcrumbs_home_image_src; ?>" /><br />

					</p>

				</div>

			</div>

		</div>
		
		<div class="postbox metabox-holder">
			
			<h3 class="hndle"><?php _e( 'Textual settings', 'cnm' );?></h3>
			
			<div class="inside">
				
				<div class="setting-panel">
					
					<p>
						
						<?php $breadcrumbs_home_text = get_option( 'contextual_nav_menu_breadcrumb_home_text', __( 'Home Page', 'cnm' ) ); ?>

						<label for="contextual_nav_menu_breadcrumb_home_text"><?php _e( 'Home text', 'cnm' );?></label><br />
						<input type="text" id="contextual_nav_menu_breadcrumb_home_text" name="contextual_nav_menu_breadcrumb_home_text" value="<?php print $breadcrumbs_home_text; ?>" />

					</p>

					<p>
						
						<?php $breadcrumbs_home_link_text = get_option( 'contextual_nav_menu_breadcrumb_home_link_text', __( 'Home', 'cnm' ) ); ?>
						
						<label for="contextual_nav_menu_breadcrumb_home_link_text"><?php _e( 'Home link text', 'cnm' );?></label><br />
						<input type="text" id="contextual_nav_menu_breadcrumb_home_link_text" name="contextual_nav_menu_breadcrumb_home_link_text" value="<?php print $breadcrumbs_home_link_text; ?>" />
					
					</p>

					<p>
						
						<?php $error404text = get_option( 'contextual_nav_menu_breadcrumb_error404_text', __( 'Not Found', 'cnm' ) ); ?>
						
						<label for="contextual_nav_menu_breadcrumb_error404_text"><?php _e( 'Error 404 text', 'cnm' );?></label><br />
						<input type="text" id="contextual_nav_menu_breadcrumb_error404_text" name="contextual_nav_menu_breadcrumb_error404_text" value="<?php print $error404text; ?>" />
					
					</p>
					
					<p>
						
						<?php $searchtext = get_option( 'contextual_nav_menu_breadcrumb_search_text', __( 'Search for terms', 'cnm' ) ); ?>
						
						<label for="contextual_nav_menu_breadcrumb_search_text"><?php _e( 'Search text', 'cnm' );?></label><br />
						<input type="text" id="contextual_nav_menu_breadcrumb_search_text" name="contextual_nav_menu_breadcrumb_search_text" value="<?php print $searchtext; ?>" />
					
					</p>
					
					<p>
						
						<?php $tagtext = get_option( 'contextual_nav_menu_breadcrumb_tag_text', __( 'Tag', 'cnm' ) ); ?>
						
						<label for="contextual_nav_menu_breadcrumb_tag_text"><?php _e( 'Tag text', 'cnm' );?></label><br />
						<input type="text" id="contextual_nav_menu_breadcrumb_tag_text" name="contextual_nav_menu_breadcrumb_tag_text" value="<?php print $tagtext; ?>" />
					
					</p>
					
					<p>
						
						<?php $authortext = get_option( 'contextual_nav_menu_breadcrumb_author_text', __( 'Author', 'cnm' ) ); ?>
						
						<label for="contextual_nav_menu_breadcrumb_author_text"><?php _e( 'Author text', 'cnm' );?></label><br />
						<input type="text" id="contextual_nav_menu_breadcrumb_author_text" name="contextual_nav_menu_breadcrumb_author_text" value="<?php print $authortext; ?>" />
					
					</p>                    
				</div>

			</div>

		</div>
		
		<p class="submit">

			<input type="submit" class="button-primary" value="<?php _e( 'Save settings', 'cnm' ); ?>" />

		</p>

	</form>

</div>