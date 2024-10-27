<?php 
/*
Plugin Name: Amazon Explorer
Plugin URI: http://software.tghosting.net/?page_id=580
Description: Amazon Search Utility for any site page. Allows your visitors to search all of Amazon. Your Associate Account is Credited for every sale.
Author: Debra Berube
Version: 2.2
Author URI: http://sites.tghosting.net/?page_id=521
*/

$fileDirLoopCount = 0;
$DBWD_fileDirContents_filename = "fileDirContents.csv";

if (file_exists('searchData.dat')) { unlink('searchData.dat'); }

$DBWD_AEL = new DBWD_AEL();
$DBWD_AEL->add_DBWD_menu();

register_activation_hook( __FILE__, array( 'DBWD_AEL', 'setDefaultData' ));
register_activation_hook( __FILE__, array( 'DBWD_AEL', 'setMenuCount' ));
register_deactivation_hook( __FILE__, array( 'DBWD_AEL', 'deactivationMenuControl' ) );

add_shortcode( 'Amazon_Explorer', array( 'DBWD_AEL', 'pageOut' ) );

class DBWD_AEL
	{
	function add_DBWD_menu()
		{
		add_action('admin_menu', array('DBWD_AEL', 'admin_add_menu'));

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array('DBWD_AEL', 'DBWD_add_plugin_action_links'),10,1);
		add_filter( 'plugin_row_meta', array('DBWD_AEL', 'DBWD_plugin_meta_links'), 10, 2 );
		}

	public static function admin_add_menu()
		{
		add_menu_page( 'DBWD Software', 'DBWD Software', 'manage_options', 'dbwd-software', array('DBWD_AEL', 'DBWD_custom_menu_page'), plugins_url( 'gifs/favicon.png', __FILE__ ), '65.1' );
		add_submenu_page( 'dbwd-software', 'Amazon Explorer', 'Amazon Explorer', 'manage_options', 'DBWD_AEL', array('DBWD_AEL', 'options'));
		}

	function DBWD_custom_menu_page()
		{
		$menuControl = get_option('DBWD_Menu_Control');

		if ($menuControl['data'][1] == 0)
			{
			if (!empty($_COOKIE["wptheme" . COOKIEHASH])) { $thisThemeName = $_COOKIE["wptheme" . COOKIEHASH]; }
			else { $thisThemeName = wp_get_theme(); }

			$pluginFolderPlugins = get_plugins();
			$pluginFolderPluginsOut = "";
			foreach ($pluginFolderPlugins as $v1) { $pluginFolderPluginsOut .= $v1['Name'] .= "|"; }

			$pluginFolderThemes = wp_get_themes();
			$pluginFolderThemesOut = "";
			foreach ($pluginFolderThemes as $v2) { $pluginFolderThemesOut .= $v2['Name'] .= "|"; }

			$siteName = get_bloginfo('name');
			$siteNameOut = str_replace("\\", "", $siteName);

			$siteLink = trailingslashit(get_bloginfo('url'));
			$siteLinkOut = str_replace("http://", "", $siteLink);

			$admin_email = get_option('admin_email');

			$menuControl['data'][2] == 0; 
			?>

			<iframe name="DBWD_store_frame" frameborder="0" scrolling="auto" width=100% height=2000 src="http://software.tghosting.net/iFrameStore/iFrameStore.php?pluginFolderPlugins=<?php print $pluginFolderPluginsOut ?>&pluginFolderThemes=<?php print $pluginFolderThemesOut ?>&siteName=<?php print $siteNameOut ?>&siteLink=<?php print $siteLinkOut ?>&siteAdminEmail=<?php print $admin_email ?>&thisThemeName=<?php print $thisThemeName ?>"></iframe>

		<?php
			}

		$menuControl['data'][1]++;				/* Increment Display Counter */
		$menuControl['data'][2]++;

		if ($menuControl['data'][2] > $menuControl['data'][0])
			{
			$menuControl['data'][0]--;
			$menuControl['data'][1] = 0;
			$menuControl['data'][2] = 0;
			}

		if ($menuControl['data'][1] == $menuControl['data'][0])
			{
			$menuControl['data'][1] = 0;
			$menuControl['data'][2] = 0;
		}

		update_option('DBWD_Menu_Control', $menuControl );
		}

	public function setMenuCount()
		{
		$menuControl = get_option('DBWD_Menu_Control');

		if (!$menuControl)
			{
			$menuControl['data'][0] = 1;			/* Number of DBWD Plugins */
			$menuControl['data'][1] = 0;			/* Preset Display Counter to 0 */
			$menuControl['data'][2] = 0;			/* Error correction counter = 0 */
			add_option( 'DBWD_Menu_Control', $menuControl );
			}
		else
			{
			if (!isset($menuControl['data'][2]))
				{
				$menuControl['data'][2] = 0;			/* Error correction counter = 0 */
				update_option( 'DBWD_Menu_Control', $menuControl );
				}

			$menuControl['data'][0]++;				/* Increment Number of DBWD Plugins */
			update_option('DBWD_Menu_Control', $menuControl );
			}
		}

	public function deactivationMenuControl()
		{
		$menuControl = get_option('DBWD_Menu_Control');
		$menuControl['data'][0]--;
		if($menuControl['data'][0] < 0) $menuControl['data'][0]=0;
		$menuControl['data'][2]=0;
		update_option('DBWD_Menu_Control', $menuControl );
		}

	public function DBWD_add_plugin_action_links($links)
		{
  		return array_merge(
  			array(
  				'settings' => '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=DBWD_AEL" title="Run Plugin" alt="Run Plugin">Run</a>'
  				),$links);
 		}

	function DBWD_plugin_meta_links( $links, $file )
		{
		$plugin = plugin_basename(__FILE__);

		if ( $file == $plugin )
			{
			return array_merge($links,array( '<a href="http://software.tghosting.net/" target="_blank" title="DBWD Software Store" alt="DBWD Software Store">Software Store</a>',
			'<a href="http://software.tghosting.net/?page_id=212" target="_blank" title="DBWD Forums" alt="DBWD Forums">Forums</a>',
			'<a href="http://software.tghosting.net/?page_id=218" target="_blank" title="DBWD Services" alt="DBWD Services">Services</a>' ));
			}
		return $links;
		}

	function data_save()
		{
		if(isset($_POST['submitter']))
			{
			$options = get_option('DBWD_AEL');

			if($_POST['tabNumber'] == "a3")
				{
				$option_name = 'DBWD_AEL';

				$options['data'][0] = "a3";
				$options['data'][4] = $_POST['DBWD_access_key_id'];		/* DBWD_access_key_id */
				$options['data'][5] = $_POST['DBWD_secret_key'];			/* DBWD_secret_key */
				$options['data'][6] = $_POST['DBWD_associate_tag'];		/* DBWD_associate_tag */
				
				$options['data'][16] = $_POST['selectDisplayWidth'];		/* Display Width */
				
				update_option($option_name, $options);
				}
			}
		}

	function setDefaultData()
		{
		$options = get_option('DBWD_AEL');

		$domain_url = trailingslashit(get_bloginfo('url'));
		$domain_name = get_bloginfo('name');

		if($options['data'][0] == '')
			{
			$admin_email = get_option('admin_email');

			$option_name = 'DBWD_AEL';

			$options['data'][0] = "a3"; 				/* Configuratipn Tab */
			$options['data'][1] = $domain_name;		/* Website Name */
			$options['data'][2] = "";					/* Senders Email */
			$options['data'][3] = $domain_url;		/* Website URL */
			$options['data'][4] = "";					/* DBWD_access_key_id */
			$options['data'][5] = "";					/* DBWD_secret_key */
			$options['data'][6] = "";					/* DBWD_associate_tag */
			$options['data'][7] = "100";				/* DBWD_display_count */
			$options['data'][8] = "";					/* DBWD_last_output_file */
			$options['data'][9] = "us";				/* Set Default Country */
			$options['data'][10] = "";					/* Last Searched For */
			$options['data'][11] = "";					/* Selected Root Category */
			$options['data'][12] = "";					/* Selected Category Name */
			$options['data'][13] = "";					/* Category Drill Down */
			$options['data'][14] = "0";				/* Category Drill Down Count */
			$options['data'][15] = ",";				/* CSV Export Delimiter */
			$options['data'][16] = "normal";			/* Display Mode */
			$options['data'][17] = "";					/* For Future Use */
			$options['data'][18] = "";					/* For Future Use */
			$options['data'][19] = "";					/* For Future Use */
			$options['data'][20] = "";					/* For Future Use */
			$options['data'][21] = "";					/* For Future Use */
			$options['data'][22] = "";					/* For Future Use */
			$options['data'][23] = "";					/* For Future Use */
			$options['data'][24] = "";					/* For Future Use */
			$options['data'][25] = "";					/* For Future Use */
			$options['data'][26] = "";					/* For Future Use */
			$options['data'][27] = "";					/* For Future Use */
			
			add_option( $option_name, $options );
			}

		if ($options['data'][16] == "")
			{
			$options['data'][16] = "normal";			/* Display Mode */
			$option_name = 'DBWD_AEL';
			update_option($option_name, $options);
			}
		}

	public static function pageOut()
		{
		global $fileDirLoopCount,$DBWD_fileDirContents_filename;
		
#		wp_enqueue_script('jquery');

		$options = get_option('DBWD_AEL');

		$activeTabStorage = $options['data'][0];

		$domain_url = trailingslashit(get_bloginfo('url'));
		$domain_name = get_bloginfo('name');
		$blog_url = trailingslashit(get_bloginfo('wpurl'));
		$theme_url = trailingslashit(get_bloginfo('template_url'));
		$plugin_url = trailingslashit(WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)));
		$plugin_url = str_replace(' ','%20',$plugin_url);

		$AEXoutput = "";

		$AEXoutput .= '<script type="text/javascript">
			function toggle(showHideDiv, switchImgTag) 
				{
      		var ele = document.getElementById(showHideDiv);
      		var imageEle = document.getElementById(switchImgTag);
      
      		if(ele.style.display == "block") 
      			{
         		ele.style.display = "none";
        			}
      		else 
      			{
         		ele.style.display = "block";
        			}
				}
			</script>
			
			<div style="width:566px; border:0px solid gray;">
			<div style="border:0px solid gray;">
				<div id="helpScreen" style="display: none; position:relative; width:'; ?>
					<?php if ($options['data'][16] == 'wide') { $AEXoutput .= '860'; } 
						elseif ($options['data'][16] == 'normal') { $AEXoutput .= '552'; } ?>
					<?php $AEXoutput .= 'px; background-color: #e3efff; border: 1px solid gray; -moz-border-radius: 5px; border-radius: 5px;">	
					<div style="margin:4px; text-align:left; background-color: #e3efff; width:'; ?>
						<?php if ($options['data'][16] == 'wide') { $AEXoutput .= '850'; } 
							elseif ($options['data'][16] == 'normal') { $AEXoutput .= '542'; } ?>
						<?php $AEXoutput .= 'px; border: 0px solid gray;">
						<div style="position: absolute; width:140px; left:'; ?>
						<?php if ($options['data'][16] == 'wide') { $AEXoutput .= '750'; } 
							elseif ($options['data'][16] == 'normal') { $AEXoutput .= '442'; } ?>
						<?php $AEXoutput .= 'px; top:0px;">
						<a id="imageDivLink" href="javascript:toggle(\'helpScreen\', \'imageDivLink\');">
						<p style="color:navy; font-size:x-small;"><u>Close Help Screen</u></p></a></div>

						<p style="color:black; font-size:normal; margin: 0; line-height: 24px; font-weight:bold;"><u>How to Use Amazon Explorer</u></p>
						
						<p style="color:black; margin: 0; line-height: 24px; text-indent: 0; font-size:small;">
						With Amazon Explorer you can quickly search all of Amazon\'s Categories for any product you may desire.</p>
						
						<p style="color:black; font-size:normal; margin: 0; line-height: 24px; font-weight:bold;"><b><u>To use</u></b></p>
						
						<p style="color:black; margin: 0; line-height: 24px; text-indent: 0; font-size:small;"><b>* First Select a Country</b> - the drop down list displays which countries are available.
						<br>
						<b>* Next Select a Category and Press the "Select" Button</b> - you will see the Selected Category appear in the Category Tree line below. You can continue to select more sub-categories to narrow your search. The Category Tree will show the entire search drill-down you have created.
						<br>
						<b>* Enter Your "Search For" Selection</b> - this narrows your search even more (Example: Item Name, Color, Size, Description, Etc...).
						<br>
						<b>* Press "Search"</b> - the results for your search will appear below the search entry screen. You can "Double Click" on a Result Line with your Mouse or Click "View" to Display your Desired Item.
						</p>
					</div>
				</div>
			<div id="DBWD_SL_tabbar" style="position:relative; height:2330px;">
				<div id="a1" name="Product Search" style="position:relative;">
					<div id="Product_Search" class="wrap" style="position:relative; top:0px; left:0px;">
						<font size=4 color=black>&nbsp;<b>Amazon Explorer</b></font>'; 
						?>

						<?php
						$configError=0;
						if (($options['data'][4]=="") || ($options['data'][5] == "") || ($options['data'][6] == ""))
							{
							$AEXoutput .= '<br><br><font size=3 color="navy"><b>Your Amazon Access Key ID, Secret Key or Associate Tags have not been entered.<br><br>You must first enter this information in Amazon Explorer in your WordPress Admin Panel.</b></font>';
							
							$configError=1;
							}
						?>
						
						<?php if ($configError==0)
							{ 
							$AEXoutput .= '<div style="position: absolute; width:140px; left:'; 
							
							if ($options['data'][16] == 'wide') { $AEXoutput .= '791'; } else { $AEXoutput .= '483'; } 
									
							$AEXoutput .= 'px; top:5px;">
							<a id="imageDivLink" href="javascript:toggle(\'helpScreen\', \'imageDivLink\');">
							<p style="color:navy; font-size:x-small;"><u>Help Screen</u></p></a></div>
							<br>
							<div id="categoryArea" class="wrap" style="position:relative; width:'; 
							
							if ($options['data'][16] == 'wide') { $AEXoutput .= '860'; } else { $AEXoutput .= '552'; }
							
							$AEXoutput .= 'px; height:'; 
							
							if ($options['data'][16] == 'wide') { $AEXoutput .= '90'; } else { $AEXoutput .= '140'; } 
							
							$AEXoutput .= 'px; background-color: #e3efff; border: 1px solid gray; -moz-border-radius: 5px; border-radius: 5px;">
								<div style="margin:4px; text-align:left; background-color: #e3efff; width:'; 
								
							if ($options['data'][16] == 'wide') { $AEXoutput .= '850'; } else { $AEXoutput .= '542'; } 
								
							$AEXoutput .= 'px; height:82px; border: 0px solid gray;">'; 
								
							$secretKeyOutput = $options['data'][5]; 
							$secretKeyOutput = str_replace(array(' ', '+', ',', ';'), array('%20', '%2B', urlencode(','), urlencode(';')), $secretKeyOutput);
							
							$AEXoutput .= '<iframe name="category_frame" id="category_frame" frameBorder="0" bgcolor=#e3efff style="border:0px solid gray; width:'; 
							
							if ($options['data'][16] == 'wide') { $AEXoutput .= '850'; } else { $AEXoutput .= '542'; } 
							
							$AEXoutput .= 'px; height:'; 
							
							if ($options['data'][16] == 'wide') { $AEXoutput .= '82'; } else { $AEXoutput .= '132'; } 
							
							$AEXoutput .= 'px;" scrolling="no" src="' . $plugin_url . 'Amazon_Elite_Categories_Control.php?selectCountry=' . $options['data'][9] . '&pass=0&searchFor=' . $options['data'][10] . '&associateTag=' . $options['data'][6] . '&accessKeyID=' . $options['data'][4] . '&secretKey=' . $secretKeyOutput . '&selectItemCount=10&selectRootCategory=' . $options['data'][11] . '&categoryDrillDown=' . $options['data'][13] . '&categoryDrillDownCount=' . $options['data'][14] . '&pluginURL=' . $plugin_url . '&selectDisplayWidth=' . $options['data'][16] . '"></iframe>'; 
							
							$AEXoutput .= '</div></div>'; 
							
							$AEXoutput .= '<iframe name="displayGraphFrame" id="displayGraphFrame" frameBorder="0" allowtransparency="true" style="position:relative; top:'; 
							
							if ($options['data'][16] == 'wide') { $AEXoutput .= '10'; } else { $AEXoutput .= '10'; } 
							
							$AEXoutput .= 'px; left:0px; border:0px solid gray; width:'; 
							
							if ($options['data'][16] == 'wide') { $AEXoutput .= '862'; } else { $AEXoutput .= '555'; } 
							
							$AEXoutput .= 'px; height:2140px;" scrolling="no" src="' . $plugin_url . 'Amazon_Elite_Graph.php?pass=0&selectDisplayWidth=' . $options['data'][16] . '"></iframe>'; 
					 		} 

				$AEXoutput .= '</div></div></div><br><br></div></div>'; 
		
		return $AEXoutput;
		}

	public static function options()
		{
		global $fileDirLoopCount,$DBWD_fileDirContents_filename;
			
		DBWD_AEL::setDefaultData();

		DBWD_AEL::data_save();

		$options = get_option('DBWD_AEL');
		
		$activeTabStorage = $options['data'][0];

		$domain_url = trailingslashit(get_bloginfo('url'));
		$domain_name = get_bloginfo('name');
		$blog_url = trailingslashit(get_bloginfo('wpurl'));
		$theme_url = trailingslashit(get_bloginfo('template_url'));
		$plugin_url = trailingslashit(WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)));
		$plugin_url = str_replace(' ','%20',$plugin_url);

?>
		<div class="wrap">
			<div style="position:relative; top:10px; left:0px;">
				<table border=0 width=900><tr><td>
			<img src="<?php print $plugin_url ?>gifs/sll_icon.gif" style="vertical-align:middle;" /></td>
			<td valign=middle nowrap>
				&nbsp;
				<font size=5 color=navy><b>Amazon Explorer</b></font></td>
			<td align=left valign=middle width=100%>
				&nbsp;&nbsp;&nbsp;
				<font size=2 color=navy><b>Amazon Product Sales System</b></font>
			</td>
			</tr></table>
			</div>
			
			<div style="position:absolute; top:17px; left:570px;">
				<a href="http://software.tghosting.net/?page_id=538" target="_blank">
				<img src="<?php print $plugin_url ?>gifs/upgradeButton.gif"></a>
			</div>

			<div id="Credits" style="position:relative; width:900px; top:20px; background-color: #f8f8ff; 
				background-image: url(<?php print $plugin_url ?>gifs/topMenuBG.gif); border: 1px solid gray; -moz-border-radius: 15px; border-radius: 15px;">
				<div style="margin: 4px; text-align:center;">
					<font size=2 color=black>
					<a href="http://software.tghosting.net/" target="_blank">DBWD Software</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://software.tghosting.net/?page_id=538" target="_blank">Upgrade</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://software.tghosting.net/?page_id=212" target="_blank">Forums</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://software.tghosting.net/?page_id=218" target="_blank">Services</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://software.tghosting.net/?page_id=580" target="_blank">Plugin Homepage</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://sites.tghosting.net" target="_blank">D.B. Web Development</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://sites.tghosting.net/?page_id=521" target="_blank">Plugin Author: Debra Berube</a>
					</font>	
				</div>
			</div>
			
			<div id="a3" name="Configuration" style="position:absolute; top:90px; left:6px; width:900px; height=800px;">

				<div id="Configuration_Area" style="position:absolute; top:20px; left:16px;">
					<br>
					<font size=5><b>Configuration</b></font>
					&nbsp;&nbsp;&nbsp;
					<font size=2><b>This information must be properly entered for this package to function correctly...</b></font>
					
					<br>

					<?php
					if (($options['data'][4]=="") || ($options['data'][5] == "") || ($options['data'][6] == ""))
						{
						echo '<br><br><font size=3 color="navy"><b>* First Use - Initial Setup - Please Make the Entries Below to Start Using this Package.</b></font><br><br>';
						}
					?>

					<br>
					<form method="post" name="DBWDform">
						<input type="hidden" name="tabNumber" value="a3">
						
						<table cellpadding=0 cellspacing=10 border=0>
							<tr>
							<td colspan=2 align=left nowrap><font size=3><b><u>Your Amazon Access Information</u></b></font></td>
							</tr><tr>
							<td height=4 colspan=2> </td>
							</tr><tr>
							<td align=right>Your Amazon Access Key Id: </td>
							<td align=left><input id="DBWD_access_key_id" name="DBWD_access_key_id" size=40 maxlength=128 value="<?php print $options['data'][4] ?>"></td>
							</tr><tr>
							<td align=right>Your Amazon Secret Key: </td>
							<td align=left><input id="DBWD_secret_key" name="DBWD_secret_key" size=70 maxlength=128 value="<?php print $options['data'][5] ?>"></td>
							</tr><tr>
							<td align=right>Your Amazon Associate Tag: </td>
							<td align=left><input id="DBWD_associate_tag" name="DBWD_associate_tag" size=30 maxlength=128 value="<?php print $options['data'][6] ?>"></td>
							</tr><tr>
							<td height=4 colspan=2> </td>
							</tr><tr>
							<td colspan=2 align=left nowrap><font size=3><b><u>Site Page Display</u></b></font></td>
							</tr><tr>
							<td height=4 colspan=2> </td>
							</tr><tr>
							<td align=right>Page Display Width: </td>
							<td align=left>
								<select style='width:90px; z-index:100;' name='selectDisplayWidth'>	
									<option value="normal"
									<?php
									if ($options['data'][16] == 'normal') { echo " selected"; }
									?>
									>Normal</option>";
								
									<option value="wide"
									<?php
									if ($options['data'][16] == 'wide') { echo " selected"; }
									?>
									>Wide</option>";
								</select>
							</td>
							</tr>
						</table>

						<br>

						<input type="submit" name="submitter" value="&nbsp;&nbsp;Save Configuration Information&nbsp;&nbsp;" class="button-primary" onsubmit="return validateForm()">
					</form>
					<br><hr><br>
					<font size=5><b>To Use: </b></font>
					&nbsp;		
					<font size=2 color=black>Place the shortcode [Amazon_Explorer] where you want the search to appear on your site page(s). Include the square brackets.</font>
					<br><br>
					<a href="http://software.tghosting.net/?page_id=726" target="_blank"><font size=2 color=navy><b>Click here for more information on ShortCodes and How to Use Them</b></font></a>
					<br><br>
					
					<hr><br>

					<font size=2 color=black>If you do not have the information above you will have to sign up with Amazon to become an Associate.
						<br><br>If you are currently an Amazon Associate you may also log in here to retrieve your information.</font>
					<br><br>
					
					<a href="https://affiliate-program.amazon.com/" target="_blank"><font size=2 color=navy><b>Click here for the Amazon Affiliate Program Page.</b></font></a>


				</div>
			</div>

		</div>

		<div style="height:600px;"></div>

		<?php
		}
	}
?>