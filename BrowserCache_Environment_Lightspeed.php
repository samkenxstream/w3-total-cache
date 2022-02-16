<?php
namespace W3TC;

/**
 * Rules generation for Nginx
 */
class BrowserCache_Environment_Lightspeed {
	private $c;



	public function __construct( $config ) {
		$this->c = $config;
	}



	/**
	 * Returns cache rules
	 */
	public function rules_cache_add( $exs ) {
		// etag
		$config_file = new _LightspeedConfig();
		$config_file->load( '/usr/local/lsws/conf/httpd_config.conf' );

		$config_file->set( 'tuning.fileETag',
			$this->c->get_boolean( 'browsercache.html.etag' ) ? 28 : 0 );

		$config_file->save();


		// expires
		$config_file = new _LightspeedConfig();
		$config_file->load( '/usr/local/lsws/conf/vhosts/wp-sandbox.conf' );

		$expires_enabled = $this->c->get_boolean( 'browsercache.html.expires' ) ||
			$this->c->get_boolean( 'browsercache.cssjs.expires' ) ||
			$this->c->get_boolean( 'browsercache.other.expires' );

		$config_file->set( 'context /.enableExpires', $expires_enabled ? 1 : 0 );

		$config_file->save();

		enableExpires           1
		expiresByType           text/css=A1000

	}
}



class _LightspeedConfig {
	private $lines = [];
	private $filename;



	public function load( $filename ) {
		$this->lines = [];
		$this->filename = $filename;
		$content = file_get_contents( $filename );
		$source_lines = explode( "\n", $content );

		$current_section = [];
		foreach ( $source_lines as $line ) {
			$line_trimmed = trim( $line );
			if ( substr( $line_trimmed, -1, 1 ) == '{' ) {
				// section start
				$current_section[] = trim( substr( $line_trimmed, 0, strlen( $line_trimmed ) - 1 ) );

				$this->lines[] = [
					'type' => 'start',
					'id' => implode( '.', $current_section ),
					'content' => $line
				];
			} elseif ( $line_trimmed == '}' ) {
				// section end
				$this->lines[] = [
					'type' => 'end',
					'id' => implode( '.', $current_section ),
					'content' => $line
				];
				array_pop( $current_section );
			} elseif ( substr( $line_trimmed, 0, 16 ) == '# W3TC replaced ' ) {
				// config value
				$value_section = $current_section;
				if ( preg_match( '~^# W3TC replaced\s+([^\s]+)~', $line_trimmed, $m ) ) {
					$value_section[] = $m[1];
				}

				$this->lines[] = [
					'type' => 'w3tc_comment',
					'id' => implode( '.', $value_section ),
					'content' => $line
				];
			} elseif ( substr( $line_trimmed, 0, 1 ) == '#' ) {
				$this->lines[] = [
					'type' => 'comment',
					'id' => implode( '.', $current_section ),
					'content' => $line
				];

			} else {
				// config value
				$value_section = $current_section;
				if ( preg_match( '~^[^\s]+~', $line_trimmed, $m ) ) {
					$value_section[] = $m[0];
				}
				$prefix = '';
				if ( preg_match( '~^\s*[^\s]+\s+~', $line, $m ) ) {
					$prefix = $m[0];
				}

				$this->lines[] = [
					'type' => 'value',
					'id' => implode( '.', $value_section ),
					'content' => $line,
					'prefix' => $prefix
				];
			}
		}
	}



	public function set( $id, $value ) {
		$comment_exists = false;
		for ( $n = 0; $n < count( $this->lines ); $n++ ) {
			$line = $this->lines[$n];

			if ( $line['id'] == $id ) {
				if ( $line['type'] == 'w3tc_comment' ) {
					$comment_exists = true;
				}
				if ( $line['type'] == 'value' ) {
					$new_content = $line['prefix'] . $value;

					$this->lines[$n]['content'] =
						( $comment_exists ? '' : '# W3TC replaced ' . $line['content'] . "\n") .
						$new_content;
					break;
				}
			}
		}
	}



	public function save() {
		$lines_content = array_map(
			function( $i ) { return $i['content']; },
			$this->lines );

		file_put_contents( $this->filename, implode( "\n", $lines_content ) );
	}
}
