<?php

namespace App\DTO;

class ReviewDTO{
    public function __construct(
        public int $appointmentId,
        public string $comment,
        public int $rating,
        public int $userId
    ){}
}
