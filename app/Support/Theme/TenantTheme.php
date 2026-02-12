<?php

namespace App\Support\Theme;

use App\Models\Tenant;

class TenantTheme
{
    public const DEFAULT_PRIMARY = '#1B84FF';

    public const DEFAULT_SECONDARY = '#F1F1F4';

    public const DEFAULT_NEUTRAL = '#7E8299';

    public const LIGHT_TEXT = '#FFFFFF';

    public const DARK_TEXT = '#1E2129';

    /**
     * @return array{
     *   primary:string,
     *   primary_rgb:string,
     *   primary_active:string,
     *   primary_active_rgb:string,
     *   primary_light:string,
     *   primary_inverse:string,
     *   primary_clarity:string,
     *   primary_text_emphasis:string,
     *   primary_bg_subtle:string,
     *   primary_border_subtle:string,
     *   secondary:string,
     *   secondary_rgb:string,
     *   secondary_active:string,
     *   secondary_active_rgb:string,
     *   secondary_light:string,
     *   secondary_inverse:string,
     *   secondary_clarity:string,
     *   secondary_text_emphasis:string,
     *   secondary_bg_subtle:string,
     *   secondary_border_subtle:string,
     *   neutral:string,
     *   border_soft:string,
     *   border_strong:string,
     *   link_hover:string,
     *   surface_start:string,
     *   surface_end:string,
     *   gradient_end:string,
     *   focus_ring:string
     * }
     */
    public static function fromTenant(?Tenant $tenant): array
    {
        $rawPrimary = is_string($tenant?->primary_color) ? $tenant->primary_color : null;
        $rawSecondary = is_string($tenant?->secondary_color) ? $tenant->secondary_color : null;

        $primary = self::normalizeHex($rawPrimary, self::DEFAULT_PRIMARY);
        $secondary = self::normalizeHex($rawSecondary, self::DEFAULT_SECONDARY);
        $hasCustomSecondary = self::isValidHex($rawSecondary);

        $primaryRgb = self::hexToRgb($primary);
        $secondaryRgb = self::hexToRgb($secondary);

        $primaryActive = self::shade($primary, -14);
        $primaryActiveRgb = self::hexToRgb($primaryActive);

        $secondaryActive = self::shade($secondary, -12);
        $secondaryActiveRgb = self::hexToRgb($secondaryActive);

        $gradientEnd = $hasCustomSecondary ? $secondary : self::tint($primary, 32);

        return [
            'primary' => $primary,
            'primary_rgb' => self::toRgbString($primaryRgb),
            'primary_active' => $primaryActive,
            'primary_active_rgb' => self::toRgbString($primaryActiveRgb),
            'primary_light' => self::tint($primary, 88),
            'primary_inverse' => self::contrastTextColor($primary),
            'primary_clarity' => self::toRgbaString($primaryRgb, 0.20),
            'primary_text_emphasis' => self::shade($primary, -58),
            'primary_bg_subtle' => self::tint($primary, 82),
            'primary_border_subtle' => self::tint($primary, 70),
            'secondary' => $secondary,
            'secondary_rgb' => self::toRgbString($secondaryRgb),
            'secondary_active' => $secondaryActive,
            'secondary_active_rgb' => self::toRgbString($secondaryActiveRgb),
            'secondary_light' => self::tint($secondary, 82),
            'secondary_inverse' => self::contrastTextColor($secondary),
            'secondary_clarity' => self::toRgbaString($secondaryRgb, 0.20),
            'secondary_text_emphasis' => self::shade($secondary, -52),
            'secondary_bg_subtle' => self::tint($secondary, 92),
            'secondary_border_subtle' => self::tint($secondary, 80),
            'neutral' => self::DEFAULT_NEUTRAL,
            'border_soft' => self::tint($primary, 89),
            'border_strong' => self::tint($primary, 78),
            'link_hover' => self::shade($primary, -18),
            'surface_start' => self::tint($primary, 97),
            'surface_end' => self::tint($hasCustomSecondary ? $secondary : $primary, $hasCustomSecondary ? 90 : 86),
            'gradient_end' => $gradientEnd,
            'focus_ring' => self::toRgbaString($primaryRgb, 0.25),
        ];
    }

    public static function contrastTextColor(?string $hex, string $dark = self::DARK_TEXT, string $light = self::LIGHT_TEXT): string
    {
        if (! self::isValidHex($hex)) {
            return $dark;
        }

        $rgb = self::hexToRgb((string) $hex);

        return self::relativeLuminance($rgb) > 0.55 ? $dark : $light;
    }

    protected static function normalizeHex(?string $hex, string $fallback): string
    {
        if (! self::isValidHex($hex)) {
            return $fallback;
        }

        return '#'.strtoupper(substr((string) $hex, 1));
    }

    protected static function isValidHex(?string $hex): bool
    {
        return is_string($hex)
            && preg_match('/^#[0-9a-fA-F]{6}$/', $hex) === 1;
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    protected static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * @param  array{0:int,1:int,2:int}  $rgb
     */
    protected static function rgbToHex(array $rgb): string
    {
        return sprintf('#%02X%02X%02X', $rgb[0], $rgb[1], $rgb[2]);
    }

    protected static function shade(string $hex, int $percent): string
    {
        $percent = max(-100, min(100, $percent));
        [$red, $green, $blue] = self::hexToRgb($hex);

        $adjust = static function (int $channel) use ($percent): int {
            if ($percent >= 0) {
                return (int) round($channel + ((255 - $channel) * ($percent / 100)));
            }

            return (int) round($channel * ((100 + $percent) / 100));
        };

        return self::rgbToHex([
            $adjust($red),
            $adjust($green),
            $adjust($blue),
        ]);
    }

    protected static function tint(string $hex, int $whitePercent): string
    {
        $whitePercent = max(0, min(100, $whitePercent));
        [$red, $green, $blue] = self::hexToRgb($hex);

        $mix = static fn (int $channel): int => (int) round(
            ($channel * (100 - $whitePercent) + 255 * $whitePercent) / 100
        );

        return self::rgbToHex([
            $mix($red),
            $mix($green),
            $mix($blue),
        ]);
    }

    /**
     * @param  array{0:int,1:int,2:int}  $rgb
     */
    protected static function toRgbString(array $rgb): string
    {
        return implode(',', $rgb);
    }

    /**
     * @param  array{0:int,1:int,2:int}  $rgb
     */
    protected static function toRgbaString(array $rgb, float $alpha): string
    {
        return sprintf('rgba(%d, %d, %d, %.2f)', $rgb[0], $rgb[1], $rgb[2], $alpha);
    }

    /**
     * @param  array{0:int,1:int,2:int}  $rgb
     */
    protected static function relativeLuminance(array $rgb): float
    {
        $channels = array_map(
            static function (int $channel): float {
                $value = $channel / 255;

                return $value <= 0.03928
                    ? $value / 12.92
                    : (($value + 0.055) / 1.055) ** 2.4;
            },
            $rgb
        );

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }
}
