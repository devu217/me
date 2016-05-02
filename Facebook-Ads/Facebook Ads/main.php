<?php
/*
Plugin Name: Facebook Ads Plugin
Plugin URI: http://wordpress.org/plugins/none/
Description: This is my facebook ads plugin, 
Author: Me
Version: 9.6
Author URI: http://dewanshusharma.tt/
*/
//include( plugin_dir_path( __FILE__ ) . 'inc/facebook.php');
require_once __DIR__ . '/Facebook/autoload.php';
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
require_once __DIR__ . '/vendor/autoload.php';
use FacebookAds\Api;
use FacebookAds\Object\AdUser;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Fields\AdFields;
use FacebookAds\Object\AdCreative;
function main_function_to_display()
{ 
	global $wpdb;
	$table_name = $wpdb->prefix.'these_facebook_ads';
	$query="SELECT * FROM $table_name WHERE status = 1 ";
	$results=$wpdb->get_results( $query );
	foreach ($results as $result) 
	{
		echo "<a href='djfh'>".$result->iframe."</a>";
	}
	?>
	<script>
		jQuery(document).ready(function($) 
		{
			$("iframe").each(function() 
			{
				$(this).load(function() 
				{
					$('.unclickableMask').remove();
				});
			});
		});
	</script>
	<?php
}
add_shortcode( 'these_facebook_ads', 'main_function_to_display' );
function add_pages()
{
	add_menu_page("Main Page","Main Page settings","edit_theme_options","main-page-main-id","main_page_html");
	add_submenu_page($parent_slug="main-page-main-id", $page_title="Ads to display", $menu_title="Ads Settings",$capability= "edit_theme_options", $menu_slug="adds-options", $function ="adds_page_html" );
}
function main_page_html()
{
	global $wpdb;
	$table_name=$wpdb->prefix.'these_facebook_ads_api';
	$query="SELECT * FROM $table_name ";
	$results=$wpdb->get_results( $query );
	if (isset($_POST["submit"])) 
	{
		foreach ($results as $result) 
		{ 
			$result->id;
			$keys=$_POST[$result->name];
			$date=date("Y-m-d h:i:s a");
			$data = array( 'keys' => $keys, 'modified' => $date );
			$res = $wpdb->update( $table_name, $data ,array( 'id' => $result->id ) );
		} ?>
		<script>
			location.reload();
		</script>
		<?php
	}
	?>
	<form action="" method="post" accept-charset="utf-8"> 
		<table class="table table-hover">
			<tbody>
			<?php
			foreach ($results as $result) 
			{ 
				?>
				<tr><td><?php echo $result->name ?></td><td><input type="text" name="<?php echo $result->name ?>" value="<?php echo $result->keys ?>"></td></tr>
				<?php
			} 
			?>
			<tr><td></td><td><input type="submit" name="submit" value="submit"></td></tr>
			</tbody>
		</table>
	</form>
	<?php
}
function adds_page_html()
{
	// Init PHP Sessions
	global $wpdb;
	$table_name=$wpdb->prefix.'these_facebook_ads_api';
	$query="SELECT * FROM $table_name ";
	$results=$wpdb->get_results( $query );
	foreach ($results as $result) 
	{
	 	define($result->name, $result->keys);
	}
	session_start();
	$fb = new Facebook([
	  'app_id' => App_id,
	  'app_secret' => App_secret,
	]);
	$helper = $fb->getRedirectLoginHelper();
	if (!isset($_SESSION['facebook_access_token'])) 
	{
		$_SESSION['facebook_access_token'] = null;
	  	
	}
	if (!$_SESSION['facebook_access_token']) 
	{
	  $helper = $fb->getRedirectLoginHelper();
	  try 
	  {
	    $_SESSION['facebook_access_token'] = (string) $helper->getAccessToken();
	  } 
	  catch(FacebookResponseException $e) 
	  {
	    // When Graph returns an error
	    echo 'Graph returned an error: ' . $e->getMessage();
	    exit;
	  } 
	  catch(FacebookSDKException $e) 
	  {
	    // When validation fails or other local issues
	    echo 'Facebook SDK returned an error: ' . $e->getMessage();
	    exit;
	  }
	}

	if ($_SESSION['facebook_access_token']) 
	{ 
		$table_name = $wpdb->prefix.'these_facebook_ads';
		if (isset($_POST["submit"])) 
		{
			$c=$_POST["count"];
			for ($i=0; $i < $c; $i++) 
			{ 
				$id=$_POST["id_".$i];
				$name=$_POST["name_".$i];
				$iframe=stripslashes($_POST["iframe_".$i]);
				$status=$_POST["status_".$i];
				$date=date("Y-m-d h:i:s a");
				$data = array('id' => $id, 'name' => $name,'iframe' => $iframe,'status'  => $status, 'created' => $date, 'modified' => $date );
				$query="SELECT id FROM $table_name WHERE id = $id ";
				$results=$wpdb->get_row( $query );
				if ($results->id) 
				{
					$res = $wpdb->update( $table_name, $data , array( 'id' => $id ));
					echo "Updated info at ID: ".$id."<br>";
				}
				else
				{
					$res = $wpdb->insert( $table_name, $data); 
					echo "Inserted info at ID: ".$wpdb->insert_id."<br>";
				}
			}
		}
	 	// echo "You are logged in!";
		echo '<br/>Logout from <a href="#" id="logout">Facebook</a><br>';
	 	// Initialize a new Session and instanciate an Api object
		Api::init(App_id,App_secret,$_SESSION['facebook_access_token']);
		// The Api object is now available trough singleton
		$api = Api::instance();
		// Add to header of your file
		// Add after Api::init()
		$me = new AdUser('me');
		$my_adaccount = $me->getAdAccounts()->current();
		$data=$my_adaccount->getData();
		$account_id=$data["id"];
		$account = new AdAccount($account_id);
		$ads = $account->getAds(array(
		  AdFields::NAME,
		  AdFields::ID,
		));
		// Outputs names of Ads.
		?>
		<form action="" name="adsinfoform" method="post" accept-charset="utf-8">
			<table class="table table-hover">
				<tbody>
				<?php
				$c=0;
				foreach ($ads as $ad) 
				{ 
					$query="SELECT * FROM $table_name WHERE id = $ad->id ";
					$results=$wpdb->get_row( $query );
					?>
					<tr style="background-color: #ebc98e;">
						<td>ID</td>
						<td><input type="hidden" name="id_<?php echo $c; ?>" value="<?php echo $ad->id; ?>"><input type="text" disabled="true" value="<?php echo $ad->id; ?>"></td>
					</tr>
					<tr>
						<td>Name</td>
						<td><input type="hidden" name="name_<?php echo $c; ?>" value="<?php echo $ad->name; ?>"><input type="text" disabled="true" value="<?php echo $ad->name; ?>"></td>
					</tr>
					<tr>
						<td>Status</td>
						<?php 
						if ($results->id) 
						{
							?>
							<td>
								<input type="radio" name="status_<?php echo $c; ?>" value="1" <?php if ($results->status==1) { echo "checked"; } ?> >Enable
								<input type="radio" name="status_<?php echo $c; ?>" value="0" <?php if ($results->status==0) { echo "checked"; } ?> >Disable
							</td>
							<?php
						}
						else
						{
							?>
							<td style="background-color: #f6546a;">
								<input type="radio" name="status_<?php echo $c; ?>" value="1" checked>Enable
								<input type="radio" name="status_<?php echo $c; ?>" value="0">Disable
								<label style="align-self: left;"> New Ad</label>
							</td>
							<?php
						}
						?>
					</tr>
					<tr>
						<td>Iframe</td>
						<td><?php
							$creative = new AdCreative($ad->id);
							$Ads=$creative->getAdPreviews(array(),
								array('ad_format'=>'DESKTOP_FEED_STANDARD'));
							foreach ($Ads as $ad) 
							{
								?>
								<textarea style="display:none;" rows="8" cols="150" name="iframe_<?php echo $c; ?>"><?php echo $ad->body; ?></textarea>
								<?php 
									preg_match('/src="([^"]+)"/', $ad->body, $match);
									$url = $match[1];
								?>
								<a href="<?php echo $url; ?>" target="_blank">Iframe Preview Here</a>
								<?php
							}	?>
						</td>
					</tr>
					<?php
					$c++;
				}	
				?>
				<tr>
					<td><input type="hidden" name="count" value="<?php echo $c; ?>" placeholder=""></td>
					<td><input type="submit" name="submit" value="Submit"></td>
				</tr>
				</tbody>
			</table>
		</form>
		<?php
	} 
	else 
	{
	$_SESSION["HOMEURL"]='http://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$permissions = ['ads_management'];
	$loginUrl = $helper->getLoginUrl($_SESSION["HOMEURL"], $permissions);
	echo '<a href="' . $loginUrl . '">Log in with Facebook</a>';
	} 
}
add_action('admin_menu','add_pages');

function install_plugin_function()
{
	global $wpdb;
	// Create Table insert API Details
	$table_name=$wpdb->prefix.'these_facebook_ads_api';
	$sql="CREATE TABLE $table_name (
	`id` bigint(255) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `keys` text COLLATE utf8_unicode_ci NOT NULL,
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	$wpdb->query($sql);
	// Insert Dummy Keys
	$insert="INSERT INTO $table_name VALUES 
	('1', 'App_id','Dummy Keys',  '',''),
	('2', 'App_secret','Dummy Keys', '','')";
	$wpdb->query($insert);
	// Create Table insert Ads
	$table_name=$wpdb->prefix.'these_facebook_ads';
	$sql="CREATE TABLE $table_name (
	`id` bigint(255) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `iframe` text COLLATE utf8_unicode_ci NOT NULL,
	`status` int(11) NOT NULL ,
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	$wpdb->query($sql);
}
register_activation_hook( __FILE__,'install_plugin_function');

// Write our Logot JS below here
add_action( 'admin_footer','my_logout_function' ); 
function my_logout_function() 
{ ?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) 
	{
		$("#logout").click(function(event) 
		{
			var data = 
			{
				'action': 'my_logout_action'
			};
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) 
			{
				alert('Logout Successful');
			window.location= '<?php echo $_SESSION["HOMEURL"]; ?>';
			});
		});
	});
	</script> <?php
}
add_action( 'wp_ajax_my_logout_action', 'my_logout_action_callback' );
function my_logout_action_callback() 
{
	session_start();
	unset($_SESSION['userdata']);
	session_destroy();
}