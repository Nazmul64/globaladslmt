<?php

namespace App\Http\Controllers;
use App\Models\Privacyandpolicy;
use Illuminate\Http\Request;

class PrivacyandPolicyController extends Controller
{
    // List all privacy policies
    public function index()
    {
        $policies =Privacyandpolicy::latest()->get();
        return view('admin.privacy_policies.index', compact('policies'));
    }

    // Show create form
    public function create()
    {
        return view('privacy_policies.create');
    }

    // Store new policy
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        Privacyandpolicy::create($request->all());

        return redirect()->route('privacy_policies.index')
                         ->with('success', 'Privacy Policy created successfully');
    }

    // Show edit form
    public function edit(Privacyandpolicy $privacyPolicy)
    {
        return view('admin.privacy_policies.edit', compact('privacyPolicy'));
    }

    // Update policy
    public function update(Request $request, Privacyandpolicy $privacyPolicy)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $privacyPolicy->update($request->all());

        return redirect()->route('privacy_policies.index')
                         ->with('success', 'Privacy Policy updated successfully');
    }

    // Delete policy
    public function destroy(Privacyandpolicy $privacyPolicy)
    {
        $privacyPolicy->delete();

        return redirect()->route('privacy_policies.index')
                         ->with('success', 'Privacy Policy deleted successfully');
    }
}
