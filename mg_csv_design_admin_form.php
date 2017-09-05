<?php 
// This includes gives us all the WordPress functionality
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
ini_set("default_charset", 'utf-8');

$themeArray = array(
	'Animals',
	'Bunkerfish',
	'Family',
	'Food and Drink',
	'Funny',
	'Fitness',
	'Harry Potter',
	'Holiday',
	'Movie and TV',
	'Music',
	'Natural',
	'Philly Pride',
	'Pop Culture',
	'Greek Life',
	'Sports',
	'Featured ChariTEE'
	);

$groupArray = array(
	"unisex-all" => "Unisex - All",
	"unisex-adult" => "Unisex - Adult",
	"unisex-no-baby-toddler" => "Unisex - No Baby & Toddler",
	"unisex-baby" => "Unisex - Baby",
	"unisex-toddler" => "Unisex - Toddler",
	"unisex-kids" => "Unisex - Kids",
	"unisex-baby-toddler" => "Unisex - Baby & Toddler",
	"unisex-kids-toddler" => "Unisex - Kids & Toddler",
	"female-all" => "Female - All",
	"female-adult" => "Female - Adult",
	"female-no-baby-toddler" => "Female - No Baby & Toddler",
	"male-all" => "Male - All",
	"male-adult" => "Male - Adult",
	"male-no-baby-toddler" => "Male - No Baby & Toddler"
);

$graphicTypeArray = array(
	'Illustration',
	'Text',
	'Blank',
	'Custom'
);

$pagePositionArray = array(
	'One',''
);

if (isset($_GET['sku'])) {
	$sku = $_GET['sku'];
	$mgdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
	$design = $mgdb->get_row( $mgdb->prepare( "SELECT * FROM wp_mtwt_print_designs WHERE sku = %s", $sku ), ARRAY_A );
}

?>
<style>
	table#themes td {
		padding: 3px;
		border: solid 1px #cecece;
		width: 20%;
	}
	table#graphicOptions td {
		padding-right: 30px;
		width: 25%;
	}
	table#graphicOptions select {
		width: 100%;
	}

</style>
<script>
function setSKU() {
	var url = window.location.href;
	var sku = document.getElementById("designAbbr").value;
	var full_path = url + "&sku=" + sku;
	window.location.href = full_path;
}
function btnText() {
	var sku = document.getElementById("designAbbr").value;
	if (sku != '') {
		document.getElementById("fillForm").innerHTML = 'Fill Form';
	} else {
		document.getElementById("fillForm").innerHTML = 'Clear Form';		
	}
}
</script>
<div class="wrap">
	<h2>New Design Set</h2>
 	<form id="newProductForm" action="<?php echo plugin_dir_url( __FILE__ ); ?>mg_csv_export.php" method="post">
		<input type="hidden" name="formType" value="design" />
		<h3>Design SKU</h3>
		<input type="text" name="designAbbr" id="designAbbr" maxlength="8" value="<?=$design['sku'];?>" onChange='btnText()' required>
		<button id="fillForm" type="button" onClick='setSKU()'>Clear Form</button>
		<h3>Name</h3>
		<input type="text" name="designName" maxlength="80" value="<?=$design['name'];?>">
		<h3>Description</h3>
		<p>*The word "item" will be replaced by the appropriate word for each product type.</p>
		<textarea form="newProductForm" name="designDesc" rows="4" cols="50"><?=$design['description'];?></textarea>
		<h3>Tags</h3>
		<p>*Separate each tag with a comma and a space.</p>
		<input type="text" name="designTags" maxlength="80" value="<?=$design['tags'];?>">
		<table id="graphicOptions">
			<tr>
				<td>
					<h3>Gender-Age Group</h3>
					<select id='productGroup' name="productGroup">
						<?php
						foreach($groupArray as $group => $name) {
							if($group == $design['group']) {
								$selected = ' selected="selected"';
							} else {
								$selected = '';
							}
							echo '<option value="' . $group . '"' . $selected . '>' . $name . '</option>';
						}
						?>
					</select>
				</td>
				<td>
					<h3>Graphic Type</h3>
					<select id='graphicType' name="graphicType">
						<?php
						foreach($graphicTypeArray as $type) {
							if($type == $design['graphic_type']) {
								$selected = ' selected="selected"';
							} else {
								$selected = '';
							}
							echo '<option value="' . $type . '"' . $selected . '>' . $type . '</option>';
						}
						?>
					</select>
				</td>
				<td>
					<h3>Google Category</h3>
					<select id='g_theme' name="g_theme">
						<?php
						foreach($themeArray as $theme) {
							if($theme == $design['google_product_type']) {
								$selected = ' selected="selected"';
							} else {
								$selected = '';
							}
							echo '<option value="' . $theme . '"' . $selected . '>' . $theme . '</option>';
						}
						?>
					</select>
				</td>
				<td>
					<h3>Page Position</h3>
					<select id='pagePosition' name="pagePosition">
						<?php
						foreach($pagePositionArray as $page) {
							if($page == $design['page_position']) {
								$selected = ' selected="selected"';
							} else {
								$selected = '';
							}
							echo '<option value="' . $page . '"' . $selected . '>' . $page . '</option>';
						}
						?>
					</select>
				</td>
			</tr>
		</table>
		<h3>Theme Categories</h3>
		<table id='themes'>
			<tr>
			<?php
			$i = 1;
			$count = count($themeArray);
			$selected = explode('|', $design['categories']);
			foreach($themeArray as $theme) {
				if(in_array($theme, $selected)) {
					$checked = ' checked';
				} else {
					$checked = '';
				}
				echo '<td><input type="checkbox" name="themes[]" value="' . $theme . '"' . $checked . '>' . $theme . '</td>';
				if($i % 5 == 0) {
					echo '</tr><tr>';
				}
				$i++;
			}
			?>
		</tr>
		</table>
		<h3>Export CSVs</h3>
		<input type="submit" name="btnAmazon" value="Create Amazon Import">
		<input type="submit" name="btnEbay" value="Create eBay Import">
		<input type="submit" name="btnOpenSky" value="Create OpenSky Import">
		<br>
		<input type="submit" name="btnFancy" value="Create Fancy Import">
		<input type="submit" name="btnWish" value="Create Wish Import">
		<input type="submit" name="btnShopify" value="Create Shopify Import">
		<h3>Add/Remove Design</h3>
		<input type="submit" name="btnAddDesign" value="Add Design to Database">
		<input type="submit" name="btnRemoveDesign" value="Remove Design from Database" onClick="return confirm('Are you sure you want to remove this design from the design list?')">
		<h3>Create Images</h3>
		<input type="submit" name="btnImages" value="Create Images">
	</form>
</div>
<script>
window.onload = btnText();
</script>