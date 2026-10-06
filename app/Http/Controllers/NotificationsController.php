<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    /**
     * Display the notifications page.
     */
    public function page()
    {
        $notifications = $this->generateNotifications();
        
        return view('pages.notifications', ['notifications' => $notifications]);
    }
    
    /**
     * Get notifications list (API).
     */
    public function index()
    {
        $notifications = $this->generateNotifications();
        return response()->json($notifications);
    }

    /**
     * Generate notifications based on inventory status.
     */
    public function generateNotifications()
    {
        $notifications = [];

        // 1. Low stock alerts
        $lowStockItems = InventoryItem::lowStock()->get();
        foreach ($lowStockItems as $item) {
            $notifications[] = [
                'id' => 'low-stock-' . $item->id,
                'type' => 'warning',
                'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'title' => 'Low Stock Alert',
                'message' => "{$item->name} is running low on stock. Current: {$item->stock_quantity} {$item->unit}, Reorder level: {$item->reorder_level} {$item->unit}",
                'time' => 'Just now',
                'action_text' => 'Restock Now',
                'action_url' => '/inventory',
            ];
        }

        // 2. Out of stock alerts
        $outOfStockItems = InventoryItem::outOfStock()->get();
        foreach ($outOfStockItems as $item) {
            $notifications[] = [
                'id' => 'out-of-stock-' . $item->id,
                'type' => 'critical',
                'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'title' => 'Out of Stock',
                'message' => "{$item->name} is completely out of stock. Immediate restocking required.",
                'time' => 'Just now',
                'action_text' => 'Add Stock',
                'action_url' => '/inventory',
            ];
        }

        // 3. Expiring soon (within 7 days)
        $expiringBatches = InventoryBatch::with('inventoryItem')
            ->where('status', 'available')
            ->expiringSoon(7)
            ->get();
        
        foreach ($expiringBatches as $batch) {
            $daysRemaining = $batch->remaining_shelf_life;
            $urgency = $daysRemaining <= 3 ? 'critical' : 'warning';
            
            $notifications[] = [
                'id' => 'expiring-' . $batch->id,
                'type' => $urgency,
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'title' => 'Product Expiring Soon',
                'message' => "{$batch->inventoryItem->name} (Batch: {$batch->batch_code}) expires in {$daysRemaining} day(s). Quantity: {$batch->quantity} {$batch->inventoryItem->unit}",
                'time' => $daysRemaining . ' days',
                'action_text' => 'View Details',
                'action_url' => '/inventory',
            ];
        }

        // 4. Expired batches
        $expiredBatches = InventoryBatch::with('inventoryItem')
            ->where('status', 'available')
            ->expired()
            ->get();
        
        foreach ($expiredBatches as $batch) {
            $notifications[] = [
                'id' => 'expired-' . $batch->id,
                'type' => 'critical',
                'icon' => 'M6 18L18 6M6 6l12 12',
                'title' => 'Expired Product',
                'message' => "{$batch->inventoryItem->name} (Batch: {$batch->batch_code}) has expired. Quantity: {$batch->quantity} {$batch->inventoryItem->unit}. Remove from inventory.",
                'time' => 'Expired',
                'action_text' => 'Remove Stock',
                'action_url' => '/inventory',
            ];
        }

        // 5. High value expiring items (value > 5000)
        $highValueExpiring = InventoryBatch::with('inventoryItem')
            ->where('status', 'available')
            ->expiringSoon(14)
            ->get()
            ->filter(function($batch) {
                $value = $batch->quantity * $batch->price_per_unit;
                return $value > 5000;
            });
        
        foreach ($highValueExpiring as $batch) {
            $value = $batch->quantity * $batch->price_per_unit;
            $daysRemaining = $batch->remaining_shelf_life;
            
            $notifications[] = [
                'id' => 'high-value-expiring-' . $batch->id,
                'type' => 'warning',
                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'title' => 'High Value Item Expiring',
                'message' => "{$batch->inventoryItem->name} worth ₱" . number_format($value, 2) . " expires in {$daysRemaining} days. Consider discounting for quick sale.",
                'time' => $daysRemaining . ' days',
                'action_text' => 'Create Discount',
                'action_url' => '/pos',
            ];
        }

        // Sort by priority (critical first, then warning, then info)
        usort($notifications, function($a, $b) {
            $priority = ['critical' => 0, 'warning' => 1, 'info' => 2, 'success' => 3];
            return $priority[$a['type']] - $priority[$b['type']];
        });

        return $notifications;
    }

    /**
     * Get notification count.
     */
    public function getCount()
    {
        $notifications = $this->generateNotifications();
        
        $counts = [
            'total' => count($notifications),
            'critical' => count(array_filter($notifications, fn($n) => $n['type'] === 'critical')),
            'warning' => count(array_filter($notifications, fn($n) => $n['type'] === 'warning')),
            'info' => count(array_filter($notifications, fn($n) => $n['type'] === 'info')),
        ];

        return response()->json($counts);
    }

    /**
     * Get notifications as JSON.
     */
    public function getData()
    {
        $notifications = $this->generateNotifications();
        return response()->json($notifications);
    }

    /**
     * Get unread notification count for badge.
     */
    public function getUnreadCount()
    {
        // For now, all generated notifications are considered "unread"
        // You can implement a read/unread tracking system later
        $notifications = $this->generateNotifications();
        
        return response()->json([
            'count' => count($notifications),
            'has_critical' => count(array_filter($notifications, fn($n) => $n['type'] === 'critical')) > 0,
        ]);
    }
}
