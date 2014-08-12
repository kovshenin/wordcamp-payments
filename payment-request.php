<?php

class WCP_Payment_Request {
	const POST_TYPE = 'wcp_payment_request';

	public function __construct() {
		add_action( 'init',           array( $this, 'init_post_type' ));
		add_action( 'init',           array( $this, 'create_post_taxonomies' ) );
		add_action( 'add_meta_boxes', array( $this, 'init_meta_box' ) );
		add_action( 'save_post',      array( $this, 'save_payment' ) );

		add_filter( 'manage_'. self::POST_TYPE .'_posts_columns',        array( $this, 'get_columns' ) );
		add_action( 'manage_'. self::POST_TYPE .'_posts_custom_column' , array( $this, 'render_column' ), 10, 2 );
	}

	/**
	 * Prepares site to use the plugin during activation
	 *
	 * @param bool $network_wide
	 */
	public function activate( $network_wide ) {
		$this->init_post_type();
		$this->create_post_taxonomies();
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
				),
				'hierarchical' => true,
			)
		);

		// todo will have to hardcode these, or manage them from central/network admin
	}

	public function init_meta_box() {
		add_meta_box(
			'camp_general_info',
			__( 'General Information', 'wordcamporg' ),
			array( $this, 'render_general_metabox' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'camp_payment_details',
			__( 'Payment Details', 'wordcamporg' ),
			array( $this, 'render_payment_metabox' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'camp_vendor_details',
			__( 'Vendor Details', 'wordcamporg' ),
			array( $this, 'render_vendor_metabox' ),
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

		echo '<table class="form-table">';

		$this->render_text_input( $post, 'Request ID', 'request_id', '', '', true );
		$this->render_text_input( $post, 'Requester', 'requester', '', '', true );
		$this->render_select_input( $post, 'WordCamp', 'wordcamp' );
		$this->render_textarea_input( $post, 'Description', 'description' );
		$this->render_text_input( $post, 'Requested date for payment/due by', 'due_by', 'Format: ' . date( 'F jS, Y' ), 'date' );
		$this->render_files_input( $post, 'Files', 'files', __( 'Attach supporting documentation including Invoices, Contracts, or other vendor correspondence. If no supporting documentation is available, indicate reason in the notes below.', 'wordcamporg' ) );
		$this->render_text_input( $post, 'Amount', 'payment_amount' );
		$this->render_select_input( $post, 'Currency', 'currency' );
		$this->render_textarea_input( $post, 'Notes', 'notes', 'Any other details you want to share.' );

		echo '</table>';
	}

	/**
	 * Render the Vendor Details metabox
	 *
	 * @param WP_Post $post
	 */
	public function render_vendor_metabox( $post ) {
		wp_nonce_field( 'vendor_details', 'vendor_details_nonce' );

		echo '<table class="form-table">';

		$this->render_text_input( $post, 'Vendor name', 'vendor_name' );
		$this->render_text_input( $post, 'Vendor Phone Number', 'vendor_phone_number', '', 'tel' );
		$this->render_text_input( $post, 'Vendor Email Address', 'vendor_email_address', '', 'email' );
		$this->render_text_input( $post, 'Vendor Street Address', 'vendor_street_address' );
		$this->render_text_input( $post, 'Vendor City', 'vendor_city' );
		$this->render_text_input( $post, 'Vendor State / Province', 'vendor_state' );
		$this->render_text_input( $post, 'Vendor ZIP / Postal Code', 'vendor_zip_code' );
		$this->render_text_input( $post, 'Vendor Country', 'vendor_country' );

		echo '</table>';
	}

	/**
	 * Render the Payment Details
	 *
	 * @param $post
	 */
	public function render_payment_metabox( $post ) {
		wp_nonce_field( 'payment_details', 'payment_details_nonce' );

		echo '<table class="form-table">';
		$this->render_radio_input( $post, 'Payment Method', 'payment_method' );
		echo '</table>';

		?>

		<p id="payment_method_credit_card" class="description">
			<?php _e( 'Please make sure that you upload an authorization form above, if one is required by the vendor.', 'wordcamporg' ); ?>
		</p>

		<?php

		echo '<table id="payment_method_wire" class="form-table">';
		$this->render_text_input( $post, 'Beneficiary’s Bank', 'bank_name' );
		$this->render_text_input( $post, 'Beneficiary’s Bank Address', 'bank_address' );   // todo multiple fields
		$this->render_text_input( $post, 'Beneficiary’s Bank SWIFT BIC', 'bank_bic' );
		$this->render_text_input( $post, 'Beneficiary’s Name', 'beneficiary_name' );
		$this->render_text_input( $post, 'Beneficiary’s Address', 'beneficiary_address' );        // todo multiple
		// todo add account number
		echo '</table>';
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

		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr( $name ); ?>">
					<?php echo esc_html( $label ); ?>:
				</label>
			</th>

			<td>
				<textarea id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" class="large-text"><?php echo esc_html( $date ); ?></textarea>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</td>
		</tr>

		<?php
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

		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr( $name ); ?>">
					<?php echo esc_html( $label ); ?>:
				</label>
			</th>

			<td>
				<select id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>">
					<option value="null-select-one">
						<?php printf( __( 'Select a %s', 'wordcamporg' ), $label ); ?>
					</option>
					<option value="null-separator1"></option>

					<?php foreach ( $options as $value => $option_label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $selected ); ?>>
							<?php echo esc_attr( $option_label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>

		<?php
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

		?>

		<tr>
			<th>
				<?php echo esc_html( $label ); ?>:
			</th>

			<td>
				<?php foreach ( $options as $option ) : ?>
					<?php $option_name = $name . '_' . sanitize_title_with_dashes( $option ); ?>

					<input
						type="radio"
						id="<?php echo esc_attr( $option_name ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
						value="<?php echo esc_attr( $option ); ?>"
						<?php checked( $option, $selected ); ?>
					/>

					<label for="<?php echo esc_attr( $option_name ); ?>">
						<?php echo esc_html( $option ); ?>:
					</label>
				<?php endforeach; ?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Render a <input type="date"> field with the given attributes.
	 *
	 * @param WP_Post $post
	 * @param string $label
	 * @param string $name
	 */
	protected function render_text_input( $post, $label, $name, $description = '', $variant = 'text', $readonly = false ) {
		$value = $this->get_field_value( $name, $post );

		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr( $name ); ?>">
					<?php echo esc_html( $label ); ?>:
				</label>
			</th>

			<td>
				<input
					type="<?php echo esc_attr( $variant ); ?>"
					id="<?php echo esc_attr( $name ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					<?php if ( $readonly ) { echo 'readonly="readonly"'; } ?>
					class="regular-text"
				/>

				<?php if ( ! empty( $description ) ) : ?>
					<span class="description"><?php echo esc_html( $description ); ?></span>
				<?php endif; ?>
			</td>
		</tr>

		<?php
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
					$value = date( 'F jS, Y', $value );
				}
				break;

			case 'wordcamp':
				$value = $this->get_wordcamps();
				break;

			case 'currency':
				$value = $this->get_currencies();
				break;

			case 'payment_method':
				$value = array( 'Check', 'Visa', 'Wire' );
				break;

			default:
				$value = get_post_meta( $post->ID, '_camppayments_' . $name, true );
				break;
		}

		return $value;
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
		$files = ''; // get post attachments

		// todo this is just a ui stub w/ hacky inline style and such, redo the right way after get feedback on direction

		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr( $name ); ?>">
					<?php echo esc_html( $label ); ?>:
				</label>
			</th>

			<td>
				<?php if ( ! empty( $description ) ) : ?>
					<p class="description" style="margin-bottom: 15px;"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>

				<div class="wp-media-buttons">
					<a href="" class="button insert-media add_media">Add files</a>
				</div>

				<div style="margin-top: 15px; margin-bottom: 0;">
					<p>Attached files:</p>
					<ul>
						<li style="list-style-type: disc; margin-left: 15px;"><a href="">invoice.pdf</a></li>
						<li style="list-style-type: disc; margin-left: 15px;"><a href="">receipt.pdf</a></li>
					</ul>
				</div>
			</td>
		</tr>

		<?php
	}

	/**
	 * Get a list of all (recently/) active WordCamps.
	 *
	 * @return array
	 */
	protected function get_wordcamps() {
		switch_to_blog( BLOG_ID_CURRENT_SITE ); // central.wordcamp.org

		$wordcamps      = array();
		$wordcamp_posts = get_posts( array(
			'post_type'   => 'wordcamp',
			'post_status' => array( 'pending', 'publish' ),
			'numberposts' => -1,
			'meta_query'  => array(
				array(
					'key'     => 'Start Date (YYYY-mm-dd)',
					'value'   => strtotime( 'now - 1 year' ),
					'compare' => '>'
				),
			),
		) );

		restore_current_blog();

		foreach ( $wordcamp_posts as $post ) {
			$wordcamps[ $post->ID ] = $post->post_title;
		}

		return $wordcamps;
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

	public function save_payment( $post_id ) {
		// Verify nonces
		$nonces = array( 'general_info_nonce', 'payment_details_nonce', 'vendor_details_nonce' );

		foreach ( $nonces as $nonce ) {
			if ( ! isset( $_POST[ $nonce ] ) || ! wp_verify_nonce( $_POST[ $nonce ], str_replace( '_nonce', '', $nonce ) ) ) {
				return;
			}
		}

		// Sanitize and save field values
		foreach ( $_POST as $key => $unsafe_value ) {
			switch ( $key ) {
				case 'wordcamp':
					$safe_value = absint( $unsafe_value );
					break;

				case 'description':
				case 'notes':
					$safe_value = wp_kses( $unsafe_value, wp_kses_allowed_html( 'strip' ) );
					break;

				case 'payment_amount':
				case 'currency':
				case 'payment_method':
				case 'vendor_name':
				case 'vendor_phone_number':
				case 'vendor_email_address':
				case 'vendor_street_address':
				case 'vendor_city':
				case 'vendor_state':
				case 'vendor_zip_code':
				case 'vendor_country':
					$safe_value = sanitize_text_field( $unsafe_value );
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

	public function get_columns( $_columns ) {
		$columns = array(
			'cb'             => $_columns['cb'],
			'wordcamp'       => __( 'WordCamp', 'wordcamporg' ),
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

	public function render_column( $column, $post_id ) {
		switch ( $column ) {
			case 'wordcamp':
				if ( $wordcamp_id = get_post_meta( $post_id, '_camppayments_wordcamp', true ) ) {
					$wordcamp = get_post( $wordcamp_id );
					echo esc_html( $wordcamp->post_title );
				}
				break;

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

			default:
				echo esc_html( get_post_meta( $post_id, '_camppayments_' . $column, true ) );
				break;
		}

	}
}