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
                'group' => 'Administracion',
                'label' => 'Gestionar roles',
                'description' => 'Crear, editar y eliminar roles de la empresa activa.',
            ],
            'tenant.users.manage' => [
                'group' => 'Administracion',
                'label' => 'Gestionar usuarios',
                'description' => 'Asignar usuarios y roles en la empresa activa.',
            ],
            'tenant.domains.manage' => [
                'group' => 'Administracion',
                'label' => 'Gestionar dominios',
                'description' => 'Administrar dominios vinculados a la empresa.',
            ],
            'tenant.branding.manage' => [
                'group' => 'Administracion',
                'label' => 'Gestionar branding',
                'description' => 'Editar logo, colores y configuracion visual de la empresa.',
            ],
            'games.view.entity' => [
                'group' => 'Juegos',
                'label' => 'Ver juegos (entidad)',
                'description' => 'Acceso al listado de juegos de la empresa.',
            ],
            'games.edit.entity' => [
                'group' => 'Juegos',
                'label' => 'Editar juegos (entidad)',
                'description' => 'Crear y modificar juegos de la empresa.',
            ],
            'games.view.content' => [
                'group' => 'Juegos',
                'label' => 'Ver contenido de juegos',
                'description' => 'Ver contenido interno de juegos filtrado por empresa.',
            ],
            'games.edit.content' => [
                'group' => 'Juegos',
                'label' => 'Editar contenido de juegos',
                'description' => 'Modificar contenido interno de juegos filtrado por empresa.',
            ],
            'participants.view.entity' => [
                'group' => 'Participacion',
                'label' => 'Ver participantes (entidad)',
                'description' => 'Acceso al listado de participantes de la empresa.',
            ],
            'participants.view.content' => [
                'group' => 'Participacion',
                'label' => 'Ver datos de participantes',
                'description' => 'Ver detalle de participantes limitado a la empresa.',
            ],
            'winners.view.entity' => [
                'group' => 'Participacion',
                'label' => 'Ver ganadores (entidad)',
                'description' => 'Acceso al listado de ganadores de la empresa.',
            ],
            'winners.view.content' => [
                'group' => 'Participacion',
                'label' => 'Ver datos de ganadores',
                'description' => 'Ver detalle de ganadores limitado a la empresa.',
            ],
            'exports.run.entity' => [
                'group' => 'Operacion',
                'label' => 'Ejecutar exportaciones',
                'description' => 'Permite lanzar exportaciones de datos de la empresa.',
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
