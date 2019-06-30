<?php
namespace Pkerrigan\Xray;

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    /**
     * 
     * @dataProvider provideMatchesCriteria
     */
    public function testMatchesCriterai($criteria, $input, $expected)
    {
        $this->assertEquals($expected, Utils::matchesCriteria($criteria, $input));
    }
    
    public function provideMatchesCriteria()
    {
        return [
            "Single-character wildcard (?)" => [
                "T?st",
                "Test",
                true
            ],
            "Single-character wildcard (?)" => [
                "T?st",
                "Tast",
                true
            ],
            "Single-character wildcard (?)" => [
                "T?st",
                "Testo",
                false
            ],
            "Multi-character wildcard (*)" => [
                "T*st",
                "Test",
                true
            ],
            "Multi-character wildcard (*)" => [
                "T*st",
                "Teest",
                true
            ],
            "Multi-character wildcard (*)" => [
                "T*st",
                "Best",
                false
            ],
            "One wildcard character matches anything" => [
                "*",
                "",
                true
            ],
            "Case insensitivity" => [
                "test",
                "Test",
                true
            ],
            "Protect against arbitray regex" => [
                "(Test){2}",
                "TestTest",
                false
            ]
        ];
    }
}
