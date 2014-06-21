<?php
/*
Plugin Name: Client Dash Status Cake Add-on
Description: Integrates Status Cake with Client Dash
Version: 0.1
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
*/

class CDStatusCake {

	/*
	* These variables you can change
	*/
	// Define the plugin name
	private $plugin = 'Client Dash Status Cake Add-on';
	// Setup your prefix
	private $pre = 'cdsc';
	// Set this to be your tab name
	private $tabname = 'Uptime';
	// Set the tab slug
	private $tab = 'uptime';
	// Set this to the page you want your tab to appear on (account, help and reports exist in Client Dash)
	private $page = 'reports';
	// A text field option
	private $username = '_username';
	// A URL/text field option
	private $api = '_api';

	/*
	* This constructor function sets up what happens when the plugin
	* is activated. It is where you'll place all your actions, filters
	* and other setup components.
	*/
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_filter( 'cd_tabs', array( $this, 'add_tab' ) );
		add_action( 'cd_'. $this->page .'_'. $this->tab .'_tab', array( $this, 'tab_contents' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'cd_settings_general_tab', array( $this, 'settings_display' ), 11 );
	}

	// Notices for if CD is not active (no need to change)
	public function notices() {
		if ( !is_plugin_active( 'client-dash/client-dash.php' ) ) { ?>
		<div class="error">
			<p><?php echo $this->plugin; ?> requires <a href="http://w.org/plugins/client-dash">Client Dash</a>.
			Please install and activate <b>Client Dash</b> to continue using.</p>
		</div>
		<?php
		}
	}

	// Register settings
	public function register_settings() {
		register_setting( 'cd_options_general', $this->pre.$this->username, 'esc_html' );
		register_setting( 'cd_options_general', $this->pre.$this->api, 'esc_html' );
	}

	// Add settings to General tab
	public function settings_display() {
		$username = $this->pre.$this->username;
		$api = $this->pre.$this->api;
		?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><h3><?php echo $this->plugin; ?> settings</th>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="<?php echo $username; ?>">Username</label>
				</th>
				<td><input type="text" 
					id="<?php echo $username; ?>" 
					name="<?php echo $username; ?>" 
					value="<?php echo get_option( $username ); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="<?php echo $api; ?>">API key</label>
				</th>
				<td><input type="text" 
					id="<?php echo $api; ?>" 
					name="<?php echo $api; ?>" 
					value="<?php echo get_option( $api ); ?>" />
				</td>
			</tr>
		</tbody>
	</table>
	<?php }

	// Add the new tab (no need to change)
	public function add_tab( $tabs ) {
	$tabs[$this->page][$this->tabname] = $this->tab;
	return $tabs;
	}

	// Insert the tab contents
	public function tab_contents() {
		$un = get_option( $this->pre.$this->username );
		$api = get_option( $this->pre.$this->api );
		$content = wp_remote_get( 'https://statuscake.com/API/Tests/?API='. $api .'&Username='. $un );
		print_r($content);
	}
}
// Instantiate the class
$cdsc = new CDStatusCake;