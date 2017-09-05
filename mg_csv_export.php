<?php
// This includes gives us all the WordPress functionality
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
ini_set("default_charset", 'utf-8');
/********    Validation    ********/
$errorMessage = "";

/***************    Static Variables    ***************/

$varPlaceHolder = ' item';
$varImageFolder = 'http://www.momentgearprinting.com/wp-content/uploads/wpallimport/files/';
$varShopifyImageFolder = 'https://cdn.shopify.com/s/files/1/2029/2589/products/';

/***************    Variables from the New Product Set Form   ***************/

$varImages = $_POST['createImages'];
$varItemSKU = $_POST['printItem'];
$formType = $_POST['formType'];

if($formType == 'product') {
	//Get the item specifics from the database
	$varItem = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "print_items WHERE sku = %s", $varItemSKU ), ARRAY_A );
	//Get the list of designs that go on this item and the related design details
	if ($varItem['age'] == 'Baby') {
		$varDesigns = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_designs WHERE `group` = 'unisex-all' 
											OR `group` = 'female-all' OR `group` = 'male-all' OR `group` = 'unisex-baby' 
											OR `group` = 'unisex-baby-toddler'", ARRAY_A);
	} elseif ($varItem['age'] == 'Toddler') {
		$varDesigns = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_designs WHERE `group` = 'unisex-all' 
											OR `group` = 'female-all' OR `group` = 'male-all' OR `group` = 'unisex-toddler'
											OR `group` = 'unisex-kids-toddler' OR `group` = 'unisex-baby-toddler'", ARRAY_A);
	} elseif ($varItem['age'] == 'Kids') {
		$varDesigns = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_designs WHERE `group` = 'unisex-all'
											OR `group` = 'female-all' OR `group` = 'male-all' OR `group` = 'unisex-no-baby-toddler' 
											OR `group` = 'male-no-baby-toddler' OR `group` = 'female-no-baby-toddler' 
											OR `group` = 'unisex-kids' OR `group` = 'unisex-kids-toddler'", ARRAY_A);
	} elseif ($varItem['gender'] == 'Female' || $varItem['sku'] == '8860') {
		$varDesigns = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_designs WHERE `group` = 'female-all' 
											OR `group` = 'female-adult' OR `group` = 'female-no-baby-toddler' OR `group` = 'unisex-all' 
											OR `group` = 'unisex-adult' OR `group` = 'unisex-no-baby-toddler'", ARRAY_A);
	} else {
		$varDesigns = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_designs WHERE `group` != 'unisex-baby'
											AND `group` != 'unisex-baby-toddler' AND `group` != 'unisex-toddler' AND `group` != 'unisex-kids'
											AND `group` != 'unisex-kids-toddler'", ARRAY_A);
	}
	$varItems = array($varItem);
	foreach ($varDesigns as $design) {
		if($design['categories'] == '') {
			$design['categories'] = $design['g_theme'];
		}
	}
} else {
	$productGroup = $_POST['productGroup'];
	switch ($productGroup) {
		case 'unisex-adult':
		case 'female-adult':
			$varItems = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_items WHERE `age` = 'Adult'", ARRAY_A);
			break;
		case 'unisex-no-baby-toddler':
		case 'female-no-baby-toddler':
			$varItems = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_items WHERE `age` != 'Baby' AND `age` != 'Toddler'", ARRAY_A);
			break;
		case 'unisex-baby':
			$varItems = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_items WHERE `age` = 'Baby'", ARRAY_A);
			break;
		case 'unisex-baby-toddler':
			$varItems = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_items WHERE `age` = 'Baby' OR `age` = 'Toddler'", ARRAY_A);
			break;
		case 'unisex-toddler':
			$varItems = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_items WHERE `age` = 'Toddler'", ARRAY_A);
			break;
		case 'unisex-kids':
			$varItems = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_items WHERE `age` = 'Kids'", ARRAY_A);
			break;
		case 'unisex-kids-toddler':
			$varItems = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_items WHERE `age` = 'Kids' OR `age` = 'Toddler'", ARRAY_A);
			break;
		case 'male-all':
			$varItems = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_items WHERE `gender` = 'Unisex'", ARRAY_A);
			break;
		case 'male-adult':
			$varItems = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_items WHERE `gender` = 'Unisex' AND `age` = 'Adult'", ARRAY_A);
			break;
		case 'male-no-baby-toddler':
			$varItems = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_items WHERE `gender` = 'Unisex' AND `age` != 'Baby' AND `age` != 'Toddler'", ARRAY_A);
			break;
		default:
			$varItems = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "print_items", ARRAY_A);
			break;
	}
	$varThemeArray = $_POST['themes'];
	if (is_array($varThemeArray)) {
		$varThemes = implode("|", $varThemeArray);
	} else {
		$varThemes = $varThemeArray;
	}
	$varDesign = array(
		sku => strtoupper($_POST['designAbbr']),
		name => mg_clean_characters($_POST['designName']),
		group => $productGroup,
		graphic_type => $_POST['graphicType'],
		page_position => $_POST['pagePosition'],
		description => mg_clean_characters($_POST['designDesc']),
		tags => mg_clean_characters(ucwords($_POST['designTags'])),
		categories => $varThemes,
		google_product_type => $_POST['g_theme']
	);
	if($varDesign['categories'] == '') {
		$varDesign['categories'] = $varDesign['g_theme'];
	}
	$varDesigns = array($varDesign);
}

/**************************************************************************************
***************************************************************************************
**************************    Clean Character Function    *****************************
***************************************************************************************
**************************************************************************************/

function mg_clean_characters($str){
	$search = array(
		'&#8220;', //  1. Left Double Quotation Mark “
		'&#8221;', //  2. Right Double Quotation Mark ”
		'&#8216;', //  3. Left Single Quotation Mark ‘
		'&#8217;', //  4. Right Single Quotation Mark ’
		'&#039;',  //  5. Normal Single Quotation Mark '
		'&amp;',   //  6. Ampersand &
		'&quot;',  //  7. Normal Double Qoute
		'&lt;',    //  8. Less Than <
		'&gt;',    //  9. Greater Than >
		'&rsquo;', // 10. Right Single Quotation Mark ’
		'&lsquo;', // 11. Left Single Quotation Mark ‘
		'&ndash;', // 12. Double Dash –
		'&ldquo;', // 13. Left Double Quotation Mark “
		'&rdquo;', //  14. Right Double Quotation Mark ”
		'&#8230;', // 15. Horizontal Ellipses …
		'&hellip;', // 16. Horizontal Ellipses …
		'\\n\\n' // 17. New Paragraph (Two Line Breaks)
		//'&eacute;' // 18. Latin small letter e with acute é
	);
	$replace = array(
		'"', // 1
		'"', // 2
		"'", // 3
		"'", // 4
		"'", // 5
		"&", // 6
		'"', // 7
		"<", // 8
		">", // 9
		"'", // 10
		"'", // 11
		"-", // 12
		'"', // 13
		'"', // 14
		'...', // 15
		'...', // 16
		'</p><p>' // 17
		//'é' // 18
	);
	
	// Fix the String
	$str = htmlentities($str, ENT_QUOTES, 'UTF-8');
	$str = stripslashes($str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
	$str = str_replace($search, $replace, $str);
	return $str;
}

/**************************************************************************************
***************************************************************************************
****************************     Abbreviate Function     ******************************
***************************************************************************************
**************************************************************************************/

function mg_abbreviate($str){
	$search = array(
		"X-Small",
		"Small",
		"Medium",
		"XXXX-Large",
		"XXX-Large",
		"XX-Large",
		"X-Large",
		"Large",
		"One Size", 
		"Newborn",
		"6 Months",
		"12 Months",
		"18 Months",
		"24 Months",
		"5 x 7",
		"8 x 10",
		"11 x 14",
		"Oxford",
		"Oyster",
		"Athletic Heather",
		"Heather Stone",
		"Heather White",
		"Heather Grey",
		"Light Blue",
		"Khaki",
		"Grey Triblend",
		"Charcoal Black Triblend",
		"Solid Black Triblend",
		"Solid Black Blend",
		"Solid White Triblend",
		"Solid White Blend",
		"White Fleck Triblend",
		"White Body / Black Trim",
		"White Body / Blue Trim",
		"White Body / Pink Trim",
		"Natural Body / Black Straps",
		"White Body / Black Sleeves",
		"White Triblend Body / Black Triblend Sleeves",
		"Black Sleeves / Dark Heather Grey Body",
		"Vintage Black Sleeves / Premium Heather Body",
		"Tri Vintage Grey Body / Tri Onyx Sleeves",
		"Tri Oatmeal",
		"Tri Vintage Grey",
		"Eco Tri Natural",
		"Eco Tri Grey",
		"Vintage Black",
		"Pink",
		"Natural",
		"White",
		"Black",
		"Heather",
		"Oatmeal Triblend",
		"Smoke",
		"Eco Grey",
		"Eco Oatmeal",
		"Ivory"
	);
	$replace = array(
		"XS",
		"S",
		"M",
		"4XL",
		"3XL",
		"2XL",
		"XL",
		"L",
		"OS", 
		"NB",
		"6M",
		"12M",
		"18M",
		"24M",
		"5X7",
		"8X10",
		"11X14",
		"Oxfrd",
		"Ostr",
		"AthltcHthr",
		"HthrStn",
		"HthrWht",
		"HthrGry",
		"LghtBlu",
		"Khk",
		"GryTrblnd",
		"BlckChrclTrblnd",
		"BlckSldTrblnd",
		"BlckSldBlnd",
		"WhtSldTrblnd",
		"WhtSldBlnd",
		"WhtFlkTrblnd",
		"Wht-Blck",
		"Wht-LghtBlu",
		"Wht-Pnk",
		"Ntrl-Blck",
		"Wht-Blck",
		"WhtFlkTrb-BlckChrclTrb",
		"BlckS-DrkHthrGryB",
		"VntgBlckS-PrmHthrB",
		"TrVntgGryBdy-TrOnxSlvs",
		"TrOtml",
		"TrVntgGry",
		"EcTrNtrl",
		"EcTrGry",
		"VntgBlck",
		"Pnk",
		"Ntrl",
		"Wht",
		"Blck",
		"Hthr",
		"OtmlTrblnd",
		"Smk",
		"EcGry",
		"EcOtml",
		"Ivry"
	);
	
	// Fix the String
	$str = str_replace($search, $replace, $str);
	return $str;
}

/**************************************************************************************
***************************************************************************************
*************************     Image Name Color Function     ***************************
***************************************************************************************
**************************************************************************************/

function mg_image_color($str){

	switch ($str) {
		case 'Oxford':
		case 'Athletic Heather':
		case 'Grey Triblend':
		case 'Smoke':
		case 'Eco Grey':
		case 'Eco Tri Grey':
		case 'Tri Vintage Grey':
		case 'Heather Grey':
		case 'Heather':
			$newColor = 'Grey';
			break;
		case 'Charcoal Black Triblend':
		case 'Solid Black Triblend':
		case 'Solid Black Blend':
		case 'Vintage Black':
			$newColor = 'Black';
			break;
		case 'Solid White Triblend':
		case 'Solid White Blend':
		case 'White Fleck Triblend':
		case 'Heather White':
			$newColor = 'White';
			break;
		case 'Oatmeal Triblend':
		case 'Heather Stone':
		case 'Eco Oatmeal':
		case 'Ivory':
		case 'Eco Tri Natural':
		case 'Tri Oatmeal':
			$newColor = 'Natural';
			break;
		case 'Vintage Black Sleeves / Premium Heather Body':
		case 'Black Sleeves / Dark Heather Grey Body':
		case 'Tri Vintage Grey Body / Tri Onyx Sleeves':
			$newColor = 'BlackSleeves-GreyBody';
			break;
		case 'White Body / Black Sleeves':
		case 'White Triblend Body / Black Triblend Sleeves':
			$newColor = 'White-BlackSleeves';
			break;
		case 'Light Blue':
			$newColor = 'Blue';
			break;
		case 'Natural Body / Black Straps':
			$newColor = 'Natural-BlackStraps';
			break;
		case 'White Body / Black Trim':
			$newColor = 'White-BlackTrim';
			break;
		case 'White Body / Blue Trim':
			$newColor = 'White-BlueTrim';
			break;
		case 'White Body / Pink Trim':
			$newColor = 'White-PinkTrim';
			break;
		default:
			$newColor = $str;
			break;
	}
	
	return $newColor;
}

/**************************************************************************************
***************************************************************************************
****************************     Color Map Function     *******************************
***************************************************************************************
**************************************************************************************/

function mg_color_map($str, $amazon=FALSE){
	
	switch ($str) {
		case 'Oxford':
		case 'Athletic Heather':
		case 'Grey Triblend':
		case 'Smoke':
		case 'Eco Grey':
		case 'Eco Tri Grey':
		case 'Tri Vintage Grey':
		case 'Heather Grey':
		case 'Heather':
			$newColor = 'Grey';
			break;
		case 'Charcoal Black Triblend':
		case 'Solid Black Triblend':
		case 'Solid Black Blend':
		case 'Vintage Black':
			$newColor = 'Black';
			break;
		case 'Solid White Triblend':
		case 'Solid White Blend':
		case 'White Fleck Triblend':
		case 'Heather White':
			$newColor = 'White';
			break;
		case 'Oatmeal Triblend':
		case 'Heather Stone':
		case 'Eco Oatmeal':
		case 'Ivory':
		case 'Eco Tri Natural':
		case 'Tri Oatmeal':
			$newColor = 'Natural';
			break;
		case 'Vintage Black Sleeves / Premium Heather Body':
		case 'Black Sleeves / Dark Heather Grey Body':
		case 'Tri Vintage Grey Body / Tri Onyx Sleeves':
			$newColor = 'Black Sleeves / Grey Body';
			break;
		case 'White Triblend Body / Black Triblend Sleeves':
			$newColor = 'White Body / Black Sleeves';
			break;
		case 'Light Blue':
			$newColor = 'Blue';
			break;
		default:
			$newColor = $str;
			break;
	}
	
	if($amazon) {
		$multicolored = array (
			"White Body / Black Trim",
			"White Body / Blue Trim",
			"White Body / Pink Trim",
			"White Body / Black Sleeves",
			"Black Sleeves / Grey Body"
		);
		
		$newColor = str_replace($multicolored, "multicoloured", $newColor);
	}
	
	return $newColor;
}
 
/**************************************************************************************
***************************************************************************************
*************************     Wish Color Map Function     ***************************
***************************************************************************************
**************************************************************************************/

function mg_wish_color_map($str){

	switch ($str) {
		case 'Oxford':
		case 'Athletic Heather':
		case 'Grey Triblend':
		case 'Smoke':
		case 'Eco Grey':
		case 'Eco Tri Grey':
		case 'Tri Vintage Grey':
		case 'Heather':
		case 'Heather Grey':
			$newColor = 'Grey';
			break;
		case 'Charcoal Black Triblend':
		case 'Solid Black Triblend':
		case 'Solid Black Blend':
		case 'Vintage Black':
			$newColor = 'Black';
			break;
		case 'Solid White Triblend':
		case 'Solid White Blend':
		case 'White Fleck Triblend':
		case 'Heather White':
			$newColor = 'White';
			break;
		case 'Oatmeal Triblend':
		case 'Heather Stone':
		case 'Eco Oatmeal':
		case 'Ivory':
		case 'Eco Tri Natural':
		case 'Tri Oatmeal':
			$newColor = 'Natural';
			break;
		case 'Vintage Black Sleeves / Premium Heather Body':
		case 'Black Sleeves / Dark Heather Grey Body':
		case 'Tri Vintage Grey Body / Tri Onyx Sleeves':
			$newColor = 'Black & Grey';
			break;
		case 'White Body / Black Sleeves':
		case 'White Triblend Body / Black Triblend Sleeves':
			$newColor = 'White & Black';
			break;
		case 'Light Blue':
			$newColor = 'Blue';
			break;
		case 'Natural Body / Black Straps':
			$newColor = 'Natural & Black';
			break;
		case 'White Body / Black Trim':
			$newColor = 'White & Black';
			break;
		case 'White Body / Blue Trim':
			$newColor = 'White & Blue';
			break;
		case 'White Body / Pink Trim':
			$newColor = 'White & Pink';
			break;
		default:
			$newColor = $str;
			break;
	}
	
	return $newColor;
}
/**************************************************************************************
***************************************************************************************
**************************    Image Creation Functions    *****************************
***************************************************************************************
**************************************************************************************/

function mg_prepare_images($code,$abbr,$color,$modelType = NULL) {
	set_time_limit(30);
	$color = mg_image_color($color);
	// array holding all of the safe zones for standard product images (left offset, top offset, scaling)
	$standardSafeZones = array	(
		"1004"    => array (375,630,41),
		"20039"   => array (380,320,41),
		"20360"   => array (405,450,36),
		"20660"   => array (370,430,42),
		"25065"   => array (420,335,34), //need to fix this when we have real images
		"3001"    => array (390,220,38),
		"3001Y"   => array (420,335,34),
		"3200"    => array (395,270,37),
		"32131"   => array (430,420,33),
		"32161"   => array (400,385,38),
		"3352"    => array (410,370,35),
		"3413"    => array (380,240,41),
		"3415C"   => array (380,280,41),
		"3480"    => array (380,350,40),
		"3480Y"   => array (435,390,30),
		"3901"    => array (400,290,37),
		"4400"    => array (450,290,28),
		"4411"    => array (450,290,28),
		"562B"    => array (425,320,33),
		"562M"    => array (375,230,42),
		"6051"    => array (390,270,40),
		"6400"    => array (400,240,37),
		"6405"    => array (415,350,35),
		"6733"    => array (415,360,33),
		"6951"    => array (415,300,34),
		"7501"    => array (365,260,43),
		"8435"    => array (425,425,31),
		"8838"    => array (400,400,38),
		"8860"    => array (340,600,48),
		"8868"    => array (400,630,38),
		"9575"    => array (380,270,42),
		"996M"    => array (440,315,30),
		"996Y"    => array (500,400,19),
		"B8413"   => array (400,275,37),
		"B8430"   => array (410,370,35),
		"B98902"  => array (455,630,28),
		"C3560"   => array (355,450,46),
	);

	// array holding all of the safe zones for model product images (left offset, top offset, scaling)
	$modelSafeZones = array (
		'6400' => array(480,460,22),
		'6405' => array(480,520,22),
		'6733' => array(480,530,22),
	);

	$sourcePath = dirname( __FILE__ ) . "/images/blanks/";
	$sourceName = $sourcePath . $code . "-BLANK-" . $color . ".jpg";

	$destPath = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/wpallimport/files/"; //destination for the finished images
	$destName = $destPath . $code . "-" . $abbr . "-" . $color . ".jpg";  //full file path and name of the finished images

	// load either the light design or the dark one, depending on the color
	if ($color == 'Black' || $color == 'Black-Triblend') {
		$designSource = dirname( __FILE__ ) . '/images/designs/' . strtolower($abbr) . '-white.png';
	} else {
		$designSource = dirname( __FILE__ ) . '/images/designs/' . strtolower($abbr) . '.png';
	}


	mg_create_image($sourceName,$designSource,$destName,$standardSafeZones[$code]);

	if($modelSafeZones[$code] && $modelType) {
		$modelSourceName = $sourcePath . $code . "-BLANK-" . $modelType . $color . ".jpg";
		$modelDestName = $destPath . $code . "-" . $abbr . "-" . $modelType . $color . ".jpg";

		mg_create_image($modelSourceName,$designSource,$modelDestName,$modelSafeZones[$code]);
	}

}

function mg_create_image($productSource, $designSource, $name, $safeZones){

	// load the blank product image 
	if(file_exists($productSource)) $product = imagecreatefromjpeg($productSource);

	// load the design
	if(file_exists($designSource)) $design = imagecreatefrompng($designSource);

	if ($product && $design) {
		 
		$safe_x = $safeZones[0];
		$safe_y = $safeZones[1];
		$safe_w = (imagesx($design) * $safeZones[2]/100);
		$safe_h = (imagesy($design) * $safeZones[2]/100);


		// get the dimensions of the frame, which we'll also be using for the composited final image. 
		$width = imagesx($product);
		$height = imagesy($product); 

		// create the destination/output image. 
		$img=imagecreatetruecolor( $width, $height ); 
		// enable alpha blending on the destination image. 
		imagealphablending($img, true); 
		imagesavealpha($img,true); 

		// load the safe zone size
		$frame = imagecreatetruecolor( $safe_w, $safe_h );
		imagealphablending($frame, true); 
		imagesavealpha($frame,true);

		// Allocate a transparent color and fill the new image with it. 
		// Without this the image will have a black background instead of being transparent. 
		$transparent = imagecolorallocatealpha( $frame, 0, 0, 0, 127 ); 
		imagefill( $frame, 0, 0, $transparent ); 

		imagecopyresampled($frame,$design,0,0,0,0, imagesx( $frame ), imagesy( $frame ), imagesx( $design ), imagesy( $design ) );

		// copy the thumbnail into the output image. 
		imagecopyresampled($img,$product,0,0,0,0,$width,$height,$width,$height ); 

		// copy the frame into the output image (layered on top of the thumbnail) 
		imagecopyresampled($img,$frame,$safe_x,$safe_y,0,0,$safe_h, $safe_h, $safe_w, $safe_h); 

		// save the image 
		imagejpeg( $img, $name ); 

		// dispose 
		imagedestroy($img); 
		imagedestroy($product); 
		imagedestroy($design); 
	}
}

function mg_image_loop() {
	global $varItems, $varDesigns;

	foreach ($varItems as $item) {
		$colors = explode(", ", $item['colors']);
		foreach($varDesigns as $design) {
			foreach ($colors as $color) {
				if($item['age'] == 'Baby') {
					mg_prepare_images($item['sku'], $design['sku'], $color, 'BM-');
				} elseif ($item['age'] == 'Kids') {
					mg_prepare_images($item['sku'], $design['sku'], $color, 'KM-');
				} elseif ($item['gender'] == 'Female' || $design['group'] == 'female-all' || $design['group'] == 'female-no-baby' || $design['group'] == 'female-adult' ) {
					mg_prepare_images($item['sku'], $design['sku'], $color, 'FM-');
				} else {
					mg_prepare_images($item['sku'], $design['sku'], $color, 'MM-');
				}
			}
		}
	}
	$referer = $_SERVER['HTTP_REFERER'] . '';
	header("Location: $referer");
}
/**************************************************************************************
***************************************************************************************
**************************    New Product Set Function    *****************************
***************************************************************************************
**************************************************************************************/

function mg_main_export() {
	global $varItems, $varDesigns, $varImages, $formType;
	$headers = array('sku', 'name', 'description', 'categories', 'themes', 'price', 'weight', 'size', 'color', 'tags', 'gender', 'age', 'sleeve-length', 'style', 'neckline', 'material', 'google_product_type', 'google_product_category', 'google_gender', 'google_age_group', 'google_material', 'parent_sku', 'image');
	$productSet = array($headers);

	foreach ($varItems as $item) {

		$sizes = explode(", ", $item['sizes']);
		$colors = explode(", ", $item['colors']);
		$childCount = (count($sizes) * count($colors));
		
		foreach ($varDesigns as $design) {
			foreach($headers as $header) {
				$currentItem[$header] = $item[$header];
			}
			$currentItem['sku'] = $item['sku'] . "-" . $design['sku'];
			$currentItem['name'] = $design['name'] . " " . $item['name'];
			$currentItem['description'] = '<p>' . str_replace(' item', ' ' . $item['item_type'], trim($design['description'])) . $item['description'];
			$currentItem['categories'] = $design['categories'] . $item['categories'];
			$currentItem['themes'] = $design['categories'];
			$currentItem['tags'] = $design['tags'];
			$currentItem['sleeve-length'] = $item['sleeve-length'];
			$currentItem['google_product_type'] = $design['google_product_type'] . ' > ' . $item['google_product_type'];
			$currentItem['parent_sku'] = '';
			
			if ($design['group'] == 'female-all' || $design['group'] == 'female-adult') {
				$currentItem['name'] = str_replace(' Mens', ' Unisex', $currentItem['name']);
				$currentItem['description'] = str_replace(" Men's", '', $currentItem['description']);
				$currentItem['gender'] = 'Unisex';
			}

			if ($childCount == 1) {
				$currentItem['price'] = $item['price'];
				$currentItem['color'] = mg_color_map($item['colors']);
				$currentItem['size'] = $item['sizes'];
				$currentItem['image'] = $currentItem['sku'] . '-' . mg_image_color($currentItem['color']) . '.jpg';
				$productSet[] = $currentItem;
				mg_prepare_images($item['sku'], $design['sku'], $item['colors']);
			} else {
				$currentItem['price'] = '';
				$currentItem['color'] = '';
				$currentItem['size'] = '';
				$currentItem['image'] = $currentItem['sku'] . '-' . mg_image_color($colors[rand(0, (count($colors) - 1))]) . '.jpg';
				$productSet[] = $currentItem;
				
				foreach ($colors as $color) {
					foreach ($sizes as $size) {
						foreach ( $currentItem as $key => $value )	{
							$child[$key] = $value;
						}
						$child['sku'] .= '-' . mg_abbreviate($color) . '-' . mg_abbreviate($size);
						$child['color'] = mg_color_map($color);
						$child['size'] = $size;
						if (mg_color_map($color) == 'Black') {
							$child['price'] = intval($item['price']) + 5;
						} else {	
							$child['price'] = $item['price'];
						}
						if ($size == 'XX-Large') {
							$child['price'] += 2;		
						}
						if ($size == 'XXX-Large') {
							$child['price'] += 3;		
						}
						if ($size == 'XXXX-Large') {
							$child['price'] += 4;		
						}
						if ($size == '8 x 10') {
							$child['price'] += 10;		
						}
						if ($size == '11 x 14') {
							$child['price'] += 20;		
						}
						$child['parent_sku'] = $currentItem['sku'];
						$child['image'] = $currentItem['sku'] . '-' . mg_image_color($color) . '.jpg';
						
						$productSet[] = $child;
					}
					if ($varImages == 'Y') {
						if($item['age'] == 'Baby') {
							mg_prepare_images($item['sku'], $design['sku'], $color, 'BM-');
						} elseif ($item['age'] == 'Kids') {
							mg_prepare_images($item['sku'], $design['sku'], $color, 'KM-');
						} elseif ($item['gender'] == 'Female' || $design['group'] == 'female-all' || $design['group'] == 'female-no-baby' || $design['group'] == 'female-adult' ) {
							mg_prepare_images($item['sku'], $design['sku'], $color, 'FM-');
						} else {
							mg_prepare_images($item['sku'], $design['sku'], $color, 'MM-');
						}
					}
				}
			}
		}
	}
	//echo "<pre>";
	//print_r($productSet);
	//echo "</pre>";
	if ($formType == 'product') {
		$file = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/wpallimport/files/mg-new-product-set.csv";
		$fp = fopen( $file ,"w");
	} else {
		$file = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/wpallimport/files/mg-new-design-set.csv";
		if( file_exists($file) ) {
			$fp = fopen( $file ,"a");
		} else {
			$fp = fopen( $file ,"w");
		}
	}
	
	foreach ($productSet as $fields) {
		 fputcsv($fp, $fields);
	}
	fflush ($fp);
	fclose($fp);

	$referer = $_SERVER['HTTP_REFERER'];
	header("Location: $referer");
}
/**************************************************************************************
***************************************************************************************
***************************    Shopify Export Function    *******************************
***************************************************************************************
**************************************************************************************/

function mg_shopify_export() {
	global $varItems, $varDesigns, $varImageFolder, $formType;
	$shopifyList = NULL; // This will be the array we print.

	foreach ($varItems as $item) {
		$sizes = explode(", ", $item['sizes']);
		$colors = explode(", ", $item['colors']);
		$childCount = (count($sizes) * count($colors));
		// loop through each design
		foreach ($varDesigns as $design) {
			$childNumber = 1;
			shuffle($colors);
			foreach ($colors as $color) {
				foreach ($sizes as $size) {
					
					$currentItem = array
					(
						"Handle" =>  strtolower(str_replace(" ", "-", $design['name'] . "-" . $item['name'])),
						"Title" => $design['name'] . " " . $item['name'],
					   	"Body (HTML)" => '<p>' . str_replace(' item', ' ' . $item['item_type'], trim($design['description'])) . $item['description'],
					   	"Vendor" => "Moment Gear",
					   	"Type" => $item['google_product_type'],
						"Tags" => $design['tags'],
						"Published" => "TRUE",
						"Option1 Name" => '',
						"Option1 Value" => '',
						"Option2 Name" => '',
						"Option2 Value" => '',
						"Variant SKU" => $item['sku'] . "-" . $design['sku'],
						"Variant Grams" => ($item['weight'] * 453.592), //convert pounds to grams
						"Variant Inventory Tracker" => '',
						"Variant Inventory Qty" => '',
						"Variant Inventory Policy" => 'continue',
						"Variant Fulfillment Service" => 'manual',
						"Variant Price" => $item['price'],
						"Variant Compare At Price" => intval($item['price']),
						"Variant Requires Shipping" => 'TRUE',
						"Variant Taxable" => 'FALSE',
						"Variant Barcode" => '',
						"Image Src" => '',
						"Image Alt Text" => '',
						"Gift Card" => 'False',
						"Google Shopping / MPN" => '',
						"Google Shopping / Age Group" => $item['google_age_group'],
						"Google Shopping / Gender" => $item['google_gender'],
						"Google Shopping / Google Product Category" => $item['google_product_category'],
						"SEO Title" => $design['name'] . " " . $item['name'],
						"SEO Description" => str_replace(' item', ' ' . $item['item_type'], trim($design['description'])),
						"Google Shopping / AdWords Grouping" => $design['google_product_type'] . ' > ' . $item['google_product_type'],
						"Google Shopping / AdWords Labels" => '',
						"Google Shopping / Condition" => 'new',
						"Google Shopping / Custom Product" => 'FALSE',
						"Google Shopping / Custom Label 0" => '',
						"Google Shopping / Custom Label 1" => '',
						"Google Shopping / Custom Label 2" => '',
						"Google Shopping / Custom Label 3" => '',
						"Google Shopping / Custom Label 4" => '',
						"Variant Image" => '',
						"Variant Weight Unit" => 'lb'
					);


					//add tags that will be used by shopify for filtering
					if (isset($item['gender']) && $item['gender'] != '') {
						$currentItem['Tags'] .= ',Gender_' . $item['gender'];
					}
					if (isset($item['age']) && $item['age'] != '') {
						$currentItem['Tags'] .= ',Age_' . $item['age'];
						if($item['sku'] == 'C3560') {
							$currentItem['Tags'] .= ',Age_Kids';
						}
					}
					if (isset($item['sleeve-length']) && $item['sleeve-length'] != '') {
						$currentItem['Tags'] .= ',Sleeve Length_' . $item['sleeve-length'];
					}
					if (isset($item['google_product_type']) && $item['google_product_type'] != '') {
						$currentItem['Tags'] .= ',Product Type_' . $item['google_product_type'];
					}
					if (isset($item['neckline']) && $item['neckline'] != '') {
						$currentItem['Tags'] .= ',Neckline_' . $item['neckline'];
					}
					if (isset($item['material']) && $item['material'] != '') {
						$currentItem['Tags'] .= ',Material_' . $item['material'];
					}
					if (isset($design['graphic_type']) && $design['graphic_type'] != '') {
						$currentItem['Tags'] .= ',Graphic Type_' . $design['graphic_type'];
					}
					if (isset($design['page_position']) && $design['page_position'] != '') {
						$currentItem['Tags'] .= ',Page Position_' . $design['page_position'];
					}
					if (isset($design['categories'])) {
						$graphicThemes = explode('|', $design['categories']);
						foreach ($graphicThemes as $theme) {
							$currentItem['Tags'] .= ',Graphic Theme_' . $theme;
						}
					}

					//set the type of model used
					if($item['age'] == 'Baby') {
						$modelType = 'BM-';
					} elseif ($item['age'] == 'Kids') {
						$modelType = 'KM-';
					} elseif ($item['gender'] == 'Female') {
						$modelType = 'FM-';
					} else {
						$modelType = 'MM-';
					}
					
					//check if the design is female only, then change the title and gender accordingly
					if ($design['group'] == 'female-all' || $design['group'] == 'female-adult' || $design['group'] == 'female-all') {
						if($item['age'] == 'Adult') {
							$currentItem['Title'] = str_replace(' Mens', ' Unisex', $currentItem['Title']);
							$currentItem['SEO Title'] = str_replace(' Mens', ' Unisex', $currentItem['SEO Title']);
							$currentItem['Body (HTML)'] = str_replace(" Men's", '', $currentItem['Body (HTML)']);
							$currentItem['Tags'] = str_replace('Gender_Male', 'Gender_Female', $currentItem['Tags']);
							$currentItem['Tags'] = str_replace('Gender_Unisex', 'Gender_Female', $currentItem['Tags']);
							$modelType == 'FM-';
						}
					}
					/*
					if ($item['sku'] == '4400' || $item['sku'] == '4411') {
						$currentItem['Title'] = str_replace("Onesie", "Romper", $currentItem['Title']);	
						$currentItem['SEO Title'] = str_replace("Onesie", "Romper", $currentItem['SEO Title']);	
					}*/
					
					if ($childCount == 1) {
						$currentItem['Image Src'] = $varImageFolder . $currentItem['Variant SKU'] . '-' . mg_image_color($color) . '.jpg';
						$currentItem['Image Alt Text'] = $currentItem['Title'];
					} else {
						if($childNumber == 1) { //this only happens for the first variation of each product
							$currentItem['Option1 Name'] = 'Color';
							$currentItem['Option2 Name'] = 'Size';						
						} else {
							$currentItem['Title'] = '';
							$currentItem['Body (HTML)'] = '';
							$currentItem['Vendor'] = '';
							$currentItem['Tags'] = '';
						}
						if($childNumber <= count($colors)) {
							$currentItem['Image Src'] = $varImageFolder . $currentItem['Variant SKU'] . '-' . mg_image_color($colors[($childNumber-1)]) . '-MDL.jpg';
							$currentItem['Image Alt Text'] = $design['name'] . " " . $item['name'] . ' - ' . mg_color_map($colors[($childNumber-1)]);
						}
						$currentItem['Option1 Value'] = mg_color_map($color);
						$currentItem['Option2 Value'] = $size;
						$currentItem['Variant Image'] = $varImageFolder . $currentItem['Variant SKU'] . '-' . mg_image_color($color) . '.jpg';
						$currentItem['Variant SKU'] .= "-" . mg_abbreviate($color) . '-' . mg_abbreviate($size);

						if (mg_color_map($color) == 'Black') {
							$currentItem["Variant Price"] += 5;
						}
						if ($size == 'XX-Large') {
							$currentItem["Variant Price"] += 2;		
						}
						if ($size == 'XXX-Large') {
							$currentItem["Variant Price"] += 3;		
						}
						if ($size == 'XXXX-Large') {
							$currentItem["Variant Price"] += 4;		
						}
						if ($size == '8 x 10') {
							$currentItem["Variant Price"] += 10;		
						}
						if ($size == '11 x 14') {
							$currentItem["Variant Price"] += 20;		
						}
						++$childNumber;
					}
					if(!isset($shopifyList)) {
						$shopifyList = array();
						$shopifyList[] = array_keys($currentItem);
					}
					$shopifyList[] = $currentItem;
				}
			}
		} //end foreach Design
	} //end foreach Item
	if ($formType == 'product'){
		$filename = "shopify_product_set_" . $varItems[0]['sku'] .".csv";
	} else {
		$filename = "shopify_product_set_" . $varDesigns[0]['sku'] .".csv";
	}
	
	$output = fopen("php://output",'w') or die("Can't open php://output");
	header("Content-type: application/csv; charset=UTF-8;");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	foreach($shopifyList as $row) {
		fputcsv($output, $row);
	}
	fclose($output) or die("Can't close php://output");
}

/**************************************************************************************
***************************************************************************************
***************************    Amazon Export Function    ******************************
***************************************************************************************
**************************************************************************************/

function mg_amazon_export() {
	global $varItems, $varDesigns, $varShopifyImageFolder, $formType;
	$amazonList = array (
		array ("TemplateType=Custom","Version=2016.0818","The top 3 rows are for Amazon.com use only. Do not modify or delete the top 3 rows."),
		array ("SKU", "Product Name", "Brand Name", "Product Description", "Item Type Keyword", "Style Number", "Standard Price", "Product Tax Code", "Fulfullment Latency","Quantity", "Key Product Features1", "Key Product Features2", "Key Product Features3","Key Product Features4","Search Terms1", "SearchTerms2", "Main Image URL", "Parentage", "Parent SKU", "Relationship Type", "Variation Theme", "Colour", "Colour Map", "Department", "Size", "Size Map", "Fabric Type", "Material Type", "Materal Fabric", "Product Type"),
		array ("item_sku", "item_name", "brand_name", "product_description", "item_type", "model", "standard_price", "product_tax_code", "fulfillment_latency", "quantity", "bullet_point1", "bullet_point2", "bullet_point3", "bullet_point4", "generic_keywords1", "generic_keywords2", "main_image_url", "parent_child", "parent_sku", "relationship_type", "variation_theme", "color_name", "color_map", "department_name", "size_name", "size_map", "fabric_type", "material_type", "material_type1", "feed_product_type")
	);
	
	foreach ($varItems as $item) {
		$sizes = explode(", ", $item['sizes']);
		$colors = explode(", ", $item['colors']);
		$childCount = (count($sizes) * count($colors));
		//prepare the list of colors for the parent product
			$colorList = '';
			foreach($colors as $c) {
				$colorList .= mg_color_map($c) . ", ";
			}
		
		// loop through each design
		foreach ($varDesigns as $design) {

			$currentItem = array 
			(
				"item_sku" => $item['sku'] . "-" . $design['sku'],
				"item_name" => 'Moment Gear ' . $design['name'] . " " . $item['name'],
				"brand_name" => 'Moment Gear',
				"product_description" => str_replace(' item', ' ' . $item['item_type'], trim($design['description'])),
				"item_type" => $item['amazon_item_type'],
				"model" => $item['sku'] . "-" . $design['sku'],
				"standard_price" => '',
				"product_tax_code" => 'A CLTH GEN',
				"fulfillment_latency" => '5',
				"quantity" => '500',
				"bullet_point1" => 'Printed in the USA using eco-friendly ink.',
				"bullet_point2" => 'Available Sizes: ' . $item['sizes'],
				"bullet_point3" => 'Available Colors: ' . $colorList,
				"bullet_point4" => $item['amazon_bullet4'],
				"generic_keywords1" => 'Fashionable Cool Stylish Trendy Casual',
				"generic_keywords2" => $design['tags'],
				"main_image_url" => '',
				"parent_child" => '',
				"parent_sku" => '',
				"relationship_type" => '',
				"variation_theme" => '',
				"color_name" => '',
				"color_map" => '',
				"department_name" => $item['amazon_department'],
				"size_name" => '',
				"size_map" => '',
				"fabric_type" => $item['material'],
				"material_type" => '',
				"material_type1" => '',
				"feed_product_type" => ''
			);		

			if ($design['group'] == 'female-all' || $design['group'] == 'female-adult' || $design['group'] == 'female-all') {
				$currentItem['item_name'] = str_replace(' Mens', ' Unisex', $currentItem['item_name']);
				$currentItem['department_name'] = str_replace("boys", 'girls', $currentItem['department_name']);
				if ($currentItem['department_name'] == 'mens'){
					$currentItem['department_name'] = 'womens';
				}
			}

			if ($item['sku'] == '4400' || $item['sku'] == '4411') {
				$currentItem['item_name'] = str_replace("Onesie", "Romper", $currentItem['item_name']);	
			}
			if ($item['sku'] == 'EC6015') {
				$currentItem['feed_product_type'] = 'HomeAndDecor';
			}

			if ($childCount == 1) {
				$currentItem['standard_price'] = $item['price'];
				$currentItem['size_name'] = $item['sizes'];
				$currentItem['size_map'] = $item['sizes'];
				$currentItem['color_name'] = mg_color_map($item['colors']);
				$currentItem['color_map'] = mg_color_map($item['colors'], TRUE);
				$currentItem['main_image_url'] = $varShopifyImageFolder . $currentItem['item_sku'] . '-' . mg_image_color($item['colors']) . '.jpg';
				if ($currentApparel == '8868') {
					$currentItem['standard_price'] = '30.90';
					$currentItem['material_type1'] = 'Cotton';
					$currentItem['fabric_type'] = '';
				}
				if ($currentApparel == 'C3560') {
					$currentItem['standard_price'] = '22.90';
					$currentItem['material_type'] = 'Cotton';
					$currentItem['fabric_type'] = '';
					$currentItem['feed_product_type'] = 'BedAndBath';
				}
				if ($currentApparel == 'B98902') {
					$currentItem['standard_price'] = '22.90';
					$currentItem['material_type'] = 'Linen';
					$currentItem['fabric_type'] = '';
					$currentItem['feed_product_type'] = 'Kitchen';
				}
				$amazonList[] = $currentItem;
			} else {
				$currentItem['parent_child'] = 'Parent';
				$currentItem['variation_theme'] = 'sizecolor';
				$currentItem['main_image_url'] = $varShopifyImageFolder . $currentItem['item_sku'] . '-' . mg_image_color($colors[rand(0, (count($colors) - 1))]) . '.jpg';
				$amazonList[] = $currentItem;

				foreach ($colors as $color) {
					foreach ($sizes as $size) {
						foreach ( $currentItem as $key => $value )	{
							$child[$key] = $value;
						}
						$child['parent_child'] = 'Child';
						$child['relationship_type'] = 'Variation';
						$child['parent_sku'] = $currentItem['item_sku'];
						$child['item_sku'] .= '-' . mg_abbreviate($color) . '-' . mg_abbreviate($size);
						$child['model'] .= '-' . mg_abbreviate($color) . '-' . mg_abbreviate($size);
						$child['item_name'] .= ' ' . mg_color_map($color) . ' ' . $size;
						if ($item['sku'] == '4400' || $item['sku'] == '4411') {
							$child['item_name'] = str_replace("Heather", "Grey", $child['item_name']);	
						}
						$child['color_name'] = str_replace(' Body', '', str_replace(' Sleeves', '', mg_color_map($color)));
						$child['color_map'] = mg_color_map($color, TRUE);
						$child['size_name'] = $size;
						$child['size_map'] = $size;
						if (mg_color_map($color) == 'Black') {
							$child['standard_price'] = intval($item['price']) + 5;
						} else {	
							$child['standard_price'] = $item['price'];
						}
						if ($size == 'XX-Large') {
							$child['standard_price'] += 2;		
						}
						if ($size == 'XXX-Large') {
							$child['standard_price'] += 3;		
						}
						if ($size == 'XXXX-Large') {
							$child['standard_price'] += 4;		
						}
						if ($size == '8 x 10') {
							$child['standard_price'] += 10;		
						}
						if ($size == '11 x 14') {
							$child['standard_price'] += 20;		
						}
						$child['main_image_url'] = $varShopifyImageFolder . $currentItem['item_sku'] . '-' . mg_image_color($color) . '.jpg';

						$amazonList[] = $child;
					}
				}
			}
		}
	}
	//checks the length of the name and makes it shorter if it's over 80 characters.
	foreach($amazonList as $key => $row) {
	
		if (strlen($row['item_name']) > 80) {
			//shorten the size in the name
			$amazonList[$key]['item_name'] = str_replace($row['size_name'], mg_abbreviate($row['size_name']), $amazonList[$key]['item_name']);
			$amazonList[$key]['item_name'] = str_replace(" Tee ", " T-", $amazonList[$key]['item_name']);
			$amazonList[$key]['item_name'] = str_replace("Solid ", "", $amazonList[$key]['item_name']);
			$amazonList[$key]['item_name'] = str_replace("Vintage ", "", $amazonList[$key]['item_name']);
			$amazonList[$key]['item_name'] = str_replace("Charcoal ", "", $amazonList[$key]['item_name']);
			$amazonList[$key]['item_name'] = str_replace("Fleck ", "", $amazonList[$key]['item_name']);
			if (strlen($row['item_name']) > 80) {
				$amazonList[$key]['item_name'] = str_replace('Triblend ', '', $amazonList[$key]['item_name']);
				$amazonList[$key]['item_name'] = str_replace('Blend ', '', $amazonList[$key]['item_name']);
				if (strlen($row['item_name']) > 80) {
					$amazonList[$key]['item_name'] = str_replace('Body ', '', $amazonList[$key]['item_name']);
					if (strlen($row['item_name']) > 80) {
						$amazonList[$key]['item_name'] = str_replace('Sleeves ', '', $amazonList[$key]['item_name']);
						if (strlen($row['item_name']) > 80) {
							$amazonList[$key]['item_name'] = str_replace(' T-Shirt', ' Tee', $amazonList[$key]['item_name']);
							if (strlen($row['item_name']) > 80) {
								$amazonList[$key]['item_name'] = str_replace(' Tee', ' T', $amazonList[$key]['item_name']);
							}
						}
					}
				}
			}
		}
	}
	if ($_POST['formType'] == "product") {
		$filename = "amazon_product_set_" . $varItems[0]['sku'] .".txt";
	} else {
		$filename = "amazon_product_set_" . $varDesigns[0]['sku'] .".txt";
	}
	
	header("Content-type: application/octet-stream;");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	foreach($amazonList as $row) {
		$rowString = implode("\t", $row);
		print $rowString . "\r\n";
	}
	return; 
}

/**************************************************************************************
***************************************************************************************
****************************    eBay Export Function    *******************************
***************************************************************************************
**************************************************************************************/

function mg_ebay_export() {
	global $varItems, $varDesigns, $varShopifyImageFolder, $formType;
	// Array containing the eBay headers
	$ebayFields = array ("*Action(SiteID=US|Country=US|Currency=USD|Version=745|CC=UTF-8)","CustomLabel","*Category","StoreCategory","*Title","Relationship","RelationshipDetails","*ConditionID","C:Brand","*C:Style","*C:Size Type","*C:Size (Men's)","*C:Size (Women's)","C:Material","C:Occasion","C:Season","C:Sleeve Length","C:Pattern","C:Country/Region of Manufacture","PicURL","*Description","*Format","*Duration","*StartPrice","*Quantity","PayPalAccepted","PayPalEmailAddress","*Location","*DispatchTimeMax","*ReturnsAcceptedOption","ShippingProfileName","ReturnProfileName","PaymentProfileName","Product:UPC","Product:ISBN","Product:EAN");
	// Add the header array to an array. This will be the array we print.
	$ebayList = array ($ebayFields);

	foreach ($varItems as $item) {
		if ($item['ebay_category']) {

			$sizes = explode(", ", $item['sizes']);
			$colors = explode(", ", $item['colors']);
			$childCount = (count($sizes) * count($colors));
			$ebayLogo = "<img src='" . $varShopifyImageFolder . "top_logo.png'>";
			$ebayEndText = "<h3>No Heat Transfers, Iron-Ons, or Decals used. All designs are printed on premium, soft, high quality garments using state of the art technology with eco-friendly water based ink which produces a smooth texture of the graphic which can hardly be felt with the touch of your hand. The ink used preserves image quality and prevents cracking when washed.</h3><h3>SHIPPING, RETURNS AND EXCHANGES</h3><p>We process and ship all orders in 1-3 business days. If an exchange or refund is needed, we kindly ask that you let us know within 14 fourteen days of receiving the item. You may send the shirt back and request the correct size and we will make you a new shirt and mail it to you. If for whatever reason you are not happy with the item and want to return it for a refund that is accepted as well. We will refund your order value but the buyer is liable for the postage.</p><p><b>*****OUR #1 priority is to have your experience be a memorable one each and every time you order from us. If you are not 100% satisfied, please contact or send a message ASAP and we will resolve the issue!*****</b></p>";
			//prepare the list of colors for the parent product
			$colorList = '';
			foreach($colors as $c) {
				$colorList .= mg_color_map($c) . ";";
			}
			// loop through each design
			foreach ($varDesigns as $design) {
				
				$currentItem = array (
					"*Action(SiteID=US|Country=US|Currency=USD|Version=745|CC=UTF-8)" => 'Add',
					"CustomLabel" => $item['sku'] . "-" . $design['sku'],
					"*Category" => $item['ebay_category'],
					"StoreCategory" => $item['ebay_store_category'],
					"*Title" => $design['name'] . " " . $item['name'],
					"Relationship" => '',
					"RelationshipDetails" => 'Size=' . str_replace(', ', ';', $item['sizes']) . "|Color=" . $colorList,
					"*ConditionID" => '1000',
					"C:Brand" => 'Moment Gear',
					"*C:Style" => 'Graphic Tee',
					"*C:Size Type" => 'Regular',
					"*C:Size (Men's)" => '',
					"*C:Size (Women's)" => '',
					"C:Material" => $item['material'],
					"C:Occasion" => '',
					"C:Season" => '',
					"C:Sleeve Length" => $item['sleeve-length'],
					"C:Pattern" => 'Solid',
					"C:Country/Region of Manufacture" => 'United States',
					"PicURL" => '',
					"*Description" => '',
					"*Format" => 'FixedPrice',
					"*Duration" => 'GTC',
					"*StartPrice" => '',
					"*Quantity" => '',
					"PayPalAccepted" => '1',
					"PayPalEmailAddress" => 'MGacctng@MomentGear.com',
					"*Location" => '19147',
					"*DispatchTimeMax" => '2',
					"*ReturnsAcceptedOption" => 'ReturnsAccepted',
					"ShippingProfileName" => '',
					"ReturnProfileName" => 'Returns Accepted,Buyer,14 Days,Money Back',
					"PaymentProfileName" => 'Use as Default Per RB',
					"Product:UPC" => '',
					"Product:ISBN" => '',
					"Product:EAN" => ''
					);
				
				if ( $item['sku'] == '4400' || $item['sku'] == '4411' ) {
					$currentItem['*Title'] = str_replace("Onesie", "Romper", $currentItem['*Title']);	
				}
				//add tags to the title if there's room
				$ebayTags = explode(", ", $design['tags']); //design specific tags
				array_push($ebayTags, "Soft", "Comfy", "Top", $item['material']); //generic tags plus the material
				$tagCount = count($ebayTags); //number of tags
				$tagsAdded = 0; //keeps track of the tags added so we can loop through them and end the while loop if there are no more tags
				while ( strlen($currentItem['*Title']) < 80 && $tagsAdded < $tagCount ) {
					if (strlen($currentItem['*Title']) + strlen($ebayTags[$tagsAdded]) < 80) {
						$currentItem['*Title'] .= " " . $ebayTags[$tagsAdded];
					}
					$tagsAdded++;
				}
				//remove top from 1004, 4400 and 4411 
				if ( $item['sku'] == '4400' || $item['sku'] == '4411' || $item['sku'] == '1004') {
					if (strpos($currentItem['*Title'], 'Top') !== false )
					$currentItem['*Title'] = str_replace(" Top", "", $currentItem['*Title']);	
				}
				// check if this is a men's or women's shirt, then set the appropriate size
				switch($currentItem['*Category']) {
					case  "15687":
					case  "11484":
					case "155194": 
					   $currentItem['*C:Size (Men\'s)'] = "Multiple Sizes";
						break;
					case "63866":
					case "63869":
						$currentItem['*C:Size (Women\'s)'] = "Multiple Sizes";
					break;
				}
				// check if this is a onesie or a kid's shirt, then add info
				if ($currentItem['*Category'] == "155199" || $currentItem['*Category'] == "163425") {
					$currentItem['C:Occasion'] = 'Every Day';
					$currentItem['C:Season'] = 'All Seasons';
				}
				$currentItem['PicURL'] = $varShopifyImageFolder . $currentItem['CustomLabel'] . '-' . mg_image_color($colors[rand(0, (count($colors) - 1))]) . '.jpg';
				//check if this is not a bib, then add the size chart
				if ($item['sku'] != "1004") {
					$currentItem['PicURL'] .= "|" . $varShopifyImageFolder . "sc-" . $item['sku'] . ".jpg";
				}
				// create the ebay description by combining the logo, the title as an h1 header, the full description plus the size and color options, and the ebay end text
				$ebayBullets = "<li>Available Colors: " . $colorList . "</li><li>Available Sizes: " . $item['sizes'] . "</li></ul>";
				$currentItem['*Description'] = $ebayLogo . "<h1>" . $design['name'] . " " . $item['name'] . "</h1><p>" . str_replace(' item', ' ' . $item['item_type'], trim($design['description'])) . str_replace( "</ul>", $ebayBullets, $item['description']) . $ebayEndText;
				//check the store category, then assign a shipping profile
				if ($currentItem['StoreCategory'] == "12446221018") {
					$currentItem['ShippingProfileName'] = '5.Biz.Days - Sweatshirts - Adult';
				} else { $currentItem['ShippingProfileName'] = '5.Biz.Days - Tanks/Tees - Adult'; }
				//add the current item to the ebay list
				$ebayList[] = $currentItem;
				
				//loop through the child products 
				foreach($colors as $color) {
					foreach($sizes as $size) {
						$child = array_fill_keys($ebayFields, '');
						$child['CustomLabel'] = $item['sku'] . '-' . $design['sku'] . '-' . mg_abbreviate($color) . '-' . mg_abbreviate($size);
						$child['Relationship'] = 'Variation';
						$child['RelationshipDetails'] = "Size=" . $size . "|Color=" . mg_color_map($color);
						$child['PicURL'] = mg_color_map($color) . "=" . $varShopifyImageFolder . $item['sku'] . '-' . $design['sku'] . '-' . mg_image_color($color) . '.jpg';
						$child['*StartPrice'] = $item['price'] + 6;
						if (mg_color_map($color) == 'Black') {
							$child['*StartPrice'] += 5;
						}
						if ($size == 'XX-Large') {
							$child['*StartPrice'] += 2;		
						}
						if ($size == 'XXX-Large') {
							$child['*StartPrice'] += 3;		
						}
						if ($size == 'XXXX-Large') {
							$child['*StartPrice'] += 4;		
						}
						if ($size == '8 x 10') {
							$child['*StartPrice'] += 10;		
						}
						if ($size == '11 x 14') {
							$child['*StartPrice'] += 20;		
						}
						$child['*Quantity'] = '20';
						$child['Product:UPC'] = 'Does not apply';
						$child['Product:ISBN'] = 'Does not apply';
						$child['Product:EAN'] = 'Does not apply';
						
					$ebayList[] = $child;

					} //end foreach size
				} //end foreach color
			} //end foreach design
		} //end if
	} //end foreach item

	if ($formType == 'product') {
		$filename = "ebay_product_set_" . $varItems[0]['sku'] .".csv";
	} else {
		$filename = "ebay_product_set_" . $varDesigns[0]['sku'] .".csv";
	}
	$output = fopen("php://output",'w') or die("Can't open php://output");
	header("Content-type: application/csv; charset=UTF-8;");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	foreach($ebayList as $row) {
		fputcsv($output, $row);
	}
	fclose($output) or die("Can't close php://output");
}

/**************************************************************************************
***************************************************************************************
**************************    Open Sky Export Function    *****************************
***************************************************************************************
**************************************************************************************/

function mg_opensky_export() {
	global $varItems, $varDesigns, $varShopifyImageFolder, $formType;
	// This will be the array we print.
	$openSkyList = NULL;

	// loop through each item on our main product list
	foreach ($varItems as $item) {
		$sizes = explode(", ", $item['sizes']);
		$colors = explode(", ", $item['colors']);
		$childCount = (count($sizes) * count($colors));
		foreach ($varDesigns as $design) {
			$currentSKU = $item['sku'] . "-" . $design['sku'];
			$currentItem = array (
				"ItemID" => $currentSKU,
				"GroupItemID" => '',
				"Title" => $design['name'] . " " . $item['name'],
				"Description" => '<p>' . str_replace(' item', ' ' . $item['item_type'], trim($design['description'])) . $item['description'],
				"Quantity" => '',
				"OpenSkyCategory" => $item['open_sky_category'],
				"Price" => '',
				"Color" => '',
				"Size" => '',
				"AgeGroup" => $item['age'],
				"Gender" => $item['gender'],
				"Material" => $item['google_material'],
				"ShippingPrice" => '3.75',
				"ShippingPriceWithAdditional" => '1',
				"AlaskaHawaiiShippingPrice" => '3.75',
				"AlaskaHawaiiShippingPriceWithAdditional" => '1',
				"PuertoRicoShippingPrice" => '3.75',
				"PuertoRicoShippingPriceWithAdditional" => '1',
				"EstimatedShippingDays" => '5',
				"OpenSkyMadeToOrder" => 'yes',
				"OpenSkyCustomizationPrompt" => '',
				"OpenSkyExclusive" => 'no',
				"Brand" => 'Moment Gear',
				"Image1" => '',
				"Image2" => '',
				"Return Policy" => 'standard',
				"Weight" => $item['weight'],
				"OpenSkyPublishStatus" => 'PUBLISHED'
				);
			if($item['weight'] > 0.75) {
				$currentItem['ShippingPrice'] = '7.5';
				$currentItem['AlaskaHawaiiShippingPrice'] = '7.5';
				$currentItem['PuertoRicoShippingPrice'] = '7.5';
			}
			if($childCount == 1){
				$currentItem['Color'] = $item['colors'];
				$currentItem['Quantity'] = '25';
				$currentItem['Price'] = $item['price'];
				$currentItem['Image1'] = $varShopifyImageFolder . $currentSKU . '-' . mg_image_color($item['colors']) . '.jpg';
				//add the current item to the openSky list
				if(!isset($openSkyList)) {
					$openSkyList = array();
					$openSkyList[] = array_keys($currentItem);
				}
				$openSkyList[] = $currentItem;
			} else {
				if ($item['sku'] == '4400' || $item['sku'] == '4411') {
					$currentItem['Title'] = str_replace("Onesie", "Romper", $currentItem['Title']);	
				}
				$currentItem['Color'] = 'Yes';
				$currentItem['Size'] = 'Yes';
				$currentItem['Image1'] = $varShopifyImageFolder . $currentSKU . '-' . mg_image_color($colors[rand(0, (count($colors) - 1))]) . '.jpg';
				$currentItem['Image2'] = $varShopifyImageFolder . 'sc-' . $item['sku'] . '.jpg';
				//add the current item to the openSky list
				if(!isset($openSkyList)) {
					$openSkyList = array();
					$openSkyList[] = array_keys($currentItem);
				}
				$openSkyList[] = $currentItem;
				
				foreach($colors as $color){
					foreach($sizes as $size){
						// copy the openSkyFields array as the keys for the child item
						$child = array_fill_keys(array_keys($currentItem), '');
						$child['Title'] = '';
						$child['ItemID'] = $currentSKU . '-' . mg_abbreviate($color) . '-' . mg_abbreviate($size);
						$child['GroupItemID'] = $currentItem['ItemID'];
						if(strlen($child['ItemID']) > 30) {
							$child['ItemID'] = str_replace('WhtFlkTrb', 'WhtTrb', $child['ItemID']);
							$child['ItemID'] = str_replace('BlckChrclTrb', 'BlkTrb', $child['ItemID']);
							$child['ItemID'] = str_replace('BlckS-DrkHthrGryB', 'BlckS-GryB', $child['ItemID']);
							if(strlen($child['ItemID']) > 30) {
								$child['ItemID'] = str_replace('BlkTrb', 'Blk', $child['ItemID']);
								$child['ItemID'] = str_replace('BlckS', 'BlkS', $child['ItemID']);
							}
						}
						$child['Quantity'] = "25";
						$child['Weight'] = $item['weight'];
						$child['Price'] = $item['price'];
						if (mg_color_map($color) == 'Black') {
							$child['Price'] += 5;
						}
						if ($size == 'XX-Large') {
							$child['Price'] += 2;		
						}
						if ($size == 'XXX-Large') {
							$child['Price'] += 3;		
						}
						if ($size == 'XXXX-Large') {
							$child['Price'] += 4;		
						}
						if ($size == '8 x 10') {
							$child['Price'] += 10;		
						}
						if ($size == '11 x 14') {
							$child['Price'] += 20;		
						}
						$child['Color'] = mg_color_map($color);
						$child['Size'] = $size;
						$child['Image1'] = $varShopifyImageFolder . $currentSKU . '-' . mg_image_color($color) . '.jpg';

						//add the current item to the openSky list
						$openSkyList[] = $child;
					}//end foreach size
				}//end foreach color
			} //end else
		} //end foreach design
	}
	if ($formType =='product') {
		$filename = "openSky_product_set_" . $varItems[0]['sku'] .".csv";
	} else {
		$filename = "openSky_product_set_" . $varDesigns[0]['sku'] .".csv";
	}
	
	$output = fopen("php://output",'w') or die("Can't open php://output");
	header("Content-type: application/csv; charset=UTF-8;");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	foreach($openSkyList as $row) {
		fputcsv($output, $row);
	}
	fclose($output) or die("Can't close php://output");
}

/**************************************************************************************
***************************************************************************************
**************************    Bonanza Export Function    ******************************
***************************************************************************************
**************************************************************************************/

function mg_bonanza_export() {
	global $varItem, $varDesigns, $varShopifyImageFolder;
	$sizes = explode(", ", $varItem['sizes']);
	$colors = explode(", ", $varItem['colors']);
	$childCount = (count($sizes) * count($colors));
	//prepare the list of colors for the parent product
	$colorList = '';
	foreach($colors as $c) {
		$colorList .= mg_color_map($c) . ", ";
	}
	if ($varItem['ebay_category']) {

		$logo = "<img src='" . $varShopifyImageFolder . "top_logo.png'>";
		$endText = "<h3>No Heat Transfers, Iron-Ons, or Decals used. All designs are printed on premium, soft, high quality garments using state of the art technology with eco-friendly water based ink which produces a smooth texture of the graphic which can hardly be felt with the touch of your hand. The ink used preserves image quality and prevents cracking when washed.</h3><h3>SHIPPING, RETURNS AND EXCHANGES</h3><p>We process and ship all orders in 1-3 business days. If an exchange or refund is needed, we kindly ask that you let us know within 14 fourteen days of receiving the item. You may send the shirt back and request the correct size and we will make you a new shirt and mail it to you. If for whatever reason you are not happy with the item and want to return it for a refund that is accepted as well. We will refund your order value but the buyer is liable for the postage.</p><p><b>*****OUR #1 priority is to have your experience be a memorable one each and every time you order from us. If you are not 100% satisfied, please contact or send a message ASAP and we will resolve the issue!*****</b></p>";
		// Array containing the Bonanza headers
		$bonanzaFields = array ("id","title","description","price","images","category","booth_category","quantity","trait");
		// Add the header array to an array. This will be the array we print.
		$bonanzaList = array ($bonanzaFields);
		// loop through each design
		foreach ($varDesigns as $design) {
			
			$currentItem = array (
				"id" => $varItem['sku'] . "-" . $design['sku'],
				"title" => $design['name'] . " " . $varItem['name'],
				"description" => '',
				"price" => '',
				"images" => '',
				"category" => $varItem['ebay_category'],
				"booth_category" => $varItem['google_product_type'],
				"quantity" => '',
				"trait" => '',
				);
			
			if ( $varItem['sku'] == '4400' || $varItem['sku'] == '4411' ) {
				$currentItem['title'] = str_replace("Onesie", "Romper", $currentItem['title']);	
			}
			// create the description by combining the logo, the title as an h1 header, the full description plus the size and color options, and the end text
			$bullets = "<li>Available Colors: " . $colorList . "</li><li>Available Sizes: " . $varItem['sizes'] . "</li></ul>";
			$currentItem['description'] = $logo . "<h1>" . $design['name'] . " " . $varItem['name'] . "</h1><p>" . str_replace(' item', ' ' . $varItem['item_type'], trim($design['description'])) . str_replace( "</ul>", $bullets, $varItem['description']) . $endText;
			
			
			//create the traits for each size and color 
			foreach($colors as $color) {
				$currentItem['images'] .= $varShopifyImageFolder . $currentItem['id'] . '-' . mg_image_color($color) . '.jpg,';
				foreach($sizes as $size) {
					$childSKU = $currentItem['id'] . '-' . mg_abbreviate($color) . '-' . mg_abbreviate($size);
					
					$price = $varItem['price'];
					if ($color == 'Vintage Black' || $color == 'Charcoal Black Triblend' || $color == 'Solid Black Triblend' || $color == 'Solid Black Blend' || $color == 'Black') {
						$price += 5;
					}
					if ($size == 'XX-Large') {
						$price += 2;		
					}
					if ($size == 'XXX-Large') {
						$price += 3;		
					}
					if ($size == 'XXXX-Large') {
						$price += 4;		
					}
					if ($size == '8 x 10') {
						$price += 10;		
					}
					if ($size == '11 x 14') {
						$price += 20;		
					}
					
					$currentItem['trait'] .= "[[color:" . $color . "][size:" . $size . "][price:" . $price . "][quantity:50][id=" . $childSKU . "]]";
				} //end foreach size
			} //end foreach color
			//check if this is not a bib, then add the size chart
			if ($varItem['sku'] != "1004") {
				$currentItem['images'] .= $varShopifyImageFolder . "sc-" . $varItem['sku'] . ".jpg";
			}
			//add the current item to the Bonanza list
			$bonanzaList[] = $currentItem;
		} //end foreach design

		$filename = "bonanza_product_set_" . $varItem['sku'] .".csv";

		$output = fopen("php://output",'w') or die("Can't open php://output");
		header("Content-type: application/csv; charset=UTF-8;");
		header("Content-Disposition: attachment; filename=$filename");
		header("Pragma: no-cache");
		header("Expires: 0");
		foreach($bonanzaList as $row) {
			fputcsv($output, $row);
		}
		fclose($output) or die("Can't close php://output");
	} else {
		$referer = $_SERVER['HTTP_REFERER'];
		header("Location: $referer");
	}
}

/**************************************************************************************
***************************************************************************************
***************************    Fancy Export Function    *******************************
***************************************************************************************
**************************************************************************************/

function mg_fancy_export() {
	global $varItems, $varDesigns, $varShopifyImageFolder, $formType;
	$fancyList = NULL; // This will be the array we print.

	foreach ($varItems as $item) {
		$sizes = explode(", ", $item['sizes']);
		$colors = explode(", ", $item['colors']);
		$childCount = (count($sizes) * count($colors));
		// loop through each design
		foreach ($varDesigns as $design) {
			
			$currentItem = array 
			(
				"title" => $design['name'] . " " . $item['name'],
			   	"description" => '<p>' . str_replace(' item', ' ' . $item['item_type'], trim($design['description'])) . $item['description'],
				"is_active" => "TRUE",
				"quantity"=> "",
				"price" => $item['price'],
				"image1_url" => "",
				"image2_url" => "",
				"image3_url" => "",
				"image4_url" => "",
				"categories" => $item['fancy_category'],
				"collections" => $design['tags'],
				"sale_start_date" => "",
				"seller_sku" => $item['sku'] . "-" . $design['sku'],
				"prod_colors" => "",
				"prod_country_of_origin" => "US",
				"prod_length" => "",
				"prod_height" => "",
				"prod_width" => "",
				"prod_weight" => "",
				"international_shipping" => "TRUE",
				"us_window_start" => "3",
				"us_window_end" => "5",
				"intl_start" => "7",
				"intl_end" => "10",
				"brand" => 'Moment Gear',
			);		
			
			if ($design['group'] == 'female-all' || $design['group'] == 'female-adult' || $design['group'] == 'female-all') {
				$currentItem['title'] = str_replace(' Mens', ' Unisex', $currentItem['title']);
				$currentItem['categories'] = str_ireplace('Men', 'Women', $currentItem['categories']);
				$currentItem['categories'] = str_ireplace('Boy', 'Girls', $currentItem['categories']);
			}
			if ($item['sku'] == '4400' || $item['sku'] == '4411') {
				$currentItem['title'] = str_replace("Onesie", "Romper", $currentItem['title']);	
			}
			
			if ($childCount == 1) {
				$currentItem['price'] = $item['price'];
				$currentItem['prod_colors'] = mg_color_map($item['colors']);
				$currentItem['image1_url'] = $varShopifyImageFolder . $currentItem['seller_sku'] . '-' . mg_image_color($item['colors']) . '.jpg';
			} else {
				$childNumber = 1;
				foreach ($colors as $color) {
					if(sizeof($colors) > 1 && $item['sku'] != '1004') {
						$currentItem['prod_colors'] .= mg_color_map($color) . ",";
					}
					foreach ($sizes as $size) {
						$currentItem["option_name$childNumber"] = str_replace('/', '&', $color) . " / " . $size;
						$currentItem["option_available_quantity$childNumber"] = '';
						$currentItem["option_sku$childNumber"] = $currentItem['seller_sku'] . '-' . mg_abbreviate($color) . '-' . mg_abbreviate($size);
						if ($color == 'Vintage Black' || $color == 'Charcoal Black Triblend' || $color == 'Solid Black Triblend' || $color == 'Solid Black Blend' || $color == 'Black') {
							$currentItem["option_price$childNumber"] = intval($item['price']) + 5;
						} else {	
							$currentItem["option_price$childNumber"] = $item['price'];
						}
						if ($size == 'XX-Large') {
							$currentItem["option_price$childNumber"] += 2;		
						}
						if ($size == 'XXX-Large') {
							$currentItem["option_price$childNumber"] += 3;		
						}
						if ($size == 'XXXX-Large') {
							$currentItem["option_price$childNumber"] += 4;		
						}
						if ($size == '8 x 10') {
							$currentItem['option_price$childNumber'] += 10;		
						}
						if ($size == '11 x 14') {
							$currentItem['option_price$childNumber'] += 20;		
						}
						++$childNumber;
					}
				}
				$currentItem['prod_colors'] = rtrim($currentItem['prod_colors'], ",");
				$colorNumber = 1;
				$imageArray = explode(", ", $item['colors']);
				shuffle($imageArray);
				foreach($imageArray as $image) {
					$currentItem["image" . $colorNumber . "_url"] = $varShopifyImageFolder . $currentItem['seller_sku'] . '-' . mg_image_color($image) . '.jpg';
					++$colorNumber;
				}
				if($item['sku'] != '1004') {
					$currentItem["image" . $colorNumber . "_url"] = $varShopifyImageFolder . "sc-" . $item['sku'] . ".jpg";
				}
			}
			if(!isset($fancyList)) {
				$fancyList = array();
				$fancyList[] = array_keys($currentItem);
			} elseif (count($fancyList[0]) < count(array_keys($currentItem))) {
				$fancyList[0] = array_keys($currentItem);
			}
			$fancyList[] = $currentItem;
		} //end foreach Design
	} //end foreach Item
	if ($formType == 'product') {
		$filename = "fancy_product_set_" . $varItems[0]['sku'] .".csv";
	} else {
		$filename = "fancy_product_set_" . $varDesigns[0]['sku'] .".csv";
	}
	
	$output = fopen("php://output",'w') or die("Can't open php://output");
	header("Content-type: application/csv; charset=UTF-8;");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	foreach($fancyList as $row) {
		fputcsv($output, $row);
	}
	fclose($output) or die("Can't close php://output");
}

/**************************************************************************************
***************************************************************************************
***************************    Wish Export Function    ******************************
***************************************************************************************
**************************************************************************************/

function mg_wish_export() {
	global $varItems, $varDesigns, $varShopifyImageFolder, $formType;

	foreach ($varItems as $item) {
		$sizes = explode(", ", $item['sizes']);
		$colors = explode(", ", $item['colors']);
		$childCount = (count($sizes) * count($colors));
		// loop through each design
		foreach ($varDesigns as $design) {
			
			$currentItem = array 
			(
				"Parent Unique ID" => $item['sku'] . "-" . $design['sku'],
				"Unique ID" => $item['sku'] . "-" . $design['sku'],
				"Product Name" => $design['name'] . " " . $item['name'],
				"Description" => str_replace(' item', ' ' . $item['item_type'], trim($design['description'])),
				"Price" => $item['price'],
				"Quantity" => '500',
				"Tags" => $design['tags'],
				"Main Image URL" => '',
				"Color" => '',
				"Size" => '',
				"Shipping" => '6',
				"Shipping Time" => '5-10'	,
				"Brand" => 'Moment Gear',
			);		
			
			if ($design['group'] == 'female-all' || $design['group'] == 'female-adult' || $design['group'] == 'female-all') {
				$currentItem['Product Name'] = str_replace(' Mens', ' Unisex', $currentItem['Product Name']);
			}
			
			if ($item['sku'] == '4400' || $item['sku'] == '4411') {
				$currentItem['Product Name'] = str_replace("Onesie", "Romper", $currentItem['Product Name']);	
			}
			
			if ($childCount == 1) {
				$currentItem['standard_price'] = $item['price'];
				$currentItem['Color'] = mg_wish_color_map($item['colors']);
				$currentItem['Main Image URL'] = $varShopifyImageFolder . $currentItem['Unique ID'] . '-' . mg_image_color($item['colors']) . '.jpg';
				
				if(!isset($wishList)) {
					$wishList = array();
					$wishList[] = array_keys($currentItem);
				}
				$wishList[] = $currentItem;
			} else {
				$currentItem['Main Image URL'] = $varShopifyImageFolder . $currentItem['Unique ID'] . '-' . mg_image_color($colors[rand(0, (count($colors) - 1))]) . '.jpg';
				if(!isset($wishList)) {
					$wishList = array();
					$wishList[] = array_keys($currentItem);
				}	
				$wishList[] = $currentItem;
				
				foreach ($colors as $color) {
					foreach ($sizes as $size) {
						foreach ( $currentItem as $key => $value )	{
							$child[$key] = $value;
						}
						$child['Unique ID'] .= '-' . mg_abbreviate($color) . '-' . mg_abbreviate($size);
						$child['Color'] = mg_wish_color_map($color)	;
						$child['Size'] = $size;
						$child['Main Image URL'] = $varShopifyImageFolder . $currentItem['Unique ID'] . '-' . mg_image_color($color) . '.jpg';
						if ($color == 'Vintage Black' || $color == 'Charcoal Black Triblend' || $color == 'Solid Black Triblend' || $color == 'Solid Black Blend' || $color == 'Black') {
							$child['Price'] += 5;
						}
						if ($size == 'XX-Large') {
							$child['Price'] += 2;		
						}
						if ($size == 'XXX-Large') {
							$child['Price'] += 3;		
						}
						if ($size == 'XXXX-Large') {
							$child['Price'] += 4;		
						}
						if ($size == '8 x 10') {
							$child["Price"] += 10;		
						}
						if ($size == '11 x 14') {
							$child["Price"] += 20;		
						}
											
						$wishList[] = $child;
					}
				}
			}
		} //end foreach Design
	} //end foreach Item
	if ($formType == 'product') {
		$filename = "wish_product_set_" . $varItems[0]['sku'] .".csv";
	} else {
		$filename = "wish_product_set_" . $varDesigns[0]['sku'] .".csv";
	}
	
	$output = fopen("php://output",'w') or die("Can't open php://output");
	header("Content-type: application/csv; charset=UTF-8;");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	foreach($wishList as $row) {
		fputcsv($output, $row);
	}
	fclose($output) or die("Can't close php://output");
}

/**************************************************************************************
***************************************************************************************
************************    Clear Product List Function    ****************************
***************************************************************************************
**************************************************************************************/

function mg_clear_products() {
		$referer = $_SERVER['HTTP_REFERER'];
		$path = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/uploads/wpallimport/files/";
		$old = $path . "mg-new-design-set.csv";
		$new = $path . "mg-new-design-list-" . date('m-d-Y-His-A-e') . ".csv";
		if (file_exists($old)){
			$fp = rename( $old , $new );
		}
		header("Location: $referer");
		print '<script type="text/javascript">'; 
		print 'alert("Product List cleared")'; 
		print '</script>';
}

/**************************************************************************************
***************************************************************************************
**********************  Add and Remove Designs from Database  *************************
***************************************************************************************
**************************************************************************************/

function mg_add_design() {
	global $varDesign, $wpdb;

	$update = $wpdb->replace(  $wpdb->prefix . 'print_designs', $varDesign	);

	if ($update === false) {
		$referer = $_SERVER['HTTP_REFERER'] . '&result=error';
	} else {
		$referer = $_SERVER['HTTP_REFERER'] . '&rows_updated=' . $update;
	}
	header("Location: $referer");
}

function mg_remove_design() {
	global $varDesign, $wpdb;

	$delete = $wpdb->delete(  $wpdb->prefix . 'print_designs', array( 'sku' => $varDesign['sku'])  );

	if ($delete === false) {
		$referer = $_SERVER['HTTP_REFERER'] . '&result=error';
	} else {
		$referer = $_SERVER['HTTP_REFERER'] . '&rows_deleted=' . $delete;
	}
	header("Location: $referer");
}


/**************************************************************************************
***************************************************************************************
**************************  Run the CSV Export Functions  *****************************
***************************************************************************************
**************************************************************************************/
try {
	if (isset($_POST['btnAmazon'])) {
		mg_amazon_export();
	} else if (isset($_POST['btnEbay'])) {
		mg_ebay_export();
	} else if (isset($_POST['btnOpenSky'])) {
		mg_opensky_export();
	} else if (isset($_POST['btnBonanza'])) {
		mg_bonanza_export();
	}  else if (isset($_POST['btnShopify'])) {
		mg_shopify_export();
	} else if (isset($_POST['btnFancy'])) {
		mg_fancy_export();
	} else if (isset($_POST['btnWish'])) {
		mg_wish_export();
	} else if (isset($_POST['btnClear'])) {
		mg_clear_products();
	} else if (isset($_POST['btnImages'])){
		mg_image_loop();
	} else if (isset($_POST['btnAddDesign'])){
		mg_add_design();
	} else if (isset($_POST['btnRemoveDesign'])){
		mg_remove_design();
	} else if (isset($_POST['btnExport'])){
		mg_main_export();
	}
}
//catch exception
catch(Exception $e) {
  echo 'Message: ' .$e->getMessage();
}
?>