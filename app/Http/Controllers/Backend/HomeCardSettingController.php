<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\HomeCardSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeCardSettingController extends Controller
{
    /**
     * Display listing of home card settings in admin panel.
     */
    public function index()
    {
        $cards = HomeCardSetting::orderBy('sort_order', 'asc')->get();

        if ($cards->isEmpty()) {
            $this->seedDefaults();
            $cards = HomeCardSetting::orderBy('sort_order', 'asc')->get();
        } else {
            // Ensure top profile header card exists for admin customization
            if (!$cards->contains('key', 'header')) {
                HomeCardSetting::create([
                    'key' => 'header',
                    'title' => 'Top Profile Header',
                    'icon_type' => 'code',
                    'icon' => '<i class="fa fa-id-card"></i>',
                    'bg_color' => '#4A80F6',
                    'icon_color' => '#FFFFFF',
                    'text_color' => '#FFFFFF',
                    'sort_order' => 0,
                    'is_active' => true,
                ]);
                $cards = HomeCardSetting::orderBy('sort_order', 'asc')->get();
            }
        }

        return view('admin.homecards.index', compact('cards'));
    }

    /**
     * Update specified card setting.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'icon_type' => 'required|string|in:code,image',
            'icon' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'bg_color' => 'required|string|max:50',
            'icon_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $card = HomeCardSetting::findOrFail($id);

        $imagePath = $card->image;
        if ($request->hasFile('image')) {
            // Delete previous image if exists
            if ($card->image && file_exists(public_path($card->image))) {
                @unlink(public_path($card->image));
            }

            $uploadDir = public_path('uploads/homecard_icons');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);

            $imagePath = 'uploads/homecard_icons/' . $fileName;
        } elseif ($request->has('remove_image') && $request->remove_image == 1) {
            if ($card->image && file_exists(public_path($card->image))) {
                @unlink(public_path($card->image));
            }
            $imagePath = null;
        }

        $card->update([
            'title' => $request->title,
            'icon_type' => $request->icon_type,
            'icon' => $request->icon ?? $card->icon,
            'image' => $imagePath,
            'bg_color' => $request->bg_color,
            'icon_color' => $request->icon_color ?? '#FFFFFF',
            'text_color' => $request->text_color ?? $request->title_color ?? '#FFFFFF',
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->back()->with('success', 'Home card "' . $card->title . '" updated successfully!');
    }

    /**
     * Reset all cards to default values.
     */
    public function reset()
    {
        // Delete uploaded images
        $cards = HomeCardSetting::whereNotNull('image')->get();
        foreach ($cards as $card) {
            if ($card->image && file_exists(public_path($card->image))) {
                @unlink(public_path($card->image));
            }
        }

        HomeCardSetting::truncate();
        $this->seedDefaults();

        return redirect()->back()->with('success', 'All home card settings reset to default successfully!');
    }

    /**
     * Helper to seed default cards.
     */
    private function seedDefaults()
    {
        $defaultCards = [
            ['key' => 'header', 'title' => 'Top Profile Header', 'icon_type' => 'code', 'icon' => '<i class="fa fa-id-card"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 0],
            ['key' => 'start_task', 'title' => 'Start Task', 'icon_type' => 'code', 'icon' => '<i class="fa fa-tasks"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 1],
            ['key' => 'profile', 'title' => 'Profile', 'icon_type' => 'code', 'icon' => '<i class="fa fa-user"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 2],
            ['key' => 'refer', 'title' => 'Refer', 'icon_type' => 'code', 'icon' => '<i class="fa fa-gift"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 3],
            ['key' => 'option', 'title' => 'Option', 'icon_type' => 'code', 'icon' => '<i class="fa fa-cog"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 4],
            ['key' => 'friend_request', 'title' => 'Friend Request', 'icon_type' => 'code', 'icon' => '<i class="fa fa-users"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 5],
            ['key' => 'withdraw', 'title' => 'Withdraw', 'icon_type' => 'code', 'icon' => '<i class="fa fa-money"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 6],
            ['key' => 'p2p', 'title' => 'P2P', 'icon_type' => 'code', 'icon' => '<i class="fa fa-exchange"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 7],
            ['key' => 'how_to_work', 'title' => 'How To Work', 'icon_type' => 'code', 'icon' => '<i class="fa fa-info-circle"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 8],
            ['key' => 'total_deposit', 'title' => 'Total Deposit', 'icon_type' => 'code', 'icon' => '<i class="fa fa-dollar"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 9],
            ['key' => 'support', 'title' => 'Support', 'icon_type' => 'code', 'icon' => '<i class="fa fa-headphones"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 10],
            ['key' => 'social_post', 'title' => 'SocialPost', 'icon_type' => 'code', 'icon' => '<i class="fa fa-globe"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 11],
            ['key' => 'total_withdraw', 'title' => 'Total Withdraw', 'icon_type' => 'code', 'icon' => '<i class="fa fa-credit-card"></i>', 'bg_color' => '#4A80F6', 'icon_color' => '#FFFFFF', 'text_color' => '#FFFFFF', 'sort_order' => 12],
        ];

        foreach ($defaultCards as $card) {
            HomeCardSetting::create($card);
        }
    }
}
