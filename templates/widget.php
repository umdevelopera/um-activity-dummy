<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<form name="post" action="" method="post" class="initial-form hide-if-no-js">

	<div class="input-number-wrap">
		<label for="uma-number">Number of posts</label>
		<input type="number" name="uma-number" id="uma-number" min="1" max ="99">
	</div>

	<br>

	<p class="submit">
		<input type="hidden" name="action" value="um-activity-dummy">
		<?php wp_nonce_field( 'um-activity-dummy' ); ?>
		<input type="submit" name="uma-create" id="uma-create" class="button button-primary" value="Create Activity posts">
		<br class="clear">
	</p>

</form>