<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\Seo;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** admin/seo: title, description, keywords, share image and robots of each public page (home, exams, every exam type page ...). */
class SeoController extends Controller
{
    private function allow(Request $request): void
    {
        StaffAccess::authorize($request, 'Content manage');
    }

    public function index(Request $request)
    {
        $this->allow($request);
        $rows = DB::table('seo_pages')->get()->keyBy('page_key');
        $q = trim((string) $request->query('q'));
        $pages = collect(Seo::pages())->map(fn ($p, $key) => (object) [
            'key' => $key, 'label' => $p[0], 'path' => $p[1], 'auto_title' => $p[2], 'auto_description' => $p[3], 'row' => $rows[$key] ?? null,
            'group' => str_starts_with($key, 'exam:') ? 'Exam type pages' : 'Pages',
        ])->when($q !== '', fn ($c) => $c->filter(fn ($p) => stripos($p->label . ' ' . $p->path, $q) !== false))->values();

        return view('admin.seo.index', ['pages' => $pages, 'q' => $q, 'siteKeywords' => Seo::DEFAULT_KEYWORDS]);
    }

    public function save(Request $request)
    {
        $this->allow($request);
        $data = $request->validate([
            'page_key' => ['required', 'string', 'max:80'], 'title' => ['nullable', 'string', 'max:160'], 'description' => ['nullable', 'string', 'max:400'],
            'keywords' => ['nullable', 'string', 'max:400'], 'robots' => ['nullable', \Illuminate\Validation\Rule::in(['index,follow', 'noindex,follow', 'noindex,nofollow', 'index,nofollow'])],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);
        abort_unless(array_key_exists($data['page_key'], Seo::pages()), 404);

        $existing = DB::table('seo_pages')->where('page_key', $data['page_key'])->first();
        $row = [
            'title' => trim((string) ($data['title'] ?? '')) ?: null,
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            // "seo, words, like, this": tidy the spaces and drop duplicates
            'keywords' => ($k = collect(explode(',', (string) ($data['keywords'] ?? '')))->map(fn ($w) => trim($w))->filter()->unique()->implode(', ')) ? $k : null,
            'robots' => $request->input('robots') ?: null, 'updated_by' => $request->user()->id, 'updated_at' => now(),
        ];
        if ($request->hasFile('og_image')) {
            if ($existing && $existing->og_image) {
                Storage::disk('public')->delete('files/' . $existing->og_image);
            }
            $name = 'seo/' . Str::random(30) . '.' . $request->file('og_image')->extension();
            $request->file('og_image')->storeAs('files', $name, 'public');
            $row['og_image'] = $name;
        } elseif ($request->boolean('remove_image') && $existing && $existing->og_image) {
            Storage::disk('public')->delete('files/' . $existing->og_image);
            $row['og_image'] = null;
        }
        DB::table('seo_pages')->updateOrInsert(['page_key' => $data['page_key']], $row + ($existing ? [] : ['created_at' => now()]));
        Seo::forget($data['page_key']);

        return redirect(url('admin/seo') . '#' . Str::slug($data['page_key']))->with('message', 'SEO saved for ' . Seo::pages()[$data['page_key']][0]);
    }

    public function reset(Request $request, string $key)
    {
        $this->allow($request);
        $row = DB::table('seo_pages')->where('page_key', $key)->first();
        if ($row && $row->og_image) {
            Storage::disk('public')->delete('files/' . $row->og_image);
        }
        DB::table('seo_pages')->where('page_key', $key)->delete();
        Seo::forget($key);

        return redirect(url('admin/seo'))->with('message', 'Back to the automatic text.');
    }
}
