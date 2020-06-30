<?php

namespace Pkerrigan\Xray\Sampling\TargetRepository;

use AWS\Result;
use Aws\XRay\XRayClient;
use Pkerrigan\Xray\Sampling\Rule;
use Pkerrigan\Xray\Trace;

/**
 * Retrieves sampling targets from the AWS console
 *
 */
class AwsSdkTargetRepository
{

    /** @var XRayClient */
    private $xrayClient;

    public function __construct(XRayClient $xrayClient)
    {
        $this->xrayClient = $xrayClient;
    }


    /**
     * Get targets for all the Rules passed through with a specific client id
     * @param Rule[] $rules
     * @param $clientId
     * @return Target[]
     */
    public function getAll(array $rules, $clientId)
    {
        $segment = Trace::getInstance()->startSubsegment('AwsSdkTargetRepository::getAll');
        $awsRules = $this->convertRulesForAWSSDK($rules, $clientId);
        
        $awsSegment = Trace::getInstance()->startSubsegment('XRayClient::getSamplingTargets');
        $awsTargets = $this->xrayClient->getSamplingTargets($awsRules);
        $awsSegment->end();
        
        $targets = $this->assembleTargets($awsTargets);
        $segment->end();
        return $targets;
    }

    private function assembleTargets(Result $targetResults)
    {
        $segment = Trace::getInstance()->startSubsegment('AwsSdkTargetRepository::assembleTargets');
        if (!isset($targetResults['SamplingTargetDocuments'])) {
            $segment->end();
            return [];
        }

        $targetDocuments = [];
        foreach ($targetResults['SamplingTargetDocuments'] as $targetDocumentArray) {
            $targetDocuments[] = (new Target())
                ->setRuleName($targetDocumentArray['RuleName'])
                ->setRate($targetDocumentArray['FixedRate'])
                ->setInterval($targetDocumentArray['Interval'])
                ->setQuota($targetDocumentArray['ReservoirQuota'])
                ->setTtl(isset($targetDocumentArray['ReservoirQuotaTTL']) ?
                    $targetDocumentArray['ReservoirQuotaTTL']->getTimestamp() :
                    null);
        }
        $segment->end();
        return $targetDocuments;
    }

    /**
     * @param Rule[] $rules
     * @param $clientId
     * @return array[][]
     */
    private function convertRulesForAWSSDK(array $rules, $clientId)
    {
        $segment = Trace::getInstance()->startSubsegment('AwsSdkTargetRepository::convertRulesForAWSSDK');
        $documents = [];

        $now = time();

        foreach ($rules as $rule) {
            $statistics = $rule->snapshotStatistics();
            $documents[] = [
                'RuleName' => $rule->getName(),
                'ClientID' => $clientId,
                'RequestCount' => $statistics['requestCount'],
                'BorrowCount' => $statistics['borrowCount'],
                'SampledCount' => $statistics['sampledCount'],
                'Timestamp' => $now,
            ];
        }

        $segment->end();
        return ['SamplingStatisticsDocuments' => $documents];
    }
}
