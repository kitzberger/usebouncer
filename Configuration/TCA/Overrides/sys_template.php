<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

ExtensionManagementUtility::addStaticFile(
    'usebouncer',
    'Configuration/TypoScript/Powermail',
    'Usebouncer for Powermail'
);
