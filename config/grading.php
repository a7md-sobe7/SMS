<?php

declare(strict_types=1);

/**
 * Academic Grading Configuration
 * 
 * Defines the institutional grading scale, point weights, and GPA conversion table.
 * Separating this into configuration ensures grading policies can be adjusted without touching business logic.
 */
return [
    // Component Grade Weights (Must sum to 100%)
    'weights' => [
        'assignment' => 0.20, // 20%
        'midterm'    => 0.30, // 30%
        'final'      => 0.50, // 50%
    ],

    // Grade scale thresholds (Score >= Minimum Score)
    'scale' => [
        ['letter' => 'A',  'min' => 90.0, 'max' => 100.0, 'gpa' => 4.0, 'remark' => 'Excellent'],
        ['letter' => 'B+', 'min' => 85.0, 'max' => 89.99, 'gpa' => 3.5, 'remark' => 'Very Good'],
        ['letter' => 'B',  'min' => 80.0, 'max' => 84.99, 'gpa' => 3.0, 'remark' => 'Good'],
        ['letter' => 'C+', 'min' => 75.0, 'max' => 79.99, 'gpa' => 2.5, 'remark' => 'Above Average'],
        ['letter' => 'C',  'min' => 70.0, 'max' => 74.99, 'gpa' => 2.0, 'remark' => 'Average'],
        ['letter' => 'D',  'min' => 60.0, 'max' => 69.99, 'gpa' => 1.0, 'remark' => 'Pass'],
        ['letter' => 'F',  'min' => 0.0,  'max' => 59.99, 'gpa' => 0.0, 'remark' => 'Fail'],
    ],

    // Passing score threshold
    'passing_threshold' => 60.0,
];
