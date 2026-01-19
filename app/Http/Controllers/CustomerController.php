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
        //
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
