<?php
/**
 * About view.
 *
 * @since 2.0.0
 */

?>

<style>
.wu-about-content a {
	text-decoration: none;
	font-weight: 500;
	color: #333;
}

.wu-about-content a::after {
	content: "↖︎";
	transform: scale(-0.7, 0.7);
	display: inline-block;
}
</style>

<a class="wu-fixed wu-inline-block wu-bottom-0 wu-left-1/2 wu-transform wu--translate-x-1/2 wu-bg-white wu-p-4 wu-rounded-full wu-shadow wu-m-4 wu-no-underline wu-z-10 wu-border-gray-300 wu-border-solid wu-border" href="<?php echo esc_attr(network_admin_url()); ?>">
	<?php esc_html_e('← Back to the Dashboard', 'multisite-ultimate'); ?>
</a>

<div id="wp-ultimo-wrap" class="wrap wu-about-content">

	<div style="max-width: 730px;" class="wu-max-w-screen-md wu-mx-auto wu-my-10 wu-p-12 wu-bg-white wu-shadow wu-text-justify">

	<p class="wu-text-lg wu-leading-relaxed">
		A new release strategy
	</p>

	<h1 class="wu-text-3xl">
		Here's <span class="wu-font-bold">Erasmo</span>:<br>
		WP Ultimo version 2.3.0
	</h1>

	<p class="wu-text-lg wu-leading-relaxed">
		Hi guys!
	</p>

	<p class="wu-text-lg wu-leading-relaxed">
		With WP Ultimo 2.3.0 we start a new streamlined approach of releases, focusing on a central feature in minors and rolling out fixes as soon as they are ready in patches. Less time waiting and leaner versions.
	</p>
	<p class="wu-text-lg wu-leading-relaxed">
		This way we intend to avoid a significant gap in time between updates and an extensive scope of changes and fixes, all bundled into one massive release.
	</p>
	<p class="wu-text-lg wu-leading-relaxed">
		This version focuses on allowing custom meta fields for customers in the admin area. This tool lets you collect additional information from your users. The potential for automation and WP Ultimo customizations is now expanded, as these fields may help tailor and streamline your operations.
	</p>
	<p class="wu-text-lg wu-leading-relaxed">
		We also added a bunch of improvements and fixes that go from more translated strings for Spanish, Brazilian Portuguese, and French, to better PHP 8.2 compatibility, to webhook triggering.
	</p>
	<div class="wu-inline-block wu-float-right wu-ml-8 wu-mb-4">
		<small class="wu-block wu-mt-1">Gilberto Gil</small>
	</div>
	<p class="wu-text-lg wu-leading-relaxed">
		This version is called Erasmo in honor of the Brazilian singer and songwriter 
		<a href="https://en.wikipedia.org/wiki/Erasmo_Carlos" target="_blank">
		Erasmo Carlos
		</a>
		, who left us in 2022, at the age of 81.
	</p>
	<p class="wu-text-lg wu-leading-relaxed">
		Erasmo was one of the faces of Jovem Guarda, a Brazilian musical TV show aired during the 1960’s. The show was highly influenced by American rock’n roll and the British Invasion of rock bands of that decade.
	</p>
	<p class="wu-text-lg wu-leading-relaxed">
		Erasmo's songs are about love, friendship, ecology, and many other subjects distributed along more than two dozen albums. Here, you can listen to one of his classic songs 
		<a href="https://www.youtube.com/watch?v=ICnivS25bDc" target="_blank">Minha fama de mau</a>.
		If you’re feeling more romantic, go for this version of 
		<a href="https://www.youtube.com/watch?v=I5KJyKsLNGk" target="_blank">Do fundo do meu coração</a>, 
		with Adriana Calcanhoto. And don’t forget to check out this 
		<a href="https://open.spotify.com/playlist/37i9dQZF1DZ06evO3F0tyd?si=6bb306446698495f" target="_blank">awesome playlist</a>.
	</p>
	<p class="wu-text-lg wu-leading-relaxed">
		As always, let me know if you have any questions.
	</p>
	<p class="wu-text-lg wu-leading-relaxed wu-mb-8">
		Yours truly,
	</p>

	<p class="wu-text-lg wu-leading-relaxed wu-mb-0">

		<?php
		echo get_avatar(
			'arindo@wpultimo.com',
			64,
			'',
			esc_attr('Arindo Duque'),
			[
				'class' => 'wu-rounded-full',
			]
		);
		?>

		<strong class="wu-block">Arindo Duque</strong>
		<small class="wu-block">Founder and CEO of NextPress, the makers of WP Ultimo</small>
	</p>

	</div>

	<div style="max-width: 700px;" class="wu-max-w-screen-md wu-mx-auto wu-mb-10">

	<hr class="hr-text wu-my-4 wu-text-gray-800" data-content="THIS VERSION WAS CRAFTED WITH LOVE BY">

	<?php

	$key_people = [
		'arindo'         => [
			'email'     => 'arindo@wpultimo.com',
			'signature' => 'arindo.png',
			'name'      => esc_attr('Arindo Duque'),
			'position'  => 'Founder and CEO',
		],
		'allyson'        => [
			'email'     => 'allyson@wpultimo.com',
			'signature' => '',
			'name'      => 'Allyson Souza',
			'position'  => 'Developer',
		],
		'anyssa'         => [
			'email'     => 'anyssa@wpultimo.com',
			'signature' => '',
			'name'      => 'Anyssa Ferreira',
			'position'  => 'Designer',
		],
		'gustavo'        => [
			'email'     => 'gustavo@wpultimo.com',
			'signature' => '',
			'name'      => 'Gustavo Modesto',
			'position'  => 'Developer',
		],
		'juliana'        => [
			'email'     => 'juliana@wpultimo.com',
			'signature' => '',
			'name'      => 'Juliana Dias Gomes',
			'position'  => 'Do-it-all',
		],
		'lucas-carvalho' => [
			'email'     => 'lucas@wpultimo.com',
			'signature' => '',
			'name'      => 'Lucas Carvalho',
			'position'  => 'Developer',
		],
		'yan'            => [
			'email'     => 'yan@wpultimo.com',
			'signature' => '',
			'name'      => 'Yan Kairalla',
			'position'  => 'Developer',
		],
	];

	?>

	<div class="wu-flex wu-flex-wrap wu-mt-8">

		<?php foreach ($key_people as $person) { ?>

		<div class="wu-text-center wu-w-1/4 wu-mb-5">

			<?php
			echo get_avatar(
				$person['email'],
				64,
				'',
				esc_attr('Arindo Duque'),
				[
					'class' => 'wu-rounded-full',
				]
			);
			?>
			<strong class="wu-text-base wu-block"><?php echo esc_html($person['name']); ?></strong>
			<small class="wu-text-xs wu-block"><?php echo esc_html($person['position']); ?></small>

		</div>

		<?php } ?>

	</div>

	</div>

</div>

<style>
.hr-text {
	line-height: 1em;
	position: relative;
	outline: 0;
	border: 0;
	/* color: black; */
	text-align: center;
	height: 1.5em;
	opacity: .5;
}
.hr-text:before {
	content: '';
	background: -webkit-gradient(linear, left top, right top, from(transparent), color-stop(#818078), to(transparent));
	background: linear-gradient(to right, transparent, #818078, transparent);
	position: absolute;
	left: 0;
	top: 50%;
	width: 100%;
	height: 1px;
}
.hr-text:after {
	content: attr(data-content);
	position: relative;
	display: inline-block;
	/* color: black; */
	padding: 0 .5em;
	line-height: 1.5em;
	color: #818078;
	background-color: #eef2f5;
}
</style>
