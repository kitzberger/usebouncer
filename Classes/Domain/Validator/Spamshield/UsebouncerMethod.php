<?php

namespace Kitzberger\Usebouncer\Domain\Validator\Spamshield;

use In2code\Powermail\Domain\Validator\SpamShield\AbstractMethod;
use Kitzberger\Usebouncer\Service\Api;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UsebouncerMethod extends AbstractMethod
{
    public function initialize(): void
    {
        $this->api = GeneralUtility::makeInstance(Api::class);
    }

    /**
     * @return bool true if spam recognized
     */
    public function spamCheck(): bool
    {
        foreach ($this->mail->getAnswers() as $answer) {
            if ($answer->getField()->isSenderEmail()) {
                $email = trim((string) $answer->getValue());
                if (empty($email)) {
                    return false;
                } else {
                    return $this->api->checkMail($email) === false;
                }
            }
        }

        return false;
    }
}
