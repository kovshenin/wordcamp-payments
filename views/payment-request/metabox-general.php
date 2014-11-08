<table class="form-table">
	<?php
	$this->render_text_input( $post, 'Request ID', 'request_id', '', '', true );
	$this->render_text_input( $post, 'Requester', 'requester', '', '', true );
	$this->render_textarea_input( $post, 'Description', 'description' );
	$this->render_text_input( $post, 'Requested date for payment/due by', 'due_by', '', 'date' );
	$this->render_text_input( $post, 'Amount', 'payment_amount' );
	$this->render_select_input( $post, 'Currency', 'currency' );
	$this->render_textarea_input( $post, 'Notes', 'general_notes', 'Any other details you want to share.' );
	?>

	<tr>
		<th>Category</th>
		<td>
			<?php
			wp_dropdown_categories( array(
				'show_option_none' => '-- Select a Category --',
				'option_none_value' => 'null',

				'orderby'    => 'title',
				'hide_empty' => false,
				'selected'   => $assigned_category,
				'name'       => 'payment_category',
				'taxonomy'   => 'payment-category',
			) );    // todo make 'other' show last
			?>
		</td>
	</tr>

	<?php $this->render_text_input( $post, 'Other Category', 'other_category_explanation', __( 'Please add details if you selected "Other" in the Category dropdown.', 'wordcamporg' ) ); // todo only show this if 'other' is selected ?>
</table>
