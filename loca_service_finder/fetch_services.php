<?php
// ============================================================
// fetch_services.php — Return services as JSON (AJAX endpoint)
// Supports: ?category=plumbing  ?search=keyword  ?id=5
// ============================================================
header('Content-Type: application/json');
require_once 'db.php';

$category = trim($_GET['category'] ?? '');
$search   = trim($_GET['search']   ?? '');
$id       = (int)($_GET['id']      ?? 0);

// ---- Single service ----
if ($id > 0) {
    $stmt = mysqli_prepare($conn,
        'SELECT s.*, u.name AS worker_name, u.phone AS worker_phone
         FROM services s
         JOIN users u ON u.id = s.user_id
         WHERE s.id = ?
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $service = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($service) {
        echo json_encode(['success' => true, 'data' => $service]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Service not found.']);
    }
    exit;
}

// ---- Build query for list ----
$where  = [];
$params = [];
$types  = '';

if ($category !== '' && $category !== 'all') {
    $where[]  = 's.category LIKE ?';
    $params[] = '%' . $category . '%';
    $types   .= 's';
}

if ($search !== '') {
    $where[]  = '(s.title LIKE ? OR s.category LIKE ? OR s.location LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT s.*, u.name AS worker_name
        FROM services s
        JOIN users u ON u.id = s.user_id
        {$whereClause}
        ORDER BY s.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);

if ($types && $params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result   = mysqli_stmt_get_result($stmt);
$services = [];

while ($row = mysqli_fetch_assoc($result)) {
    $services[] = $row;
}

mysqli_stmt_close($stmt);
echo json_encode(['success' => true, 'data' => $services, 'count' => count($services)]);
exit;
