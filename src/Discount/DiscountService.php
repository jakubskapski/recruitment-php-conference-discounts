<?php

namespace RstGroup\Recruitment\ConferenceSystem\Discount;

use RstGroup\Recruitment\ConferenceSystem\Conference\ConferenceRepository;

class DiscountService
{
    protected $allDiscounts = [
        'group', 'code'
    ];

    public function calculate($conferenceId, $attendantsCount = null, $price = null, $discountCode = null, $discountsTypes = [], &$is_error = false, &$error_message = null)
    {
        if (empty($discountsTypes)) {
            $discountsToProcess = $this->allDiscounts;
        } else {
            $discountsToProcess = array_intersect($this->allDiscounts, $discountsTypes);
        }

        $totalDiscount = 0;
        $excludeCodeDiscount = false;

        foreach ($discountsToProcess as $discount) {
            switch ($discount) {
                case 'group':
                    $conference = $this->getConferencesRepository()->getConference($conferenceId);

                    if ($conference === null) {
                        throw new \InvalidArgumentException(sprintf("Conference with id %s not exist", $conferenceId));
                    }

                    $groupDiscount = $conference->getGroupDiscount();

                    if (!is_array($groupDiscount)) {
                        $is_error = true;
                        $error_message = 'Error';
                        return;
                    }

                    $matchingDiscountPercent = 0;

                    foreach ($groupDiscount as $minAttendantsCount => $discountPercent) {
                        if ($attendantsCount >= $minAttendantsCount) {
                            $matchingDiscountPercent = $discountPercent;
                        }
                    }

                    $totalDiscount += $price * (float)"0.{$matchingDiscountPercent}";

                    $excludeCodeDiscount = true;

                    break;
                case 'code':
                    if ($excludeCodeDiscount == true) {
                        continue;
                    }

                    $conference = $this->getConferencesRepository()->getConference($conferenceId);

                    if ($conference === null) {
                        throw new \InvalidArgumentException(sprintf("Conference with id %s not exist", $conferenceId));
                    }

                    if ($conference->isCodeNotUsed($discountCode)) {
                        list($type, $discount) =  $conference->getDiscountForCode($discountCode);

                        if ($type == 'percent') {
                            $totalDiscount += $price * (float)"0.{$discount}";
                        } else if ($type == 'money') {
                            $totalDiscount += $discount;
                        } else {
                            $is_error = true;
                            $error_message = 'Error';
                            return;
                        }

                        $conference->markCodeAsUsed($discountCode);
                    }

                    break;
            }
        }

        return (float)$totalDiscount;
    }

    protected function getConferencesRepository()
    {
        return new ConferenceRepository();
    }
}