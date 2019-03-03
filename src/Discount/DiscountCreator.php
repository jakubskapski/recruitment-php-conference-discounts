<?php

namespace RstGroup\Recruitment\ConferenceSystem\Discount;

use RstGroup\Recruitment\ConferenceSystem\Conference\ConferenceRepository;

class DiscountCreator
{
    private $conference;

    public function __construct($conferenceId)
    {
        $this->conference = $this->getConferencesRepository()->getConference($conferenceId);

        if ($this->conference === null)
            throw new \InvalidArgumentException(sprintf("Conference with id %s not exist", $conferenceId));
    }

    public function createGroupDiscount($attendants)
    {
        return new GroupDiscount($this->conference, $attendants);
    }

    public function createCodeDiscount($discountCode)
    {
        return new CodeDiscount($this->conference, $discountCode);
    }

    protected function getConferencesRepository()
    {
        return new ConferenceRepository();
    }
}