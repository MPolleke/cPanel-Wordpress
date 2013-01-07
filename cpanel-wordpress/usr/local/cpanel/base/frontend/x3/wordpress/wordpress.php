<?php
/*
 * Wordpress auto installer
 * Version 1.1
 * Documentation: http://wptest1.weblicity.nl/serverbeheer/cpanel/wordpress-installer
*/

function wp_form() {
	global $user_domain, $user_cpuser;
	?>
	<div><p>Je bent bezig Wordpress te installeren/updaten.</p></div>
	<p><form name="installformulier" action="wordpress.php" method="post">
		<fieldset>
			<div>
				<label for="domain">Domeinnaam: <?php echo $user_domain;?></label>
				<input name="domain" id="domain" size="20" type="hidden" value="<?php echo $user_domain;?>" />
			</div>
			<div>
				<label for="path">Installatiepad: /home/<?php echo $user_cpuser;?>/public_html/</label>
				<input name="path" id="path" size="40" type="hidden" value="/home/<?php echo $user_cpuser;?>/public_html/" />
			</div>
			<div>
				<label for="user">Gebruikersnaam: <?php echo $user_cpuser;?></label>
				<input name="user" id="user" size="20" type="hidden" value="<?php echo $user_cpuser;?>" />
			</div>
			<div>
				<input type="submit" value="installeren" />
			</div>
		</fieldset>
	</form></p>
	<?php
}

function wp_install() {
	global $wp_domain, $wp_user, $wp_path;

	// eerst webpage downloaden
	$output = file_get_contents('http://nl.wordpress.org/');
	$temp = explode('.zip"',$output);

	// we zoeken de download url van de zipfile
	if(count($temp)>1){
		$temp2 = explode('href="',$temp[0]);
		if(count($temp2)>1){
			$url = array_pop($temp2);
			$url .= ".zip";
			// ook nog even de echte filename uitlezen
			$filename = explode("/", $url);
			$filename = array_pop($filename);
		} else {
			die('<p>Er ging iets mis met het samenstellen van het adres. Is het adres van de zipfile misschien veranderd?</p>');
		}
	} else {
		die('<p>Er ging iets mis met downloaden van de index. Ligt nl.wordpress.org soms plat?</p>');
	}
	echo "<p>Bezig met installeren van " . $filename . "</p>";

	// we downloaden de zip van wordpress.org
	$data = file_get_contents($url);
	// en uploaden hem naar de server
	$upload = file_put_contents("/home/" . $wp_user . "/tmp/" . $filename, $data);
	// check upload status
	if ($upload) {
		echo "<p>Zipfile is ge-upload naar $wp_domain</p>\n";
	} else {
		die('<p>Upload mislukt</p>\n');
	}

	// we pakken de zipfile uit
	$output = shell_exec("cd /home/$wp_user/tmp && unzip $filename" );

	// we verplaatsen de inhoud met cp (mv heeft geen -r optie)
	$output = shell_exec("cp -rf /home/$wp_user/tmp/wordpress/* $wp_path" );

	// opruimen van zipfile en uitgepakte zipfile
	unlink("/home/" . $wp_user . "/tmp/" . $filename);
	$output = shell_exec("rm -rf /home/$wp_user/tmp/wordpress" );

	// echo succes
	echo "<p>Het installeren van Wordpress is gelukt.</p>";
}

/////////////////
// Main script //
/////////////////
?>

<cpanel setvar="dprefix=../">
<cpanel Branding="include(stdheader.html)">

<?php
// cpanel data ophalen
$user_domain = '<cpanel print="$CPDATA{'DNS'}">';
$user_cpuser = '<cpanel print="$CPDATA{'USER'}">';

// we lezen de post query uit
if($_POST['domain']){
     $wp_domain = $_POST['domain'];
}
if($_POST['path']){
     $wp_path = $_POST['path'];
}
if($_POST['user']){
     $wp_user = $_POST['user'];
}

if (isset($wp_domain) && isset($wp_path) && isset($wp_user)) {
	wp_install();
} else {
	wp_form();
}

?>