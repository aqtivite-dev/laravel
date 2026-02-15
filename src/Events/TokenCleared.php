<?php

namespace Aqtivite\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TokenCleared
{
    use Dispatchable, SerializesModels;
}
