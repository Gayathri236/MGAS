elseif ($action === 'generate_tracking' && isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    
    // Generate tracking number
    $trackingNumber = 'TRK' . date('Ymd') . strtoupper(substr(uniqid(), -6)) . rand(10, 99);
    $trackingLink = "https://{$_SERVER['HTTP_HOST']}/track.html?code=" . urlencode($trackingNumber);
    
    // Update order with tracking info
    $stmt = $db->prepare("UPDATE orders SET tracking_number = ?, tracking_link = ? WHERE id = ?");
    
    if ($stmt->execute([$trackingNumber, $trackingLink, $order_id])) {
        response([
            'success' => true,
            'message' => 'Tracking link generated successfully',
            'tracking_number' => $trackingNumber,
            'tracking_link' => $trackingLink
        ]);
    } else {
        response(['error' => 'Failed to generate tracking link'], 500);
    }
}