<?php
/*
Plugin Name: NBA Standings
Plugin URI: http://nothing.golddave.com/plugins/nba-standings/
Description: Displays the standings for a given conference of the NBA as either a sidebar widget or template tag.
Version: 1.0
Author: David Goldstein
Author URI: http://nothing.golddave.com
*/

/*
Change Log

1.0
  * First public release. Based on codebase for MLB Standings 2.0.3.
*/

function ShowNBAStandings() {
	$options = get_option('NBAStandings_options');
	if (!download_nba()) {
		echo "failed to copy $sourcefile...\n";
	}
	$xml = simplexml_load_string($options['xml']);
	$type = $xml->xpath("//standing/standing-metadata/standing-metadata/sports-content-codes/sports-content-code/@code-type");
	$key = $xml->xpath("//standing/standing-metadata/standing-metadata/sports-content-codes/sports-content-code/@code-key");
	for ($i = 0; $i < 4; $i++) {
		if (($type[$i]=="league") && ($key[$i]==str_replace("-",".",$options['division']))){
			$x = $i;
			$division = $xml->xpath("/sports-content/standing");
			?>
			<link rel="stylesheet" href="<?php bloginfo('wpurl') ?>/wp-content/plugins/nba-standings/nbastandings.css" type="text/css" media="screen" />
			<div id="nba_standings_body">
			<?php
			echo "<table><tr><th align='left'>Team</th><th align='right'>W</th><th align='right'>L</th><th align='right'>Pct.</th><th align='right'>GB</th></tr>";
			for ($j = 0; $j < count($division[$x]->team); $j++) {
				if ($division[$x]->team[$j]->{'team-metadata'}->name->attributes()->last == $options['team']) {
					//echo $division[$x]->team[$j]->{'team-metadata'}->name->attributes()->last."<br>";
				  //echo $options['team']."<br>";
					echo "<tr class='nbateam'><td align='left'>".$division[$x]->team[$j]->{'team-metadata'}->name->attributes()->last."</td><td align='right'>".$division[$x]->team[$j]->{'team-stats'}->{'outcome-totals'}->attributes()->wins."</td><td align='right'>".$division[$x]->team[$j]->{'team-stats'}->{'outcome-totals'}->attributes()->losses."</td><td align='right'>".$division[$x]->team[$j]->{'team-stats'}->{'outcome-totals'}->attributes()->{'winning-percentage'}."</td>";
				} else {
					echo "<tr><td align='left'>".$division[$x]->team[$j]->{'team-metadata'}->name->attributes()->last."</td><td align='right'>".$division[$x]->team[$j]->{'team-stats'}->{'outcome-totals'}->attributes()->wins."</td><td align='right'>".$division[$x]->team[$j]->{'team-stats'}->{'outcome-totals'}->attributes()->losses."</td><td align='right'>".$division[$x]->team[$j]->{'team-stats'}->{'outcome-totals'}->attributes()->{'winning-percentage'}."</td>";
				}
				if ($j=='0'){
					echo "<td align='center'> - </td>";
				} else {
					echo "<td align='right'>".$division[$x]->team[$j]->{'team-stats'}->attributes()->{'games-back'}."</td>";
				}
			}
			echo "</tr></table>";
			$timestamp = $xml->{'sports-metadata'}->attributes()->{'date-time'};
			putenv("TZ=US/Pacific");
			$time=date("g:i A T", mktime(substr($timestamp,11,2),substr($timestamp,14,2),substr($timestamp,17,2)));			
			//echo "<p class='nbadate'>Last updated: ".substr($timestamp,5,2)."/".substr($timestamp,8,2)."/".substr($timestamp,0,4)." - ".$time."</p></div>";
			echo "<p class='nbadate'>Last updated: ".substr($timestamp,5,2)."/".substr($timestamp,8,2)."/".substr($timestamp,0,4)."</p></div>";
		}
	}
}

register_activation_hook(__FILE__, 'NBAStandings_add_defaults');
add_action('admin_init', 'NBAStandings_init' );
add_action('admin_menu', 'NBAStandings_add_options_page');
add_filter('plugin_action_links', 'NBAStandings_plugin_action_links', 10, 2);

function NBAStandings_add_defaults() {
	$tmp = get_option('NBAStandings_options');
    if(!is_array($tmp)) {
		//delete_option('NBAStandings_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(	"division" => "Eastern",
						"team" => "Knicks",
						"xml" => "" );
		update_option('NBAStandings_options', $arr);
	}
}

function NBAStandings_init(){
	register_setting( 'NBAStandings_plugin_options', 'NBAStandings_options' );
}

function NBAStandings_add_options_page() {
	add_options_page('NBA Standings Options Page', 'NBA Standings', 'manage_options', __FILE__, 'NBAStandings_render_form');
	
}

function NBAStandings_render_form() {
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>NBA Standings Options</h2>
		<form method="post" action="options.php">
			<?php settings_fields('NBAStandings_plugin_options'); ?>
			<?php if (!download_nba()) {echo "failed to copy $sourcefile...\n"; }; ?>
			<?php $options = get_option('NBAStandings_options'); ?>
			<table class="form-table">
				<tr>
					<th scope="row">Conference</th>
					<td>
						<select name='NBAStandings_options[division]' id='mydiv'>
							<option value='NBA.EAST' <?php selected('NBA.EAST', $options['division']); ?>>Eastern</option>
							<option value='NBA.WEST' <?php selected('NBA.WEST', $options['division']); ?>>Western</option>
						</select>
						<span style="color:#666666;margin-left:2px;">Select the conference you'd like to display on your blog.</span>
					</td>
				</tr>
				<tr>
					<th scope="row">Team</th>
					<td>
						<select name='NBAStandings_options[team]' id="myteam">
						</select>
						<span style="color:#666666;margin-left:2px;">Select the team you'd like bolded in the standings.</span>
					</td>
				</tr>
			</table>
			<input type="hidden" name='NBAStandings_options[xml]' value=<?php substr($options['xml'],0,strlen($options['xml'])); ?>>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>
	
	<script type='text/javascript'>
		function teamchanger() {
			jQuery("#myteam").empty()
			switch(jQuery("#mydiv").val()) {
				case "NBA.EAST":
					jQuery("#myteam").append("<option value='Hawks' <?php selected('Hawks', $options['team']); ?>>Atlanta Hawks</option><option value='Celtics' <?php selected('Celtics', $options['team']); ?>>Boston Celtics</option><option value='Nets' <?php selected('Nets', $options['team']); ?>>Brooklyn Nets</option><option value='Bobcats' <?php selected('Bobcats', $options['team']); ?>>Charlotte Bobcats</option><option value='Bulls' <?php selected('Bulls', $options['team']); ?>>Chicago Bulls</option><option value='Cavaliers' <?php selected('Cavaliers', $options['team']); ?>>Cleveland Cavaliers</option><option value='Pistons' <?php selected('Pistons', $options['team']); ?>>Detroit Pistons</option><option value='Pacers' <?php selected('Pacers', $options['team']); ?>>Indiana Pacers</option><option value='Heat' <?php selected('Heat', $options['team']); ?>>Miami Heat</option><option value='Bucks' <?php selected('Bucks', $options['team']); ?>>Milwaukee Bucks</option><option value='Knicks' <?php selected('Knicks', $options['team']); ?>>New York Knicks</option><option value='Magic' <?php selected('Magic', $options['team']); ?>>Orlando Magic</option><option value='76ers' <?php selected('76ers', $options['team']); ?>>Philadelphia 76ers</option><option value='Raptors' <?php selected('Raptors', $options['team']); ?>>Toronto Raptors</option><option value='Wizards' <?php selected('Wizards', $options['team']); ?>>Washington Wizards</option>");
					break;
				case "NBA.WEST":
					jQuery("#myteam").append("<option value='Mavericks' <?php selected('Mavericks', $options['team']); ?>>Dallas Mavericks</option><option value='Nuggets' <?php selected('Nuggets', $options['team']); ?>>Denver Nuggets</option><option value='Warriors' <?php selected('Warriors', $options['team']); ?>>Golden State Warriors</option><option value='Rockets' <?php selected('Rockets', $options['team']); ?>>Houston Rockets</option><option value='Clippers' <?php selected('Clippers', $options['team']); ?>>Los Angeles Clippers</option><option value='Lakers' <?php selected('Lakers', $options['team']); ?>>Los Angeles Lakers</option><option value='Grizzlies' <?php selected('Grizzlies', $options['team']); ?>>Memphis Grizzlies</option><option value='Timberwolves' <?php selected('Timberwolves', $options['team']); ?>>Minnesota Timberwolves</option><option value='Pelicans' <?php selected('Pelicans', $options['team']); ?>>New Orleans Pelicans</option><option value='Thunder' <?php selected('Thunder', $options['team']); ?>>Oklahoma City Thunder</option><option value='Suns' <?php selected('Suns', $options['team']); ?>>Phoenix Suns</option><option value='Trail Blazers' <?php selected('Trail Blazers', $options['team']); ?>>Portland Trail Blazers</option><option value='Kings' <?php selected('Kings', $options['team']); ?>>Sacramento Kings</option><option value='Spurs' <?php selected('Spurs', $options['team']); ?>>San Antonio Spurs</option><option value='Jazz' <?php selected('Jazz', $options['team']); ?>>Utah Jazz</option>");
					break;
			}
		}
		jQuery(document).ready(function() {
			teamchanger()
			jQuery('#mydiv').change(function(){
				teamchanger() 
			});
		});
	</script>
	<?php	
}

function NBAStandings_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$NBAStandings_links = '<a href="'.get_admin_url().'options-general.php?page=nba-standings/NBAStandings.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $NBAStandings_links );
	}
	return $links;
}

function download_nba() {
	$options = get_option('NBAStandings_options');
	$transient = get_transient("nbastandingsxml");
	if ((!$transient) || (!$options['xml']) || (strlen($options['xml'])<5000)) {
		if( !class_exists( 'WP_Http' ) ) include_once( ABSPATH . WPINC. '/class-http.php' );
		$url = "http://erikberg.com/nba/standings.xml";
		$filename = dirname(__FILE__)."/standings.xml";
		$request = new WP_Http;
		$args = array();
		$args['useragent'] = 'NBAStandings/1.0; (support@golddave.com)';
		$args['referer'] = get_bloginfo('url');
		$args['timeout'] =  300;
		$args['compress'] =  TRUE;
		$args['sslverify'] =  FALSE;
		//$args['headers'] =  "Authorization: Bearer 479f3565-fc75-42fb-bf05-63e74c83ba7e";
		$result = $request->request($url, $args);
		if ( $options['xml'] != $result[body] ) {
			$options['xml'] = $result[body];
			update_option('NBAStandings_options', $options);
		}
		set_transient("nbastandingsxml", $filename, 60*60);
	}
	$transient = $filename;
	return true;
}

class NBAStandings_Widget extends WP_Widget {

	public function __construct() {
		// widget actual processes
		parent::__construct(
	 		'NBAStandings_widget', // Base ID
			'NBA Standings', // Name
			array( 'description' => __( 'A widget to display the standings for a division of NBA.', 'text_domain' ), ) // Args
		);
	}

 	public function form( $instance ) {
		// outputs the options form on admin
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'New title', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	public function widget( $args, $instance ) {
		// outputs the content of the widget
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		echo $before_widget;
		if ( ! empty( $title ) ) echo $before_title . $title . $after_title;
		ShowNBAStandings();
		echo $after_widget;
	}

}

add_action( 'widgets_init', create_function( '', 'register_widget( "NBAStandings_Widget" );' ) );
?>