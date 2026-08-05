<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use stdClass;

class StateController extends Controller
{
    /**
     * getStatesData function
     *
     * @return View
     */
    public function getStatesData() : View
    {
        $table = new stdClass;
        $table->filters = tableSearch([/* 'statuses', 'priorities', */'clients', /* 'warehouses' */]);
        $table->list = route('budgets.list');
        $table->js = 'states-table';
        $table->name = 'states-table';

        return view('states');
    }

    public function budgetsList()
    {
        return response()->json([]);
    }
}
