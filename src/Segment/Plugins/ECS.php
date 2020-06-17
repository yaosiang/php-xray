<?php

namespace Pkerrigan\Xray\Segment\Plugins;

/**
 * Adds ECS data to the Segment
 *
 * Borrowed from:
 * https://github.com/aws/aws-xray-sdk-node/blob/master/packages/core/lib/segments/plugins/ecs_plugin.js
 *
 * Class ECS
 * @package Pkerrigan\Xray\Segment\Plugins
 */
class ECS implements Plugin
{

    public function getData()
    {
        return [
            'ecs' => [
                'container' => gethostname()
            ],
            'origin' => 'AWS::ECS::Container'
        ];
    }
}
