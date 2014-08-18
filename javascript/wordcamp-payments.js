jQuery( document ).ready( function( $ ) {
	$.wordcampPayments = {

		/**
		 * Main entry point
		 */
		init: function () {
			$.wordcampPayments.registerEventHandlers();
			$.wordcampPayments.setupDatePicker();
		},

		/**
		 * Registers event handlers
		 */
		registerEventHandlers: function() {
			$( '#wcp_payment_details' ).find( 'input[name=payment_method]' ).change( $.wordcampPayments.togglePaymentMethodFields );
			$( '#wcp_files' ).find( 'a.add-media' ).click( $.wordcampPayments.showUploadModal );
		},

		/**
		 * Example event handler
		 *
		 * @param event
		 */
		togglePaymentMethodFields: function( event ) {
			event.preventDefault();
			var active_fields_container = '#' + $( this ).attr( 'id' ) + '_fields';
			var payment_method_fields   = '.payment_method_fields';

			$( payment_method_fields   ).removeClass( 'active' );
			$( payment_method_fields   ).addClass( 'inactive' );
			$( active_fields_container ).removeClass( 'inactive' );
			$( active_fields_container ).addClass( 'active' );

			// todo make the transition smoother
		},

		// Call this from the upload button to initiate the upload frame.
		showUploadModal : function( event ) {
			var frame = wp.media();

			// Handle results from media manager.
			frame.on( 'close', function() {
				// todo attach files to post. do that here, or on php side when post is saved?
			} );

			frame.open();
			return false;
		},

		/**
		* Fallback to the jQueryUI datepicker if the browser doesn't support <input type="date">
		*/
		setupDatePicker : function() {
			var browserTest = document.createElement( 'input' );
			browserTest.setAttribute( 'type', 'date' );

			if ( 'text' === browserTest.type ) {
				$( '#wcp_general_info' ).find( 'input[type=date]' ).datepicker( {
					dateFormat: 'yy-mm-dd'
				} );
			}
		}
	};

	$.wordcampPayments.init();
} );
