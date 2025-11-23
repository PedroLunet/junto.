<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

class ReportController extends Controller
{
  /**
   * Submit a report for a post
   */
  public function reportPost(Request $request, $id)
  {
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
    // Check if user is admin
    if (!auth()->check() || !auth()->user()->isAdmin) {
      abort(403, 'Unauthorized');
    }

    $reports = Report::getAllReports();

    return view('pages.reports', compact('reports'));
  }

  /**
   * Get pending reports (admin only)
   */
  public function pending()
  {
    // Check if user is admin
    if (!auth()->check() || !auth()->user()->isAdmin) {
      abort(403, 'Unauthorized');
    }

    $reports = Report::getPendingReports();

    return view('pages.reports', compact('reports'));
  }

  /**
   * Update report status (admin only)
   */
  public function updateStatus(Request $request, $id)
  {
    // Check if user is admin
    if (!auth()->check() || !auth()->user()->isAdmin) {
      return response()->json([
        'success' => false,
        'message' => 'Unauthorized'
      ], 403);
    }

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
