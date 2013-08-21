<?php
/*
 * Wordpress auto installer
 * Version 2.0
 * Documentation: http://timelord.nl/wordpress/cpanel-plugin-installer-for-wordpress?lang=en
*/


function wp_form() {
	/*
	 * Show the form on the first load of the page, or in case of error.
	 * Only the install path can be changed here.
	 * 
	 */
	
	global $user_domain, $user_cpuser, $error;
	
	if (isset($error)) { ?>
		<div class="error">
			<p>Error: <?php echo $error; ?></p>
		</div>
		<?php
	}
	?>

	<div style="border:1px solid #2266AA;">
		<div style="width: 98%; margin: auto;">
			<p>You are about to install or update Wordpress.</p>
			<p>Domain: <?php echo $user_domain;?></p>
			<p>User: <?php echo $user_cpuser;?></p>
		</div>
	</div>
	
	<p><form name="installform" action="wordpress.php" method="post">
		<fieldset>
			<div>
				<p><label for="path">Installpath: /home/<?php echo $user_cpuser; ?>/public_html/</label></p>
				<p>Add a subdirectory if so desired.</p>
				<p><input name="path" id="path" size="40" type="text" value="" /></p>
			</div>
			<div>
				<input type="submit" value="Install" />
			</div>
		</fieldset>
	</form></p>
	<?php
}


function wp_install() {
	/*
	 * This function handles the install of WordPress.
	 * It uses the information from cPanel, and the install path of the POST
	 * It downloads the zipfile from a localised version of WordPress.
	 * It then installs it in the specified path.
	 * 
	 */
	
	global $wp_domain, $wp_user, $wp_path, $loc;

	// first test if we are sane
	if ( !file_exists($wp_path) ) {
		die('<p class="error">The directory ' . $wp_path . ' does not exist. Please make it first.</p>');
	}
	if ( !is_writable($wp_path) ) {
		die('<p class="error">The directory ' . $wp_path . ' is not writable. Please make it writable.</p>');
	}

	// download the webpage
	if ($loc != 'http://wordpress.org/') {
    	$output = file_get_contents($loc);
    	$temp = explode('.zip"',$output);
    	
    	// we try to find the download-url of the zipfile
    	if(count($temp)>1){
    		$temp2 = explode('href="',$temp[0]);
    		if(count($temp2)>1){
    			$url = array_pop($temp2);
    			$url .= ".zip";
    			// now check for the real filename
    			$filename = explode("/", $url);
    			$filename = array_pop($filename);
    		} else {
    			die('<p class="error">Something went wrong with collecting the address. Could it be that the location of the zipfile has changed?</p>');
    		}
    	} else {
    		die('<p class="error">Something went wrong with downloading the webpage. Could it be that the website ' . $loc . ' is down?</p>');
    	}
    } else {
        // English version
        $url = 'http://wordpress.org/latest.zip';
        $filename = 'latest.zip';
    }
    
	echo "<p>Busy installing " . $filename . "</p>";
	// Download the zipfile from the location
	$data = file_get_contents($url);
	// upload it to the server
	$upload = file_put_contents("/home/" . $wp_user . "/tmp/" . $filename, $data);
	// check upload status
	if ($upload) {
		echo "<p>Zipfile is uploaded to $wp_domain</p>\n";
	} else {
		die('<p class="error">Upload of the zipfile to /home/' . $wp_user . '/tmp/ failed</p>\n');
	}

	// unpack the zipfile
	$output = shell_exec("cd /home/$wp_user/tmp && unzip $filename" );

	// copy the contents with cp (mv has no -r option)
	$output = shell_exec("cp -rf /home/$wp_user/tmp/wordpress/* $wp_path" );

	// cleanup zipfile and contents
	unlink("/home/" . $wp_user . "/tmp/" . $filename);
	$output = shell_exec("rm -rf /home/$wp_user/tmp/wordpress" );

	// echo succes
	echo "<p>Installation of WordPress has succeeded.</p>";
}


/////////////////
// Main script //
/////////////////

/* Locations */
$loc_de = 'http://de.wordpress.org/';
$loc_en = 'http://wordpress.org/';
$loc_nl = 'http://nl.wordpress.org/';
$loc = $loc_en; // default location
?>


<cpanel setvar="dprefix=../">
<cpanel Branding="include(stdheader.html)">

<style type='text/css'>
	.error {
		border: red 1px solid;
		margin-bottom: 10px;
		padding: 1%;
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
	
	if ( isset($_POST) ) {
		if (isset($_POST['path'])){
			preg_match('/\.\./', $_POST['path'], $matches);
			if ($matches) {
				$error = "Please don't install in a directory above public_html.";				
			} else {
				$wp_path = htmlspecialchars( trim( $_POST['path'] ) );
			}

			$wp_domain = $user_domain;
			$wp_path = '/home/' . $user_cpuser . '/public_html/' . $wp_path;
			$wp_user = $user_cpuser;
		}
	}
	
	if ( isset($wp_domain) && isset($wp_path) && isset($wp_user) && !isset($error) ) {
		wp_install();
	} else {
		wp_form();
	}
?>

</div>
<cpanel Branding="include(stdfooter.html)">


