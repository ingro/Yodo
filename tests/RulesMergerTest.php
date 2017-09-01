<?php namespace Ingruz\Yodo\Test;

use Ingruz\Yodo\Helpers\RulesMerger;

class RulesMergerTest extends TestCase
{
    public function testShouldMergeRulesCorrectly()
    {
        $rules = [];
        $result = RulesMerger::merge($rules, 'create');

        $this->assertEquals($result, []);

        $rules = [
            'title' => 'required|min:10',
            'author' => 'alphanum'
        ];
        $result = RulesMerger::merge($rules, 'create');

        $this->assertEquals($result, $rules);

        $rules = [
            'save' => [
                'title' => 'required|unique',
                'author' => 'alphanum'
            ],
            'create' => [
                'title' => 'min:20'
            ]
        ];
        $result = RulesMerger::merge($rules, 'create');

        $this->assertEquals($result, [
            'title' => 'required|unique|min:20',
            'author' => 'alphanum'
        ]);
    }
}
