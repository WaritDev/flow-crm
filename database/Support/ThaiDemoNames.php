<?php

namespace Database\Support;

/**
 * ชื่อ–นามสกุลไทย + ชื่อเล่นที่จับคู่กัน สำหรับ seed / factory (ข้อมูลเดโม่เท่านั้น)
 */
final class ThaiDemoNames
{
    /**
     * @return list<array{name: string, nickname: string}>
     */
    public static function pairs(): array
    {
        return [
            ['name' => 'สมชาย ใจดี', 'nickname' => 'ชาย'],
            ['name' => 'มาลี สุขสม', 'nickname' => 'มาลี'],
            ['name' => 'วิภา ศรีสุข', 'nickname' => 'แพร'],
            ['name' => 'ประเสริฐ วงศ์ใหญ่', 'nickname' => 'เสริฐ'],
            ['name' => 'นภัสวรรณ ทองคำ', 'nickname' => 'พลอย'],
            ['name' => 'อรทัย แสงทอง', 'nickname' => 'หญิง'],
            ['name' => 'สุรชัย พันธุ์เจริญ', 'nickname' => 'ชัย'],
            ['name' => 'กัญญารัตน์ มณีรัตน์', 'nickname' => 'หนิง'],
            ['name' => 'ธนากร สินธุพงศ์', 'nickname' => 'กร'],
            ['name' => 'ปภัสสร เทียมทอง', 'nickname' => 'แป้ง'],
            ['name' => 'อาทิตย์ รุ่งเรือง', 'nickname' => 'ต้อม'],
            ['name' => 'รัตนา บัวบาน', 'nickname' => 'รัต'],
            ['name' => 'ชาญชัย ดีงาม', 'nickname' => 'ปืน'],
            ['name' => 'ศิริพร แก้วใส', 'nickname' => 'ปุ๋ย'],
            ['name' => 'วีระ คงมั่น', 'nickname' => 'หมู'],
            ['name' => 'ปิยะนุช สายใย', 'nickname' => 'นุช'],
            ['name' => 'ธีรัจน์ พงศ์พิพัฒน์', 'nickname' => 'ธีร์'],
            ['name' => 'อภิชญา ล้ำเลิศ', 'nickname' => 'แอม'],
            ['name' => 'กิตติพงษ์ ศักดิ์สิน', 'nickname' => 'กิต'],
            ['name' => 'นันทวัน แสงจันทร์', 'nickname' => 'แนน'],
            ['name' => 'พีรพล รักสงบ', 'nickname' => 'พล'],
            ['name' => 'อรุณรัตน์ เพชรรัตน์', 'nickname' => 'อร'],
            ['name' => 'มานะ ขยันเพียร', 'nickname' => 'นา'],
            ['name' => 'สุดา ผลบุญ', 'nickname' => 'ด้า'],
            ['name' => 'จักรพันธ์ ทวีสุข', 'nickname' => 'เอก'],
        ];
    }
}
