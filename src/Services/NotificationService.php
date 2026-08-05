<?php

namespace App\Services;

/**
 * NotificationService
 *
 * Dispatches and retrieves notifications via the remote API.
 * No local database connection is used — all data comes from https://civentral.tech/api/employee.
 */
class NotificationService
{
    /**
     * Dispatch a notification triggered by an audited action.
     *
     * @param int         $auditId       The ID of the audit log record
     * @param string      $actionName    The audited action string (e.g., "Create User Account")
     * @param int         $actorUserId   The ID of the user who performed the action
     * @param int|null    $departmentId  The department related to the action
     * @param string|null $targetTable   The table impacted by the action
     * @param string|null $targetId      The record ID impacted
     * @param string|null $description   Full audit log description
     * @return bool
     */
    public static function dispatchFromAudit(
        int $auditId,
        string $actionName,
        int $actorUserId,
        ?int $departmentId,
        ?string $targetTable,
        ?string $targetId,
        ?string $description
    ): bool {
        try {
            // 1. Filter out routine / excluded actions (page views, standard logins, OTPs, etc.)
            $excludedPatterns = [
                '/view/i',
                '/export/i',
                '/download/i',
                '/login/i',
                '/logout/i',
                '/2fa/i',
                '/otp/i',
                '/timeout/i',
                '/session/i'
            ];

            foreach ($excludedPatterns as $pattern) {
                if (preg_match($pattern, $actionName)) {
                    return true;
                }
            }

            // 2. Resolve Priority
            $priority = self::resolvePriority($actionName, $targetTable);

            // 3. Generate Title & Message
            $title = self::generateTitle($actionName, $targetTable);

            // Actor name — use session data if actor is current user, otherwise use a generic label
            $actorName = 'System User';
            $currentUserId = intval($_SESSION['user_id'] ?? 0);
            if ($actorUserId > 0 && $actorUserId === $currentUserId) {
                $firstName = $_SESSION['first_name'] ?? ($_SESSION['current_user_details']['first_name'] ?? '');
                $lastName  = $_SESSION['last_name']  ?? ($_SESSION['current_user_details']['last_name'] ?? '');
                $fullName  = trim("{$firstName} {$lastName}");
                if (!empty($fullName)) {
                    $actorName = $fullName;
                }
            }

            if (!empty($description)) {
                $message = (stripos($description, $actorName) === false)
                    ? "{$actorName}: {$description}"
                    : $description;
            } else {
                $message = "{$actorName} performed '{$actionName}' on {$targetTable}"
                    . ($targetId ? " (ID: {$targetId})" : "");
            }

            // 4. Post notification to remote API
            self::ensureProxy();
            if (!function_exists('proxyRequest')) {
                return false;
            }

            $apiBaseUrl      = getenv('EXPO_PUBLIC_API_BASE_URL') ?: 'https://civentral.tech/api/employee';
            $notificationsUrl = rtrim($apiBaseUrl, '/') . '/notifications.php';

            $payload = [
                'audit_id'            => $auditId,
                'action_name'         => $actionName,
                'actor_user_id'       => $actorUserId,
                'department_id'       => $departmentId,
                'target_table'        => $targetTable,
                'target_id'           => $targetId,
                'title'               => $title,
                'message'             => $message,
                'priority'            => $priority,
                'notification_status' => 'Unread',
                'created_at'          => date('Y-m-d H:i:s')
            ];

            $result = proxyRequest($notificationsUrl, 'POST', $payload);
            return ($result['code'] >= 200 && $result['code'] < 300);

        } catch (\Throwable $e) {
            error_log("NotificationService Dispatch Failure: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Determine notification priority.
     */
    private static function resolvePriority(string $actionName, ?string $targetTable): string
    {
        $lower = strtolower($actionName);

        if (
            strpos($lower, 'delete') !== false ||
            strpos($lower, 'reject') !== false ||
            strpos($lower, 'lock') !== false ||
            strpos($lower, 'disable') !== false
        ) {
            return 'Critical';
        }

        if (
            strpos($lower, 'create') !== false ||
            strpos($lower, 'approve') !== false ||
            strpos($lower, 'grant') !== false
        ) {
            return 'High';
        }

        return 'Normal';
    }

    /**
     * Generate user-friendly notification title.
     */
    private static function generateTitle(string $actionName, ?string $targetTable): string
    {
        $object = $targetTable ? ucwords(str_replace('_', ' ', $targetTable)) : 'System';

        if (substr($object, -1) === 's' && $object !== 'Process') {
            $object = substr($object, 0, -1);
        }

        return "{$actionName} — {$object}";
    }

    /**
     * Fetch unread/read notifications for a user via the remote API.
     */
    public function getForUser(int $userId, int $limit = 25): array
    {
        self::ensureProxy();
        if (!function_exists('proxyRequest')) {
            return [];
        }

        $apiBaseUrl      = getenv('EXPO_PUBLIC_API_BASE_URL') ?: 'https://civentral.tech/api/employee';
        $notificationsUrl = rtrim($apiBaseUrl, '/') . '/notifications.php';
        $qs = http_build_query(['user_id' => $userId, 'limit' => $limit]);

        $result = proxyRequest("{$notificationsUrl}?{$qs}", 'GET', null);

        if ($result['code'] === 200 && is_array($result['body'])) {
            return $result['body']['data'] ?? $result['body'] ?? [];
        }

        return [];
    }

    /**
     * Get count of unread notifications for a user via the remote API.
     */
    public function getUnreadCount(int $userId): int
    {
        self::ensureProxy();
        if (!function_exists('proxyRequest')) {
            return 0;
        }

        $apiBaseUrl      = getenv('EXPO_PUBLIC_API_BASE_URL') ?: 'https://civentral.tech/api/employee';
        $notificationsUrl = rtrim($apiBaseUrl, '/') . '/notifications.php';
        $qs = http_build_query(['user_id' => $userId, 'filter' => 'unread', 'count' => 1]);

        $result = proxyRequest("{$notificationsUrl}?{$qs}", 'GET', null);

        if ($result['code'] === 200 && is_array($result['body'])) {
            return (int)($result['body']['total'] ?? $result['body']['count'] ?? 0);
        }

        return 0;
    }

    /**
     * Mark a single notification as read via the remote API.
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        self::ensureProxy();
        if (!function_exists('proxyRequest')) {
            return false;
        }

        $apiBaseUrl      = getenv('EXPO_PUBLIC_API_BASE_URL') ?: 'https://civentral.tech/api/employee';
        $notificationsUrl = rtrim($apiBaseUrl, '/') . '/notifications.php';

        $result = proxyRequest($notificationsUrl, 'PATCH', [
            'notification_id'     => $notificationId,
            'recipient_user_id'   => $userId,
            'notification_status' => 'Read',
            'read_at'             => date('Y-m-d H:i:s')
        ]);

        return ($result['code'] >= 200 && $result['code'] < 300);
    }

    /**
     * Mark all notifications as read for a user via the remote API.
     */
    public function markAllAsRead(int $userId): bool
    {
        self::ensureProxy();
        if (!function_exists('proxyRequest')) {
            return false;
        }

        $apiBaseUrl      = getenv('EXPO_PUBLIC_API_BASE_URL') ?: 'https://civentral.tech/api/employee';
        $notificationsUrl = rtrim($apiBaseUrl, '/') . '/notifications.php';

        $result = proxyRequest($notificationsUrl, 'PATCH', [
            'recipient_user_id'   => $userId,
            'mark_all'            => true,
            'notification_status' => 'Read',
            'read_at'             => date('Y-m-d H:i:s')
        ]);

        return ($result['code'] >= 200 && $result['code'] < 300);
    }

    /**
     * Ensure proxy.php is loaded so proxyRequest() is available.
     */
    private static function ensureProxy(): void
    {
        if (!function_exists('proxyRequest')) {
            $proxyPath = __DIR__ . '/../../config/proxy.php';
            if (file_exists($proxyPath)) {
                require_once $proxyPath;
            }
        }
    }
}
