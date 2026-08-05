<?php

namespace App\Repositories;

/**
 * PermissionRepository
 *
 * Retrieves role permissions from the remote API.
 * No local database connection is used — all data comes from https://civentral.tech/api/employee.
 */
class PermissionRepository
{
    public function __construct($db = null)
    {
        // $db is intentionally unused — this repository uses the remote API only.
    }

    public function getPermissionsByRoleId($roleId)
    {
        // Return from session cache if already loaded
        if (!empty($_SESSION['user_permissions_raw']) && isset($_SESSION['user_permissions_raw_role']) && (int)$_SESSION['user_permissions_raw_role'] === (int)$roleId) {
            return $_SESSION['user_permissions_raw'];
        }

        $configPath = __DIR__ . '/../../config/proxy.php';
        if (!function_exists('proxyRequest') && file_exists($configPath)) {
            require_once $configPath;
        }

        if (!function_exists('proxyRequest')) {
            return [];
        }

        $apiBaseUrl = getenv('EXPO_PUBLIC_API_BASE_URL') ?: 'https://civentral.tech/api/employee';
        $permissionsUrl = rtrim($apiBaseUrl, '/') . '/permissions.php';

        $res = proxyRequest($permissionsUrl, 'GET', null);

        if (empty($res['body']) || $res['code'] !== 200) {
            return [];
        }

        $body = $res['body'];
        $rolesPerms   = $body['role_permissions'] ?? [];
        $perms        = $body['permissions']      ?? [];
        $actions      = $body['actions']          ?? [];
        $resources    = $body['resources']        ?? [];

        // Build lookup maps
        $actionsMap = [];
        foreach ($actions as $a) {
            $actionsMap[$a['action_id']] = $a['action_name'];
        }

        $resourcesMap = [];
        foreach ($resources as $r) {
            $resourcesMap[$r['resource_id']] = $r['resource_name'];
        }

        $permsMap = [];
        foreach ($perms as $p) {
            $permsMap[$p['permission_id']] = [
                'action_id'   => $p['action_id'],
                'resource_id' => $p['resource_id']
            ];
        }

        // Filter permissions for the requested role
        $result = [];
        $targetRoleId = intval($roleId);
        foreach ($rolesPerms as $rp) {
            if (intval($rp['role_id']) !== $targetRoleId) continue;
            $pId = $rp['permission_id'];
            if (!isset($permsMap[$pId])) continue;
            $actId = $permsMap[$pId]['action_id'];
            $resId = $permsMap[$pId]['resource_id'];
            $result[] = [
                'action_name'   => $actionsMap[$actId]   ?? '',
                'resource_name' => $resourcesMap[$resId] ?? ''
            ];
        }

        // Cache in session for this request
        $_SESSION['user_permissions_raw']      = $result;
        $_SESSION['user_permissions_raw_role'] = $roleId;

        return $result;
    }
}
