<?php
namespace W3TC;

if ( ! defined( 'W3TC' ) ) {
	die();
}

Util_Ui::config_item(
	array(
		'key'          => 'cdn.s3_compatible.api_host',
		'label'        => __( 'API host:', 'w3-total-cache' ),
		'control'      => 'textbox',
		'textbox_size' => 30,
		'description'  => __( 'Host of API endpoint, comptabile with Amazon S3 API', 'w3-total-cache' ),
	)
);
Util_Ui::config_item(
	array(
		'key'          => 'cdn.s3.key',
		'label'        => __( 'Access key ID:', 'w3-total-cache' ),
		'control'      => 'textbox',
		'textbox_size' => 30,
		'description'  => __( 'Theme files, media library attachments, <acronym title="Cascading Style Sheet">CSS</acronym>, <acronym title="JavaScript">JS</acronym> files etc will appear to load instantly for site visitors.', 'w3-total-cache' ),
	)
);

?>
<tr>
	<th><label for="cdn_s3_secret"><?php esc_html_e( 'Secret key:', 'w3-total-cache' ); ?></label></th>
	<td>
		<input id="cdn_s3_secret" class="w3tc-ignore-change"
			<?php Util_Ui::sealing_disabled( 'cdn.' ); ?> type="password" name="cdn__s3__secret" value="<?php echo esc_attr( $this->_config->get_string( 'cdn.s3.secret' ) ); ?>" size="60" />
	</td>
</tr>
<tr>
	<th><label for="cdn_s3_bucket"><?php esc_html_e( 'Bucket:', 'w3-total-cache' ); ?></label></th>
	<td>
		<input id="cdn_s3_bucket" type="text" name="cdn__s3__bucket"
			<?php Util_Ui::sealing_disabled( 'cdn.' ); ?> value="<?php echo esc_attr( $this->_config->get_string( 'cdn.s3.bucket' ) ); ?>" size="30" />
	</td>
</tr>
<tr>
	<th>
		<label for="cdn_s3_ssl">
			<?php
			echo wp_kses(
				sprintf(
					// translators: 1 opening HTML acronym tag, 2 closing HTML acronym tag.
					__(
						'%1$sSSL%2$s support:',
						'w3-total-cache'
					),
					'<acronym title="' . __( 'Secure Sockets Layer', 'w3-total-cache' ) . '">',
					'</acronym>'
				),
				array(
					'acronym' => array(
						'title' => array(),
					),
				)
			);
			?>
		</label>
	</th>
	<td>
		<select id="cdn_s3_ssl" name="cdn__s3__ssl" <?php Util_Ui::sealing_disabled( 'cdn.' ); ?>>
			<option value="auto"<?php selected( $this->_config->get_string( 'cdn.s3.ssl' ), 'auto' ); ?>><?php esc_html_e( 'Auto (determine connection type automatically)', 'w3-total-cache' ); ?></option>
			<option value="enabled"<?php selected( $this->_config->get_string( 'cdn.s3.ssl' ), 'enabled' ); ?>><?php esc_html_e( 'Enabled (always use SSL)', 'w3-total-cache' ); ?></option>
			<option value="disabled"<?php selected( $this->_config->get_string( 'cdn.s3.ssl' ), 'disabled' ); ?>><?php esc_html_e( 'Disabled (always use HTTP)', 'w3-total-cache' ); ?></option>
		</select>
		<p class="description">
			<?php
			echo wp_kses(
				sprintf(
					// translators: 1 opening HTML acronym tag, 2 closing HTML acronym tag,
					// translators: 3 opening HTML acronym tag, 4 closing HTML acronym tag.
					__(
						'Some %1$sCDN%2$s providers may or may not support %3$sSSL%4$s, contact your vendor for more information.',
						'w3-total-cache'
					),
					'<acronym title="' . __( 'Content Delivery Network', 'w3-total-cache' ) . '">',
					'</acronym>',
					'<acronym title="' . __( 'Secure Sockets Layer', 'w3-total-cache' ) . '">',
					'</acronym>'
				),
				array(
					'acronym' => array(
						'title' => array(),
					),
				)
			);
			?>
		</p>
	</td>
</tr>
<tr>
	<th><?php esc_html_e( 'Replace site\'s hostname with:', 'w3-total-cache' ); ?></th>
	<td>
		<?php
		$cnames = $this->_config->get_array( 'cdn.s3.cname' );
		require W3TC_INC_DIR . '/options/cdn/common/cnames.php';
		?>
		<p class="description">
			<?php
			echo wp_kses(
				sprintf(
					// translators: 1 opening HTML a tag to Amazon UserGuide for Virtual Hosting, 2 closing HTML a tag,
					// translators: 3 opening HTML acronym tag, 4 closing HTML acronym tag.
					__(
						'If you have already added a %1$sCNAME%2$s to your %3$sDNS%4$s Zone, enter it here.',
						'w3-total-cache'
					),
					'<a href="https://docs.aws.amazon.com/AmazonS3/latest/userguide/VirtualHosting.html#VirtualHostingCustomURLs" target="_blank">',
					'</a>',
					'<acronym title="' . __( 'Domain Name System', 'w3-total-cache' ) . '">',
					'</acronym>'
				),
				array(
					'a'       => array(
						'href'   => array(),
						'target' => array(),
					),
					'acronym' => array(
						'title' => array(),
					),
				)
			);
			?>
		</p>
	</td>
</tr>
<tr>
	<th colspan="2">
		<input id="cdn_test" class="button {type: 's3_compatible', nonce: '<?php echo esc_attr( wp_create_nonce( 'w3tc' ) ); ?>'}" type="button" value="<?php esc_attr_e( 'Test S3 upload', 'w3-total-cache' ); ?>" /> <span id="cdn_test_status" class="w3tc-status w3tc-process"></span>
	</th>
</tr>
