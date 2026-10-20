<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ===== SUPER_ADMIN ONLY =====
if (strtoupper($_SESSION['role_name'] ?? '') !== 'SUPER_ADMIN') {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Only a Super Admin can change user roles.'];
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetUserId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $newRoleId    = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);

    if (!$targetUserId || !$newRoleId) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid request.'];
        header("Location: index.php#users-section");
        exit();
    }

    // Never allow changing your own role
    if ($targetUserId === (int)$_SESSION['user_id']) {
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'You cannot change your own role.'];
        header("Location: index.php#users-section");
        exit();
    }

    // ===== Find the SUPER_ADMIN role id (used for both locks below) =====
    $stmt = $conn->prepare("SELECT role_id FROM roles WHERE UPPER(role_name) = 'SUPER_ADMIN' LIMIT 1");
    $stmt->execute();
    $superRow = $stmt->get_result()->fetch_assoc();
    $superAdminRoleId = $superRow ? (int)$superRow['role_id'] : 0;

    // ===== Lock 1: nobody can be assigned the SUPER_ADMIN role =====
    if ($newRoleId === $superAdminRoleId) {
        $_SESSION['flash'] = ['type' => 'warning',
            'message' => 'Only one Super Admin is allowed, and that account cannot be changed.'];
        header("Location: index.php#users-section");
        exit();
    }

    // ===== Verify the target user exists =====
    $stmt = $conn->prepare("SELECT role_id FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $targetUserId);
    $stmt->execute();
    $target = $stmt->get_result()->fetch_assoc();

    if (!$target) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'User not found.'];
        header("Location: index.php#users-section");
        exit();
    }

    // ===== Lock 2: the SUPER_ADMIN account itself cannot be modified =====
    if ($superAdminRoleId > 0 && (int)$target['role_id'] === $superAdminRoleId) {
        $_SESSION['flash'] = ['type' => 'warning',
            'message' => 'The Super Admin account is locked and cannot be edited.'];
        header("Location: index.php#users-section");
        exit();
    }

    // Verify the chosen role is real (never trust the posted role_id)
    $stmt = $conn->prepare("SELECT role_name FROM roles WHERE role_id = ?");
    $stmt->bind_param('i', $newRoleId);
    $stmt->execute();
    $roleRow = $stmt->get_result()->fetch_assoc();
    if (!$roleRow) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid role selected.'];
        header("Location: index.php#users-section");
        exit();
    }

    // Update the role
    $stmt = $conn->prepare("UPDATE users SET role_id = ? WHERE user_id = ?");
    $stmt->bind_param('ii', $newRoleId, $targetUserId);
    $stmt->execute();

    $_SESSION['flash'] = ['type' => 'success',
        'message' => 'Role updated: ' . $roleRow['role_name'] . '. The change takes effect on that user\'s next login.'];
}

header("Location: index.php#users-section");
exit();