<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Deal;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroSlideController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.hero-slides.index', compact('slides'));
    }

    public function create()
    {
        $slide = new HeroSlide([
            'is_active'  => true,
            'sort_order' => 0,
        ]);

        $categories = Category::orderBy('name')->get(['id','name']);
        $deals = Deal::orderByDesc('starts_at')->get(['id','name']);

        return view('admin.hero-slides.create', compact('slide','categories','deals'));
    }

    public function store(Request $request)
    {
        $data = $this->validateSlide($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('hero/slides', 'public');
        }

        HeroSlide::create($data);

        return redirect()
            ->route('admin.hero-slides.index')
            ->with('success', 'Hero slide created.');
    }

    public function edit(HeroSlide $heroSlide)
    {
        $categories = Category::orderBy('name')->get(['id','name']);
        $deals = Deal::orderByDesc('starts_at')->get(['id','name']);

        return view('admin.hero-slides.edit', [
            'slide'       => $heroSlide,
            'categories'  => $categories,
            'deals'       => $deals,
        ]);
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $data = $this->validateSlide($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('hero/slides', 'public');

            if ($heroSlide->image_path) {
                Storage::disk('public')->delete($heroSlide->image_path);
            }
        }

        $heroSlide->update($data);

        return redirect()
            ->route('admin.hero-slides.edit', $heroSlide)
            ->with('success', 'Hero slide updated.');
    }

    public function destroy(HeroSlide $heroSlide)
    {
        if ($heroSlide->image_path) {
            Storage::disk('public')->delete($heroSlide->image_path);
        }

        $heroSlide->delete();

        return redirect()
            ->route('admin.hero-slides.index')
            ->with('success', 'Hero slide deleted.');
    }

    private function validateSlide(Request $request): array
    {
        return $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'subtitle'     => ['nullable', 'string'],
            'tag'          => ['nullable', 'string', 'max:100'],
            'button_label' => ['nullable', 'string', 'max:100'],
            'button_url'   => ['nullable', 'string', 'max:2048'],
            'deal_id'      => ['nullable', 'integer', 'exists:deals,id'],
            'category_id'  => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order'   => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active'    => ['sometimes', 'boolean'],
            'image'        => ['nullable', 'image', 'max:4096'],
        ]) + [
            'is_active'  => $request->boolean('is_active'),
            'sort_order' => (int) $request->input('sort_order', 0),
        ];
    }
}
