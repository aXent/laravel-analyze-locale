# Translation helpers for Laravel 5
-----
This package provides you with commands to analyze the translation keys used in your Laravel 5 application.

These commands can detect untranslated and invalid keys.

![Alt text](http://www.robinfranssen.be/screenshot0.png "Scan everything")

## Installation

```bash
composer require robinfranssen/analyzelocale --dev
```

Add the service provider:
```php
// config/app.php

'providers' => [
	...
	'RobinFranssen\AnalyzeLocale\Providers\ServiceProvider',
	...
],
```
## Usage

From the command line, run `php artisan locale:scan` to see a full overview of the analyzation. 
This will show you all information provided by the three other locale commands.

![Alt text](http://www.robinfranssen.be/screenshot1.png "Scan everything")

`php artisan locale:invalid` will show you the invalid keys.

![Alt text](http://www.robinfranssen.be/screenshot2.png "Scan invalid keys")

`php artisan locale:untranslated` will show you the untranslated keys.

![Alt text](http://www.robinfranssen.be/screenshot3.png "Scan untranslated keys")

`php artisan locale:allkeys` will show you a table with untranslated and invalid keys.

![Alt text](http://www.robinfranssen.be/screenshot4.png "scan all keys")

Every command supports the --locale flag. 
For example: `php artisan locale:allkeys --locale=nl`

![Alt text](http://www.robinfranssen.be/screenshot5.png "Scan with different locale")

