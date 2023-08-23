<?php

namespace Kitzberger\Usebouncer\Domain\Validator;

use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Domain\Validator\AbstractValidator;
use In2code\Powermail\Utility\LocalizationUtility;
use Kitzberger\Usebouncer\Service\Api;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UsebouncerValidator extends AbstractValidator
{
    /**
    * Check email via usebouncer
    *
    * @param Mail $mail
    * @return Result
    */
    public function validate($mail)
    {
        $result = new Result();

        if ($this->configuration['_enable'] ?? false) {
            foreach ($mail->getAnswers() as $answer) {
                if ($answer->getField()->isSenderEmail()) {
                    $senderEmail = $answer->getValue();
                    $this->api = GeneralUtility::makeInstance(Api::class);
                    if ($this->api->checkMail($senderEmail)) {
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

        return $result;
    }
}
