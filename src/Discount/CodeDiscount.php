<?php

namespace RstGroup\Recruitment\ConferenceSystem\Discount;

class CodeDiscount implements DiscountInterface
{
    private $conference, $discountCode;

    public function __construct($conference, $discountCode)
    {
        $this->conference = $conference;
        $this->discountCode = $discountCode;
    }

    public function calculate()
    {
        try {
            return $this->getCalculatedDiscount();
        } catch (DiscountException $e) {
            return 0;
        }
    }

    protected function getCalculatedDiscount()
    {
        if ($this->conference->isCodeNotUsed($this->discountCode) === false)
            return 0;

        $discountObject = $this->getDiscountByCodeType();

        $this->conference->markCodeAsUsed($this->discountCode);

        return $discountObject;
    }

    private function getDiscountByCodeType()
    {
        list($type, $discount) = $this->conference->getDiscountForCode($this->discountCode);

        if ($type === 'percent') {
            return new Percentage($discount);
        } else if ($type === 'money') {
            return new Value($discount);
        } else {
            throw new DiscountException;
        }
    }
}