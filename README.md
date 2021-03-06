<img src="https://img.mikeycomicsinc.com/cryptex_xl.png" width="300px">

# Cryptex: 2-way Authenticated Encryption Class

Cryptex is a simple PHP class that performs 2-way authenticated encryption using XChaCha20 + Poly1305.


# Requirements

* PHP 7.2.0 or newer


# Installation

The preferred method of installation is with Packagist and Composer. Run the following command to install the package and add it as a requirement to your project's composer.json:

`composer require michaelmawhinney/cryptex`


# Usage

**Always use a `$salt` value. Always store or transmit your `$secret_key` and `$salt` values securely.**

```php
<?php
require 'vendor/autoload.php';

use michaelmawhinney\Cryptex;

$plaintext = "You're a certified prince.";
$secret_key = "1-2-3-4-5"; // same combination on my luggage
$salt = random_bytes(32);

try {

    // Encrypt the plaintext
    $ciphertext = Cryptex::encrypt($plaintext, $secret_key, $salt);
    // example result: 
    // 4c406399a8830dbf670832b298980280d71bfb8cba53246ed45c9b6e6fc753bc100da3d10d4bf0d406d8afd18b8a5a79f44e50424ed0970914490706418c5725258e

    // Decrypt the ciphertext
    $result = Cryptex::decrypt($ciphertext, $secret_key, $salt);

} catch (Exception $e) {

    // There was some error during encryption, authentication, or decryption
    echo 'Caught exception: ' . $e->getMessage() . "\n";

}

// Verify with a timing attack safe string comparison
if (hash_equals($plaintext, $result)) {

    // Cryptex securely encrypted and decrypted the data
    echo "Pass";

} else {

    // There was some failure that did not generate any exceptions
    echo "Fail";

}

// The above example will output: Pass

?>
```
