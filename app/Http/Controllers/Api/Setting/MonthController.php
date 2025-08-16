<?php

namespace App\Http\Controllers\Api\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Setting\Month\StoreMonthRequest;
use App\Http\Requests\Api\Setting\Month\UpdateMonthRequest;
use App\Http\Resources\Setting\MonthResource;
use App\Models\Setting\Month;

class MonthController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('month_access');

        $months = Month::orderBy('rank')->get();

        return MonthResource::collection($months);
    }

    public function store(StoreMonthRequest $request)
    {
        $this->checkAuthorization('month_create');

        $month = Month::create($request->validated());

        return response()->json([
            'data' => new MonthResource($month),
            'message' => 'Month Created Successfully',
        ], 201);
    }

    public function show(Month $month)
    {
        $this->checkAuthorization('month_access');

        return new MonthResource($month);
    }

    public function update(UpdateMonthRequest $request, Month $month)
    {
        $this->checkAuthorization('month_edit');

        $month->update($request->validated());

        return response()->json([
            'data' => new MonthResource($month),
            'message' => 'Month Updated Successfully',
        ]);
    }

    public function destroy(Month $month)
    {
        $this->checkAuthorization('month_delete');

        $month->delete();

        return response()->json([
            'data' => '',
            'message' => 'Month Deleted Successfully',
        ]);
    }
}
