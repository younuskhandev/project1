<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    Bp_Add_Group_Types
 * @subpackage Bp_Add_Group_Types/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bp_Add_Group_Types
 * @subpackage Bp_Add_Group_Types/admin
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
if ( ! class_exists( 'Bp_Add_Group_Types_Admin' ) ) :

	class Bp_Add_Group_Types_Admin {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $plugin_name    The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;

		/**
		 * Post type name.
		 *
		 * @since   1.0.0
		 *
		 * @var     string
		 */
		protected $post_type = 'bp_group_type';

		/**
		 * ID of the meta box. Used for nonce generation, too.
		 *
		 * @since   1.0.0
		 *
		 * @var     string
		 */
		protected $meta_box_id = 'bp-group-type-parameters';

		/**
		 * Meta box title.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $meta_box_title;
		private $plugin_settings_tabs;
		protected $post_type_overrides = array();

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since 1.0.0
		 * @param string $plugin_name       The name of this plugin.
		 * @param string $version    The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ) {

			$this->plugin_name = $plugin_name;
			$this->version     = $version;
		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {
			$screen = get_current_screen();
			// if ( 'wbcom_page_bp-add-group-types' === $screen->base ) {
			// wp_enqueue_style( $this->plugin_name . '-css', plugin_dir_url( __FILE__ ) . 'css/bp-add-group-types-admin.css', array(), $this->version, 'all' );
			// wp_enqueue_script( $this->plugin_name . '-js', plugin_dir_url( __FILE__ ) . 'js/bp-add-group-types-admin.js', array( 'jquery' ), $this->version, false );
			// }
			if ( 'toplevel_page_wbcomplugins' === $screen->base ) {
				wp_enqueue_style( 'font-awesome-css', 'http://use.fontawesome.com/releases/v5.5.0/css/all.css?', array(), '5.3.2', 'all' );
				wp_enqueue_style( $this->plugin_name . '-css', plugin_dir_url( __FILE__ ) . 'css/bp-add-group-types-admin.css', array(), $this->version, 'all' );
				wp_enqueue_script( $this->plugin_name . '-js', plugin_dir_url( __FILE__ ) . 'js/bp-add-group-types-admin.js', array( 'jquery' ), $this->version, false );
			}

			if ( isset( $screen->post_type ) && $this->post_type === $screen->post_type || ( isset( $screen->taxonomy ) && $screen->taxonomy == 'bp_group_type' ) ) {
				wp_enqueue_script( $this->plugin_name . '-admin-script', plugin_dir_url( __FILE__ ) . 'js/bp-add-group-types-cpt.js', array( 'jquery' ), $this->version, true );
			}
		}

		/**
		 * Register a submenu to handle group types
		 *
		 * @since    1.0.0
		 */
		public function bpgt_add_submenu_page() {
			if ( empty( $GLOBALS['admin_page_hooks']['wbcomplugins'] ) ) {
				add_menu_page( esc_html__( 'WB Plugins', 'bp-add-group-types' ), esc_html__( 'WB Plugins', 'bp-add-group-types' ), 'manage_options', 'wbcomplugins', array( $this, 'bpgt_admin_settings_page' ), 'dashicons-lightbulb', 59 );
				add_submenu_page( 'wbcomplugins', esc_html__( 'General', 'bp-add-group-types' ), esc_html__( 'General', 'bp-add-group-types' ), 'manage_options', 'wbcomplugins' );
			}
			add_submenu_page( 'wbcomplugins', esc_html__( 'Group Types Settings', 'bp-add-group-types' ), esc_html__( 'Group Types Settings', 'bp-add-group-types' ), 'manage_options', $this->plugin_name, array( $this, 'bpgt_admin_settings_page' ) );
		}

		/**
		 * Actions performed to create a submenu page content
		 *
		 * @since 2.0.0
		 */
		public function bpgt_admin_settings_page() {
			$tab = ( filter_input( INPUT_GET, 'tab' ) !== null ) ? filter_input( INPUT_GET, 'tab' ) : 'bpgt-welcome';
			?>
		<div class="wrap">
                    <hr class="wp-header-end">
                    <div class="wbcom-wrap">
			<div class="bpgt-header">
				<?php echo do_shortcode( '[wbcom_admin_setting_header]' ); ?>
				<h1 class="wbcom-plugin-heading">
					<?php esc_html_e( 'BuddyPress Group Types Settings', 'bp-add-group-types' ); ?>
				</h1>
			</div>
			<?php settings_errors(); ?>
			<div class="wbcom-admin-settings-page">
				<?php
				$this->bpgt_plugin_settings_tabs();
				settings_fields( $tab );
				do_settings_sections( $tab );
				?>
			</div>
                    </div>
		</div>
			<?php
		}

		/**
		 * Actions performed to create tabs on the sub menu page
		 *
		 * @since 1.0.0
		 */
		public function bpgt_plugin_settings_tabs() {

			if ( bp_is_network_activated() ) {
				$group_url = get_admin_url( bp_get_root_blog_id(), 'edit-tags.php?taxonomy=bp_group_type' );
			} else {
				$group_url = bp_get_admin_url( add_query_arg( array( 'taxonomy' => 'bp_group_type' ), 'edit-tags.php' ) );
			}

			$current_tab = ( filter_input( INPUT_GET, 'tab' ) !== null ) ? filter_input( INPUT_GET, 'tab' ) : 'bpgt-welcome';
			echo '<div class="wbcom-tabs-section"><div class="nav-tab-wrapper"><div class="wb-responsive-menu"><span>' . esc_html( 'Menu' ) . '</span><input class="wb-toggle-btn" type="checkbox" id="wb-toggle-btn"><label class="wb-toggle-icon" for="wb-toggle-btn"><span class="wb-icon-bars"></span></label></div><ul>';
			foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab === $tab_key ? 'nav-tab-active' : '';
				echo '<li><a class="nav-tab ' . esc_attr( $active ) . '" id="' . esc_attr( $tab_key ) . '-tab" href="?page=' . esc_attr( $this->plugin_name ) . '&tab=' . esc_attr( $tab_key ) . '">' . esc_attr( $tab_caption ) . '</a></li>';
			}
			echo '</div></ul></div>';
		}

		/**
		 * Register all settings.
		 *
		 * @since 2.0.0
		 */
		public function bpgt_plugin_settings() {
			global $bp_grp_types;
			$this->plugin_settings_tabs['bpgt-welcome'] = esc_html__( 'Welcome', 'bp-add-group-types' );
			add_settings_section( 'bpgt-welcome', ' ', array( &$this, 'bpgt_welcome_content' ), 'bpgt-welcome' );
			
			
			$this->plugin_settings_tabs['bpgt-general'] = esc_html__( 'General', 'bp-add-group-types' );
			register_setting( 'bpgt_general_settings', 'bpgt_general_settings' );
			add_settings_section( 'bpgt-general', ' ', array( &$this, 'bpgt_general_settings_content' ), 'bpgt-general' );

			if ( 'on' === $bp_grp_types->group_type_search_enabled ) {
				$this->plugin_settings_tabs['bpgt-search'] = esc_html__( 'Group Type Search', 'bp-add-group-types' );
				register_setting( 'bpgt_search_settings', 'bpgt_search_settings' );
				add_settings_section( 'bpgt-search-enabled-section', ' ', array( &$this, 'bpgt_group_type_search_settings_content' ), 'bpgt-search' );
			}
		}
		
		public function bpgt_welcome_content() {
			if ( file_exists( dirname( __FILE__ ) . '/settings/bpgt-welcome-page.php' ) ) {
				require_once dirname( __FILE__ ) . '/settings/bpgt-welcome-page.php';
			}
		}
		/**
		 * General Tab Content
		 *
		 * @since 1.0.0
		 */
		public function bpgt_general_settings_content() {
			if ( file_exists( dirname( __FILE__ ) . '/settings/bpgt-general-settings.php' ) ) {
				require_once dirname( __FILE__ ) . '/settings/bpgt-general-settings.php';
			}
		}

		/**
		 * Group Types Search Tab Content
		 *
		 * @since 1.0.0
		 */
		public function bpgt_group_type_search_settings_content() {
			if ( file_exists( dirname( __FILE__ ) . '/settings/bpgt-group-type-search-settings.php' ) ) {
				require_once dirname( __FILE__ ) . '/settings/bpgt-group-type-search-settings.php';
			}
		}

		/**
		 * Register Group Type custom post type.
		 *
		 * @since    2.0.0
		 */
		public function register_bp_group_types_cpt() {

			$labels = array(
				'name'                  => esc_html_x( 'Group Types', 'Group Type General Name', 'bp-add-group-types' ),
				'singular_name'         => esc_html_x( 'Group Type', 'Group Type Singular Name', 'bp-add-group-types' ),
				'parent_item_colon'     => esc_html__( 'Parent Type:', 'bp-add-group-types' ),
				'all_items'             => esc_html__( 'Group Types', 'bp-add-group-types' ),
				'add_new_item'          => esc_html__( 'Add New Type', 'bp-add-group-types' ),
				'add_new'               => esc_html__( 'Add New', 'bp-add-group-types' ),
				'new_item'              => esc_html__( 'New Group Type', 'bp-add-group-types' ),
				'edit_item'             => esc_html__( 'Edit Group Type', 'bp-add-group-types' ),
				'update_item'           => esc_html__( 'Update Group Type', 'bp-add-group-types' ),
				'view_item'             => esc_html__( 'View Group Type', 'bp-add-group-types' ),
				'search_items'          => esc_html__( 'Search Group Types', 'bp-add-group-types' ),
				'not_found'             => esc_html__( 'Not found', 'bp-add-group-types' ),
				'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'bp-add-group-types' ),
				'insert_into_item'      => esc_html__( 'Insert into item', 'bp-add-group-types' ),
				'uploaded_to_this_item' => esc_html__( 'Uploaded to this item', 'bp-add-group-types' ),
				'items_list'            => esc_html__( 'Types list', 'bp-add-group-types' ),
				'items_list_navigation' => esc_html__( 'Types list navigation', 'bp-add-group-types' ),
				'filter_items_list'     => esc_html__( 'Filter types list', 'bp-add-group-types' ),
			);
			$args   = array(
				'label'               => esc_html__( 'Group Type', 'bp-add-group-types' ),
				'description'         => esc_html__( 'Create generated group types.', 'bp-add-group-types' ),
				'labels'              => $labels,
				'supports'            => array( 'title' ),
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_admin_bar'   => false,
				'show_in_nav_menus'   => false,
				'can_export'          => true,
				'has_archive'         => false,
				'rewrite'             => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => true,
			);
			register_post_type( $this->post_type, $args );
			$this->bpgt_export_existing_group_type();
		}

		/**
		 * Import existing Group Type created by this plugin.
		 *
		 * @since    2.0.0
		 */
		public function bpgt_export_existing_group_type() {
			$existing_groups_types = get_site_option( 'bpgt_group_types' );
			$bpgt_general_setting  = get_site_option( 'bpgt_general_settings' );
			$bpgt_search_setting   = get_site_option( 'bpgt_group_type_search_settings' );
			$is_update_group_types = get_site_option( 'bpgt_group_types_export' );
			$setting               = array();
			if ( ! $is_update_group_types ) {
				if ( ! empty( $existing_groups_types ) ) {
					foreach ( $existing_groups_types as $group_type ) {
						bp_groups_register_group_type(
							$group_type['slug'],
							array(
								'labels'                => array(
									'name'          => $group_type['name'],
									'singular_name' => $group_type['name'],
								),
								'has_directory'         => false,
								'show_in_list'          => false,
								'show_in_create_screen' => true,
								'create_screen_checked' => false,
								'description'           => $group_type['desc'],
							)
						);

						$post_id = wp_insert_post(
							array(
								'post_type'    => $this->post_type,
								'post_title'   => $group_type['name'],
								'post_content' => $group_type['desc'],
								'post_status'  => 'publish',
							)
						);

						if ( $post_id ) {
							// insert post meta
							add_post_meta( $post_id, 'type_id', $group_type['slug'] );
							add_post_meta( $post_id, 'singular_name', $group_type['name'] );
							if ( isset( $group_type['display'] ) ) {
								add_post_meta( $post_id, 'display_as_tab', $group_type['display'] );
							}
							add_post_meta( $post_id, 'has_directory', false );
							add_post_meta( $post_id, 'has_directory_slug', '' );
							add_post_meta( $post_id, 'show_in_create_screen', false );
							add_post_meta( $post_id, 'show_in_list', false );
							add_post_meta( $post_id, 'description', $group_type['desc'] );
						}
					}
					update_site_option( 'bpgt_group_types_export', 1 );
				}

				// Update all settings.
				if ( ! empty( $bpgt_general_setting ) ) {
					if ( isset( $bpgt_general_setting['group_type_search_enabled'] ) ) {
						if ( 'yes' === $bpgt_general_setting['group_type_search_enabled'] ) {
							$enable_search                        = 'on';
							$setting['group_type_search_enabled'] = $enable_search;
							update_site_option( 'bpgt_general_settings', $setting );
						}
					}
				}

				if ( ! empty( $bpgt_search_setting ) ) {
					if ( isset( $bpgt_search_setting['group_type_search_template'] ) ) {
						$search_setting                               = array();
						$search_setting['group_type_search_template'] = $bpgt_search_setting['group_type_search_template'];
						update_site_option( 'bpgt_search_settings', $search_setting );
					}
				}
			}
		}

		/**
		 * Add the Group Type management page to the BP Groups menu item.
		 *
		 * Function commented @2.4.0
		 *
		 * @since 2.0.0
		 */
		public function relocate_cpt_admin_screen() {
			if ( is_network_admin() && bp_is_network_activated() ) {
				$group_url = get_admin_url( bp_get_root_blog_id(), 'edit-tags.php?taxonomy=bp_group_type' );
			} else {
				$group_url = bp_get_admin_url( add_query_arg( array( 'taxonomy' => 'bp_group_type' ), 'edit-tags.php' ) );
			}

			add_submenu_page(
				'bp-groups',
				esc_html_x( 'Group Types', 'Group Type General Name', 'bp-add-group-types' ),
				esc_html_x( 'Group Types', 'Group Type General Name', 'bp-add-group-types' ),
				'manage_options',
				$group_url
			);
		}

		/**
		 * Add a meta box for the properties for this type.
		 *
		 * @since    2.0.0
		 */
		public function add_meta_box() {
			$screen = get_current_screen();
			add_meta_box(
				$this->meta_box_id,
				esc_html_x( 'Group Type Properties', 'Title of Group Type post meta box.', 'bp-add-group-types' ),
				array( $this, 'output_meta_box' ),
				$this->post_type,
				'normal',
				'high'
			);

			if ( 'add' != $screen->action ) {
				add_meta_box( 'bp-group-type-short-code', __( 'Shortcode', 'bp-add-group-types' ), array( $this, 'bp_group_type_shortcode_meta_box' ), $this->post_type, 'normal', 'high' );
			}
		}

		/**
		 * Create markup for type properties meta box.
		 *
		 * @since    2.0.0
		 * @return   html
		 */
		public function output_meta_box( $post ) {
			// Fetch the saved properties.
			$meta = $this->get_type_meta( $post->ID );
			// Add a nonce field
			wp_nonce_field( 'edit_' . $this->post_type . '_' . $post->ID, $this->meta_box_id . '_nonce' );
			?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Group Type ID', 'bp-add-group-types' ); ?></label>
					</th>
					<td>
						<input type="text" id="type_id" name="type_id" value="<?php echo isset( $meta['type_id'] ) ? esc_attr( $meta['type_id'] ) : ''; ?>" />
						<p class="description"><?php esc_html_e( 'Enter a lower-case string without spaces or special characters (used internally to identify the group type).', 'bp-add-group-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Singular Name', 'bp-add-group-types' ); ?></label>
					</th>
					<td>
						<input type="text" name="singular_name" value="<?php echo isset( $meta['singular_name'] ) ? esc_attr( $meta['singular_name'] ) : ''; ?>" />
						<p class="description"><?php esc_html_e( 'Enter a capitalized string (used as a label).', 'bp-add-group-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Display as a tab', 'bp-add-group-types' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="display_as_tab" name="display_as_tab" <?php checked( $meta['display_as_tab'], 'on' ); ?>/>
						<p class="description"><?php esc_html_e( 'Display as a tab in group directory page.', 'bp-add-group-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Create type filtered directory view link', 'bp-add-group-types' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="has_directory" name="has_directory" class="prerequisite"<?php checked( $meta['has_directory'], 'on' ); ?>/>
						<p for="has_directory" class="selectit description"><?php esc_html_e( 'Displays directory view of groups that belong to the respective group type.(e.g. http://example.com/groups/type/ninja/).', 'bp-add-group-types' ); ?></p>
						<br>
						<input type="text" id="has_directory_slug" name="has_directory_slug"  class="contingent" value="<?php echo isset( $meta['has_directory_slug'] ) ? esc_attr( $meta['has_directory_slug'] ) : ''; ?>" />
						<p class="description"><?php esc_html_e( 'If you want to use a slug that is different from the Group Type ID above, enter it here.', 'bp-add-group-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Add to Available Types on Create Screen', 'bp-add-group-types' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="show_in_create_screen" name="show_in_create_screen" class="prerequisite"<?php checked( $meta['show_in_create_screen'], 'on' ); ?>/> <p for="show_in_create_screen" class="selectit description"><?php esc_html_e( 'Include this group type during group creation and when a group administrator is on the group&rsquo;s &ldquo;Manage > Settings&rdquo; page.', 'bp-add-group-types' ); ?></p>
						<br>
						<input type="checkbox" id="create_screen_checked" name="create_screen_checked" class="contingent"<?php checked( $meta['create_screen_checked'], 'on' ); ?>/> <p for="create_screen_checked" class="description"><?php esc_html_e( 'Pre-select this group type on the group creation screen.', 'bp-add-group-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Include when Group Types are Listed for a Group(Single group header).', 'bp-add-group-types' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="show_in_list" name="show_in_list"<?php checked( $meta['show_in_list'], 'on' ); ?>/> <p for="show_in_list" class="selectit description"><?php esc_html_e( 'Include this group type when group types are listed, like in the group header. ', 'bp-add-group-types' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Description', 'bp-add-group-types' ); ?></label>
					</th>
					<td>
						<?php
						// We're saving this as the post's content.
						// We have to decide what is allowable in the description. HTML tags? Images? Text only?
						?>
						<textarea class="wp-editor-area" autocomplete="off" name="content" id="content" aria-hidden="true" style="width:100%; height: 100px;"><?php echo esc_textarea( $post->post_content ); ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
			<?php
		}

		/**
		 * Save the group type definition parameters.
		 *
		 * @since    2.0.0
		 * @return   bool True if all meta fields save successfully, false otherwise.
		 */
		public function save( $post_id ) {
			if ( get_post_type( $post_id ) != $this->post_type ) {
				return;
			}

			// Save meta.
			$meta_fields_to_save = array(
				'type_id',
				'singular_name',
				'display_as_tab',
				'has_directory',
				'has_directory_slug',
				'show_in_create_screen',
				'show_in_list',
				'create_screen_checked',
			);
			return $this->save_meta_fields( $post_id, $meta_fields_to_save );
		}

		/**
		 * Fetch the post meta that contains the type's properties and parse against defaults.
		 *
		 * @param int $post_id The ID of the type's post.
		 * @return array The type's parsed properties.
		 * @since 2.0.0
		 */
		public function get_type_meta( $post_id = 0 ) {
			if ( 0 === $post_id ) {
				$post_id = get_the_ID();
			}
			$saved_meta = array(
				'type_id'               => get_post_meta( $post_id, 'type_id', true ),
				'singular_name'         => get_post_meta( $post_id, 'singular_name', true ),
				'display_as_tab'        => get_post_meta( $post_id, 'display_as_tab', true ),
				'plural_name'           => get_the_title( $post_id ),
				'has_directory'         => get_post_meta( $post_id, 'has_directory', true ),
				'has_directory_slug'    => get_post_meta( $post_id, 'has_directory_slug', true ),
				'show_in_create_screen' => get_post_meta( $post_id, 'show_in_create_screen', true ),
				'show_in_list'          => get_post_meta( $post_id, 'show_in_list', true ),
				'create_screen_checked' => get_post_meta( $post_id, 'create_screen_checked', true ),
			);
			$meta       = wp_parse_args(
				$saved_meta,
				array(
					'type_id'               => '',
					'singular_name'         => '',
					'plural_name'           => '',
					'has_directory'         => false,
					'has_directory_slug'    => '',
					'show_in_create_screen' => false,
					'show_in_list'          => null,
					'create_screen_checked' => false,
				)
			);
			return $meta;
		}

		/**
		 * Fetch the saved member types and register them.
		 *
		 * @since 2.0.0
		 */
		public function register_group_types() {
			$group_types   = new WP_Query(
				array(
					'post_type'   => $this->post_type,
					'post_status' => 'publish',
					'nopaging'    => true,
				)
			);
			$has_directory = true;
			if ( $group_types->have_posts() ) {
				while ( $group_types->have_posts() ) {
					$group_types->the_post();
					$meta = $this->get_type_meta();

					// Types added via code take precedence.
					if ( null !== bp_groups_get_group_type_object( $meta['type_id'] ) ) {
						continue;
					}
					if ( 'on' == $meta['has_directory'] ) {
						$has_directory = $meta['has_directory_slug'] ? $meta['has_directory_slug'] : true;
					}
					bp_groups_register_group_type(
						$meta['type_id'],
						array(
							'labels'                => array(
								'name'          => $meta['singular_name'],
								'singular_name' => $meta['singular_name'],
							),
							'has_directory'         => $has_directory,
							'show_in_list'          => ( 'on' == $meta['show_in_list'] ),
							'show_in_create_screen' => ( 'on' == $meta['show_in_create_screen'] ),
							'create_screen_checked' => ( 'on' == $meta['create_screen_checked'] ),
							'description'           => get_the_content(),
						)
					);
				}
			}
		}

		/**
		 * Listen for AJAX Type ID checks.
		 *
		 * @since 2.0.0
		 */
		public function ajax_check_type_id() {
			if ( ! isset( $_POST['pagenow'] ) ) {
				return;
			}

			if ( 'bp_group_type' == $_POST['pagenow'] ) {
				add_action( 'bp_groups_register_group_types', array( $this, 'ajax_check_type_id_send_response' ), 11 );
			}
		}

		/**
		 * General handler for saving post meta.
		 *
		 * @since   2.0.0
		 *
		 * @param   int                          $post_id
		 * @param   array meta_key names to save
		 *
		 * @return  bool
		 */
		function save_meta_fields( $post_id, $fields = array() ) {
			$successes = 0;

			// Check that this user is allowed to take this action.
			$nonce_name   = $this->meta_box_id . '_nonce';
			$nonce_action = 'edit_' . $this->post_type . '_' . $post_id;
			if ( ! $this->user_can_save( $post_id, $nonce_name, $nonce_action ) ) {
				return false;
			}

			// Generate fallbacks for needed information if left blank.
			if ( empty( $_POST['post_title'] ) ) {
				$_POST['post_title'] = $this->post_type . '-' . $post_id;
			}
			if ( empty( $_POST['singular_name'] ) ) {
				$_POST['singular_name'] = $_POST['post_title'];
			}
			if ( empty( $_POST['type_id'] ) ) {
				$_POST['type_id'] = $_POST['singular_name'];
			}
			// Sanitize the type_id and custom directory slug.
			$_POST['type_id'] = sanitize_title( $_POST['type_id'] );
			if ( $_POST['has_directory_slug'] ) {
				$_POST['has_directory_slug'] = sanitize_title( $_POST['has_directory_slug'] );
			}
			$_POST['type_id'] = sanitize_title( $_POST['type_id'] );
			foreach ( $fields as $field ) {
				$old_setting = get_post_meta( $post_id, $field, true );
				$new_setting = ( isset( $_POST[ $field ] ) ) ? $_POST[ $field ] : '';
				$success     = false;

				if ( empty( $new_setting ) && ! empty( $old_setting ) ) {
					$success = delete_post_meta( $post_id, $field );
				} elseif ( $new_setting == $old_setting ) {
					/*
					 * No need to resave settings if they're the same.
					 * Also, update_post_meta returns false in this case,
					 * which is confusing.
					 */
					$success = true;
				} else {
					$success = update_post_meta( $post_id, $field, $new_setting );
				}

				if ( $success ) {
					$successes++;
				}
			}

			if ( $successes == count( $fields ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Determines whether or not the current user has the ability to save meta data associated with this post.
		 *
		 * @param  int    $post_id      The ID of the post being saved.
		 * @param  string $nonce_name   The name of the passed nonce.
		 * @param  string $nonce_action The action of the passed nonce.
		 *
		 * @return bool Whether or not the user has the ability to save this post.
		 * @since 2.0.0
		 */
		public function user_can_save( $post_id, $nonce_name, $nonce_action ) {

			// Don't save if the user hasn't submitted the changes.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return false;
			}

			// Verify that the input is well nonced.
			if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) ) {
				return false;
			}

			// Make sure the user has permission to edit this post.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Check whether a type ID has been registered via code.
		 *
		 * @since    2.0.0
		 */
		public function ajax_check_type_id_send_response() {
			if ( ! isset( $_POST['type'] ) ) {
				if ( ! isset( $_POST['singular_name'] ) ) {
					wp_send_json_error( esc_html__( 'Unknown type', 'bp-add-group-types' ) );
				} else {
					$type = sanitize_title( $_POST['singular_name'] );
				}
			} else {
				$type = sanitize_title( $_POST['type'] );
			}

			if ( 'bp_member_type' == $_POST['pagenow'] ) {
				if ( null == bp_get_member_type_object( $type ) ) {
					wp_send_json_success( esc_html__( 'Type ID is unique.', 'bp-add-group-types' ) );
				}
			} elseif ( 'bp_group_type' == $_POST['pagenow'] ) {
				if ( null == bp_groups_get_group_type_object( $type ) ) {
					wp_send_json_success( esc_html__( 'Type ID is unique.', 'bp-add-group-types' ) );
				}
			}

			wp_send_json_error( esc_html__( 'Type ID is already in use.', 'bp-add-group-types' ) );
		}

		/**
		 * Shortcode metabox for the group types admin edit screen.
		 *
		 * @since 2.0.0
		 *
		 * @param WP_Post $post
		 */

		public function bp_group_type_shortcode_meta_box( $post ) {
			$key = $this->bp_get_group_type_key( $post->ID );
			?>

		<p><?php esc_html_e( 'To display all groups with this group type on a dedicated page, add this shortcode to any WordPress page.', 'bp-add-group-types' ); ?></p>
		<code id="group-type-shortcode"><?php echo esc_html( '[bp_group type="' . $key . '"]' ); ?></code>
			<?php
		}


		/**
		 * Return group type key.
		 *
		 * @since 2.0.0
		 *
		 * @param $post_id
		 * @return mixed|string
		 */
		public function bp_get_group_type_key( $post_id ) {
			if ( empty( $post_id ) ) {
				return '';
			}

			$key     = get_post_meta( $post_id, '_bp_group_type_key', true );
			$type_id = get_post_meta( $post_id, 'type_id', true );

			// Fallback to legacy way of generating group type key from singular label
			// if Key is not set by admin user
			if ( empty( $key ) ) {
				$key = get_post_field( 'post_name', $post_id );

				if ( $type_id != '' ) {
					$key = $type_id;
				}

				$term = term_exists( sanitize_key( $key ), 'bp_group_type' );
				if ( 0 !== $term && null !== $term ) {
					$term = get_term( $term['term_id'], 'bp_group_type' );
				} else {
					$term_info = wp_insert_term( sanitize_key( $key ), 'bp_group_type' );
					$term      = get_term( $term_info['term_id'], 'bp_group_type' );
				}
				update_post_meta( $post_id, '_bp_group_type_key', $term->slug );
			}

			return apply_filters( 'bp_get_group_type_key', $key );
		}

		public function bp_create_groups_type_taxo_custom_fields( $group_type_fields ) {

			// $temp['bp_type_show_in_list'] = $group_type_fields['bp_type_show_in_list'];
			// unset($group_type_fields['bp_type_show_in_list']);
			$group_type_fields['bp_group_type_create_screen_checked'] = esc_html__( 'Pre select on group creation?', 'bp-add-group-types' );

			// $group_type_fields['bp_type_show_in_list'] = $temp['bp_type_show_in_list'];

			$group_type_fields['bp_group_type_display_as_tab'] = esc_html__( 'Display as a tab', 'bp-add-group-types' );

			return $group_type_fields;
		}

		public function bp_create_groups_type_metadata_schema( $schema = array(), $taxonomy = '' ) {
			if ( function_exists( 'bp_get_group_type_tax_name' ) && bp_get_group_type_tax_name() === $taxonomy ) {
				$temp['bp_type_show_in_list'] = $schema['bp_type_show_in_list'];
				unset( $schema['bp_type_show_in_list'] );

				$schema['bp_group_type_create_screen_checked'] = array(
					'description'       => __( 'Pre-select this group type on the group creation screen.', 'bp-add-group-types' ),
					'type'              => 'boolean',
					'single'            => true,
					'sanitize_callback' => 'absint',
				);

				$schema['bp_type_show_in_list'] = $temp['bp_type_show_in_list'];

				$schema['bp_group_type_display_as_tab'] = array(
					'description'       => __( 'Display as a tab in group directory page.', 'bp-add-group-types' ),
					'type'              => 'boolean',
					'single'            => true,
					'sanitize_callback' => 'absint',
				);

			}

			return $schema;
		}

		public function bpgt_bp_group_type_edit_form_fields( $term = null, $taxonomy = '' ) {

			?>
			<tr class="form-field bp-types-form term-bp_group_type_shorcode-wrap">
				<th scope="row"><label for="bp_group_type_shorcode"><?php esc_html_e( 'Shortcode', 'bp-add-group-types' ); ?></label></th>
				<td>
					<code id="group-type-shortcode"><?php echo esc_html( '[bp_group type="' . $term->slug . '"]' ); ?></code>

					<p><?php esc_html_e( 'To display all groups with this group type on a dedicated page, add this shortcode to any WordPress page.', 'bp-add-group-types' ); ?></p>
				</td>
			</tr>
			<?php
		}
	}
endif;
