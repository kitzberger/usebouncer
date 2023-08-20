# EXT:usebouncer

This TYPO3 extension provides mail address validation via usebouncer.com as EXT:powermail spamshield.

## Installation

* Install this extension via composer: `composer require kitzberger/usebourcer`
* Set API key in extension settings
* Include TypoScript `Usebouncer for powermail`

This'll run a usebouncer.com check on the field that's defined as the 'sender email address'.

## Logger

Set this in your AdditionalConfiguration.php to activate logging for debugging purposes.

```php
$logWriterConf = [
    'Kitzberger' => [
        'Usebouncer' => [
            'writerConfiguration' => [
                \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
                    \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                        'logFileInfix' => 'usebouncer'
                    ],
                ],
            ],
        ],
    ],
];
$GLOBALS['TYPO3_CONF_VARS']['LOG'] = array_replace_recursive($GLOBALS['TYPO3_CONF_VARS']['LOG'], $logWriterConf);
```
