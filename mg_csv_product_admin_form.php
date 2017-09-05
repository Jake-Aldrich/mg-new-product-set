<div class="wrap">
	<h2>New Product Set</h2>
 	<form id="newProductSetForm" action="<?php echo plugin_dir_url( __FILE__ ); ?>mg_csv_export.php" method="post">
		<h3>Print Item</h3>
		<select id='printItem' name="printItem">
			<?php
				global $wpdb;
				$printitems = $wpdb->get_results( "SELECT sku FROM " . $wpdb->prefix . "print_items");

				foreach ( $printitems as $printitem ) 
				{
					echo '<option value="' . $printitem->sku . '">' . $printitem->sku . '</option>';
				}
			?>
		</select>
		<input type="hidden" name="formType" value="product" />
		<h3>Export CSVs</h3>
		<input type="submit" name="btnAmazon" value="Create Amazon Import">
		<input type="submit" name="btnEbay" value="Create eBay Import">
		<input type="submit" name="btnOpenSky" value="Create OpenSky Import">
		<br>
		<input type="submit" name="btnFancy" value="Create Fancy Import">
		<input type="submit" name="btnWish" value="Create Wish Import">
		<input type="submit" name="btnShopify" value="Create Shopify Import">
		<h3>Create Images</h3>
		<input type="submit" name="btnImages" value="Create Images">
	</form>
</div>