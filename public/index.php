<?php

require('../vendor/djchen/pocket-api-php/src/Pocket.php');

require('../config.php');

$pocket = new Pocket($params);

if (isset($_GET['authorized'])) {
	// Convert the requestToken into an accessToken
	// Note that a requestToken can only be covnerted once
	// Thus refreshing this page will generate an auth error
	try {
		$user = $pocket->convertToken($_GET['authorized']);
	} catch (Exception $e) {
		// Authentication failed, redirect to homepage so the oath dance can be tried again
		header('Location: http://cronweekly-generator.test/');
		exit (0);
	}

	// Set the user's access token to be used for all subsequent calls to the Pocket API
	$pocket->setAccessToken($user['access_token']);

	// Retrieve the user's list of unread items (limit 5)
	// http://getpocket.com/developer/docs/v3/retrieve for a list of params
	$params = array(
		'state' => 'unread',
		'sort' => 'newest',
		'detailType' => 'simple',
	);

	$items = $pocket->retrieve($params, $user['access_token']);

?>
<html>
<head>
	<link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-white font-sans leading-normal tracking-normal text-lg">

<div class="container w-full md:max-w-4xl mx-auto mt-12">
	<h1 class="text-3xl text-gray-900 underline">Retrieved <?= count($items['list']) ?> bookmarks from Pocket</h1>

	<span class="text-gray-600 text-sm">If the content is OK, submit it below.</span>

	<form action="generate.php" method="POST" accept-charset="utf-8">

		<textarea name="markdown" class="block mt-8 py-4 pl-4 rounded shadow-lg text-sm bg-gray-200 text-gray-900 border-l-4 border-indigo-300 leading-loose w-full" rows="35">
<?php
	$markdown = "";

	foreach ($items['list'] as $id => $item) {
		$markdown .= "## [". $item['resolved_title'] ."](". $item['resolved_url'] .")\n";
		$markdown .= "\n";
		$markdown .= $item['excerpt'];
		$markdown .= "\n\n";
	}

	echo $markdown;
?>
		</textarea>

		<input type="submit" name="generate" value="Generate newsletter in markdown &raquo;" class="mt-8 mb-16 appearance-none text-white text-base font-semibold tracking-wide uppercase p-3 rounded shadow bg-blue-900 hover:bg-blue-800" />

	</form>
</div>

<?php

} else {
	// Attempt to detect the url of the current page to redirect back to
	// Normally you wouldn't do this
	$redirect = 'http://cronweekly-generator.test/?authorized=';

	// Request a token from Pocket
	$result = $pocket->requestToken($redirect);
	/*
		$result['redirect_uri']		this is the URL to send the user to getpocket.com to authorize your app
		$result['request_token']	this is the request_token which you will need to use to
						obtain the user's access token after they have authorized your app
	*/

	/*
	This is a hack to redirect back to us with the requestToken
	Normally you should save the 'request_token' in a session so it can be
	retrieved when the user is redirected back to you
	*/
	$result['redirect_uri'] = str_replace(
		urlencode('?authorized='),
		urlencode('?authorized=' . $result['request_token']),
		$result['redirect_uri']
	);
	// END HACK

	header('Location: ' . $result['redirect_uri']);
}

