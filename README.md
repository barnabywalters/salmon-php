salmon-php
==========

Some PHP classes for dealing with Salmon, both original code and modified forks of stuff which is elsewhere. Packaged up as a Composer package, all PSR-0 compliant for awesome autoloading.

Contains:

* Nat Sakimura's [php-magic-signatures](https://bitbucket.org/Nat/php-magic-signatures/overview), packaged up as an autoloadable class

## Installation

Install using [Composer](http://getcomposer.org). If you don't already have composer, download it per their instructions. Then:

1. Add `indieweb/salmon` to your project's `composer.json` file, so it looks a bit like this:
	
		{
			"require" : {
				"indieweb/salmon": "*"
			},
			"minimum-stability": "dev"
		}
	
	If you've never used composer, this is just specifying that your project needs the indieweb/salmon package in order to work, it doesn't matter which version you get, and it's all right to use packages which are in development.
1. Run `php composer.phar update`
1. Provided there were no errors, you should now have indieweb/salmon installed

## Usage

indieweb/push supports psr-0 autoloading, so using the classes is easy provided you're familiar with PHP namespaces.

	<?php
	// This script uses some indieweb/salmon code
	
	use indieweb\Salmon\MagicSignatures;
	
	$ms = new MagicSignatures;
	
	$json_magic_envelope = $ms -> sign('Here is some data', 'text/text', '/path/to/your/key/private.pem', '');
	
	if ($ms -> verify($json_magic_envelope, '/path/to/your/key/public.pem'))
	{
		// Verified! The envelope was signed by the issuer
	}
	else
	{
		// Verification failed - the envelope was either incorrectly handled or has been modified in transit!
	}