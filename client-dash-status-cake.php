<?php
/*
Plugin Name: Client Dash Status Cake Add-on
Description: Integrates Status Cake with Client Dash
Version: 0.2
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
*/

class CDStatusCake {

	// Define the plugin name
	private $plugin = 'Client Dash Status Cake Add-on';
	// Setup your prefix
	private $pre = 'cdsc';
	// Set this to be your tab name
	private $block_name = 'Uptime';
	// Set the tab slug
	private $tab = 'uptime';
	// Set this to the page you want your tab to appear on (account, help and reports exist in Client Dash)
	private $page = 'reports';

	private $username = '_username';
	private $api = '_api';
	private $test = '_test';

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_action( 'plugins_loaded', array( $this, 'content_block' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'cd_settings_general_tab', array( $this, 'settings_display' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles'), 11 );
	}

	public function content_block() {
		cd_content_block( $this->block_name, $this->page, $this->tab, array( $this, 'block_contents' ) );
	}

	public function register_styles() {
		wp_register_style( $this->pre, plugin_dir_url(__FILE__).'style.css' );
		$page = get_current_screen();
		$tab = $_GET['tab'];

		if ( $page->id != 'dashboard_page_cd_'.$this->page && $tab != $this->tab )
			return;

		wp_enqueue_style( $this->pre );
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
		register_setting( 'cd_options_general', $this->pre.$this->test, 'esc_html' );
	}

	// Add settings to General tab
	public function settings_display() {
		$username = $this->pre.$this->username;
		$api = $this->pre.$this->api;
		$test = $this->pre.$this->test;
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
			<tr valign="top">
				<th scope="row">
					<label for="<?php echo $test; ?>">Test ID</label>
				</th>
				<td><input type="text" 
					id="<?php echo $test; ?>" 
					name="<?php echo $test; ?>" 
					value="<?php echo get_option( $test ); ?>" />
				</td>
			</tr>
		</tbody>
	</table>
	<?php }

	// Insert the Status Cake report data
	public function block_contents() {
		$un = get_option( $this->pre.$this->username );
		$api = get_option( $this->pre.$this->api );
		$test = get_option( $this->pre.$this->test );
		$content = wp_remote_retrieve_body( wp_remote_get( 'https://statuscake.com/API/Tests/Details/?TestID='. $test .'&API='. $api .'&Username='. $un ) );

		$content = json_decode( $content );
		// Uncomment the next line to see the raw data
		//print_r($content);
		if ( empty( $un) ) {
			echo '<h2>Please enter a valid Status Cake username in <a href="'.cd_get_settings_url().'">Settings</a></h2>';
		} elseif ( empty( $api) ) {
			echo '<h2>Please enter a valid Status Cake API key in <a href="'.cd_get_settings_url().'">Settings</a></h2>';
		} elseif ( empty( $test ) ) {
			echo '<h2>Please enter a valid Status Cake test ID in <a href="'.cd_get_settings_url().'">Settings</a></h2>';
		} elseif ( is_wp_error( $content ) OR empty( $content ) OR !empty($content->Error) ) {
		echo '<h2>Please enter valid Status Cake test values in <a href="'.cd_get_settings_url().'">Settings</a></h2>';
		} else {

			$status = $content->Status;
			$uptime = $content->Uptime;
		?>
		<div class="cdsc">
			<h1><?php echo $content->WebsiteName; ?></h1>
			<div class="cd-col-two cdsc-status">
				<h2>Status</h2>
				<div class="
				<?php
					if ( $status == 'Up' ) {
						echo 'cdsc-ninety';
					} else {
						echo 'cdsc-fifty';
					} ?>
					">
					<span class="dashicons 
					<?php
					if ( $status == 'Up' ) {
						echo 'dashicons-smiley';
					} ?>
					">
						<?php echo $status; ?>
					</span>
				</div>
			</div>
			<div class="cd-col-two cdsc-uptime">
				<h2>Uptime</h2>
				<div class="
				<?php
					if ( $uptime >= 90 ) {
						echo 'cdsc-ninety';
					} elseif ( $uptime >= 80 && $uptime <= 90 ) {
						echo 'cdsc-eighty';
					} elseif ( $uptime >= 70 && $uptime <= 80 ) {
						echo 'cdsc-seventy';
					} elseif ( $uptime >= 60 && $uptime <= 70 ) {
						echo 'cdsc-sixty';
					} elseif ( $uptime <= 60 ) {
						echo 'cdsc-fifty';
					}
					?>
				">
					<span class="dashicons 
					<?php
					if ( $uptime >= 90 ) {
						echo 'dashicons-smiley';
					} ?>
					">
						<?php echo $uptime; ?>%
					</span>
				</div>
			</div>
			<div class="cd-col-two">
				<h3>Test info</h3>
				<ul>
					<li><strong>Test ID:</strong> <?php echo $content->TestID; ?></li>
					<li><strong>Contact ID:</strong> <?php echo $content->ContactID; ?></li>
					<li><strong>Contact group:</strong> <?php echo $content->ContactGroup; ?></li>
					<li><strong>Test type:</strong> <?php echo $content->TestType; ?></li>
					<li><strong>Website host:</strong> <?php echo $content->WebsiteHost; ?></li>
				</ul>
			</div>
			<div class="cd-col-two">
				<h3>Stats</h3>
				<ul>
					<li><strong>Down times:</strong> <?php echo $content->DownTimes; ?></li>
					<li><strong>Check rate:</strong> <?php echo $content->CheckRate; ?></li>
					<li><strong>Timeout:</strong> <?php echo $content->Timeout; ?></li>
					<li><strong>Last tested:</strong> <?php echo $content->LastTested; ?></li>
					<li><strong>Next test location:</strong> <?php echo $content->NextLocation; ?></li>
				</ul>
			</div>
		</div>
		
		<?php
		}
	}
}

// Instantiate the class
$cdsc = new CDStatusCake;