<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $canApprove = $user->hasPermissionTo('leave_approve');

        $myRequests = $user->personil
            ? $user->personil->leaveRequests()->latest('id')->take(20)->get()
            : collect();

        $pending = $canApprove
            ? LeaveRequest::with('personil')->where('status', 'Diajukan')->latest('id')->get()
            : collect();

        $history = $canApprove
            ? LeaveRequest::with('personil', 'approver')->whereIn('status', ['Disetujui', 'Ditolak'])->latest('approved_at')->take(20)->get()
            : collect();

        return view('izin.index', compact('myRequests', 'pending', 'history', 'canApprove'));
    }

    public function store(Request $request): RedirectResponse
    {
        $personil = auth()->user()->personil;
        if (! $personil) {
            return back()->with('error', 'Akun Anda belum terhubung dengan data personil.');
        }

        $data = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $data['personil_id'] = $personil->id;
        $data['status'] = 'Diajukan';

        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('leave-documents', 'local');
        }
        unset($data['document']);

        LeaveRequest::create($data);

        return back()->with('success', 'Pengajuan izin/cuti terkirim, menunggu persetujuan.');
    }

    public function approve(LeaveRequest $leave): RedirectResponse
    {
        $leave->update([
            'status' => 'Disetujui',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
        ]);

        return back()->with('success', "Izin {$leave->personil->name} disetujui.");
    }

    public function reject(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $leave->update([
            'status' => 'Ditolak',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
            'note' => $request->input('note'),
        ]);

        return back()->with('success', "Izin {$leave->personil->name} ditolak.");
    }
}
