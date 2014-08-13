jQuery( document ).ready( function( $ ) {
	$.wordcampPayments = {

		/**
		 * Main entry point
		 */
		init: function () {
			$.wordcampPayments.registerEventHandlers();
		},

		/**
		 * Registers event handlers
		 */
		registerEventHandlers: function () {
			$( 'input[name=payment_method]' ).change( $.wordcampPayments.togglePaymentMethodFields );
		},

		/**
		 * Example event handler
		 *
		 * @param event
		 */
		togglePaymentMethodFields: function ( event ) {
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
