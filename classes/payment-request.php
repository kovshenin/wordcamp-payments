<?php

/*
 * Create the Payment Request post type and associated functionality
 */
class WCP_Payment_Request {
	const POST_TYPE = 'wcp_payment_request';

	public function __construct() {
		add_action( 'init',           array( $this, 'init_post_type' ));
		add_action( 'init',           array( $this, 'create_post_taxonomies' ) );
		add_action( 'add_meta_boxes', array( $this, 'init_meta_boxes' ) );
		add_action( 'save_post',      array( $this, 'save_payment' ) );

		add_filter( 'manage_'.      self::POST_TYPE .'_posts_columns',       array( $this, 'get_columns' ) );
		add_filter( 'manage_edit-'. self::POST_TYPE .'_sortable_columns',    array( $this, 'get_sortable_columns' ) );
		add_action( 'manage_'.      self::POST_TYPE .'_posts_custom_column', array( $this, 'render_columns' ), 10, 2 );
		add_action( 'pre_get_posts',                                         array( $this, 'sort_columns' ) );
	}

	/**
	 * Prepares site to use the plugin during activation
	 *
	 * @param bool $network_wide
	 */
	public function activate( $network_wide ) {
		$this->init_post_type();
		$this->create_post_taxonomies();
		$this->insert_default_terms();
	}

	public function init_post_type() {
		$labels = array(
			'name'               => _x( 'Payment Requests', 'post type general name', 'wordcamporg' ),
			'singular_name'      => _x( 'Payment Request', 'post type singular name', 'wordcamporg' ),
			'menu_name'          => _x( 'Payment Requests', 'admin menu', 'wordcamporg' ),
			'name_admin_bar'     => _x( 'Payment Request', 'add new on admin bar', 'wordcamporg' ),
			'add_new'            => _x( 'Add New', 'payment', 'wordcamporg' ),
			'add_new_item'       => __( 'Add New Payment Request', 'wordcamporg' ),
			'new_item'           => __( 'New Payment Request', 'wordcamporg' ),
			'edit_item'          => __( 'Edit Payment Request', 'wordcamporg' ),
			'view_item'          => __( 'View Payment Request', 'wordcamporg' ),
			'all_items'          => __( 'All Payment Requests', 'wordcamporg' ),
			'search_items'       => __( 'Search Payment Requests', 'wordcamporg' ),
			'parent_item_colon'  => __( 'Parent Payment Requests:', 'wordcamporg' ),
			'not_found'          => __( 'No payment requests found.', 'wordcamporg' ),
			'not_found_in_trash' => __( 'No payment requests found in Trash.', 'wordcamporg' )
		);

		$args = array(
			'labels'            => $labels,
			'description'       => 'WordCamp Payment Requests',
			'public'            => false,
			'show_ui'           => true,
			'show_in_nav_menus' => true,
			'menu_position'     => 25,
			'supports'          => array( 'title' ),
			'taxonomies'        => array( 'payment-category' ),
			'has_archive'       => true,
		);

		return register_post_type( self::POST_TYPE, $args );
	}

	public function create_post_taxonomies() {
		register_taxonomy(
			'payment-category',
			self::POST_TYPE,
			array(
				'labels' => array(
					'name'          => __( 'Payment Request Categories', 'wordcamporg' ),
					'singular_name' => __( 'Payment Request Category', 'wordcamporg' ),
					'all_items'     => __( 'All Categories', 'wordcamporg' ),
				),
				'show_ui'      => false,
				'hierarchical' => true,
				'capabilities' => array(
					'manage_terms' => 'do_not_allow',
					'edit_terms'   => 'do_not_allow',
					'delete_terms' => 'do_not_allow',
				)
			)
		);
	}

	/**
	 * Insert the default category terms.
	 */
	protected function insert_default_terms() {
		wp_insert_term( 'After Party', 'payment-category' );
		wp_insert_term( 'Audio Visual', 'payment-category' );
		wp_insert_term( 'Food & Beverage', 'payment-category' );
		wp_insert_term( 'Office Supplies', 'payment-category' );
		wp_insert_term( 'Signage & Badges', 'payment-category' );
		wp_insert_term( 'Speaker Event', 'payment-category' );
		wp_insert_term( 'Swag (t-shirts, stickers, etc)', 'payment-category' );
		wp_insert_term( 'Venue', 'payment-category' );
		wp_insert_term( 'Other', 'payment-category' );
	}

	public function init_meta_boxes() {
		add_meta_box(
			'wcp_general_info',
			__( 'General Information', 'wordcamporg' ),
			array( $this, 'render_general_metabox' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'wcp_payment_details',
			__( 'Payment Details', 'wordcamporg' ),
			array( $this, 'render_payment_metabox' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'wcp_vendor_details',
			__( 'Vendor Details', 'wordcamporg' ),
			array( $this, 'render_vendor_metabox' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'wcp_files',
			__( 'Attach Supporting Documentation', 'wordcamporg' ),
			array( $this, 'render_files_metabox' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the General Information metabox
	 *
	 * @param WP_Post $post
	 */
	public function render_general_metabox( $post ) {
		wp_nonce_field( 'general_info', 'general_info_nonce' );

		$assigned_category = wp_get_object_terms( $post->ID, 'payment-category' );
		if ( empty( $assigned_category ) || is_wp_error( $assigned_category ) ) {
			$assigned_category = 'null';
		} else {
			$assigned_category = $assigned_category[0]->term_id;
		}

		require_once( dirname( __DIR__ ) . '/views/payment-request/metabox-general.php' );

		// todo If they select other but don't fill in the explanation, set to draft and display error msg, similar to require_complete_meta_to_publish_wordcamp()
	}

	/**
	 * Render the Vendor Details metabox
	 *
	 * @param WP_Post $post
	 */
	public function render_vendor_metabox( $post ) {
		wp_nonce_field( 'vendor_details', 'vendor_details_nonce' );

		require_once( dirname( __DIR__ ) . '/views/payment-request/metabox-vendor.php' );
	}

	/**
	 * Render the Payment Details
	 *
	 * @param $post
	 */
	public function render_payment_metabox( $post ) {
		wp_nonce_field( 'payment_details', 'payment_details_nonce' );
		$selected_payment_method = get_post_meta( $post->ID, '_camppayments_payment_method', true );

		require_once( dirname( __DIR__ ) . '/views/payment-request/metabox-payment.php' );
	}

	/**
	 * Render the Vendor Details metabox
	 *
	 * @param WP_Post $post
	 */
	public function render_files_metabox( $post ) {
		wp_nonce_field( 'wcp_files', 'wcp_files_nonce' );

		require_once( dirname( __DIR__ ) . '/views/payment-request/metabox-files.php' );
	}

	/**
	 * Render a <textarea> field with the given attributes.
	 *
	 * @param WP_Post $post
	 * @param string $label
	 * @param string $name
	 * @param string $description
	 */
	protected function render_textarea_input( $post, $label, $name, $description = '' ) {
		$date = get_post_meta( $post->ID, '_camppayments_' . $name, true );

		require( dirname( __DIR__ ) . '/views/payment-request/input-textarea.php' );
	}

	/**
	 * Render a <select> field with the given attributes.
	 *
	 * @param WP_Post $post
	 * @param string $label
	 * @param string $name
	 */
	protected function render_select_input( $post, $label, $name ) {
		$selected = get_post_meta( $post->ID, '_camppayments_' . $name, true );
		$options  = $this->get_field_value( $name, $post );

		require( dirname( __DIR__ ) . '/views/payment-request/input-select.php' );
	}

	/**
	 * Render a <input type="radio"> field with the given attributes.
	 *
	 * @param WP_Post $post
	 * @param string $label
	 * @param string $name
	 */
	protected function render_radio_input( $post, $label, $name ) {
		$selected = get_post_meta( $post->ID, '_camppayments_' . $name, true );
		$options  = $this->get_field_value( $name, $post );

		require( dirname( __DIR__ ) . '/views/payment-request/input-radio.php' );
	}

	/**
	 * Render a <input type="checkbox"> field with the given attributes.
	 *
	 * @param WP_Post $post
	 * @param string $label
	 * @param string $name
	 */
	protected function render_checkbox_input( $post, $label, $name, $description = '' ) {
		$value = $this->get_field_value( $name, $post );

		require( dirname( __DIR__ ) . '/views/payment-request/input-checkbox.php' );
	}

	/**
	 * Render a <input type="text"> field with the given attributes.
	 *
	 * @param WP_Post $post
	 * @param string $label
	 * @param string $name
	 */
	protected function render_text_input( $post, $label, $name, $description = '', $variant = 'text', $readonly = false ) {
		$value = $this->get_field_value( $name, $post );

		require( dirname( __DIR__ ) . '/views/payment-request/input-text.php' );
	}

	/**
	 * Render an upload button and list of uploaded files.
	 *
	 * @param WP_Post $post
	 * @param string $label
	 * @param string $name
	 * @param string $description
	 */
	protected function render_files_input( $post, $label, $name, $description = '' ) {
		$files = get_posts( array(
			'post_parent'    => $post->ID,
			'post_type'      => 'attachment',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		foreach ( $files as &$file ) {
			$file->filename = wp_basename( $file->guid );
			$file->url      = wp_get_attachment_url( $file->ID );
		}

		require( dirname( __DIR__ ) . '/views/payment-request/input-files.php' );
	}

	/**
	 * Get the value of a given field.
	 *
	 * @param string $name
	 * @param WP_Post $post
	 *
	 * @return mixed
	 */
	protected function get_field_value( $name, $post ) {
		switch( $name ) {
			case 'request_id':
				$value = $post->ID;
				break;

			case 'requester':
				$requester = get_user_by( 'id', $post->post_author );
				if ( is_a( $requester, 'WP_User' ) ) {
					$value = sprintf( '%s <%s>', $requester->get( 'display_name' ), $requester->get( 'user_email' ) );
				} else {
					$value = '';
				}
				break;

			case 'due_by':
				if ( $value = get_post_meta( $post->ID, '_camppayments_due_by', true ) ) {
					$value = date( 'Y-m-d', $value );
				}
				break;

			case 'currency':
				$value = $this->get_currencies();
				break;

			case 'payment_method':
				$value = array( 'Check', 'Credit Card', 'Wire' );
				break;

			default:
				$value = get_post_meta( $post->ID, '_camppayments_' . $name, true );
				break;
		}

		return $value;
	}

	/**
	 * Get a list of all world currencies, with the most frequently used at the top.
	 *
	 * @return array
	 */
	protected function get_currencies() {
		$currencies = array (
			'null-most-frequently-used' => 'Most Frequently Used:',
			'USD' => 'United States Dollar',
			'EUR' => 'Euro Member Countries',

			'null-separator2' => '',

			'null-all' => 'All:',
			'ALL' => 'Albania Lek',
			'AFN' => 'Afghanistan Afghani',
			'ARS' => 'Argentina Peso',
			'AWG' => 'Aruba Guilder',
			'AUD' => 'Australia Dollar',
			'AZN' => 'Azerbaijan New Manat',
			'BSD' => 'Bahamas Dollar',
			'BBD' => 'Barbados Dollar',
			'BDT' => 'Bangladeshi taka',
			'BYR' => 'Belarus Ruble',
			'BZD' => 'Belize Dollar',
			'BMD' => 'Bermuda Dollar',
			'BOB' => 'Bolivia Boliviano',
			'BAM' => 'Bosnia and Herzegovina Convertible Marka',
			'BWP' => 'Botswana Pula',
			'BGN' => 'Bulgaria Lev',
			'BRL' => 'Brazil Real',
			'BND' => 'Brunei Darussalam Dollar',
			'KHR' => 'Cambodia Riel',
			'CAD' => 'Canada Dollar',
			'KYD' => 'Cayman Islands Dollar',
			'CLP' => 'Chile Peso',
			'CNY' => 'China Yuan Renminbi',
			'COP' => 'Colombia Peso',
			'CRC' => 'Costa Rica Colon',
			'HRK' => 'Croatia Kuna',
			'CUP' => 'Cuba Peso',
			'CZK' => 'Czech Republic Koruna',
			'DKK' => 'Denmark Krone',
			'DOP' => 'Dominican Republic Peso',
			'XCD' => 'East Caribbean Dollar',
			'EGP' => 'Egypt Pound',
			'SVC' => 'El Salvador Colon',
			'EEK' => 'Estonia Kroon',
			'FKP' => 'Falkland Islands (Malvinas) Pound',
			'FJD' => 'Fiji Dollar',
			'GHC' => 'Ghana Cedis',
			'GIP' => 'Gibraltar Pound',
			'GTQ' => 'Guatemala Quetzal',
			'GGP' => 'Guernsey Pound',
			'GYD' => 'Guyana Dollar',
			'HNL' => 'Honduras Lempira',
			'HKD' => 'Hong Kong Dollar',
			'HUF' => 'Hungary Forint',
			'ISK' => 'Iceland Krona',
			'INR' => 'India Rupee',
			'IDR' => 'Indonesia Rupiah',
			'IRR' => 'Iran Rial',
			'IMP' => 'Isle of Man Pound',
			'ILS' => 'Israel Shekel',
			'JMD' => 'Jamaica Dollar',
			'JPY' => 'Japan Yen',
			'JEP' => 'Jersey Pound',
			'KZT' => 'Kazakhstan Tenge',
			'KPW' => 'Korea (North) Won',
			'KRW' => 'Korea (South) Won',
			'KGS' => 'Kyrgyzstan Som',
			'LAK' => 'Laos Kip',
			'LVL' => 'Latvia Lat',
			'LBP' => 'Lebanon Pound',
			'LRD' => 'Liberia Dollar',
			'LTL' => 'Lithuania Litas',
			'MKD' => 'Macedonia Denar',
			'MYR' => 'Malaysia Ringgit',
			'MUR' => 'Mauritius Rupee',
			'MXN' => 'Mexico Peso',
			'MNT' => 'Mongolia Tughrik',
			'MZN' => 'Mozambique Metical',
			'NAD' => 'Namibia Dollar',
			'NPR' => 'Nepal Rupee',
			'ANG' => 'Netherlands Antilles Guilder',
			'NZD' => 'New Zealand Dollar',
			'NIO' => 'Nicaragua Cordoba',
			'NGN' => 'Nigeria Naira',
			'NOK' => 'Norway Krone',
			'OMR' => 'Oman Rial',
			'PKR' => 'Pakistan Rupee',
			'PAB' => 'Panama Balboa',
			'PYG' => 'Paraguay Guarani',
			'PEN' => 'Peru Nuevo Sol',
			'PHP' => 'Philippines Peso',
			'PLN' => 'Poland Zloty',
			'QAR' => 'Qatar Riyal',
			'RON' => 'Romania New Leu',
			'RUB' => 'Russia Ruble',
			'SHP' => 'Saint Helena Pound',
			'SAR' => 'Saudi Arabia Riyal',
			'RSD' => 'Serbia Dinar',
			'SCR' => 'Seychelles Rupee',
			'SGD' => 'Singapore Dollar',
			'SBD' => 'Solomon Islands Dollar',
			'SOS' => 'Somalia Shilling',
			'ZAR' => 'South Africa Rand',
			'LKR' => 'Sri Lanka Rupee',
			'SEK' => 'Sweden Krona',
			'CHF' => 'Switzerland Franc',
			'SRD' => 'Suriname Dollar',
			'SYP' => 'Syria Pound',
			'TWD' => 'Taiwan New Dollar',
			'THB' => 'Thailand Baht',
			'TTD' => 'Trinidad and Tobago Dollar',
			'TRY' => 'Turkey Lira',
			'TRL' => 'Turkey Lira',
			'TVD' => 'Tuvalu Dollar',
			'UAH' => 'Ukraine Hryvna',
			'GBP' => 'United Kingdom Pound',
			'UYU' => 'Uruguay Peso',
			'UZS' => 'Uzbekistan Som',
			'VEF' => 'Venezuela Bolivar',
			'VND' => 'Viet Nam Dong',
			'YER' => 'Yemen Rial',
			'ZWD' => 'Zimbabwe Dollar'
		);

		return $currencies;
	}

	/**
	 * Save the post's data
	 *
	 * @param int $post_id
	 */
	public function save_payment( $post_id ) {
		// Verify nonces
		$nonces = array( 'general_info_nonce', 'payment_details_nonce', 'vendor_details_nonce' );

		foreach ( $nonces as $nonce ) {
			if ( ! isset( $_POST[ $nonce ] ) || ! wp_verify_nonce( $_POST[ $nonce ], str_replace( '_nonce', '', $nonce ) ) ) {
				return;
			}
		}

		// Sanitize and save the field values
		$this->sanitize_save_normal_fields( $post_id );
		$this->sanitize_save_misc_fields( $post_id );
	}

	/**
	 * Sanitize and save values for all normal fields
	 *
	 * @param int $post_id
	 */
	protected function sanitize_save_normal_fields( $post_id ) {
		foreach ( $_POST as $key => $unsafe_value ) {
			switch ( $key ) {
				case 'description':
				case 'notes':
					$safe_value = wp_kses( $unsafe_value, wp_kses_allowed_html( 'strip' ) );
					break;

				case 'payment_amount':
				case 'currency':
				case 'vendor_name':
				case 'vendor_phone_number':
				case 'vendor_email_address':
				case 'vendor_street_address':
				case 'vendor_city':
				case 'vendor_state':
				case 'vendor_zip_code':
				case 'vendor_country':
				case 'bank_name':
				case 'bank_bic':
				case 'beneficiary_account_number':
				case 'beneficiary_name':
				case 'payable_to':
				case 'vendor_contact_person':
				case 'other_category_explanation':
					$safe_value = sanitize_text_field( $unsafe_value );
					break;

				case 'payment_method':
					if ( in_array( $unsafe_value, $this->get_field_value( 'payment_method', null ) ) ) {
						$safe_value = $unsafe_value;
					} else {
						$safe_value = false;
					}
					break;

				case 'due_by':
					if ( empty( $_POST[ $key ] ) ) {
						$safe_value = '';
					} else {
						$safe_value = strtotime( sanitize_text_field( $unsafe_value ) );
					}
					break;

				default:
					$safe_value = null;
					break;
			}

			if ( ! is_null( $safe_value ) ) {
				update_post_meta( $post_id, '_camppayments_' . $key, $safe_value );
			}
		}
	}

	/**
	 * Sanitize and save values for all checkbox fields
	 *
	 * @param int $post_id
	 */
	protected function sanitize_save_misc_fields( $post_id ) {
		// Checkboxes
		$checkbox_fields = array( 'requesting_reimbursement' );
		foreach( $checkbox_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, '_camppayments_' . $field, $_POST[ $field ] );
			} else {
				delete_post_meta( $post_id, '_camppayments_' . $field );
			}
		}

		// Taxonomies
		if ( ! empty( $_POST['payment_category'] ) ) {
			wp_set_object_terms( $post_id, absint( $_POST['payment_category'] ), 'payment-category' );
		}
	}

	/**
	 * Define columns for the Payment Requests screen.
	 *
	 * @param array $_columns
	 * @return array
	 */
	public function get_columns( $_columns ) {
		$columns = array(
			'cb'             => $_columns['cb'],
			'author'         => __( 'Author' ),
			'title'          => $_columns['title'],
			'date'           => $_columns['date'],
			'due_by'         => __( 'Due by', 'wordcamporg' ),
			'vendor_name'    => __( 'Vendor', 'wordcamporg' ),
			'payment_amount' => __( 'Amount', 'wordcamporg' ),
			'status'         => __( 'Status', 'wordcamporg' ),
		);

		return $columns;
	}

	/**
	 * Register our sortable columns.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function get_sortable_columns( $columns ) {
		$columns['due_by']   = '_camppayments_due_by';

		return $columns;
	}

	/**
	 * Render custom columns on the Payment Requests screen.
	 *
	 * @param string $column
	 * @param int $post_id
	 */
	public function render_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'status':
				$post = get_post( $post_id );
				echo esc_html( ucwords( $post->post_status ) );
				break;

			case 'payment_amount':
				$currency = get_post_meta( $post_id, '_camppayments_currency', true );
				if ( false === strpos( $currency, 'null' ) ) {
					echo esc_html( $currency ) . ' ';
				}

				echo esc_html( get_post_meta( $post_id, '_camppayments_payment_amount', true ) );
				break;

			case 'due_by':
				if ( $date = get_post_meta( $post_id, '_camppayments_due_by', true ) ) {
					echo date( 'F jS, Y', $date );
				}
				break;

			default:
				echo esc_html( get_post_meta( $post_id, '_camppayments_' . $column, true ) );
				break;
		}
	}

	/**
	 * Sort our custom columns.
	 *
	 * @param WP_Query $query
	 */
	public function sort_columns( $query ) {
		if ( self::POST_TYPE != $query->get( 'post_type' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		switch( $orderby ) {
			case '_camppayments_due_by':
				$query->set( 'meta_key', '_camppayments_due_by' );
				$query->set( 'orderby', 'meta_value_num' );
				break;

			default:
				break;
		}
	}
}
