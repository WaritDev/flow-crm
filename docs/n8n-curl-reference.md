# cURL reference — n8n + terminal tests

คัดลอกจาก `docs/n8n_workflow.md` ในโปรเจกต์หลัก (สเปคเต็มอยู่ที่นั่น)

---

## cURL cookbook (n8n + ทดสอบจากเทอร์มินัล)

ตั้งค่าตัวแปร (แทนที่ `BASE` ด้วย origin ของ Laravel เช่น `http://localhost` หรือ `https://api.flowcrm.app`):

```bash
export BASE="http://localhost"
export TOKEN="1|xxxxxxxx"   # Plain Sanctum token จากหน้า Integrations (แสดงครั้งเดียว)
```

**Auth สำหรับเส้นทางใน `routes/api.php` (แนะนำสำหรับ n8n)**

- `Authorization: Bearer $TOKEN`
- ถ้าวาง token แล้ว Node ฟ้องเรื่องอักขระแปลก: ใช้ header สำรอง **`X-FlowCRM-Token`** = Base64 ของ plain token (ไม่มี newline):
  ```bash
  export TOKEN_B64=$(printf '%s' "$TOKEN" | base64 | tr -d '\n')
  # แล้วส่ง: -H "X-FlowCRM-Token: $TOKEN_B64"
  ```

**Optional query `team_id`** — ใช้เมื่อ service user (n8n) ไม่มี `team_id` บนแถว User และต้องการบังคับทีม (ต้องเป็นทีมใน org เดียวกับ token)

---

### 1) REST สำหรับ n8n — automation / inbound (Sanctum Bearer)

**ดีลที่ “เงียบ” ตามชั่วโมง**

```bash
curl -sS -G "$BASE/api/sales/deals/inactive" \
  --data-urlencode "hours=48" \
  --data-urlencode "team_id=1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**สร้าง next action + Activity (Action Stream)**

```bash
DEAL_ID=123
curl -sS -X POST "$BASE/api/sales/automation/deals/${DEAL_ID}/next-action" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "next_action": "[n8n] ทักติดตามลูกค้าหลังเงียบ 48 ชม.",
    "next_action_date": "2026-03-26",
    "priority": 3
  }'
```

`priority`: `1`–`3` (optional — ถ้าไม่ส่ง ระบบคำนวณจากวันครบกำหนด)

**Context ลูกค้าตาม LINE userId**

```bash
curl -sS -G "$BASE/api/integrations/line/context" \
  --data-urlencode "line_user_id=Uxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  --data-urlencode "hours=48" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**สร้าง/อัปเดตลูกค้าจาก LINE**

```bash
curl -sS -X POST "$BASE/api/integrations/line/customers" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "line_user_id": "Uxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "name": "ลูกค้าจาก LINE",
    "nickname": "คุณสมชาย",
    "phone_num": "0812345678",
    "team_id": 1
  }'
```

**สร้าง Activity (งาน/บันทึก)**

```bash
curl -sS -X POST "$BASE/api/integrations/line/activities" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "line_user_id": "Uxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "deal_id": 123,
    "name": "[LINE] ลูกค้าสนใจแพ็กเกจ",
    "description": "สรุปจาก AI / n8n",
    "activity_type": "line",
    "priority_label": "high"
  }'
```

หรือใช้ `customer_id` แทน `line_user_id` (ต้องส่งอย่างใดอย่างหนึ่ง) — ประเภทกิจกรรม: `call` | `message` | `line` | `meeting` | `note` | `email` | `task`

**บันทึกบทสนทนา (Redis ฝั่ง Laravel)**

```bash
curl -sS -X POST "$BASE/api/integrations/line/conversation" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "line_user_id": "Uxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "role": "user",
    "content": "ข้อความจากลูกค้า"
  }'
```

**อ่านประวัติบทสนทนา**

```bash
curl -sS -G "$BASE/api/integrations/line/conversation" \
  --data-urlencode "line_user_id=Uxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

### 2) Webhook — LINE OA → **n8n** (ไม่ใช่ Laravel โดยตรง)

LINE Messaging API ส่ง **POST** ไปที่ URL ของ **Webhook node ใน n8n** ตามที่ตั้งในช่อง LINE Developers:

`{N8N_URL}` + `{N8N_WEBHOOK_PREFIX}` + `{line_webhook_path}`

ค่า `line_webhook_path` / URL เต็มอยู่ใน FlowCRM หน้า **Integrations** (manager) — การทดสอบจำลองจากเครื่องทำได้โดย POST body แบบ LINE ไปที่ URL n8n ของคุณเอง:

```bash
export N8N_HOOK="https://your-n8n.example/webhook/flowcrm-line-1-abc123..."
curl -sS -X POST "$N8N_HOOK" \
  -H "Content-Type: application/json" \
  -d '{"destination":"U...","events":[]}'
```

(รูปแบบ body จริงให้ตาม [Messaging API webhook](https://developers.line.biz/en/reference/messaging-api/#webhook-event-objects) — มักมี `events[]` พร้อม `source.userId`, `message`, ฯลฯ)

---

### 3) REST แบบ **session + CSRF** (เส้นทางเดียวกับ SvelteKit / Blade ที่ login แล้ว)

ใช้เมื่อต้องการยิงเส้นทางใน `routes/web.php` ที่อยู่ภายใต้ middleware `auth` เช่น Action Stream:

| Method | Path | หมายเหตุ |
|--------|------|-----------|
| GET | `/api/sales/csrf` | คืน `{ "csrf_token": "..." }` หลังมี session |
| GET | `/api/sales/activities?completed=0` | รายการ activities |
| POST | `/api/sales/activities/{id}/complete` | ต้องส่ง CSRF |
| GET | `/api/sales/dashboard` | แดชบอร์ดเซลล์ |
| GET/POST | `/api/sales/customers`, … | CRUD ลูกค้าฝั่งเซลล์ |

**แนวทาง curl:** เปิด login ด้วย cookie jar — ดึงหน้า `GET $BASE/login` เก็บ cookie session, แยก `_token` จาก HTML, แล้ว `POST $BASE/login` (form `email`, `password`, `_token`). จากนั้นเรียก `GET $BASE/api/sales/csrf` ด้วย cookie เดิม ได้ token สำหรับ `POST`:

```bash
curl -sS -X POST "$BASE/api/sales/activities/456/complete" \
  -b cookies.txt -c cookies.txt \
  -H "X-XSRF-TOKEN: <ค่าจาก cookie XSRF-TOKEN หลัง decrypt หรือใช้ค่าจาก csrf_token JSON ตามที่ frontend ส่ง>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{}'
```

ในการใช้งานจริง **frontend (SvelteKit)** จัดการ cookie + CSRF ให้แล้ว — ส่วน **n8n แนะนำใช้ Bearer ที่หัวข้อ (1)** แทนการไล่ session
