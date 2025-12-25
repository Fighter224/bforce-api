<?php

namespace App\Http\Controllers;

use App\Models\TodayTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TodayTaskController extends Controller
{
    /**
     * Get all today's tasks for the authenticated technician.
     */
    public function index(Request $request)
    {
        // Get technician_id from request parameter
        $technicianId = $request->input('technician_id');

        if (!$technicianId) {
            return response()->json([
                'success' => false,
                'message' => 'technician_id is required'
            ], 400);
        }

        $today = Carbon::today()->toDateString();

        \Log::info("Fetching today tasks", [
            'technician_id' => $technicianId,
            'today' => $today,
            'server_time' => Carbon::now()->toDateTimeString()
        ]);

        $tasks = TodayTask::where('user_id', $technicianId)
            ->whereDate('created_at', $today)
            ->get();

        \Log::info("Found tasks", ['count' => $tasks->count()]);

        return response()->json([
            'success' => true,
            'data' => $tasks
        ], 200);
    }

    /**
     * Create or update a task specifically for today.
     * Logic: If a task of this type exists for today, update it? Or just create new?
     * The frontend sends a list or single update. Let's handle single store/update.
     */
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('TodayTask Store Request:', $request->all());
        if ($request->hasFile('file')) {
            \Illuminate\Support\Facades\Log::info('File present: ' . $request->file('file')->getClientOriginalName());
        } else {
            \Illuminate\Support\Facades\Log::info('No file in request.');
        }

        $validator = Validator::make($request->all(), [
            'technician_id' => 'required|string',
            'task_type' => 'required|string',
            'label' => 'nullable|string',
            'status' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB max
            'details' => 'nullable|string',
            'meta_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $technicianId = $request->input('technician_id');

        // Handle File Upload
        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Store in specific folder
            $filePath = $file->storeAs('task_uploads/' . $technicianId, $filename, 'public');
        } elseif ($request->has('file_path')) {
            // Allow manual string path (for mock/simulated uploads)
            $filePath = $request->input('file_path');
        }

        // Create Task
        $task = TodayTask::create([
            'user_id' => $technicianId,
            'task_type' => $request->task_type,
            'label' => $request->label,
            'status' => $request->status,
            'file_path' => $filePath,
            'details' => $request->details,
            'meta_data' => $request->meta_data // casts to json automatically
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task saved successfully',
            'data' => $task
        ]);
    }

    /**
     * Update an existing task
     */
    public function update(Request $request, $id)
    {
        $technicianId = $request->input('technician_id');

        if (!$technicianId) {
            return response()->json([
                'success' => false,
                'message' => 'technician_id is required'
            ], 400);
        }

        $task = TodayTask::find($id);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Verify ownership
        if ($task->user_id !== $technicianId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Handle File Upload if new file exists
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('task_uploads/' . $technicianId, $filename, 'public');
            $task->file_path = $path;
        } elseif ($request->has('file_path')) {
            $task->file_path = $request->input('file_path');
        }

        // Update fields if present
        if ($request->has('status'))
            $task->status = $request->input('status');
        if ($request->has('details'))
            $task->details = $request->input('details');
        if ($request->has('meta_data'))
            $task->meta_data = $request->input('meta_data');

        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $task
        ]);
    }
}
