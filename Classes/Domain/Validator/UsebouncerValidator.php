<?php

namespace Kitzberger\Usebouncer\Domain\Validator;

use In2code\Powermail\Domain\Validator\AbstractValidator;
use In2code\Powermail\Utility\LocalizationUtility;
use Kitzberger\Usebouncer\Service\Api;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

class UsebouncerValidator extends AbstractValidator
{
    /**
    * Check email via usebouncer
    */
    public function isValid(mixed $value): void
    {
        $mail = $value;
        $result = new Result();

        if ($this->configuration['_enable'] ?? false) {
            foreach ($mail->getAnswers() as $answer) {
                if ($answer->getField()->isSenderEmail()) {
                    $senderEmail = trim($answer->getValue());
                    if (!empty($senderEmail)) {
                        $this->api = GeneralUtility::makeInstance(Api::class);
                        if ($this->api->checkMail($senderEmail) === false) {
                            $result->addError(new Error(
                                LocalizationUtility::translate(
                                    'usebouncer-doesnt-like-mail-address',
                                    'usebouncer'
                                ),
                                1692805794,
                                ['marker' => $answer->getField()->getMarker()]
                            ));
                        }
                    }
                }
            }
        }
    }
}
