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
			$( 'input[name=payment_method]' ).change( $.wordcampPayments.togglePaymentMethodFields );
		},

		/**
		 * Fallback to the jQueryUI datepicker if the browser doesn't support <input type="date">
		 */
		setupDatePicker : function() {
			var browserTest = document.createElement( 'input' );
			browserTest.setAttribute( 'type', 'date' );

			if ( 'text' === browserTest.type ) {
				$( 'body.post-type-wcp_payment_request' ).find( '#post-body' ).find( 'input[type=date]' ).datepicker( {
						dateFormat: 'yy-mm-dd'
				} );
			}
		},

		/**
		 * Example event handler
		 *
		 * @param event
		 */
		togglePaymentMethodFields: function( event ) {
			event.preventDefault();
			var active_fields_container = '#' + $( this ).attr( 'id' ) + '_fields';

			$( '.payment_method_fields' ).removeClass( 'active' );
			$( '.payment_method_fields' ).addClass( 'inactive' );
			$( active_fields_container ).removeClass( 'inactive' );
			$( active_fields_container ).addClass( 'active' );

			// todo make the transition smoother
		}
	};

	$.wordcampPayments.init();

} );
