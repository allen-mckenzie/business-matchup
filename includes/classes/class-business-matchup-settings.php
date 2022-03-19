<?php
    namespace BusinessMatchup;

    class Business_Matchup_Settings {

        /**
		 * save function
		 * This function takes the submitted form information from the custom post type editor screen
		 * and verifies the entered data before saving them as postmeta entries in the database for the current post.
		 * 
		 * @since 0.1.3
		 * 
		 * @param integer $post_id contains the id number of the current post
		 * @return integer $post_id if performing verification fails or if performing an autosave
		 */
		public function save( $post_id ) {
			$businessMatchupYelpAPI = new \BusinessMatchup\Business_Matchup_API();
			if ( ! wp_verify_nonce( filter_input( INPUT_POST, 'business_matchup_metabox_nonce'), 'business_matchup_metabox' ) ) {
				return $post_id;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			if ( 'page' == filter_input( INPUT_POST, 'business-matchups') ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return $post_id;
				}
			}

			$bmBizLoc = sanitize_text_field( filter_input( INPUT_POST, 'business-matchups-business-location') );
			$business_matchup_type = sanitize_text_field( filter_input( INPUT_POST, 'business-matchups-type') );
			$postID = sanitize_text_field( filter_input( INPUT_POST, 'post_ID') );
			$has_poll = get_post_meta( $postID, '_business_matchup_poll', true );
			if( null === $has_poll ) {
				return;
			}
			if( $has_poll === '' ) {
				$business_matchup_poll = $businessMatchupYelpAPI->addPoll( $postID, $business_matchup_type, $bmBizLoc );
				update_post_meta( $post_id, '_business_matchup_poll', $business_matchup_poll );
			}
			update_post_meta( $post_id, '_business_matchup_business_location', $bmBizLoc );
			update_post_meta( $post_id, '_business_matchup_type', $business_matchup_type );
		}

		/**
		 * bm_menu function
		 * This function creates a new admin menu item to allow users to enter their Yelp and StrawPoll API keys.
		 * 
		 * @since 0.1.3
		 */
		public function bm_menu() {
			add_menu_page( 'Business Matchup Settings', 'Business Matchup Settings', 'manage_options', 'business-matchups', array( $this, 'bmForm' ), 'dashicons-admin-generic', 0 );
			add_submenu_page( 'business-matchups', 'Business Matchup Settings', 'Settings', 'manage_options', 'business-matchups', array( $this, 'bmForm' ) );
			add_action( 'admin_init', array( $this, 'settings' ) );
		}

		/**
		 * settings function
		 * This function creates custom setting fields that our new menu will use to store credentials.
		 * 
		 * @since 0.1.3
		 */
		public function settings() {
			add_settings_section( 'business_matchup_settings_section', null, null, 'business-matchups-options' );
			register_setting( 'business_matchup_Fields', 'business_matchup_Text' );
			add_settings_field( 'business_matchup_text', 'Filtered Text', array( $this, 'business_matchup_yelp_fields'), 'business-matchups-options', 'business_matchup_settings_section' );
		}
		/**
		 * handleForm_admin_notice__success function
		 * This function handles the output and display of custom admin notices upon submitting the credentials in the settings menu.
		 * 
		 * @since 0.1.3
		 */
		public function handleForm_admin_notice__success() {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Your API Key\'s were saved.', 'business-matchup-polls' ); ?></p>
			</div>
			<?php
		}
		
		/**
		 * handleForm_admin_notice__error function
		 * This function handles the output and display of custom admin notices upon submitting the credentials in the settings menu
		 * without passing a valid nonce.
		 * 
		 * @since 0.1.0
		 */
		public function handleForm_admin_notice__error() {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'Uh uh uh... you didn\t say the magic word...', 'business-matchup-polls' ); ?></p>
			</div>
			<?php
		}

		/**
		 * handleForm function
		 * This function processes and validates the information submitted in our new settings menu
		 * 
		 * @since 0.1.3
		 */
		public function handleForm() {
			if ( wp_verify_nonce( filter_input( INPUT_POST, 'nonce'), 'business_matchup' ) AND current_user_can( 'manage_options' ) ) {
				update_option( 'business_matchup_yelp_api', sanitize_text_field( filter_input( INPUT_POST, 'yelp_api_key') ) );
				update_option( 'business_matchup_straw_poll_api', sanitize_text_field( filter_input( INPUT_POST, 'straw_poll_api_key') ) );
				add_action( 'admin_notices', 'handleForm_admin_notice__success' );
			}
			if ( !wp_verify_nonce( filter_input( INPUT_POST, 'nonce'), 'business_matchup' ) AND !current_user_can( 'manage_options' ) ) {
				add_action( 'admin_notices', 'handleForm_admin_notice__error' );
			}
		}

		/**
		 * bmForm function
		 * This function generates the form used in the settings menu to allow users to submit the credentials. That input is then
		 * passed to the handleForm function and stored into the options table to be used while making API calls to Yelp and StrawPoll.
		 * 
		 * @since 0.1.3
		 */
		public function bmForm() {
			?>
				<div class="wrap">
					<h1>Business Matchup Polls</h1>
					<?php
						if ( wp_verify_nonce( filter_input( INPUT_POST, 'nonce'), 'business_matchup' ) AND current_user_can( 'manage_options' ) ) {
							if (filter_input( INPUT_POST, 'justsubmitted') == "true") $this->handleForm();
						}
					?>
					<form method="POST">
						<input type="hidden" name="justsubmitted" value="true">
						<?php wp_nonce_field( 'business_matchup', 'nonce' ); ?>
						<label for="yelp_api_key"><p>Enter your Yelp API Key</p>
						<div class="business_matchup__flex-container">    
							<input style="width:100%" name="yelp_api_key" id="yelp_api_key" placeholder="aFMUqcUXCqlUbIn9uPn3x_" value="<?php esc_html_e( esc_textarea( get_option( 'business_matchup_yelp_api' ) ), 'business-matchup-polls' ); ?>" />
						</div>
						<label for="straw_poll_api_key"><p>Enter your Straw Poll API Key</p>
						<div class="business_matchup__flex-container">
							<input style="width:100%" name="straw_poll_api_key" id="straw_poll_api_key" placeholder="aFMUqcUXCqlUbIn9uPn3x_" value="<?php esc_html_e( esc_textarea( get_option( 'business_matchup_straw_poll_api' ) ), 'business-matchup-polls' ); ?>" />
						</div>
						<br style="padding-bottom: 20px;"/>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
					</form>
				</div>
			<?php
		}

		/**
		 * optionsSubPage function
		 * This function help define our new settings page in our new custom menu item to help users easily find where to enter their credentials.
		 * 
		 * @since 0.1.3
		 */
		public function optionsSubPage() {
			?>
				<div class="wrap">
					<h1>Business Matchup Polls Options</h1>
					<form action="options.php" method="POST">
						<?php
							settings_fields( 'business_matchup_Fields' );
							do_settings_sections( 'business-matchups-options' );
							submit_button();
						?>
					</form>
				</div>
			<?php
		}

    }