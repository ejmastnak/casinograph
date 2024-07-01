<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;

/**
 *  Use for Position update requests to require position_images.*.image when
 *  position_images.*.id is null (i.e. for new position images).
 */
class PositionImageRequiredIf implements ValidationRule, DataAwareRule
{

    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // A roundabout way to extract the image's index within
        // `position_images` using regex and attribute name; I don't know of a
        // native way. E.g. extracts "2" from "position_images.2.image".
        preg_match('/position_images\.(\d+)\.image/', $attribute, $matches);
        $idx = intval($matches[1]);

        // position_images.*.image is required if parallel position_image.*.id is null
        // I.e. fail on null position_images.*.id AND null position_images.*.image
        if (is_null($this->data['position_images'][$idx]['id']) && is_null($value)) {
            $fail("An image file is required for new position images.");
        }
    }
}
