<?php

namespace RstGroup\Recruitment\ConferenceSystem\Discount;

class GroupDiscount implements DiscountInterface, ExcludesCodeDiscountInterface
{

    private $conference, $attendants;
    private $excludeCodeDiscount = false;

    public function __construct($conference, $attendants)
    {
        $this->conference = $conference;
        $this->attendants = $attendants;
    }

    public function calculate()
    {
        try {
            return $this->getCalculatedDiscount();
        } catch (DiscountException $e) {
            return 0;
        }
    }

    public function getExcludeCodeDiscount()
    {
        return $this->excludeCodeDiscount;
    }

    protected function getCalculatedDiscount()
    {
        $matchingDiscountPercent = $this->matchDiscount();

        $this->excludeCodeDiscount();

        return $matchingDiscountPercent;
    }

    private function matchDiscount()
    {
        $groupDiscount = $this->getGroupDiscount();

        $matchingDiscountPercent = 0;
        foreach ($groupDiscount as $minAttendantsCount => $discountPercent) {
            $discount = $this->getDiscountIfApplicable($minAttendantsCount, $discountPercent);
            if ($discount === false)
                break;

            $matchingDiscountPercent = new Percentage($discount);
        }

        return $matchingDiscountPercent;
    }

    private function getGroupDiscount()
    {
        $groupDiscount = $this->conference->getGroupDiscount();

        if (!is_array($groupDiscount)) {
            throw new DiscountException;
        }

        return $groupDiscount;
    }

    private function getDiscountIfApplicable($minAttendantsCount, $discountPercent)
    {
        if ($this->hasEnoughAttendants($minAttendantsCount)) {
            return $discountPercent;
        }

        return false;
    }

    private function hasEnoughAttendants($minAttendantsCount)
    {
        return $this->attendants >= $minAttendantsCount;
    }

    private function excludeCodeDiscount()
    {
        $this->excludeCodeDiscount = true;
    }
}