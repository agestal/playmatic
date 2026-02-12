<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Support\Theme\TenantTheme;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantThemeTest extends TestCase
{
    #[Test]
    public function it_falls_back_to_default_palette_without_a_tenant(): void
    {
        $theme = TenantTheme::fromTenant(null);

        $this->assertSame(TenantTheme::DEFAULT_PRIMARY, $theme['primary']);
        $this->assertSame(TenantTheme::DEFAULT_SECONDARY, $theme['secondary']);
        $this->assertSame(TenantTheme::DEFAULT_NEUTRAL, $theme['neutral']);
    }

    #[Test]
    public function it_uses_tenant_primary_and_secondary_colors_when_valid(): void
    {
        $tenant = new Tenant([
            'primary_color' => '#12ab34',
            'secondary_color' => '#5500ff',
        ]);

        $theme = TenantTheme::fromTenant($tenant);

        $this->assertSame('#12AB34', $theme['primary']);
        $this->assertSame('#5500FF', $theme['secondary']);
    }

    #[Test]
    public function it_uses_dark_text_for_light_primary_colors(): void
    {
        $tenant = new Tenant([
            'primary_color' => '#F4E65A',
            'secondary_color' => '#DDDDDD',
        ]);

        $theme = TenantTheme::fromTenant($tenant);

        $this->assertSame(TenantTheme::DARK_TEXT, $theme['primary_inverse']);
    }
}
