<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\ParamBag;
use Illuminate\Http\Request;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function respond($data)
    {
        $r = $_SERVER['REQUEST_TIME_FLOAT'];
        $requestTime = microtime(true) - $r;
        $responseObject = [
            "results" => $data,
            "info"    => [
                "count"        => count($data),
                "request_time" => round($requestTime, 4, PHP_ROUND_HALF_UP)
            ]
        ];

        return $responseObject;
    }

    protected function showCollection(\Illuminate\Database\Eloquent\Collection $model, TransformerAbstract $transformerAbstract, $total = 0)
    {
        $obj = new Collection($model, $transformerAbstract);
        $obj->setMetaValue("total", $total);
        $fractal = new Manager;
        //$this->fractal->setSerializer(new DataSerializer());
        $data = $fractal->createData($obj)->toArray();

        return $data;
    }

}
