<?php

namespace App\Http\Controllers\Spot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Spot;
use App\Models\Camera;

class SpotDashboardController extends Controller
{
    public function congestionsSpot($id)
    {
        $spots = Spot::where('users_id', $id)->get(['spots_id', 'spots_name', 'spots_count_day1', 'spots_violations', 'spots_max', 'spots_over_time']);
        $dataAll = [];
        $spotsId = [];

        if (count($spots) === 0) {
            return response()->json([
                [
                    "spots_name" => "駐輪場がありません",
                    "spots_violations" => [],
                    "spots_count_day1" => [],
                    "spots_congestion" => 0
                ]
            ], Response::HTTP_OK);
        }

        for ($i = 0; $i < count($spots); $i++) {
            array_push($spotsId, $spots[$i]['spots_id']);
        }

        $cameraCount = Camera::where('spots_id', $spotsId)->get(['spots_id', 'cameras_count']);

        for ($i = 0; $i < count($spots); $i++) {
            //推移
            $violationStr = explode(",",$spots[$i]["spots_violations"]);
            $violationInt = array_map('intval', $violationStr);
            $day1Str = explode(",",$spots[$i]["spots_count_day1"]);
            $day1Int = array_map('intval', $day1Str);

            // グラフの色を指定
            $r = rand(130, 255);
            $g = rand(130, 255);
            $b = rand(180, 255);
            $borderColor = "rgba(". (string)$r . ", " . (string)$g . ", " . (string)$b . ", " . "1)";

            // 混雑度
            $count = 0;

            for ($j = 0; $j < count($cameraCount); $j++) {
                if ($cameraCount[$j]['spots_id'] === $spots[$i]['spots_id']){
                    $count = $count + $cameraCount[$j]['cameras_count'];                        
                }
            }

            $data = [
                'spots_name' => $spots[$i]['spots_name'],
                'spots_violations' => $violationInt,
                'spots_count_day1' => $day1Int,
                'spots_congestion' => 100 * $count / $spots[$i]['spots_max'],
                'spots_over_time' => $spots[$i]['spots_over_time'],
                "border_color" => $borderColor
            ];

            array_push($dataAll, $data);
        }

        return response()->json($data, Response::HTTP_OK);
    }   
}
