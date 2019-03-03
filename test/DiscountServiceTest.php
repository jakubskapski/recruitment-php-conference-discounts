<?php

namespace RstGroup\Recruitment\ConferenceSystem\Test;

use RstGroup\Recruitment\ConferenceSystem\Conference\Conference;
use RstGroup\Recruitment\ConferenceSystem\Discount\DiscountCreator;
use RstGroup\Recruitment\ConferenceSystem\Discount\DiscountService;
use RstGroup\Recruitment\ConferenceSystem\Discount\GroupDiscount;
use RstGroup\Recruitment\ConferenceSystem\Discount\CodeDiscount;

class DiscountServiceTest extends \PHPUnit_Framework_TestCase
{
    /* @var DiscountCreator $discountCreator */
    private $discountCreator;
    /* @var DiscountService $discountService */
    private $discountService;
    /* @var Conference $conference */
    private $conference;

    public function setUp()
    {
        $this->conference = $this->getMock(Conference::class);
        $this->setDiscountCreatorMock();
    }

    public function test_Calculate_HundredPercentGroupDiscount_ReturnsThreeThousands()
    {
        $attendants = 20;
        $this->setDiscountService(3000);
        $this->setGroupDiscountMock([5 => 10, 10 => 20, 20 => 100]);
        $this->setCreatorToExpectGroupDiscount($attendants);

        $groupDiscount = $this->discountCreator->createGroupDiscount($attendants);
        $this->discountService->addDiscount($groupDiscount);

        $discount = $this->discountService->calculate();

        $this->assertSame(3000.0, $discount);
    }

    public function test_Calculate_GroupDiscountForThreeAttendants_ReturnsZero()
    {
        $attendants = 3;
        $this->setDiscountService(3000);
        $this->setGroupDiscountMock([5 => 10, 10 => 20]);
        $this->setCreatorToExpectGroupDiscount($attendants);

        $groupDiscount = $this->discountCreator->createGroupDiscount($attendants);
        $this->discountService->addDiscount($groupDiscount);

        $discount = $this->discountService->calculate();

        $this->assertSame(0, $discount);
    }

    public function test_Calculate_GroupDiscountForSixAttendants_ReturnsSixHundreds()
    {
        $attendants = 6;
        $this->setDiscountService(6000);
        $this->setGroupDiscountMock([5 => 10, 10 => 20]);
        $this->setCreatorToExpectGroupDiscount($attendants);

        $groupDiscount = $this->discountCreator->createGroupDiscount($attendants);
        $this->discountService->addDiscount($groupDiscount);

        $discount = $this->discountService->calculate();

        $this->assertSame(600.0, $discount);
    }

    public function test_Calculate_GroupDiscountForTenAttendants_ReturnsTwoThousands()
    {
        $attendants = 10;
        $this->setDiscountService(10000);
        $this->setGroupDiscountMock([5 => 10, 10 => 20]);
        $this->setCreatorToExpectGroupDiscount($attendants);

        $groupDiscount = $this->discountCreator->createGroupDiscount($attendants);
        $this->discountService->addDiscount($groupDiscount);

        $discount = $this->discountService->calculate();

        $this->assertSame(2000.0, $discount);
    }

    public function test_Calculate_MoneyCodeDiscount_ReturnsHundredFifty()
    {
        $discountCode = 'abc123';
        $this->setDiscountService(1000);
        $this->setNotUsedCodeDiscountMock($discountCode, ['money', 150]);
        $this->setCreatorToExpectCodeDiscount($discountCode);

        $codeDiscount = $this->discountCreator->createCodeDiscount($discountCode);
        $this->discountService->addDiscount($codeDiscount);

        $discount = $this->discountService->calculate();

        $this->assertSame(150.0, $discount);
    }

    public function test_Calculate_PercentCodeDiscount_ReturnsFiveHundreds()
    {
        $discountCode = 'abc123';
        $this->setDiscountService(1000);
        $this->setNotUsedCodeDiscountMock($discountCode, ['percent', 50]);
        $this->setCreatorToExpectCodeDiscount($discountCode);

        $codeDiscount = $this->discountCreator->createCodeDiscount($discountCode);
        $this->discountService->addDiscount($codeDiscount);

        $discount = $this->discountService->calculate();

        $this->assertSame(500.0, $discount);
    }

    public function test_Calculate_UsedCodeDiscount_ReturnsZero()
    {
        $discountCode = 'qwe987';
        $this->setDiscountService(1000);
        $this->setUsedCodeDiscountMock($discountCode);
        $this->setCreatorToExpectCodeDiscount($discountCode);

        $codeDiscount = $this->discountCreator->createCodeDiscount($discountCode);
        $this->discountService->addDiscount($codeDiscount);

        $discount = $this->discountService->calculate();

        $this->assertSame(0, $discount);
    }

    public function test_Calculate_GroupAndDiscount_ReturnsSevenHundreds()
    {
        $attendants = 7;
        $discountCode = 'asd123';
        $this->setDiscountService(7000);
        $this->setGroupDiscountMock([5 => 10, 10 => 20]);
        $this->setCreatorToExpectGroupDiscount($attendants);

        $groupDiscount = $this->discountCreator->createGroupDiscount($attendants);
        $this->discountService->addDiscount($groupDiscount);

        $this->setNotUsedCodeDiscountMockThatNotApplies($discountCode, ['money', 150]);
        $this->setCreatorToExpectCodeDiscount($discountCode);

        $codeDiscount = $this->discountCreator->createCodeDiscount($discountCode);
        $this->discountService->addDiscount($codeDiscount);

        $discount = $this->discountService->calculate();

        $this->assertSame(700.0, $discount);
    }

    public function test_Calculate_DiscountAndGroup_ReturnsSevenHundreds()
    {
        $attendants = 7;
        $discountCode = 'asd123';
        $this->setDiscountService(7000);
        $this->setGroupDiscountMock([5 => 10, 10 => 20]);
        $this->setCreatorToExpectGroupDiscount($attendants);

        $this->setNotUsedCodeDiscountMockThatNotApplies($discountCode, ['money', 150]);
        $this->setCreatorToExpectCodeDiscount($discountCode);

        $codeDiscount = $this->discountCreator->createCodeDiscount($discountCode);
        $this->discountService->addDiscount($codeDiscount);

        $groupDiscount = $this->discountCreator->createGroupDiscount($attendants);
        $this->discountService->addDiscount($groupDiscount);

        $discount = $this->discountService->calculate();

        $this->assertSame(700.0, $discount);
    }

    private function setDiscountCreatorMock()
    {
        $this->discountCreator = $this->getMockBuilder(DiscountCreator::class)->disableOriginalConstructor()->getMock();
    }

    private function setCreatorToExpectGroupDiscount($attendants)
    {
        $group = new GroupDiscount($this->conference, $attendants);

        $this->discountCreator->expects($this->once())
            ->method('createGroupDiscount')
            ->with($attendants)
            ->willReturn($group);
    }

    private function setCreatorToExpectCodeDiscount($code)
    {
        $group = new CodeDiscount($this->conference, $code);

        $this->discountCreator->expects($this->once())
            ->method('createCodeDiscount')
            ->with($code)
            ->willReturn($group);
    }

    private function setDiscountService($price)
    {
        $this->discountService = new DiscountService($price);
    }

    private function setGroupDiscountMock($groupDiscounts)
    {
        $this->conference->expects($this->once())
            ->method('getGroupDiscount')
            ->willReturn($groupDiscounts);
    }

    private function setUsedCodeDiscountMock($code)
    {
        $this->conference->expects($this->at(0))
            ->method('isCodeNotUsed')
            ->with($code)
            ->willReturn(false);
    }

    private function setNotUsedCodeDiscountMock($code, $codeDiscount)
    {
        $this->conference->expects($this->once())
            ->method('isCodeNotUsed')
            ->with($code)
            ->willReturn(true);

        $this->conference->expects($this->once())
            ->method('getDiscountForCode')
            ->willReturn($codeDiscount);

        $this->conference->expects($this->once())
            ->method('markCodeAsUsed')
            ->with($code);
    }

    private function setNotUsedCodeDiscountMockThatNotApplies($code, $codeDiscount)
    {
        $this->conference->expects($this->never())
            ->method('isCodeNotUsed')
            ->with($code)
            ->willReturn(true);

        $this->conference->expects($this->never())
            ->method('getDiscountForCode')
            ->willReturn($codeDiscount);

        $this->conference->expects($this->never())
            ->method('markCodeAsUsed')
            ->with($code);
    }
}