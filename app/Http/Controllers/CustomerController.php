<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('customers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'line_id'  => 'required|string|max:100',
            'avatar'   => 'nullable|image|max:2048',
        ]);

        $customer = new Customer();

        $customer->team_id     = Auth::user()->current_team_id ?? 1;
        $customer->user_id     = Auth::id();
        $customer->name        = $request->fullname;
        $customer->nickname    = $request->nickname;
        $customer->phone_num   = $request->phone;
        $customer->email       = $request->email;
        $customer->line_id     = $request->line_id;
        $customer->province    = $request->province;
        $customer->address     = $request->address;
        $customer->tags        = $request->tags;
        $customer->status      = $request->boolean('is_active') ? 'active' : 'inactive';

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('customers', 'public');
            $customer->img_profile = $path;
        }

        $customer->save();

        return redirect()->route('customers.index')
            ->with('success', 'เพิ่มลูกค้าใหม่เรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $teamId = $user->getTeamId();

        $customer = Customer::with([
            'deals' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'deals.stage',
            'activities' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'activities.user'
        ])
            ->withSum(['deals as lifetime_value' => function ($query) {
                $query->whereNotNull('won_at');
            }], 'value')
            ->withCount('deals as total_deals')
            ->withMax('activities as last_contacted', 'created_at')
            ->where('team_id', $teamId)
            ->findOrFail($id);

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'nickname' => $customer->nickname,
                'phone_num' => $customer->phone_num,
                'email' => $customer->email,
                'line_id' => $customer->line_id,
                'province' => $customer->province,
                'address' => $customer->address,
                'tags' => $customer->tags,
                'status' => $customer->status,
                'img_profile' => $customer->img_profile ? asset('storage/' . $customer->img_profile) : null,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
            ],
            'statistics' => [
                'lifetime_value' => (float) ($customer->lifetime_value ?? 0),
                'total_deals' => $customer->total_deals,
                'last_contacted' => $customer->last_contacted,
            ],
            'deals' => $customer->deals->map(function ($deal) {
                return [
                    'id' => $deal->id,
                    'name' => $deal->name,
                    'value' => $deal->value,
                    'currency' => $deal->currency,
                    'stage' => $deal->stage->name ?? 'Unknown',
                    'is_won' => !is_null($deal->won_at),
                    'is_lost' => !is_null($deal->lost_at),
                    'expected_close_date' => $deal->expected_close_date,
                    'created_at' => $deal->created_at,
                ];
            }),
            'activities' => $customer->activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'description' => $activity->description,
                    'activity_type' => $activity->activity_type,
                    'priority' => $activity->priority,
                    'is_completed' => $activity->is_completed,
                    'user_name' => $activity->user->name ?? 'Unknown',
                    'created_at' => $activity->created_at,
                ];
            })
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'line_id' => 'required|string|max:100',
        ]);

        $data = $this->mapCustomerData($request); // ใช้ตัวกรองเดียวกัน

        if ($request->hasFile('avatar')) {
            if ($customer->img_profile) Storage::disk('public')->delete($customer->img_profile);
            $data['img_profile'] = $request->file('avatar')->store('customers', 'public');
        }

        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'แก้ไขสำเร็จ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function mapCustomerData($request)
    {
        return [
            'team_id' => Auth::user()->current_team_id ?? 1,
            'user_id' => Auth::id(),
            'name' => $request->fullname,
            'nickname' => $request->nickname,
            'phone_num' => $request->phone,
            'line_id' => $request->line_id,
            'email' => $request->email,
            'province' => $request->province,
            'address' => $request->address,
            'tags' => $request->tags,
            'status' => $request->boolean('is_active') ? 'active' : 'inactive',
        ];
    }
}
