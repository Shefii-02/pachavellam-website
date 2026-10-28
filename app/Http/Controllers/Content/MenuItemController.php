<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Support\Landing;
use App\Support\StaffAccess;
use Illuminate\Http\Request;

/** Menu setup: the side menu of the website (admin/menu). Footer links stay under Website content > Footer links. */
class MenuItemController extends Controller
{
    private function allow(Request $r): void
    {
        StaffAccess::authorize($r, 'Content manage');
        \Illuminate\Support\Facades\Cache::forget('drawer_menu');   // the website's menu is cached for a minute
    }

    public function index(Request $request)
    {
        $this->allow($request);
        $edit = $request->filled('edit') ? MenuItem::find($request->edit) : null;

        return view('admin.content.menu.index', [
            'items' => MenuItem::whereNull('parent_id')->orderBy('position')->orderBy('id')->with('children')->get(),
            'edit' => $edit, 'parents' => MenuItem::whereNull('parent_id')->orderBy('position')->get(),
        ]);
    }

    private function data(Request $request, ?MenuItem $item): array
    {
        $data = $request->validate([
            'label' => 'required|string|max:80', 'icon' => ['nullable', 'string', 'max:40', 'regex:/^bi-[a-z0-9-]+$/'], 'link_url' => 'nullable|string|max:500',
            'parent_id' => 'nullable|integer|exists:menu_items,id', 'position' => 'nullable|integer|min:0',
        ], ['icon.regex' => 'Use a Bootstrap icon name such as bi-play-btn.']);
        [$tt, $tv] = Landing::fromRequest($request);
        if ($tt === 'link' && !$request->filled('link_url')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['link_url' => 'Type the address, or choose a course / exam type / page.']);
        }
        if (!empty($data['parent_id']) && $item && (int) $data['parent_id'] === $item->id) {
            throw \Illuminate\Validation\ValidationException::withMessages(['parent_id' => 'An item cannot be its own parent.']);
        }
        if (!empty($data['parent_id']) && MenuItem::whereNotNull('parent_id')->where('id', $data['parent_id'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['parent_id' => 'Sub items go one level deep: choose a top level item.']);
        }
        if (!empty($data['parent_id']) && $item && $item->children()->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['parent_id' => 'This item has sub items, so it must stay at the top level.']);
        }

        return $data + ['target_type' => $tt, 'target_value' => $tv, 'status' => $request->boolean('status'), 'new_tab' => $request->boolean('new_tab'), 'position' => (int) ($data['position'] ?? 0)];
    }

    public function store(Request $request)
    {
        $this->allow($request);
        $item = MenuItem::create($this->data($request, null));
        if (!$item->position) {
            $item->update(['position' => $item->id]);
        }

        return redirect(url('admin/menu'))->with('message', 'Menu item added');
    }

    public function update(Request $request, $id)
    {
        $this->allow($request);
        $item = MenuItem::findOrFail($id);
        $item->update($this->data($request, $item));

        return redirect(url('admin/menu'))->with('message', 'Menu item saved');
    }

    public function toggle(Request $request, $id)
    {
        $this->allow($request);
        $item = MenuItem::findOrFail($id);
        $item->update(['status' => !$item->status]);

        return back();
    }

    public function destroy(Request $request, $id)
    {
        $this->allow($request);
        $item = MenuItem::findOrFail($id);
        MenuItem::where('parent_id', $item->id)->delete();
        $item->delete();

        return redirect(url('admin/menu'))->with('message', 'Menu item deleted');
    }
}
