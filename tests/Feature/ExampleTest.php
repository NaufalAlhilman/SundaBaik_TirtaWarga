<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Route "/" selalu redirect ke halaman login (atau dashboard jika sudah login).
     * Status 302 adalah perilaku yang benar sesuai desain TirtaWarga.
     */
    public function test_root_mengarahkan_tamu_ke_halaman_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
