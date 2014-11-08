<tr id="row-<?php echo esc_attr( str_replace( '_', '-', $name ) ); ?>">
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
			<label for="<?php echo esc_attr( $name ); ?>">
				<span class="description"><?php echo esc_html( $description ); ?></span>
			</label>
		<?php endif; ?>
	</td>
</tr>
