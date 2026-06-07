<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::with('author')->latest('published_at')->latest('id')->paginate(10);
        $roles = Role::orderBy('label')->get();
        $canManage = auth()->user()->hasPermissionTo('announcement_manage');

        return view('announcements.index', compact('announcements', 'roles', 'canManage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['author_id'] = auth()->id();
        $data['published_at'] = $data['published_at'] ?? Carbon::today();

        Announcement::create($data);

        return back()->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->validateData($request));

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'target' => ['required', 'string', 'max:50'],
            'published_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
