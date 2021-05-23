# Google Recaptcha


To install via composer, run:

```
composer require wobeto/google-recaptcha-checkbox
```

Register a new site on https://www.google.com/recaptcha/admin

Include the follow script to your site
```html
<script src="https://www.google.com/recaptcha/api.js?hl=pt-BR"></script>
```

Include the google div recaptcha inside form tag and the site key in the `data-sitekey` attribute
```html
<div class="g-recaptcha" data-sitekey="site-key"></div>
```

PHP server use:
```php
<?php

include 'vendor/autoload.php';

use Wobeto\GoogleRecaptchaCheckbox\Recaptcha;

try {
    $secret = '4Lchd-IvAAeAAIWBvzYvAEKu1chTimd0HJroP9T4';
    
    $recaptcha = new Recaptcha($secret);

    $verify = $recaptcha->verify($_POST['g-recaptcha-response']);

    var_dump($verify); //true
} catch (Exception $e) {
    echo $e->getMessage();
}
```

Enjoy...