<?php

namespace App\Support\Authorization;

final class PermissionCatalog
{
    /**
     * @return array<string, array{group:string,label:string,description:string}>
     */
    public static function definitions(): array
    {
        return [
            'tenant.roles.manage' => [
                'group' => __('Administration'),
                'label' => __('Manage roles'),
                'description' => __('Create, edit, and delete roles for the active company.'),
            ],
            'tenant.users.manage' => [
                'group' => __('Administration'),
                'label' => __('Manage users'),
                'description' => __('Assign users and roles in the active company.'),
            ],
            'tenant.domains.manage' => [
                'group' => __('Administration'),
                'label' => __('Manage domains'),
                'description' => __('Manage domains linked to the company.'),
            ],
            'tenant.branding.manage' => [
                'group' => __('Administration'),
                'label' => __('Manage branding'),
                'description' => __('Edit the company logo, colors, and visual configuration.'),
            ],
            'games.view.entity' => [
                'group' => __('Games'),
                'label' => __('View games (entity)'),
                'description' => __('Access the company game list.'),
            ],
            'games.edit.entity' => [
                'group' => __('Games'),
                'label' => __('Edit games (entity)'),
                'description' => __('Create and modify company games.'),
            ],
            'games.view.content' => [
                'group' => __('Games'),
                'label' => __('View game content'),
                'description' => __('View internal game content filtered by company.'),
            ],
            'games.edit.content' => [
                'group' => __('Games'),
                'label' => __('Edit game content'),
                'description' => __('Modify internal game content filtered by company.'),
            ],
            'participants.view.entity' => [
                'group' => __('Participation'),
                'label' => __('View participants (entity)'),
                'description' => __('Access the company participant list.'),
            ],
            'participants.view.content' => [
                'group' => __('Participation'),
                'label' => __('View participant data'),
                'description' => __('View participant details limited to the company.'),
            ],
            'winners.view.entity' => [
                'group' => __('Participation'),
                'label' => __('View winners (entity)'),
                'description' => __('Access the company winners list.'),
            ],
            'winners.view.content' => [
                'group' => __('Participation'),
                'label' => __('View winner data'),
                'description' => __('View winner details limited to the company.'),
            ],
            'exports.run.entity' => [
                'group' => __('Operations'),
                'label' => __('Run exports'),
                'description' => __('Allow running data exports for the company.'),
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_keys(self::definitions());
    }

    /**
     * @return array<string, array<int, array{name:string,label:string,description:string}>>
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::definitions() as $name => $data) {
            $groups[$data['group']][] = [
                'name' => $name,
                'label' => $data['label'],
                'description' => $data['description'],
            ];
        }

        return $groups;
    }

    /**
     * @return array<int, string>
     */
    public static function defaultsForRole(string $roleName): array
    {
        return match ($roleName) {
            'tenant_admin' => self::names(),
            'tenant_manager' => [
                'games.view.entity',
                'games.edit.entity',
                'games.view.content',
                'games.edit.content',
                'participants.view.entity',
                'participants.view.content',
                'winners.view.entity',
                'winners.view.content',
                'exports.run.entity',
            ],
            'tenant_viewer' => [
                'games.view.entity',
                'games.view.content',
                'participants.view.entity',
                'participants.view.content',
                'winners.view.entity',
                'winners.view.content',
            ],
            default => [],
        };
    }
}
