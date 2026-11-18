<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Custom pages (written here, live at /p/{slug}) and prebuilt pages (an address the website already has). Both can be chosen as a banner / story / push / menu target. */
class SitePageController extends Controller
{
    private function allow(Request $r): void
    {
        StaffAccess::authorize($r, 'Content manage');
    }

    public function index(Request $request)
    {
        $this->allow($request);

        return view('admin.content.pages.index', ['custom' => SitePage::where('kind', 'custom')->orderBy('position')->orderByDesc('id')->get(), 'html' => SitePage::where('kind', 'html')->with('exam')->orderBy('position')->orderByDesc('id')->get(), 'prebuilt' => SitePage::where('kind', 'prebuilt')->orderBy('title')->get()]);
    }

    public function create(Request $request)
    {
        $this->allow($request);

        return view('admin.content.pages.form', ['page' => null, 'kind' => $this->kindOf($request->query('kind'))]);
    }

    public function edit(Request $request, $id)
    {
        $this->allow($request);
        $page = SitePage::findOrFail($id);

        return view('admin.content.pages.form', ['page' => $page, 'kind' => $page->kind]);
    }

    private function kindOf($k): string
    {
        return in_array($k, ['prebuilt', 'html'], true) ? $k : 'custom';
    }

    private function data(Request $request, string $kind, ?SitePage $page): array
    {
        $request->merge(['slug' => Str::slug((string) ($request->input('slug') ?: $request->input('title')))]);
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'slug' => ['required', 'regex:/^[a-z0-9-]{2,80}$/', Rule::unique('site_pages', 'slug')->ignore($page?->id)],
            'path' => [$kind === 'prebuilt' ? 'required' : 'nullable', 'string', 'max:200', 'regex:/^\/[A-Za-z0-9\-_\/\.\?=&#]*$/'],
            'html' => [$kind === 'html' && !$page ? 'required' : 'nullable', 'file', 'max:8192', function ($a, $v, $fail) {
                $ext = strtolower($v->getClientOriginalExtension());
                if (!in_array($ext, ['html', 'htm'], true) || !preg_match('/<(html|body)\b/i', file_get_contents($v->getRealPath()))) {
                    $fail('Upload a .html file.');
                }
            }],
            'exam_id' => 'nullable|exists:competitive_exams,id',
            'body' => [$kind === 'custom' ? 'required' : 'nullable', 'string', 'max:200000'],
            'meta_title' => 'nullable|string|max:150', 'meta_description' => 'nullable|string|max:300', 'position' => 'nullable|integer|min:0',
        ], ['path.regex' => 'The address must start with / (for example /free-classes).']);
        $data['status'] = $request->boolean('status');
        $data['position'] = (int) ($data['position'] ?? 0);
        if ($kind === 'prebuilt') {
            $data['body'] = null;
            $data['exam_id'] = null;
        } else {
            $data['path'] = null;
        }
        if ($kind === 'html') {
            $data['body'] = null;
            if ($request->hasFile('html')) {
                $name = Str::uuid() . '.html';
                Storage::disk('local')->putFileAs('html-pages', $request->file('html'), $name);
                if ($page && $page->html_file) {
                    Storage::disk('local')->delete('html-pages/' . basename($page->html_file));
                }
                $data['html_file'] = $name;
            }
        }
        unset($data['html']);

        return $data;
    }

    public function store(Request $request)
    {
        $this->allow($request);
        $kind = $this->kindOf($request->input('kind'));
        SitePage::create($this->data($request, $kind, null) + ['kind' => $kind, 'created_by' => $request->user()->id]);

        return redirect(url('admin/pages'))->with('message', 'Page added');
    }

    public function update(Request $request, $id)
    {
        $this->allow($request);
        $page = SitePage::findOrFail($id);
        $page->update($this->data($request, $page->kind, $page));

        return redirect(url('admin/pages'))->with('message', 'Page saved');
    }

    public function toggle(Request $request, $id)
    {
        $this->allow($request);
        $page = SitePage::findOrFail($id);
        $page->update(['status' => !$page->status]);

        return back()->with('message', $page->status ? 'Page shown' : 'Page hidden');
    }

    public function destroy(Request $request, $id)
    {
        $this->allow($request);
        $page = SitePage::findOrFail($id);
        if ($page->html_file) {
            Storage::disk('local')->delete('html-pages/' . basename($page->html_file));
        }
        $page->delete();

        return redirect(url('admin/pages'))->with('message', 'Page deleted - banners that pointed to it open nothing now');
    }

    /** Public page: /p/{slug} */
    public function show($slug)
    {
        $page = SitePage::active()->whereIn('kind', ['custom', 'html'])->where('slug', $slug)->firstOrFail();
        if ($page->isHtml()) {
            return response($page->renderHtml())->header('Content-Type', 'text/html; charset=UTF-8')->header('X-Robots-Tag', 'index');
        }
        \App\Support\Seo::set(['key' => 'page:' . $page->id, 'title' => ($page->meta_title ?: $page->title) . ' | Pachavellam', 'description' => $page->meta_description ?: Str::limit(strip_tags((string) $page->body), 160),
            'image' => $page->image ? \Illuminate\Support\Facades\Storage::url('files/' . $page->image) : null]);

        return view('kpsc.custom-page', ['page' => $page, 'title' => $page->title]);
    }
}
