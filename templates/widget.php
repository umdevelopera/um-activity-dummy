<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<form name="post" action="" method="post" class="um-activity-dummy">

	<div class="uma-field input-text-wrap">
		<label for="uma-number" class="label">Number of posts</label>
		<input type="number" name="uma-number" min="1" max ="99" value="1">
	</div>

	<div class="uma-field">
		<label class="label">Content</label>
		<label><input type="checkbox" name="uma-content-text" value="1" checked="checked">Text</label>
		<label><input type="checkbox" name="uma-content-youtube" value="1">YouTube video</label>
		<label><input type="checkbox" name="uma-content-photo" value="1">Photo</label>
		<label><input type="checkbox" name="uma-content-emoji" value="1">Emoji</label>
		<label><input type="checkbox" name="uma-content-feeling" value="1">Feeling</label>
		<label><input type="checkbox" name="uma-content-privacy" value="1">Privacy</label>
	</div>

	<br>

	<p class="submit">
		<input type="hidden" name="action" value="um-activity-dummy">
		<?php wp_nonce_field( 'um-activity-dummy' ); ?>
		<input type="submit" name="uma-create" id="uma-create" class="button button-primary" value="Create Activity posts">
		<br class="clear">
	</p>

</form>