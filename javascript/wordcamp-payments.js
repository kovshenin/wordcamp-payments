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
			$( '#wcp_files' ).find( 'a.wcp-insert-media' ).click( $.wordcampPayments.showUploadModal );
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
			$( payment_method_fields   ).addClass( 'hidden' );
			$( active_fields_container ).removeClass( 'hidden' );
			$( active_fields_container ).addClass( 'active' );

			// todo make the transition smoother
		},

		// Call this from the upload button to initiate the upload frame.
		showUploadModal : function( event ) {
			if ( 'undefined' == typeof $.wordcampPayments.fileUploadFrame ) {
				// Create the frame
				$.wordcampPayments.fileUploadFrame = wp.media( {
					title: wcpLocalizedStrings.uploadModalTitle,
					multiple: true,
					button: {
						text: wcpLocalizedStrings.uploadModalButton
					}
				} );

				// Add models to the collection for each selected attachment
				$.wordcampPayments.fileUploadFrame.on( 'select', function() {
					var attachments = $.wordcampPayments.fileUploadFrame.state().get( 'selection' ).toJSON();

					$.each( attachments, function( index, attachment ) {												// todo if selected an existing file, it isn't attached, so after post is saved it wont be in the list
						var newFile = new $.wordcampPayments.AttachedFile( {
							'ID':       attachment.id,
							'filename': attachment.filename,
							'url':      attachment.url
						} );

						$.wordcampPayments.attachedFilesView.collection.add( newFile );
					} );
				} );
			}

			$.wordcampPayments.fileUploadFrame.open();
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
					dateFormat: 'yy-mm-dd',
					changeMonth: true,
					changeYear:  true
				} );
			}
		}
	};

	$.wordcampPayments.init();
} );
