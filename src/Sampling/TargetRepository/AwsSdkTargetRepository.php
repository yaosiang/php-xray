<?php

namespace Pkerrigan\Xray\Sampling\TargetRepository;

use AWS\Result;
use Aws\XRay\XRayClient;
use Pkerrigan\Xray\Sampling\Rule;

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
        return $this->assembleTargets(
            $this->xrayClient->getSamplingTargets($this->convertRulesForAWSSDK($rules, $clientId))
        );
    }

    private function assembleTargets(Result $targetResults)
    {
        if (!isset($targetResults['SamplingTargetDocuments'])) {
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

        return $targetDocuments;
    }

    /**
     * @param Rule[] $rules
     * @param $clientId
     * @return array[][]
     */
    private function convertRulesForAWSSDK(array $rules, $clientId)
    {
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

        return ['SamplingStatisticsDocuments' => $documents];
    }
}
