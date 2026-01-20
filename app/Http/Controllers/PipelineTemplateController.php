<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PipelineTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // จำลองข้อมูล Template ตามรูปภาพ
        $templates = [
            [
                'id' => 'beauty_clinic',
                'icon' => 'sparkles', // ชื่อ icon
                'color' => 'pink', // theme สี
                'is_popular' => true,
                'title_th' => 'ธุรกิจคลินิกความงาม',
                'title_en' => 'Beauty Clinic',
                'description' => 'สำหรับคลินิกเสริมความงาม, สปา, นวดหน้า, ทำเล็บ พร้อม Script ทักลูกค้า',
                'stages' => ['สอบถาม', 'นัดคิว', 'ยืนยัน', 'ชำระเงิน', 'เสร็จสิ้น'],
                'script_count' => 12
            ],
            [
                'id' => 'construction',
                'icon' => 'wrench',
                'color' => 'orange',
                'is_popular' => false,
                'title_th' => 'ธุรกิจรับเหมาก่อสร้าง',
                'title_en' => 'Contractor / Construction',
                'description' => 'สำหรับงานรับเหมา, ตกแต่งภายใน, งานซ่อมบำรุง จัดการโปรเจกต์ใหญ่',
                'stages' => ['สำรวจหน้างาน', 'ส่งใบเสนอราคา', 'เจรจา', 'เซ็นสัญญา', 'ดำเนินการ', 'ส่งมอบ'],
                'script_count' => 8
            ],
            [
                'id' => 'preorder',
                'icon' => 'shopping-bag',
                'color' => 'purple',
                'is_popular' => true,
                'title_th' => 'ร้านพรีออเดอร์',
                'title_en' => 'Pre-order Shop',
                'description' => 'สำหรับร้านค้าพรีออเดอร์, ขายสินค้านำเข้า, รับจองสินค้าล่วงหน้า',
                'stages' => ['รับออเดอร์', 'ชำระเงิน', 'รอสินค้า', 'สินค้ามาถึง', 'จัดส่ง'],
                'script_count' => 10
            ],
            [
                'id' => 'service',
                'icon' => 'office-building',
                'color' => 'blue',
                'is_popular' => false,
                'title_th' => 'ธุรกิจบริการ',
                'title_en' => 'Service-based Business',
                'description' => 'สำหรับธุรกิจให้บริการทั่วไป เช่น ซ่อมรถ, ซ่อมแอร์, งานช่าง',
                'stages' => ['สอบถาม', 'นัดหมาย', 'ดำเนินการ', 'ชำระเงิน'],
                'script_count' => 6
            ],
            [
                'id' => 'b2b',
                'icon' => 'briefcase',
                'color' => 'indigo',
                'is_popular' => false,
                'title_th' => 'ธุรกิจ B2B Sales',
                'title_en' => 'B2B Sales',
                'description' => 'สำหรับขายองค์กร, ขายส่ง, ดีลขนาดใหญ่ที่ต้องติดตามหลายขั้นตอน',
                'stages' => ['Lead', 'ติดต่อ', 'นำเสนอ', 'Proposal', 'เจรจา', 'ปิดการขาย'],
                'script_count' => 15
            ],
            [
                'id' => 'healthcare',
                'icon' => 'heart',
                'color' => 'rose',
                'is_popular' => false,
                'title_th' => 'ธุรกิจสุขภาพ',
                'title_en' => 'Healthcare',
                'description' => 'สำหรับคลินิก, ฟิตเนส, โยคะ, นักโภชนาการ, โค้ชส่วนตัว',
                'stages' => ['สอบถาม', 'นัดปรึกษา', 'เสนอแพ็กเกจ', 'ลงทะเบียน'],
                'script_count' => 9
            ],
        ];

        return view('pipeline-templates.index', compact('templates'));
    }

    public function select(Request $request)
    {
        // Logic สำหรับบันทึก Template ที่เลือกเข้าสู่ Team
        // $team->pipelines()->create(...)
        return redirect()->route('pipeline-stages.index')->with('success', 'ติดตั้ง Pipeline เรียบร้อยแล้ว');
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
