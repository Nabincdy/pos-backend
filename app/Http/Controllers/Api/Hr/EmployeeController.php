<?php

namespace App\Http\Controllers\Api\Hr;

use App\Exports\Hr\EmployeeSampleExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Hr\Employee\ImportEmployeeRequest;
use App\Http\Requests\Api\Hr\Employee\StoreEmployeeRequest;
use App\Http\Requests\Api\Hr\Employee\UpdateEmployeeRequest;
use App\Http\Resources\Hr\EmployeeResource;
use App\Imports\Hr\EmployeeImport;
use App\Models\Hr\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function employeeCode()
    {
        return companySetting()->employee.Str::padLeft(Employee::max('id') + 1, 3, 0);
    }

    public function index(): AnonymousResourceCollection
    {
        $this->checkAuthorization('employee_access');

        return EmployeeResource::collection(Employee::with('designation', 'department', 'user')->get());
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $this->checkAuthorization('employee_create');

        $employee = DB::transaction(function () use ($request) {
            $employee = Employee::create($request->validated());
            if (! empty($request->input('role_id'))) {
                $employee->user()->create([
                    'role_id' => $request->input('role_id'),
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => 'password',
                ]);
            }

            return $employee;
        });

        return response()->json([
            'data' => new EmployeeResource($employee->load('department', 'designation')),
            'message' => 'Employee Added Successfully',
        ], 201);
    }

    public function show(Employee $employee): EmployeeResource
    {
        $this->checkAuthorization('employee_access');

        return EmployeeResource::make($employee->load('department', 'designation'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $this->checkAuthorization('employee_edit');

        if ($request->hasFile('photo') && $employee->photo) {
            $this->deleteFile($employee->photo);
        }
        if ($request->hasFile('signature') && $employee->signature) {
            $this->deleteFile($employee->signature);
        }

        $employee->update($request->validated());

        return response()->json([
            'data' => new EmployeeResource($employee->load('department', 'designation')),
            'message' => 'Employee updated successfully',
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->checkAuthorization('employee_delete');

        if ($employee->photo) {
            $this->deleteFile($employee->photo);
        }
        if ($employee->signature) {
            $this->deleteFile($employee->signature);
        }
        $employee->delete();

        return response()->json([
            'data' => '',
            'message' => 'Employee deleted successfully',
        ], 200);
    }

    public function employeeExport()
    {
        return Excel::download(new EmployeeSampleExport(), 'employee_entry_format.xlsx');
    }

    public function employeeImport(ImportEmployeeRequest $request)
    {
        $this->checkAuthorization('employee_create');
        Excel::import(new EmployeeImport($request), $request->file('excel_file'));

        return response()->json([
            'data' => '',
            'message' => 'Employee Imported Successfully',
        ]);
    }

    public function updateStatus(Employee $employee)
    {
        $this->checkAuthorization('employee_edit');

        $employee->update([
            'status' => ! $employee->status,
        ]);

        return response([
            'status' => $employee->status,
            'message' => 'Status Updated Successfully',
        ]);
    }
}
