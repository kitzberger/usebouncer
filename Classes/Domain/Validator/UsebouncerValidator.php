<?php

namespace Kitzberger\Usebouncer\Domain\Validator;

use In2code\Powermail\Domain\Validator\AbstractValidator;
use In2code\Powermail\Utility\LocalizationUtility;
use Kitzberger\Usebouncer\Service\Api;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UsebouncerValidator extends AbstractValidator
{
    /**
    * Check email via usebouncer
    */
    public function isValid(mixed $value): void
    {
        $mail = $value;

        if ($this->configuration['_enable'] ?? false) {
            foreach ($mail->getAnswers() as $answer) {
                if ($answer->getField()->isSenderEmail()) {
                    $senderEmail = trim($answer->getValue());
                    if (!empty($senderEmail)) {
                        $api = GeneralUtility::makeInstance(Api::class);
                        if ($api->checkMail($senderEmail) === false) {
                            $this->setErrorAndMessage(
                                $answer->getField(),
                                LocalizationUtility::translate(
                                    'usebouncer-doesnt-like-mail-address',
                                    'usebouncer'
                                )
                            );
                        }
                    }
                }
            }
        }
    }
}
