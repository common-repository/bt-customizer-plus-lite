<?php

/**
 * Plugin Name: Customizer Plus Lite
 * Description: Override WordPress Customizer settings on individual posts and pages.
 * Version: 1.0.0
 * Author: bitorbit
 * Author URI: http://bitorbit.biz
 */

class BT_Customizer_Plus {
	static $options = array();
	static $override_enabled = false;
	static $override_allowed = true;
	static $override_exists = false;
	static $current = array( 'id' => false );
	static $base_url = false;
	static $url = false;
}

function bt_customizer_plus_script() {
	wp_enqueue_style( 'bt-customizer-plus', plugins_url( 'style.css', __FILE__ ) );
}
add_action( 'customize_controls_enqueue_scripts', 'bt_customizer_plus_script' );

function bt_customizer_plus_print_footer_script() {
	?>
	<script>
		jQuery( document ).ready(function() {
			
			jQuery( '.collapse-sidebar' ).on( 'click', function() {
				if ( jQuery( '.preview-desktop' ).hasClass( 'expanded' ) ) {
					jQuery( '#bt_customizer_plus' ).hide();
				} else {
					jQuery( '#bt_customizer_plus' ).show();
				}
			});
			
			var targetNode = document.body;
			var config = { attributes: true };
			var enable_checked = false;
			var callback = function( mutationsList ) {
				for ( var mutation of mutationsList ) {
					var body_class = jQuery( 'body' ).attr( 'class' );
					if ( body_class.indexOf( 'saving' ) > -1 ) {
						jQuery( '#bt_customizer_plus' ).addClass( 'bt_customizer_plus_disabled' );
						jQuery( '#bt_customizer_plus_enable_override' ).prop( 'disabled', true );
						enable_checked = jQuery( '#bt_customizer_plus_enable_override' ).is( ':checked' );
 						jQuery( '#bt_customizer_plus_clear_override' ).prop( 'disabled', true );
					} else if ( jQuery( '#bt_customizer_plus' ).data( 'disabled' ) == false ) {
						jQuery( '#bt_customizer_plus' ).removeClass( 'bt_customizer_plus_disabled' );
						jQuery( '#bt_customizer_plus_enable_override' ).prop( 'disabled', false );
						if ( enable_checked ) {
							jQuery( '#bt_customizer_plus_clear_override' ).prop( 'disabled', false );
						}
					}
				}
			}
			var observer = new MutationObserver( callback );
			observer.observe( targetNode, config );
			
			<?php
			$checked = '';
			if ( BT_Customizer_Plus::$override_enabled ) {
				$checked = ' ' . 'checked';
			}
			if ( BT_Customizer_Plus::$override_exists ) {
				$clear = '<input type="submit" id="bt_customizer_plus_clear_override" class="button" value="' . esc_html__( 'Clear Override', 'bt-customizer-plus' ) . '">';
			} else {
				$clear = '<input type="submit" id="bt_customizer_plus_clear_override" class="button" value="' . esc_html__( 'Clear Override', 'bt-customizer-plus' ) . '" disabled>';
			}
			if ( BT_Customizer_Plus::$override_allowed ) { ?>
				jQuery( '<div id="bt_customizer_plus" data-disabled="false"><div><input id="bt_customizer_plus_enable_override" type="checkbox"<?php echo $checked; ?>><label for="bt_customizer_plus_enable_override"><?php esc_html_e( 'Enable Override', 'bt-customizer-plus' ); ?></label></div><span class="spinner"></span><?php echo $clear; ?></div>' ).insertBefore( '#customize-footer-actions' );
			<?php } else { ?>
				jQuery( '<div id="bt_customizer_plus" class="bt_customizer_plus_disabled" data-disabled="true"><div><input id="bt_customizer_plus_enable_override" type="checkbox" disabled><label for="bt_customizer_plus_enable_override"><?php esc_html_e( 'Enable Override', 'bt-customizer-plus' ); ?></label></div><?php echo $clear; ?></div>' ).insertBefore( '#customize-footer-actions' );
			<?php } ?>
			
			jQuery( '#bt_customizer_plus_enable_override' ).on( 'change', function( e ) {
				jQuery( this ).prop( 'disabled', true );
				jQuery( '#bt_customizer_plus_clear_override' ).prop( 'disabled', true );
				jQuery( this ).closest( '#bt_customizer_plus' ).addClass( 'bt_customizer_plus_disabled' );
				jQuery( '#bt_customizer_plus .spinner' ).css( 'visibility', 'visible' );
							
				if ( jQuery( this ).is( ':checked' ) ) {
					<?php if ( BT_Customizer_Plus::$url ) { ?>
						window.location.href = '<?php echo add_query_arg( array( 'url' => urlencode( BT_Customizer_Plus::$url ), 'bt_customizer_plus_override_enabled' => 'true' ), BT_Customizer_Plus::$base_url ); ?>';
					<?php } else { ?>
						window.location.href = '<?php echo add_query_arg( array( 'bt_customizer_plus_override_enabled' => 'true' ), BT_Customizer_Plus::$base_url ); ?>';
					<?php } ?>
				} else {
					<?php if ( BT_Customizer_Plus::$url ) { ?>
						window.location.href = '<?php echo add_query_arg( array( 'url' => urlencode( BT_Customizer_Plus::$url ), 'bt_customizer_plus_override_enabled' => 'false' ), BT_Customizer_Plus::$base_url ); ?>';
					<?php } else { ?>
						window.location.href = '<?php echo add_query_arg( array( 'bt_customizer_plus_override_enabled' => 'false' ), BT_Customizer_Plus::$base_url ); ?>';
					<?php } ?>
				}
			});
			
			jQuery( '#bt_customizer_plus_clear_override' ).on( 'click', function( e ) {
				jQuery( this ).prop( 'disabled', true );
				jQuery( '#bt_customizer_plus_enable_override' ).prop( 'disabled', true );
				jQuery( this ).closest( '#bt_customizer_plus' ).addClass( 'bt_customizer_plus_disabled' );
				jQuery( '#bt_customizer_plus .spinner' ).css( 'visibility', 'visible' );
				<?php if ( BT_Customizer_Plus::$url ) { ?>
					window.location.href = '<?php echo add_query_arg( array( 'url' => urlencode( BT_Customizer_Plus::$url ), 'bt_customizer_plus_clear_override' => 'true' ), BT_Customizer_Plus::$base_url ); ?>';
				<?php } else { ?>
					window.location.href = '<?php echo add_query_arg( array( 'bt_customizer_plus_clear_override' => 'true' ), BT_Customizer_Plus::$base_url ); ?>';	
				<?php } ?>
				return false;
			});
			
			jQuery( '#customize-controls' ).on( 'click', 'button.change-theme', function( e ) {
				jQuery( '#bt_customizer_plus' ).hide();
			});
			
			jQuery( '#customize-controls' ).on( 'click', 'button.customize-panel-back', function( e ) {
				jQuery( '#bt_customizer_plus' ).show();
			});
			
		});
	</script>
	<?php
}
add_action( 'customize_controls_print_footer_scripts', 'bt_customizer_plus_print_footer_script' );

function bt_customizer_plus_customize_controls_init() {
	$id = false;
	BT_Customizer_Plus::$base_url = get_admin_url( null, '/customize.php' );
	if ( isset( $_GET['url'] ) ) {
		$url = $_GET['url'];
		BT_Customizer_Plus::$url = $url;
		try {
			$content = @file_get_contents( $url );
			if ( $content ) {
				$re = '/<\s*body\s+class\s*=\s*("|\')(.*)("|\')/';
				preg_match( $re, $content, $matches, PREG_OFFSET_CAPTURE, 0 );
				$body_class = $matches[2][0];
				$body_class = preg_replace( '/\s+/', ' ', $body_class );
				$body_class_arr = explode( ' ', $body_class );
				
				foreach( $body_class_arr as $class ) {
					if ( strpos( $class, 'page-id-' ) !== false ) {
						$id = substr( $class, strlen( 'page-id-' ) );
						$post = get_post( $id );
						if ( $post ) {
							$id = $post->post_name;
						}
						break;
					}
					if ( strpos( $class, 'postid-' ) !== false ) {
						$id = substr( $class, strlen( 'postid-' ) );
						$post = get_post( $id );
						if ( $post ) {
							$id = $post->post_name;
						}
						break;
					}
				}
			}
		} catch ( Exception $e ) {
			
		}
	}

	$current = array( 'id' => $id );
	update_option( 'bt_customizer_plus_current', serialize( $current ) );
	
	BT_Customizer_Plus::$current = $current;
	
	if ( $id === false ) {
		BT_Customizer_Plus::$override_allowed = false;
	}
	
	if ( isset( $_GET['bt_customizer_plus_clear_override'] ) && $_GET['bt_customizer_plus_clear_override'] == 'true' && $id !== false ) {
		$override = get_option( 'bt_customizer_plus_override' );
		if ( $override ) {
			$override = unserialize( $override );
			if ( isset( $override[ $id ] ) ) {
				unset( $override[ $id ] );
				update_option( 'bt_customizer_plus_override', serialize( $override ) );
			}
		}
		
		BT_Customizer_Plus::$override_enabled = false;
		$override_enabled = get_option( 'bt_customizer_plus_override_enabled' );
		if ( $override_enabled ) {
			$override_enabled = unserialize( $override_enabled );
			if ( isset( $override_enabled[ $id ] ) ) {
				unset( $override_enabled[ $id ] );
				update_option( 'bt_customizer_plus_override_enabled', serialize( $override_enabled ) );
			}			
		}
	}
	
	if ( isset( $_GET['bt_customizer_plus_override_enabled'] ) && $id !== false ) {
		if ( $_GET['bt_customizer_plus_override_enabled'] == 'true' ) {
			BT_Customizer_Plus::$override_enabled = true;
		} else {
			BT_Customizer_Plus::$override_enabled = false;
		}

		$override_enabled = get_option( 'bt_customizer_plus_override_enabled' );
		if ( $override_enabled ) {
			$override_enabled = unserialize( $override_enabled );
		} else {
			$override_enabled = array();
		}
		
		$override_enabled[ $id ] = BT_Customizer_Plus::$override_enabled;

		update_option( 'bt_customizer_plus_override_enabled', serialize( $override_enabled ) );

	} else if ( $id !== false ) {
		$override_enabled = get_option( 'bt_customizer_plus_override_enabled' );
		if ( $override_enabled ) {
			$override_enabled = unserialize( $override_enabled );
		} else {
			$override_enabled = array();
		}
		if ( isset( $override_enabled[ $id ] ) && $override_enabled[ $id ] ) {
			BT_Customizer_Plus::$override_enabled = true;
		}
	}
	
	if ( $id !== false ) {
		$override = get_option( 'bt_customizer_plus_override' );
		if ( $override ) {
			$override = unserialize( $override );
			if ( isset( $override[ $id ] ) ) {
				BT_Customizer_Plus::$override_exists = true;
			}
		}
	}
	
}
add_action( 'customize_controls_init', 'bt_customizer_plus_customize_controls_init' );

function bt_customizer_plus_pre_get() {
	$options = get_option( 'bt_customizer_plus_options' );
	if ( $options ) {
		$options = unserialize( $options );
	} else {
		$options = array();
	}
	foreach( $options as $option ) {
		if ( $option['type'] == 'theme_mod' ) {
			$theme = get_option( 'stylesheet' );
			add_filter( "pre_option_theme_mods_{$theme}", 'bt_customizer_plus_pre_option', 10, 3 );
		} else {
			add_filter( "pre_option_{$option['id']}", 'bt_customizer_plus_pre_option', 10, 3 );
		}
	}
	add_filter( 'sidebars_widgets', 'bt_customizer_plus_sidebars_widgets' );
}
add_action( 'init', 'bt_customizer_plus_pre_get' );

function bt_customizer_plus_pre_option( $pre_option, $option, $default ) {

	$id = bt_customizer_plus_get_id();

	$override_enabled = get_option( 'bt_customizer_plus_override_enabled' );
	if ( $override_enabled ) {
		$override_enabled = unserialize( $override_enabled );
	} else {
		$override_enabled = array();
	}
	if ( ! isset( $override_enabled[ $id ] ) || ! $override_enabled[ $id ] ) {
		return false;
	}
	
	$override = get_option( 'bt_customizer_plus_override' );
	if ( $override ) {
		$override = unserialize( $override );
	} else {
		$override = array();
	}

	if ( isset( $override[ $id ] ) && isset( $override[ $id ][ $option ] ) ) {
		return $override[ $id ][ $option ];
	}
	
	return false;
}

function bt_customizer_plus_sidebars_widgets( $sidebars_widgets ) {
	
	$id = bt_customizer_plus_get_id();

	$override_enabled = get_option( 'bt_customizer_plus_override_enabled' );
	if ( $override_enabled ) {
		$override_enabled = unserialize( $override_enabled );
	} else {
		$override_enabled = array();
	}
	if ( ! isset( $override_enabled[ $id ] ) || ! $override_enabled[ $id ] ) {
		return $sidebars_widgets;
	}
	
	$override = get_option( 'bt_customizer_plus_override' );
	if ( $override ) {
		$override = unserialize( $override );
	} else {
		$override = array();
	}

	if ( isset( $override[ $id ] ) && isset( $override[ $id ][ 'sidebars_widgets' ] ) ) {
		$sidebars_widgets = $override[ $id ][ 'sidebars_widgets' ];
		if ( is_array( $sidebars_widgets ) && isset( $sidebars_widgets['array_version'] ) ) {
			unset( $sidebars_widgets['array_version'] );
		}
		return $sidebars_widgets;
	}
	
	return $sidebars_widgets;
}

function bt_customizer_plus_get_id() {
	
	$id = get_the_ID();
	$post = get_post( $id );
	if ( $post ) {
		$id = $post->post_name;
	}

	if ( ! $id && is_customize_preview() ) {
		$current = get_option( 'bt_customizer_plus_current' );
		if ( $current ) {
			$current = unserialize( $current );
			if ( $current['id'] ) {
				$id = $current['id'];
			}
		}
	}
	
	return $id;
}

function bt_customizer_plus_add_setting( $args, $id ) {
	if ( isset( $args['type'] ) && $args['type'] == 'option' ) {
		BT_Customizer_Plus::$options[] = array( 'type' => 'option', 'id' => $id );
	} else {
		BT_Customizer_Plus::$options[] = array( 'type' => 'theme_mod', 'id' => $id );
	}
	return $args;
}
add_filter( 'customize_dynamic_setting_args', 'bt_customizer_plus_add_setting', 10, 2 );

function bt_customizer_plus_customize_preview_init( $wp_customize ) {
	update_option( 'bt_customizer_plus_options', serialize( BT_Customizer_Plus::$options ) );
}
add_action( 'customize_preview_init', 'bt_customizer_plus_customize_preview_init' );

function bt_customizer_plus_preview_footer() { 
	if ( is_customize_preview() ) {
		?>
		<script>
			jQuery( document ).ready(function() {
		
				jQuery( 'a' ).on( 'click', function( e ) {
					
					if ( e.isTrigger ) {
						return;
					}

					var href = e.currentTarget.href;

					if ( href.indexOf( 'customize_changeset_uuid' ) > -1 || href.indexOf( 'customize_messenger_channel' ) > -1 ) {
						
						var customize_base_url = '<?php echo get_admin_url( null, '/customize.php' ); ?>';
						var preview_iframe_url = href.split( '?' )[0];
						var redirect_url = customize_base_url + '?url=' + encodeURIComponent( preview_iframe_url );
				
						window.top.location.href = redirect_url;

					}
					
					return false;
					
				});
				
				jQuery( 'form' ).on( 'submit', function( e ) {
					var input = jQuery( this ).find( 'input[name="s"]' );
					if ( input ) {
						var base_url = '<?php echo get_site_url(); ?>';
						var customize_base_url = '<?php echo get_admin_url( null, '/customize.php' ); ?>';
						var redirect_url = customize_base_url + '?url=' + encodeURIComponent( base_url + '?s=' + input.val() );
						window.top.location.href = redirect_url;
						return false;
					}
				});
				
			});
		</script>
		<?php
	}
}
add_action( 'wp_footer', 'bt_customizer_plus_preview_footer' );

function bt_customizer_plus_customize_save() {
	add_filter( 'pre_update_option', 'bt_customizer_plus_pre_update_option', 10, 3 );
}
add_action( 'wp_ajax_customize_save', 'bt_customizer_plus_customize_save', 1 );

function bt_customizer_plus_pre_update_option( $value, $option, $old_value ) {

	if ( $option == 'bt_customizer_plus_override' ) {
		return $value;
	}
	
	if ( $option == 'show_on_front' || $option == 'page_on_front' || $option == 'page_for_posts' ) {
		return $value;
	}

	$current = get_option( 'bt_customizer_plus_current' );
	if ( $current ) {
		$current = unserialize( $current );
	} else {
		return $value;
	}
	
	$override_enabled = get_option( 'bt_customizer_plus_override_enabled' );
	if ( $override_enabled ) {
		$override_enabled = unserialize( $override_enabled );
		if ( ! isset( $override_enabled[ $current['id'] ] ) ) {
			return $value;
		}
	} else {
		return $value;
	}
	
	$options = get_option( 'bt_customizer_plus_options' );
	if ( $options ) {
		$options = unserialize( $options );
	} else {
		$options = array();
	}
	$exists = false;
	foreach( $options as $opt ) {
		if ( $option == $opt['id'] ) {
			$exists = true;
			break;
		}
	}
	$theme = get_option( 'stylesheet' );
	if ( $option == 'sidebars_widgets' || $option == "theme_mods_{$theme}" ) {
		$exists = true;
	}
	if ( ! $exists ) {
		return $value;
	}

	$override = get_option( 'bt_customizer_plus_override' );
	if ( $override ) {
		$override = unserialize( $override );
	} else {
		$override = array();
	}

	if ( ! isset( $override[ $current['id'] ] ) ) {
		$override[ $current['id'] ] = array();
	}

	$override[ $current['id'] ][ $option ] = $value;

	update_option( 'bt_customizer_plus_override', serialize( $override ) );
	
	return $old_value;

}

function bt_customizer_plus_option_keys( $keys ) {
    $keys[] = 'bt_customizer_plus_override';
    $keys[] = 'bt_customizer_plus_options';
	$keys[] = 'bt_customizer_plus_override_enabled';
	$keys[] = 'bt_customizer_plus_current';
    return $keys;
}
add_filter( 'cei_export_option_keys', 'bt_customizer_plus_option_keys' );