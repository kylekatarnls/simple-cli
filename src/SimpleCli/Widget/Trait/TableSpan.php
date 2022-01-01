<?php

declare(strict_types=1);

namespace SimpleCli\Widget\Trait;

trait TableSpan
{
    /**
     * @param array<int, array<int, true>> $spannedCells record of spanned cells for next/from previous rows
     * @param int                          $colSpan
     * @param int                          $rowSpan
     */
    protected function recordSpan(array &$spannedCells, int $colSpan, int $rowSpan): void
    {
        for ($rowIndex = 1; $rowIndex < $rowSpan; $rowIndex++) {
            if (!isset($spannedCells[$rowIndex])) {
                $spannedCells[$rowIndex] = [];
            }

            for ($colIndex = 0; $colIndex < $colSpan; $colIndex++) {
                $spannedCells[$rowIndex][$colIndex] = true;
            }
        }
    }

    /**
     * @param array<int, array<int, true>> $spannedCells record of spanned cells for next/from previous rows
     */
    protected function shiftSpan(array &$spannedCells): void
    {
        $shiftedSpannedCells = [];

        foreach ($spannedCells as $index => $row) {
            if (!$index) {
                continue;
            }

            $shiftedSpannedCells[$index - 1] = $row;
        }

        $spannedCells = $shiftedSpannedCells;
    }
}
