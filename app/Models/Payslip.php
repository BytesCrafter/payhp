<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Payslip extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payslips';
    //protected $fillable = ['fullname','email']; //all

    public function read(array $options) {
        $wheres = [];

        $id = get_array_value($options, 'id');
        if ($id) {
            $wheres[] = ['id', '=', $id];
        }
        $uuid = get_array_value($options, 'uuid');
        if ($uuid) {
            $wheres[] = ['uuid', '=', $uuid];
        }
        $generated_by = get_array_value($options, 'generated_by');
        if ($generated_by) {
            $wheres[] = ['generated_by', '=', $generated_by];
        }

        $query = DB::table($this->table);
        foreach($wheres as $where) {
            $query->where($where[0], $where[1], $where[2]);
        }

        return $query->get();
    }

    public function store($data) {
        // $toinsert = [];
        // foreach($data as $item) {
        //     $toinsert[] = $item;
        // }

        $query = DB::table($this->table)->insert([ //TODO
            $data
        ]);
        return $query;
    }

    public function edit(array $data, array $options) {

        $wheres = [];
        $id = get_array_value($options, 'id');
        if ($id) {
            $wheres[] = ['id', '=', $id];
        }

        $query = DB::table($this->table);
        $query->update( //TODO
            [$data]
        );

        foreach($wheres as $where) {
            $query->where($where[0], $where[1], $where[2]);
        }

        return $query;
    }
}
