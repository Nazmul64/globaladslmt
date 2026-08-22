<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\LockedSetting;
use App\Models\User;
use App\Models\AgentDeposite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LockSystemController extends Controller
{
    /**
     * Show lock system settings
     */
    public function index()
    {
        $lock = LockedSetting::first();
        return view('admin.locksystem.index', compact('lock'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.locksystem.create');
    }

    /**
     * Store new lock system
     */
    public function store(Request $request)
    {
        $request->validate([
            'locked_amount' => 'required|numeric|min:0',
            'status' => 'required|boolean'
        ]);

        DB::beginTransaction();
        try {
            // Deactivate existing locks
            LockedSetting::query()->update(['status' => 0]);

            $lock = LockedSetting::create([
                'locked_amount' => $request->locked_amount,
                'status' => $request->status
            ]);

            // Apply to agents (only non-overridden ones)
            if ($request->status == 1) {
                $updated = User::where('role', 'agent')
                    ->where('is_locked_override', false)
                    ->update(['locked_amount' => $request->locked_amount]);

                $message = "Lock system activated! $" . number_format($request->locked_amount, 2) . " applied to {$updated} agents.";
            } else {
                $updated = User::where('role', 'agent')
                    ->where('is_locked_override', false)
                    ->update(['locked_amount' => 0]);

                $message = "Lock system created (inactive). {$updated} agents unlocked.";
            }

            DB::commit();

            Log::info('Lock system created', [
                'amount' => $request->locked_amount,
                'status' => $request->status,
                'agents_updated' => $updated
            ]);

            return redirect()->route('locksystem.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lock creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Show edit form
     */
    public function edit(string $id)
    {
        $lock = LockedSetting::findOrFail($id);
        return view('admin.locksystem.edit', compact('lock'));
    }

    /**
     * Update lock system
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'locked_amount' => 'required|numeric|min:0',
            'status' => 'required|boolean'
        ]);

        DB::beginTransaction();
        try {
            $lock = LockedSetting::findOrFail($id);
            $lock->update([
                'locked_amount' => $request->locked_amount,
                'status' => $request->status
            ]);

            // Apply to non-overridden agents
            if ($request->status == 1) {
                $updated = User::where('role', 'agent')
                    ->where('is_locked_override', false)
                    ->update(['locked_amount' => $request->locked_amount]);

                $message = "Lock updated to $" . number_format($request->locked_amount, 2) . ". {$updated} agents affected.";
            } else {
                $updated = User::where('role', 'agent')
                    ->where('is_locked_override', false)
                    ->update(['locked_amount' => 0]);

                $message = "Lock deactivated. {$updated} agents unlocked.";
            }

            DB::commit();

            Log::info('Lock system updated', [
                'amount' => $request->locked_amount,
                'status' => $request->status
            ]);

            return redirect()->route('locksystem.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lock update failed', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle global lock status
     */
    public function toggleStatus(string $id)
    {
        DB::beginTransaction();
        try {
            $lock = LockedSetting::findOrFail($id);
            $newStatus = !$lock->status;
            $lock->update(['status' => $newStatus]);

            // Apply to non-overridden agents only
            if ($newStatus) {
                $updated = User::where('role', 'agent')
                    ->where('is_locked_override', false)
                    ->update(['locked_amount' => $lock->locked_amount]);

                $message = "Lock activated! $" . number_format($lock->locked_amount, 2) . " applied to {$updated} agents.";
            } else {
                $updated = User::where('role', 'agent')
                    ->where('is_locked_override', false)
                    ->update(['locked_amount' => 0]);

                $message = "Lock deactivated. {$updated} agents unlocked.";
            }

            DB::commit();

            Log::info('Lock status toggled', [
                'lock_id' => $id,
                'new_status' => $newStatus,
                'agents_updated' => $updated
            ]);

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Toggle status failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete lock system
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            LockedSetting::findOrFail($id)->delete();

            // Reset all agents
            $updated = User::where('role', 'agent')->update([
                'locked_amount' => 0,
                'is_locked_override' => false
            ]);

            DB::commit();

            Log::info('Lock system deleted');

            return back()->with('success', "Lock system deleted. All {$updated} agents unlocked.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lock delete failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ Show agent management page with total deposits
     */
    public function manageAgents()
    {
        $globalLock = LockedSetting::first();

        // ✅ Get agents with their total approved deposits
        $agents = User::where('role', 'agent')
            ->select('id', 'name', 'email', 'locked_amount', 'is_locked_override')
            ->withSum([
                'agentDeposites as total_deposit' => function ($query) {
                    $query->where('status', 'approved');
                }
            ], 'amount')
            ->orderBy('name')
            ->get();

        return view('admin.locksystem.manage-agents', compact('agents', 'globalLock'));
    }

    /**
     * ✅ Toggle individual agent lock/unlock
     */
    public function toggleAgentLock(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $agent = User::where('role', 'agent')->findOrFail($id);
            $lockType = $request->input('lock_type'); // 'lock' or 'unlock'
            $customAmount = $request->input('custom_amount', 0);

            if ($lockType == 'unlock') {
                // Unlock this agent (override global)
                $agent->locked_amount = 0;
                $agent->is_locked_override = true;
                $message = "✅ {$agent->name} unlocked (global lock ignored).";

            } elseif ($lockType == 'lock') {
                // Lock with custom/global amount
                $agent->locked_amount = $customAmount;
                $agent->is_locked_override = true;
                $message = "🔒 {$agent->name} locked with $" . number_format($customAmount, 2) . " (custom lock).";
            }

            $agent->save();

            DB::commit();

            Log::info('Agent lock toggled', [
                'agent_id' => $id,
                'agent_name' => $agent->name,
                'lock_type' => $lockType,
                'amount' => $agent->locked_amount
            ]);

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Agent lock toggle failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ Reset agent to global lock settings
     */
    public function resetToGlobal(string $id)
    {
        DB::beginTransaction();
        try {
            $agent = User::where('role', 'agent')->findOrFail($id);
            $globalLock = LockedSetting::where('status', 1)->first();

            // Reset to global
            $agent->is_locked_override = false;

            if ($globalLock) {
                $agent->locked_amount = $globalLock->locked_amount;
                $message = "🔄 {$agent->name} reset to global lock ($" . number_format($globalLock->locked_amount, 2) . ").";
            } else {
                $agent->locked_amount = 0;
                $message = "🔄 {$agent->name} reset (no global lock active).";
            }

            $agent->save();

            DB::commit();

            Log::info('Agent reset to global', [
                'agent_id' => $id,
                'agent_name' => $agent->name,
                'amount' => $agent->locked_amount
            ]);

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Agent reset failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed: ' . $e->getMessage()]);
        }
    }
}
