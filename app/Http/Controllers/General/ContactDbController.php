<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\ContactDb;
use Illuminate\Http\Request;

class ContactDbController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->get('q'));

        $list = ContactDb::query()
            ->when($term !== '', function ($q) use ($term) {
                $like = '%' . $term . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('mobile', 'like', $like)
                    ->orWhere('college', 'like', $like)
                    ->orWhere('university', 'like', $like)
                    ->orWhere('district', 'like', $like));
            })
            ->latest('id')
            ->paginate(per_page(25))->withQueryString()
            ->withQueryString();

        return view('admin.general.contact-db', compact('list', 'term'));
    }

    public function destroy($id)
    {
        ContactDb::where('id', $id)->delete();

        return redirect()->back()->with('message', 'Contact deleted successfully');
    }
}
