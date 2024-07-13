<?php

namespace okpt\furnics\project\AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('json_decode', [$this, 'jsonDecode']),
            new TwigFilter('average_rating', [$this, 'averageRating']),
            new TwigFilter('interger_part', [$this, 'getIntPart']),
            new TwigFilter('is_float', [$this, 'isFloat']),
        ];
    }

    public function jsonDecode(string $serialize)
    {
        return json_decode($serialize, true);
    }

    public function averageRating(array $allReviews): float|int
    {
        $sumOfRating = 0;
        for ($i= 0; $i < count($allReviews); $i++) {
            $sumOfRating += $allReviews[$i]->getRating();
        }
        return $sumOfRating / count($allReviews);
    }

    public function getIntPart($number)
    {
        return (int)floor($number); // Adjust the number of decimal places as needed
    }

    public function isFloat($number)
    {
        return is_float($number) == 1;
    }
}
