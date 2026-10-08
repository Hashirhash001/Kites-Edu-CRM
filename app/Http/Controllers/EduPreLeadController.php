<?php

namespace App\Http\Controllers;

use App\Models\EduLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EduPreLeadController extends EduLeadController
{
    public function index(Request $request)
    {
        return $this->renderLeadsIndex($request, true);
    }

    public function convertToLead(Request $request, EduLead $eduLead)
    {
        $user = Auth::user();

        if (!$user->canCreateLeads()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!$eduLead->is_pre_lead) {
            return response()->json(['success' => false, 'message' => 'This record is already a lead.'], 422);
        }

        $eduLead->update(['is_pre_lead' => false]);

        Log::info('Pre-lead converted to lead', [
            'lead_id'   => $eduLead->id,
            'lead_code' => $eduLead->lead_code,
            'user_id'   => $user->id,
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'Pre-lead converted to Education Lead successfully.',
            'redirect_url' => route('edu-leads.show', $eduLead->id),
        ]);
    }
}
