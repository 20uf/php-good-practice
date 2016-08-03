<?php

/*
 * This file is part of the example good practice.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Criteria;

/**
 * Interface criteria
 *
 * @author Michael COULLERET <michael@coulleret.pro>
 */
interface Criteria
{
    public function apply(Request $request);
}
