<?php

namespace App\Repositories;

/**
 * UserRepository
 *
 * Retrieves user details from the remote API via session cache or proxy request.
 * No local database connection is used — all data comes from https://civentral.tech/api/employee.
 */
class UserRepository
{
    public function __construct($db = null)
    {
        // $db is intentionally unused — this repository uses the remote API only.
    }

    public function getUserWithRelations($userId, $employeeId = null)
    {
        // 1. Return from session cache if available (populated by verify-otp.php after login)
        if (!empty($_SESSION['current_user_details'])) {
            return $_SESSION['current_user_details'];
        }

        // 2. Fetch from remote API via get-profile.php proxy
        $configPath = __DIR__ . '/../../config/proxy.php';
        if (!function_exists('proxyRequest') && file_exists($configPath)) {
            require_once $configPath;
        }

        if (!function_exists('proxyRequest')) {
            return null;
        }

        $apiBaseUrl = getenv('EXPO_PUBLIC_API_BASE_URL') ?: 'https://civentral.tech/api/employee';
        $profileUrl = rtrim($apiBaseUrl, '/') . '/get-profile.php';

        $result = proxyRequest($profileUrl, 'GET', null);

        if (!empty($result['body']) && $result['code'] === 200) {
            $body = $result['body'];
            $data = null;

            if (isset($body['data']) && is_array($body['data'])) {
                $data = $body['data'];
            } elseif (isset($body['user']) && is_array($body['user'])) {
                $data = $body['user'];
            } elseif (isset($body['status']) && $body['status'] === 'success' && is_array($body)) {
                // Flatten: try the root body as user data
                $data = $body;
            }

            if ($data) {
                $_SESSION['current_user_details'] = $data;
                return $data;
            }
        }

        return null;
    }
}
