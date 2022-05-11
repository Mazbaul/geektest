<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlTest extends TestCase
{
    public function test_all_urls_without_auth_returns_successful_response(): void
    {
        $this->get('api/most-conversion')->assertOk();
        $this->get('api/transaction-info')->assertOk();
    }
}
