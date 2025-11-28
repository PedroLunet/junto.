<?php

namespace App\Http\Controllers\Post;

use Illuminate\Http\Request;
use App\Models\Post\Report;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    /**
     * Submit a report for a post
     */
    public function reportPost(Request $request, $id)
    {
        $this->authorize('create', Report::class);

        $request->validate([
            'reason' => 'required|string|min:10|max:1000'
        ]);

        try {
            Report::createPostReport($id, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Report submitted successfully. Our team will review it shortly.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit report. Please try again.'
            ], 500);
        }
    }

    /**
     * Get all reports (admin only)
     */
    public function index()
    {
        $this->authorize('viewAny', Report::class);

        $reports = Report::getAllReports();

        return view('pages.reports', compact('reports'));
    }

    /**
     * Get pending reports (admin only)
     */
    public function pending()
    {
        $this->authorize('viewAny', Report::class);

        $reports = Report::getPendingReports();

        return view('pages.reports', compact('reports'));
    }

    /**
     * Update report status (admin only)
     */
    public function updateStatus(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $this->authorize('update', $report);

        $request->validate([
            'status' => 'required|in:accepted,rejected'
        ]);

        try {
            Report::updateReportStatus($id, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Report status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update report status.'
            ], 500);
        }
    }
}
