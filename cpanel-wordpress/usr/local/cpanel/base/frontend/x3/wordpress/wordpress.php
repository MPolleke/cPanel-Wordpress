<?php
/*
 * WordPress auto installer
 * Version 2.2
 * Documentation: http://timelord.nl/wordpress/cpanel-plugin-installer-for-wordpress?lang=en
*/


function wp_form($user_domain, $user_cpuser) {
	/*
	 * Show the form on the first load of the page, or in case of error.
	 * Only the install path can be changed here.
	 * 
	 * $user_domain string domainname set in the cPanel account
	 * $user_cpuser string username of the cPanel account
	 * 
	 */

	global $error;

	if (count($error)) { ?>
		<div>
			<p>Error(s): </p>
			<?php
			foreach ( $error as $err ) {
				echo $err;
			}
			?>
		</div>
		<?php
	}
	?>

	<div class="message">
		<p>You are about to install or update WordPress.</p>
		<p>Domain Name: <?php echo $user_domain;?></p>
		<p>cPanel User: <?php echo $user_cpuser;?></p>
	</div>
	
	<form name="installform" action="wordpress.php" method="post" style="margin-top: 10px;">
		<fieldset>
			<div>
				<p><label for="path">Install Path: /home/<?php echo $user_cpuser; ?>/public_html/</label></p>
				<p>Add a subdirectory if so desired.</p>
				<p><input name="path" id="path" size="40" type="text" value="" /></p>
			
				<p>Select WordPress Language: 
					<select name="wp_lang">
						<option value="en" selected="selected">English</option>
						<option value="nl">Dutch</option>
						<option value="de">German</option>
						<option value="th">Thai</option>
					</select> 
				</p>
			</div>
			
			<div>
				<input type="submit" name="submit" value="Install" />
			</div>
		</fieldset>
	</form>
	<?php
}


function set_url($wp_lang) {
	/*
	 * This function looks at the wp_lang option that was set in the form.
	 * It decides then which url to download the zipfile from.
	 * Then it returns that url. 
	 * 
	 * $wp_lang string option that holds the country code.
	 */

	global $error;
	 
	/* Locations */
	$lang_page = Array();
	$lang_page['de'] = 'http://de.wordpress.org/';
	$lang_page['en'] = 'http://wordpress.org/';
	$lang_page['nl'] = 'http://nl.wordpress.org/';
	$lang_page['th'] = 'http://th.wordpress.org/';

	if ( $wp_lang == 'en' || $wp_lang == '' ) {
	    // English version
        $zip_url = 'http://wordpress.org/latest.zip';
		return $zip_url;
    } else {
		// download the webpage
    	$output = file_get_contents($lang_page[$wp_lang]); /* get the webpage */
    	$temp = explode('.zip"',$output);
    	
    	// we try to find the download-url of the zipfile
    	if(count($temp)>1){
    		$temp2 = explode('href="',$temp[0]);
    		if(count($temp2)>1){
    			$zip_url = array_pop($temp2);
    			$zip_url .= ".zip";
    		} else {
    			$error[] = '<p class="error">Something went wrong with collecting the address. Could it be that the location of the zipfile has changed?</p>';
				return false;
    		}
    	} else {
    		$error[] = '<p class="error">Something went wrong with downloading the webpage. Could it be that the website ' . $lang_page[$wp_lang] . ' is down?</p>';
			return false;
    	}
		return $zip_url;
    }
}


function wp_install($wp_domain, $wp_user, $wp_path, $zip_url) {
	/*
	 * This function handles the install of WordPress.
	 * It uses the information from cPanel, and the install path of the POST
	 * It downloads the zipfile from a localised version of WordPress.
	 * It then installs it in the specified path.
	 * 
	 * $wp_domain string domainname set in the cPanel account
	 * $wp_user string username of the cPanel account
	 * $wp_path string full path where to install WordPress
	 * $zip_url string url to download the WordPress zipfile from
	 * 
	 */
	
	global $error;
	
	// first test if we are sane
	if ( !file_exists($wp_path) ) {
		$error[] = '<p class="error">The directory ' . $wp_path . ' does not exist. Please make it first.</p>';
		return;
	}
	if ( !is_writable($wp_path) ) {
		$error[] = '<p class="error">The directory ' . $wp_path . ' is not writable. Please make it writable.</p>';
		return;
	}

    // now check for the real filename
    $filename = explode("/", $zip_url);
    $filename = array_pop($filename);
	
	echo "<p class='message'>1. Busy downloading " . $zip_url . "</p>";
	// Download the zipfile from the location
	$zip_file = file_get_contents($zip_url);
	// upload it to the server
	$upload = file_put_contents("/home/" . $wp_user . "/tmp/" . $filename, $zip_file);
	// check upload status
	if ($upload) {
		echo "<p class='message'>2. Zipfile is uploaded to $wp_domain</p>";
	} else {
		$error[] = '<p class="error">Upload of the zipfile to /home/' . $wp_user . '/tmp/ failed</p>';
		return;
	}

	// unpack the zipfile
	$output = shell_exec("cd /home/$wp_user/tmp && unzip $filename" );

	// copy the contents with cp (mv has no -r option)
	$output = shell_exec("cp -rf /home/$wp_user/tmp/wordpress/* $wp_path" );

	// cleanup zipfile and contents
	unlink("/home/" . $wp_user . "/tmp/" . $filename);
	$output = shell_exec("rm -rf /home/$wp_user/tmp/wordpress" );

	// echo success
	echo "<p class='message'>3. Installation of WordPress has succeeded.</p>";
}


/////////////////
// Main script //
/////////////////
?>


<cpanel setvar="dprefix=../">
<cpanel Branding="include(stdheader.html)">

<style type='text/css'>
	.error {
		border: red 1px solid;
		margin-bottom: 10px;
		padding: 1%;
		width: 98.5%;
	}
	.message {
		 border: 1px solid #2266AA;
		 margin: 0;
		 padding: 2%;
		 width: 96.5%;
	}
</style>

<div class="body-content">
	<p>
		This is the installer for WordPress.<br />
		You can install or update WordPress on this account, in the specified path (public_html is default).
	</p>
	<p>
		Below it will show you the credentials used for the install. You can decide to install in another directory if you so desire.<br />
		If you hit "Install" it will install WordPress in about 10 seconds.<br />
		Plugins are not being updated, you will have to do that manually via ftp/sftp or in the backend of WordPress.		
	</p>

	
	<?php
	// gather cPanel data for the form, or indirectly for install
	$user_domain = '<cpanel print="$CPDATA{'DNS'}">';
	$user_cpuser = '<cpanel print="$CPDATA{'USER'}">';
	$error = Array();
	
	if ( isset($_POST) ) {
		if (isset($_POST['path'])){
			preg_match('/\.\./', $_POST['path'], $matches);
			if ($matches) {
				$error[] = '<p class="error">Please do not install in a directory above public_html.</p>';				
			} else {
				$wp_path = htmlspecialchars( trim( $_POST['path'] ) );
			}

			$wp_domain = $user_domain;
			$wp_path = '/home/' . $user_cpuser . '/public_html/' . $wp_path;
			$wp_user = $user_cpuser;
			$wp_lang = $_POST['wp_lang'];
		}
	}
	
	if ( isset($wp_domain) && isset($wp_path) && isset($wp_user) && !count($error) ) {
		echo "<div>";
		$zip_url = set_url($wp_lang);
		if ($zip_url) {
			wp_install($wp_domain, $wp_user, $wp_path, $zip_url);
		}
		echo "</div>";
		if ( count($error) ) {
			/* Show the form again with errors */
			wp_form($user_domain, $user_cpuser);
		}
	} else {
		wp_form($user_domain, $user_cpuser);
	}
?>

</div>
<cpanel Branding="include(stdfooter.html)">


