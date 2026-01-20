<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Mock Top Stats
        $stats = [
            'todo_today' => 8,
            'overdue_deals' => 3,
            'confirmed_quotes' => 5,
            'revenue_month' => 420000,
            'revenue_growth' => 12, // %
        ];

        // 2. Mock Daily Activities (กิจกรรมที่ต้องทำวันนี้)
        $activities = [
            [
                'id' => 1,
                'action_type' => 'ทัก LINE',
                'customer_name' => 'คุณสมชาย',
                'description' => 'ส่งใบเสนอราคาบริการ Spa Package',
                'priority' => 'urgent', // urgent, medium, normal
                'time' => '10:00',
                'status' => 'pending'
            ],
            [
                'id' => 2,
                'action_type' => 'Follow-up',
                'customer_name' => 'คุณนก',
                'description' => 'นัดนวดหน้า Facial Treatment',
                'priority' => 'urgent',
                'time' => '11:30',
                'status' => 'pending'
            ],
            [
                'id' => 3,
                'action_type' => 'ทัก LINE',
                'customer_name' => 'คุณวิภา',
                'description' => 'แจ้งสินค้าพร้อมส่ง',
                'priority' => 'medium',
                'time' => '14:00',
                'status' => 'pending'
            ],
            [
                'id' => 4,
                'action_type' => 'ปิดการขาย',
                'customer_name' => 'คุณมานพ',
                'description' => 'ยืนยันออเดอร์และรับชำระเงิน',
                'priority' => 'normal',
                'time' => '16:00',
                'status' => 'pending'
            ],
        ];

        // 3. Mock Chart Data (Revenue Snapshot)
        $chartData = [
            'labels' => ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.'],
            'data' => [120000, 150000, 220000, 310000, 280000, 390000, 420000],
            'projected' => [120000, 155000, 230000, 320000, 350000, 410000, 450000]
        ];

        return view('dashboard.index', compact('stats', 'activities', 'chartData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
