<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('createDatatable')) {
    function createDatatable($data, $filters, $total = 0, $msg = "Listado", $limit = 10)
    {
        // $limit = 10;
        $page = (intval($filters->start)) ? (intval($filters->start) / $limit) : 0;
        $start = $page * $limit;

        $recordsFiltered = $data->get()->count();

        if ($filters->column) :
            $query = $data->offset($start)->limit($limit)->orderBy($filters->column, $filters->sort);
        else :
            $query = $data->offset($start)->limit($limit);
        endif;

        $response = [
            'draw' => $filters->draw,
            'recordsTotal' => $total,
            'data' => $query->get(),
            'recordsFiltered' => $recordsFiltered,
            'msg' => $msg,
        ];

        return $response;
    }
}

if (!function_exists('datatableFilters')) {
    function datatableFilters($request)
    {
        $filters = (object)[];
        $columns = $request->columns;
        $order = $request->order;

        $filters->draw = $request['draw'];
        $filters->start = $request['start'];
        $filters->length = $request['length'];

        if ($order):
            $filters->column = $columns[$order[0]['column']]['name'];
            $filters->sort = $order[0]['dir'];
        endif;

        return $filters;
    }
}

if (!function_exists('createKtDatatable')) {
    function createKtDatatable($data, $pagination, $sort, $msg = "Listado", $total = null)
    {

        $total = is_null($total) ? $data->count() : $total;
        $perpage = ($pagination) ? intval($pagination['perpage']) : 50;
        $page = ($pagination) ? intval($pagination['page']) : 1;
        $pages = ceil($total / $perpage);

        $field = (!is_null($sort)) ? $sort['field'] : false;
        $sort = (!is_null($sort)) ? $sort['sort'] : false;
        $limit = $perpage * $page;
        $offset = $limit - $perpage;

        $meta = array(
            'pages' => $pages,
            'page' => $page,
            'perpage' => $perpage,
            'sort' => $sort,
            'total' => $total,
            'field' => $field
        );

        if (!$field or !$sort):
            $query = $data->offset($offset)->limit($perpage);
        else:
            // $field = ($field == 'date_formatted') ? 'created_at' : $field;
            $query = $data->offset($offset)->limit($perpage)->orderBy($field, $sort);
        endif;

        $response = [
            "meta" => $meta,
            "data" => $query->get(),
            "msg" => $msg,
        ];

        return $response;
    }
}

if (!function_exists('formatDate')) {
    function formatDate($table_column, $name = "date_formatted")
    {
        if (env('DB_CONNECTION') == 'mysql'): //:%s
            $date_formatted = "DATE_FORMAT(" . $table_column . ", '%d/%m/%Y %H:%i') AS " . $name;
        elseif (env('DB_CONNECTION') == 'pgsql'): //:ss
            $date_formatted = "to_char(" . $table_column . ", 'dd/mm/YY hh24:mi') AS " . $name;
        else:
            $date_formatted = "DATE_FORMAT(" . $table_column . ", '%d/%m/%Y %H:%i') AS " . $name;
        endif;

        return DB::raw($date_formatted);
    }
}

if (!function_exists('formatOnlyDate')) {
    function formatOnlyDate($table_column, $name = "date_formatted")
    {
        if (env('DB_CONNECTION') == 'mysql'): //:%s
            $date_formatted = "DATE_FORMAT(" . $table_column . ", '%d/%m/%Y') AS " . $name;
        elseif (env('DB_CONNECTION') == 'pgsql'): //:ss
            $date_formatted = "to_char(" . $table_column . ", 'dd/mm/YY') AS " . $name;
        else:
            $date_formatted = "DATE_FORMAT(" . $table_column . ", '%d/%m/%Y') AS " . $name;
        endif;

        return DB::raw($date_formatted);
    }
}

if (!function_exists('concatColumns')) {
    function concatColumns($as, $arrayColumns, $divisor = "' ' ")
    {
        // dd($arrayColumns);
        $concat = 'TRIM(CONCAT(';
        foreach ($arrayColumns as $key => $column):
            if (!$key)
                $concat .= ' || ' .
                    $concat .= $column;
        endforeach;
        $concat .= ') AS ' . $as . ')';

        return DB::raw($concat);
    }
}

if (!function_exists('rawUser')) {
    function rawUser($prefix = '', $name = 'user')
    {
        return DB::raw("TRIM(CONCAT(" . $prefix . "users.name, ' ', " . $prefix . "users.lastname, ' ', " . $prefix . "users.m_lastname)) AS " . $name);
    }
}

if (!function_exists('concatStringToColumn')) {
    function concatStringToColumn($string, $column, $as = 'url', $divisor = '/')
    {
        return DB::raw("CONCAT('" . $string . "', " . $divisor . ", " . $column . ") AS " . $as);
    }
}

if (!function_exists('tablePermissions')) {
    function tablePermissions($permission)
    {
        $table = (object)[];
        $table->permissions = (object)[];
        // $table->permissions->create = Auth::user()->isAbleTo('create-' . $permission);
        // $table->permissions->update = Auth::user()->isAbleTo('update-' . $permission);
        // $table->permissions->delete = Auth::user()->isAbleTo('delete-' . $permission);
        // $table->permissions->authorize = Auth::user()->isAbleTo('authorize-' . $permission);
        return $table;
    }
}

if (!function_exists('tableSearch')) {
    function tableSearch($data)
    {
        $filters = (object)[];

        $extras = $data['filters'] ?? $data;

        // if (in_array('statuses', $data))
        //     $filters->statuses = \App\Models\DocumentStatus::all();

        // if (in_array('priorities', $data))
        //     $filters->priorities = \App\Models\Priority::all();

        // if (in_array('typeDepartures', $data))
        //     $filters->typeDepartures = \App\Models\CatTypeDeparture::all();

        // if (in_array('suppliers', $data))
        //     $filters->suppliers = \App\Models\Supplier::all();

        // if (in_array('clients', $data))
        //     $filters->clients = \App\Models\Client::all();

        // if (in_array('warehouses', $data))
        //     $filters->warehouses = Auth::user()->warehouses;

        // if (in_array('production_statuses', $extras)) {
        //     if (isset($data['production_statuses']) && is_array($data['production_statuses'])) {
        //         $filters->production_statuses = \App\Models\ProductionOrderStatus::whereIn('id', $data['production_statuses'])->get();
        //     } else {
        //         $filters->production_statuses = \App\Models\ProductionOrderStatus::all();
        //     }
        // }

        // if (in_array('product_codes', $data)) {
        //     $filters->product_codes = true;
        // }

        // if (in_array('products', $data)) {
        //     $filters->products = \App\Models\Product::all();
        // }

        if (in_array('date_range', $data))
            $filters->date_range = true;

        return $filters;
    }
}
