<?php

namespace App\Contracts\Inventory;

use Illuminate\Http\Request;

interface PurchaseInterface
{
    public function generatePurchaseCode(): string;

    public function all(Request $request);

    public function list(Request $request);

    public function store(array $data);

    public function show($id);

    public function destroy(Request $request, $id);
}
