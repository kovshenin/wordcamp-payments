<tr id="row-<?php echo esc_attr( str_replace( '_', '-', $name ) ); ?>">
	<th>
		<?php echo esc_html( $label ); ?>:
	</th>

	<td>
		<?php foreach ( $options as $option ) : ?>
			<?php $option_name = $name . '_' . sanitize_title_with_dashes( str_replace( ' ', '_', $option ) ); ?>

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
