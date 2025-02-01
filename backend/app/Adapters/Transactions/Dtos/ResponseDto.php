<?php
/** @noinspection SpellCheckingInspection */

namespace App\Adapters\Transactions\Dtos;

class ResponseDto
{
    public function __construct(
        public bool            $success,
        public string          $reference_number,
        public null|int|string $status_code = null,
        public ?string         $status_message = null,
        public ?string         $batch_id = null,
        public ?string         $expiry = null,
        public array           $extra_data = [],
        public ?string         $department_id = null,
    )
    {
    }
}
