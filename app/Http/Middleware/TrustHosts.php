<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts()
    {
        $localHosts = [
            'localhost',
            'localhost:*',
            '127.0.0.1',
            '127.0.0.1:*',
            '[::1]',
            '[::1]:*',
        ];

        if (app()->environment(['local', 'testing'])) {
            return $localHosts;
        }

        return [
            ...$localHosts,
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }
}
