<?php

namespace App\Service;

/**
 * LoadBalancer handles the selection of target hosts for load balancing.
 */
class LoadBalancer
{
    /**
     * Selects a target host from the provided list of targets.
     *
     * Implements round-robin algorithm with weighted distribution based on frequency of targets.
     *
     * @param string|array $targets
     * @return string
     */
    public function selectTarget(string|array $targets): string
    {
        if (is_string($targets)) {
            return $targets;
        }

        if (empty($targets)) {
            throw new \InvalidArgumentException('Targets array cannot be empty');
        }

        $targetCounts = array_count_values($targets);

        $weightedTargets = [];
        foreach ($targetCounts as $target => $count) {
            for ($i = 0; $i < $count; $i++) {
                $weightedTargets[] = $target;
            }
        }

        return $weightedTargets[array_rand($weightedTargets)];
    }
}
