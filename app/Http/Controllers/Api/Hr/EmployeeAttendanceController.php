<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Hr\EmployeeAttendance\StoreEmployeeAttendanceRequest;
use App\Http\Requests\Api\Hr\EmployeeAttendance\UpdateEmployeeAttendanceRequest;
use App\Http\Resources\Hr\EmployeeAttendanceResource;
use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeAttendanceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->checkAuthorization('employeeAttendance_access');

        $request->validate([
            'date' => ['required'],
            'department_id' => ['nullable', Rule::exists('departments', 'id')->withoutTrashed()],
            'designation_id' => ['nullable', Rule::exists('designations', 'id')->withoutTrashed()],
        ]);

        $employeeAttendances = Employee::with(['employeeAttendance' => function ($query) use ($request) {
            $query->whereDate('date', $request->input('date'));
        }])->where(function ($query) use ($request) {
            if (! empty($request->input('department_id'))) {
                $query->where('department_id', $request->input('department_id'));
            }
            if (! empty($request->input('designation_id'))) {
                $query->where('designation_id', $request->input('designation_id'));
            }
        })->orderBy('rank')->get();

        return EmployeeAttendanceResource::collection($employeeAttendances);
    }

    public function store(StoreEmployeeAttendanceRequest $request): JsonResponse
    {
        $this->checkAuthorization('employeeAttendance_create');

        DB::transaction(function () use ($request) {
            foreach ($request->validated()['employees'] as $employee) {
                EmployeeAttendance::updateOrCreate(
                    ['id' => $employee['attendance_id']],
                    [
                        'employee_id' => $employee['employee_id'],
                        'date' => $request->input('date'),
                        'status' => $employee['status'],
                        'in_time' => $employee['status'] === 'Present' ? $employee['in_time'] : null,
                        'out_time' => $employee['status'] === 'Present' ? $employee['out_time'] : null,
                        'remarks' => $employee['remarks'],
                    ]
                );
            }
        });

        return response()->json([
            'data' => '',
            'message' => 'Employee Attendance Updated Successfully',
        ]);
    }

    public function show(EmployeeAttendance $employeeAttendance): EmployeeAttendanceResource
    {
        $this->checkAuthorization('employeeAttendance_access');

        return EmployeeAttendanceResource::make($employeeAttendance);
    }

    public function update(UpdateEmployeeAttendanceRequest $request, EmployeeAttendance $employeeAttendance): JsonResponse
    {
        $this->checkAuthorization('employeeAttendance_edit');
        $employeeAttendance->update($request->validated());

        return response()->json([
            'data' => new EmployeeAttendanceResource($employeeAttendance),
            'message' => 'Employee attendance updated successfully',
        ]);
    }

    public function destroy(EmployeeAttendance $employeeAttendance): JsonResponse
    {
        $this->checkAuthorization('employeeAttendance_delete');

        $employeeAttendance->delete();

        return response()->json([
            'data' => '',
            'message' => 'Employee attendance deleted successfully',
        ], 200);
    }
}
