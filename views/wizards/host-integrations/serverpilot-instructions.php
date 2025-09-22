<?php
/**
 * ServerPilot instructions view.
 *
 * @since 2.1.2
 */
defined('ABSPATH') || exit;
?>
<article id="fullArticle">
	<h1 id="step-1-getting-a-serverpilot-api-key" class="intercom-align-left" data-post-processed="true">Step 1: Getting the API Key and the Client ID</h1>
	<p class="intercom-align-left">In Your ServerPilot admin panel, first go to the Account Settings page and navigate to the API link, there you can get the API Key and Client ID (if the API Key field is empty, click the New API Key button). <b>Paste those values somewhere as we'll need them in a later step.</b> </p>
	<div class="intercom-container intercom-align-left">
		<img class="wu-w-full" src="<?php echo esc_url(wu_get_asset('serverpilot-1.webp', 'img/hosts')); ?>">
	</div>

	<h1 id="step-2-get-the-server-id" class="intercom-align-left" data-post-processed="true">Step 2: Getting the App ID</h1>
	<p class="intercom-align-left">Next, we’ll need to get the App ID for your WordPress site. To find that ID, navigate to your app’s manage page:</p>
	<div class="intercom-container intercom-align-left">
		<img class="wu-w-full" src="<?php echo esc_url(wu_get_asset('serverpilot-2.webp', 'img/hosts')); ?>">
	</div>
	<p class="intercom-align-left">Then, take a look at the URL at the top of your browser. The APP ID is the portion between the app/ and the /settings segments of the URL.</p>

	<div class="intercom-container intercom-align-left">
		<img class="wu-w-full" src="<?php echo esc_url(wu_get_asset('serverpilot-3.webp', 'img/hosts')); ?>">
	</div>

	<p class="intercom-align-left">After this you can proceed to the next integration step where you can paste these values in the related fields.</p>

</article>
