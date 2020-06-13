<?php

namespace Pkerrigan\Xray\Sampling\RuleRepository;

use Pkerrigan\Xray\Sampling\Rule;

/**
 * Responsible for retrieving sampling rules
 *
 * @author Niklas Ekman <nikl.ekman@gmail.com>
 * @since 30/06/2019
 * @see https://docs.aws.amazon.com/xray/latest/devguide/xray-api-sampling.html
 */
interface RuleRepository
{
    /**
     * @return Rule[]
     */
    public function getAll();
}
