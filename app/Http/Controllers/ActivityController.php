<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
            $activities = [
                [
                    'id' => 1,
                    'priority' => 'urgent', // urgent, medium, normal
                    'action_type' => 'ทัก LINE',
                    'customer_nickname' => 'เจ',
                    'customer_name' => 'คุณสมชาย วงศ์ดี',
                    'title' => 'ส่งใบเสนอราคา Spa Package Premium',
                    'warning' => 'ลูกค้าคลิกดูแค็ตตาล็อก 3 ครั้ง',
                    'time' => '10:00',
                    'amount' => 15000,
                    'line_id' => '@somchai_j',
                    'last_contact' => '2 วันที่แล้ว',
                    'script' => 'สวัสดีครับคุณเจ ผมส่งใบเสนอราคา Spa Package Premium ให้ทางนี้นะครับ มีโปรโมชั่นพิเศษลด 20% ถึงสิ้นเดือนนี้ครับ'
                ],
                [
                    'id' => 2,
                    'priority' => 'urgent',
                    'action_type' => 'Follow-up',
                    'customer_nickname' => 'นก',
                    'customer_name' => 'คุณนก ศรีสุข',
                    'title' => 'นัดนวดหน้า Facial Treatment',
                    'warning' => 'ลูกค้าทักมาแต่ยังไม่ได้ตอบ',
                    'time' => '11:30',
                    'amount' => 8500,
                    'line_id' => '@nok_sri',
                    'last_contact' => 'เมื่อวาน',
                    'script' => 'สวัสดีครับคุณนก ไม่ทราบว่าสะดวกเข้ามานวดหน้าช่วงบ่ายวันเสาร์นี้ไหมครับ คิวว่างพอดีเลยครับ'
                ],
                [
                    'id' => 3,
                    'priority' => 'urgent',
                    'action_type' => 'โทร',
                    'customer_nickname' => 'พี่หนุ่ม',
                    'customer_name' => 'คุณมานพ ธุรกิจดี',
                    'title' => 'เสนอราคางานรับเหมาตกแต่งภายใน',
                    'warning' => 'ดีลค้างเกิน 5 วัน',
                    'time' => '13:00',
                    'amount' => 250000,
                    'line_id' => '@manop_biz',
                    'last_contact' => '5 วันที่แล้ว',
                    'script' => 'สวัสดีครับพี่หนุ่ม ผมขออนุญาตโทรติดตามเรื่องใบเสนอราคาตกแต่งภายในครับ ไม่ทราบว่าได้ดูรายละเอียดหรือยังครับ'
                ],
                [
                    'id' => 4,
                    'priority' => 'medium',
                    'action_type' => 'ทัก LINE',
                    'customer_nickname' => 'วิ',
                    'customer_name' => 'คุณวิภา สวยงาม',
                    'title' => 'แจ้งสินค้าพร้อมส่ง',
                    'warning' => '',
                    'time' => '14:00',
                    'amount' => 3200,
                    'line_id' => '@wipa_beauty',
                    'last_contact' => '1 สัปดาห์ที่แล้ว',
                    'script' => 'สวัสดีครับคุณวิ สินค้าเซรั่มที่สั่งจองไว้ ของเข้าแล้วนะครับ พร้อมจัดส่งวันนี้เลยครับ'
                ],
                [
                    'id' => 5,
                    'priority' => 'normal',
                    'action_type' => 'ปิดการขาย',
                    'customer_nickname' => 'ยุทธ',
                    'customer_name' => 'คุณประยุทธ์ มั่งมี',
                    'title' => 'ยืนยันออเดอร์และรับชำระเงิน',
                    'warning' => 'ลูกค้ายืนยันใบเสนอราคา',
                    'time' => '16:00',
                    'amount' => 45000,
                    'line_id' => '@yut_rich',
                    'last_contact' => 'วันนี้',
                    'script' => 'ขอบคุณครับคุณยุทธ รบกวนขอสลิปโอนเงินเพื่อยืนยันการจองคิวช่างนะครับ'
                ],
            ];

            return view('activities.index', compact('activities'));
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
