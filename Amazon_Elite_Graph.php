<?php
require ('Amazon_Elite_API_Functions.php');

$searchDataContent = "";

$delimiter = ",";

$DBWD_search_data_filename = "searchData.dat";

$pass = $_GET['pass'];

$selectCountry = "";
$selectRootCategory = "";
$categoryDrillDown = "";
$categoryDrillDownCount = "";
$searchFor = "";
$selectItemCount = "";
$browse_node_id = "";
$categoryID = "";
$pluginURL = "";    
$categoryListArray = array();
$currentTimeStamp = gmdate('Y-m-d\TH:i:s\Z');
$accessKeyID = "";
$secretKey = "";
$associateTag = "";
$selectDisplayWidth = "";

$DBWD_last_CSV_filename='Amazon_Elite_' . $searchFor . '_' . gmdate('Y-m-d\_H:i:s') . '.csv';
$DBWD_resultFile = "searchResult.csv";
$DBWD_resultFileDown = "searchResultDownload.csv";

$passCategory="Books";

$searchBNID=$browse_node_id;

$resultArray = array();

function readSearchDataFromFile()
	{
	global $searchDataContent,$DBWD_search_data_filename,$accessKeyID,$secretKey,$associateTag;
	global $selectCountry,$selectRootCategory,$categoryDrillDown,$categoryDrillDownCount,$currentTimeStamp;
	global $searchFor,$selectItemCount,$browse_node_id,$categoryID,$pluginURL,$categoryListArray,$selectDisplayWidth;
	
	$DBWD_search_data_filename_out = $DBWD_search_data_filename;

	$handle = @fopen($DBWD_search_data_filename_out, 'r');
	if (!$handle) 
		{
		$categoryDrillDown="";
		return;
		}

	$searchDataContent = @fread($handle, filesize($DBWD_search_data_filename_out));

	fclose($handle);

	$fileDataWorkArray = array();
	$fileDataWorkArray = explode('|', $searchDataContent);

	$selectCountry = $fileDataWorkArray[0];
	$selectRootCategory = $fileDataWorkArray[1];
	$categoryDrillDown = $fileDataWorkArray[2];
	$categoryDrillDownCount = $fileDataWorkArray[3];
	$searchFor = $fileDataWorkArray[4];
	$selectItemCount = $fileDataWorkArray[5];
	$categoryID = $fileDataWorkArray[6];
	$browse_node_id = $fileDataWorkArray[7];
	$pluginURL = $fileDataWorkArray[8];
	$selectDisplayWidth = $fileDataWorkArray[9];
	$accessKeyID = $fileDataWorkArray[10];
	$secretKey = $fileDataWorkArray[11];
	$associateTag = $fileDataWorkArray[12];
	
	$preInfoOffset = 13;
	for ($a=0; $a<($categoryDrillDownCount-1); $a++)
		{
		$categoryListArray[$a] = $fileDataWorkArray[$a+$preInfoOffset];
		}
	}

	function getProductInfo($passCategory,$searchFor,$searchBNID,$DBWD_last_CSV_filename)
		{
		global $searchDataContent,$DBWD_search_data_filename,$accessKeyID,$secretKey,$associateTag,$pluginURL,$resultArray;
		global $selectCountry,$selectRootCategory,$categoryDrillDown,$categoryDrillDownCount,$currentTimeStamp;
		global $selectItemCount,$browse_node_id,$categoryID,$pluginURL,$categoryListArray,$DBWD_resultFile,$DBWD_resultFileDown,$delimiter;

		$loopDBWDlimit=$selectItemCount/10;
		$ItemLoopCount=0;
		$SalesRankFailCount=0;
		$MerchantFailCount=0;

		$ASIN_Out="";
		$ItemTitle="";
		$ItemPrice="";
		$SalesRankOut="";
		$MerchantOut="";

		if ($searchFor == "") $searchFor="All";

		$searchWords = $searchFor;
		$searchWords=str_replace(" ",",",$searchWords);

		$searchCategory=$passCategory;
		$search_index = "Books";
		
		$response_group = "OfferFull,ItemAttributes,SalesRank";

		$DBWD_last_CSV_filename_out = $DBWD_resultFile;
		$DBWD_last_CSV_filename_download_out = $DBWD_resultFileDown;

		$item_page = $loopDBWDlimit;

#		$handle = fopen($DBWD_last_CSV_filename_out, 'w');

		for ($loopDBWD=1; $loopDBWD<=$loopDBWDlimit; $loopDBWD++)
			{
			$itemResponse = item_search($searchWords, $search_index, $browse_node_id, $loopDBWD, $accessKeyID, $secretKey, $associateTag, $response_group, $currentTimeStamp, $selectCountry);

			$returnCount = count($itemResponse);

			if (!$returnCount) 
				{
#				fclose($handle); 
				return;
				}

			if (isset($itemResponse['ItemSearchResponse']['Items']['Item'])) 
				{
				foreach($itemResponse['ItemSearchResponse']['Items']['Item'] as $item_key => $item)
					{
					if (is_string($item_key)) 
						{ 
#						fclose($handle); 
						return; 
						}

					if (!isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['SalesRank']))
						{ $SalesRankOut="{ NA }"; $SalesRankFailCount++; }
					else
						{
						$SalesRankOut=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['SalesRank'];
						$SalesRankOut=str_replace(","," ",$SalesRankOut);
						}
         	
					if (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['TotalOffers']))
						{
						if ($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['TotalOffers'] == 1)
							{
							if (!isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['Merchant']['Name']))
								{ $MerchantOut="{ NA }"; $MerchantFailCount++; }
							else
								{
								$MerchantOut=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['Merchant']['Name'];
								$MerchantOut=str_replace(","," ",$MerchantOut);
								}
							}
						else
							{
							if (!isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['0']['Merchant']['Name']))
								{ $MerchantOut="{ NA }"; $MerchantFailCount++; }
							else
								{
								$MerchantOut=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['0']['Merchant']['Name'];
								$MerchantOut=str_replace(","," ",$MerchantOut);
								}
							}
						}
					else
						{ $MerchantOut="{ NA }"; $MerchantFailCount++; }
         	
					$ASIN_Out=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ASIN'];
					$ASIN_Out=str_replace(","," ",$ASIN_Out);
         	
					if (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['Title'],$itemResponse))
						{
						$ItemTitle=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['Title'];
						$ItemTitle=str_replace(","," ",$ItemTitle);
						}

					if (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['OfferListing']['SalePrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['OfferListing']['SalePrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
 					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['OfferSummary']['LowestNewPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['OfferSummary']['LowestNewPrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Transaction']['TransactionItems']['TransactionItem']['TotalPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
 					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['EligibilityRequirements']['EligibilityRequirement']['CurrencyAmount'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['Offers']['Offer']['OfferListing']['Price']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['OfferSummary']['LowestCollectiblePrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['OfferSummary']['LowestNewPrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					elseif (isset($itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['ItemAttributes']['ListPrice']['FormattedPrice'],$itemResponse))
						{
						$ItemPrice=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['OfferSummary']['LowestUsedPrice']['FormattedPrice'];
						$ItemPrice=str_replace(","," ",$ItemPrice);
						}
					else 
						{
						$ItemPrice=" { NA }";
						}
         	
					$ItemLinkPage=$itemResponse['ItemSearchResponse']['Items']['Item'][$item_key]['DetailPageURL'];
         	
					if ($ItemPrice!="Too low to display")
						{
						$ItemPrice=substr($ItemPrice, 1, strlen($ItemPrice)-1);
						$ItemPriceOut=str_replace(" ","",$ItemPrice);
						}

        			if (($ItemPriceOut=="{NA}")||($ItemPriceOut=="")) { $ItemPriceOut="{ NA }"; }

					$ItemLoopCount++;

					# Output result file
					         	
#					fwrite($handle, $ItemLoopCount . $delimiter);
#					fwrite($handle, $ItemTitle . $delimiter);
#					fwrite($handle, $MerchantOut . $delimiter);
#					fwrite($handle, $ItemPriceOut . $delimiter);
#					fwrite($handle, "View Product^" . $ItemLinkPage . "^_blank\n");
         	
					$arrayPush=array();
					$arrayPush[0]=$ItemLoopCount;
					$arrayPush[1]=$ItemTitle;
					$arrayPush[2]=$MerchantOut;
					$arrayPush[3]=$ItemPriceOut;
					$arrayPush[4]=$ItemLinkPage;
					
					array_push($resultArray,$arrayPush);
					}
				}
			}

#		fclose($handle);
		}
				
?>


<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=9">

<!-- 		<script type="text/javascript" src="jquery-1.4.2.min.js"></script> -->
		
		<script type="text/javascript">
			window.onload=function()
				{
				var ele = document.getElementById("update");
				ele.style.display = "none";

				var ele = document.getElementById("update1");
				ele.style.display = "block";
				}

			function openNewTab(url)
				{
  				var win=window.open(url, '_blank');
  				win.focus();
				}
		</script>

		<style type="text/css">
			.productsTable tr:nth-child(even) { /*(even) or (2n 0)*/
				background-color: #e3efff;
				}
			.productsTable tr:nth-child(odd) { /*(odd) or (2n 1)*/
				background-color: #ffffff;
				}
			.productsTable tr:hover {
  				background-color: #fde262;
				}
		</style>

	</head>

	<body style="margin:0;padding:0;background-color:transparent;">
 
 		<?php 
		 	readSearchDataFromFile();
			if ($categoryDrillDown == "")
				{
				echo("<center><font size=2 color=navy>Select a Category (and any desired Sub Categories), Enter a 'Search For' Description (or All),");
				if ($selectDisplayWidth == "normal") { echo("<br> "); } else { echo(" "); }
				echo("Select how many Items to Display and Press Search</font>");
				echo("</center></body></html>");
				exit;
				}
		?>

		<div id="gridText1" style="position:relative; width:<?php if ($selectDisplayWidth == "normal") { echo("552"); } else { echo("860"); } ?>px; height:<?php if ($selectDisplayWidth == "normal") { echo("52"); } else { echo("30"); } ?>px; top:0px; border: 1px solid #808080; background-color: #e3efff; -moz-border-radius: 5px 5px 0px 0px; border-radius: 5px 5px 0px 0px;" align=left>
			<div id="searchResultCount0" name="searchResultCount0" style="position:absolute; top:4px; left:8px;">
				<font size=2 color="#000000"><b>Result Count:</b></font>
			</div>

			<div id="update" name="update" style="position:absolute; top:4px; right:0px;">
				<img src="<?php echo $pluginURL ?>gifs/loading_bar.gif" height=24>
			</div>

			<div id="update1" name="update1" style="position:absolute; top:4px; right:10px; display:none;">
				<font size=2 color=black><b>Search Complete</b></font>
			</div>
<!--
			<div id="searchResultCount1" name="searchResultCount1" style="position:absolute; top:34px; left:53px;">
					<font size=1 color="#000080" style="vertical-align:text-bottom;">Clicking the 'Column Headers' will Sort - change most 'Column Widths' by moving Column Dividers - { NA } is Data 'Not Available' from Amazon - Click 'View' for Item Detail Page</font>
			</div>
-->
			<div id="searchResultCount1" name="searchResultCount1" style="position:absolute; top:<?php if ($selectDisplayWidth == "normal") { echo("24px; left:38"); } else { echo("1px; left:192"); } ?>px;">
					<font size=1 color="#000080" style="vertical-align:text-bottom;">{ NA } is Data 'Not Available' from Amazon - Double Click Highlighted Line or Click 'View' for Item Detail Page</font>
			</div>

			<?php
				getProductInfo($passCategory,$searchFor,$searchBNID,$DBWD_last_CSV_filename);
#				$linecount = count(file($DBWD_resultFile));
				$linecount = count($resultArray);
				$gridheight=($linecount*20)+25; 
		 	?>

			<div id="searchResultCount2" name="searchResultCount2" style="position:absolute; top:4px; left:87px;">
				<font size=2 color="#000000"><?php echo $linecount ?></font>
			</div>


		</div>


		<div id="mygrid_container" style="position:relative; width:<?php if ($selectDisplayWidth == "normal") { echo("552"); } else { echo("860"); } ?>px; height:<?php echo $gridheight ?>px; top:-1px; border: 1px solid #808080; -moz-border-radius:  0px 0px 3px 3px; border-radius:  0px 0px 3px 3px;">
			<font size=2>
			<table class="productsTable" border=0 style="table-layout: fixed;" width=100% height=<?php echo $gridheight ?> cellpadding=0 cellspacing=0>
				<tr>
				<td width="30" nowrap bgcolor=#e3efff>&nbsp;&nbsp;# </td>
				<td width=100% nowrap bgcolor=#e3efff> Item Title </td>
				<td width="6" nowrap bgcolor=#e3efff></td>
				<td width="100" nowrap bgcolor=#e3efff> Sold By </td>
				<td width="6" nowrap bgcolor=#e3efff></td>
				<td width="50" nowrap bgcolor=#e3efff> Price </td>
				<td width="45" nowrap bgcolor=#e3efff align=center>View</td>
				</td>
				</tr>
				<tr><td width=100% colspan=7 height=1 bgcolor=#808080></td></tr>
				<tbody>
			
			<?php
			$toggleColor=0;

			for($a=0; $a<$linecount; $a++)
				{ 
				if ($toggleColor==0) { $colorOut="#ffffff"; $toggleColor=1; } else { $colorOut="#e3efff"; $toggleColor=0; }	

				$includeDollarSign="";
				if (($selectCountry == "us")&&($resultArray[$a][3] != "{ NA }"))
					{
					$includeDollarSign="$";	
					}
				?>
				<tr>
				<td ondblclick="openNewTab('<?php echo $resultArray[$a][4] ?>')" style="overflow: hidden; text-overflow: ellipsis; -ms-user-select: none; -moz-user-select: none; -webkit-user-select: none; user-select: none;" onselectstart="return false;" nowrap>&nbsp;&nbsp;<?php echo $resultArray[$a][0]; ?></td>
				<td ondblclick="openNewTab('<?php echo $resultArray[$a][4] ?>')" style="overflow: hidden; text-overflow: ellipsis; -ms-user-select: none; -moz-user-select: none; -webkit-user-select: none; user-select: none;" onselectstart="return false;" nowrap title="<?php echo $resultArray[$a][1]; ?>"><?php echo $resultArray[$a][1]; ?></td>
				<td ondblclick="openNewTab('<?php echo $resultArray[$a][4] ?>')" ></td>
				<td ondblclick="openNewTab('<?php echo $resultArray[$a][4] ?>')" style="overflow: hidden; text-overflow: ellipsis; -ms-user-select: none; -moz-user-select: none; -webkit-user-select: none; user-select: none;" onselectstart="return false;" nowrap title="<?php echo $resultArray[$a][2]; ?>"><?php echo $resultArray[$a][2]; ?></td>
				<td ondblclick="openNewTab('<?php echo $resultArray[$a][4] ?>')" ></td>
				<td ondblclick="openNewTab('<?php echo $resultArray[$a][4] ?>')" style="overflow: hidden; text-overflow: ellipsis; -ms-user-select: none; -moz-user-select: none; -webkit-user-select: none; user-select: none;" onselectstart="return false;" nowrap><?php echo $includeDollarSign . $resultArray[$a][3]; ?> </td>
				<td ondblclick="openNewTab('<?php echo $resultArray[$a][4] ?>')" align=center> <?php echo '<a href="' . $resultArray[$a][4] . '" target="_blank">View</a>'; ?> </td>
				</tr>
			<?php
				}

			?>
		</tbody>
		</table></font>
		</div>
<?php 
#var_dump($resultArray); 
?>

	</body>
</html>

<?php ?>