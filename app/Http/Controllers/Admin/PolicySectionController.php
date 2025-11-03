<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PolicySection;
use Illuminate\Http\Request;

class PolicySectionController extends Controller
{
    protected array $sections = [
        'privacy'        => 'Privacy Policy',
        'terms'          => 'Terms & Conditions',
        'seller-forum'   => 'Seller Forum Guidelines',
        'seller-tips'    => 'Seller Tips',
        'buyer-tips'     => 'Buyer Tips',
        'house-rules'    => 'House Rules & Conditions',
        'about-cetsy'    => 'About Cetsy',
        'prohibited'     => 'Prohibited Items Policy',
        'behavioural'    => 'Behavioural Policy',
        'fees'           => 'Fees & Commissions',
    ];

    public function index()
    {
        $existing = PolicySection::get()->keyBy('slug');
        $rows = [];
        foreach ($this->sections as $slug => $label) {
            $row = $existing->get($slug);
            $rows[] = [
                'slug' => $slug,
                'label' => $label,
                'has_content' => (bool) ($row?->content),
                'updated_at' => $row?->updated_at,
            ];
        }
        return view('admin.policies.index', compact('rows'));
    }

    public function edit(string $slug)
    {
        abort_unless(isset($this->sections[$slug]), 404);
        $label = $this->sections[$slug];
        $row = PolicySection::firstOrNew(['slug' => $slug]);
        return view('admin.policies.edit', [
            'slug' => $slug,
            'label' => $label,
            'content' => $row->content,
            'row' => $row,
        ]);
    }

    public function update(Request $request, string $slug)
    {
        abort_unless(isset($this->sections[$slug]), 404);
        $data = $request->validate([
            'content' => 'nullable|string',
        ]);
        PolicySection::updateOrCreate(
            ['slug' => $slug],
            ['title' => $this->sections[$slug], 'content' => $data['content'] ?? null]
        );
        return redirect()->route('admin.policies.index')->with('success', 'Section updated successfully.');
    }
}

