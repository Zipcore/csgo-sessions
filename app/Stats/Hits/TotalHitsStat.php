<?php

namespace App\Stats\Hits;

use App\Stats\BaseSegmentedStat;

class TotalHitsStat extends BaseSegmentedStat
{
	protected $name = 'hits-total';
	
	function computeStat($weapon, $type, $hitgroup, $value)
	{
		if ($type !== 'hits')
			return;
		if (!is_numeric($this->cache))
			$this->cache = 0;

		$this->cache += $value;
	}
}