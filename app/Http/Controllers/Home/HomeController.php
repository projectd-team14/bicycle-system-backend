<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Spot;
use App\Models\Bicycle;
use App\Models\Camera;

class HomeController extends Controller
{
    //駐輪情報
    public function spotData($id){
        $spots = Spot::where('spots_id', $id)->get();
        $bicycles = Bicycle::where('spots_id', $id)->get();
        $day1Str = explode(",",$spots[0]["spots_count_day1"]);
        $day1Int = array_map('intval', $day1Str);
        $day1Count = count($day1Int);
        $week1Str = explode(",",$spots[0]["spots_count_week1"]);
        $week1Int = array_map('intval', $week1Str);
        $week1Count = count($week1Int);
        $month1Str = explode(",",$spots[0]["spots_count_month1"]);
        $month1Int = array_map('intval', $month1Str);
        $month1Count = count($month1Int);
        $month3Str = explode(",",$spots[0]["spots_count_month3"]);
        $month3Int = array_map('intval', $month3Str);
        $month3Count = count($month3Int);

        $dataDay1 = [
            "label" => "1日間",
            "backgroundColor" => "#f87979",
            "data" => $day1Int,
            "labels" => range(0, 23)
        ];

        $weekLabelsData = [];
        
        for ($i = 0; $i < 7; $i++) {
            $objDateTime = date('Y-m-d', strtotime("-$i day"));
            array_push($weekLabelsData, $objDateTime);
        }

        $weekLabelsData = array_reverse($weekLabelsData);
        $dataWeek1 = [
            "label" => "1週間",
            "backgroundColor" => "#f87979",
            "data" => $week1Int,
            "labels" => $weekLabelsData
        ];

        $oneMonthLabelsData = [];

        for ($i = 0; $i < 30; $i++) {
            $objDateTime = date('Y-m-d', strtotime("-$i day"));
            array_push($oneMonthLabelsData, $objDateTime);
        }

        $oneMonthLabelsData = array_reverse($oneMonthLabelsData);
        $dataMonth1 = [
            "label" => "1か月間",
            "backgroundColor" => "#f87979",
            "data" => $month1Int,
            "labels" => $oneMonthLabelsData
        ];

        $threeMonthLabelsData = [];

        for ($i = 0; $i < 30; $i++) {
            $objDateTime = date('Y-m-d', strtotime("-$i day"));
            array_push($threeMonthLabelsData, $objDateTime);
        }

        $threeMonthLabelsData = array_reverse($threeMonthLabelsData);
        $dataMonth3 = [
            "label" => "３か月間",
            "backgroundColor" => "#f87979",
            "data" => $month3Int,
            "labels" => $threeMonthLabelsData
        ];

        $dataAll1 = [
            $dataDay1,
            $dataWeek1,
            $dataMonth1,
            $dataMonth3
        ];

        //numberChartData
        $numberChartDataDay1 = array_fill(0, 12, 0);
        $numberChartDataWeek1 = array_fill(0, 12, 0);
        $numberChartDataMonth1 = array_fill(0, 12, 0);
        $numberChartDataMonth3 = array_fill(0, 12, 0);

        for ($i=0; $i < count($numberChartDataDay1) - 1; $i++) {
            for ($j=0; $j < count($bicycles); $j++) {
                $timeBicycle = (strtotime($bicycles[$j]['updated_at']) - strtotime($bicycles[$j]['created_at'])) / 3600 ;
                if ($timeBicycle >= $i && $timeBicycle < $i+1) {
                    $numberChartDataDay1[$i] += 1;
                }
            }
        }
        
        for ($i=0; $i < count($bicycles); $i++) {
            $timeBicycle = (strtotime($bicycles[$i]['updated_at']) - strtotime($bicycles[$i]['created_at'])) / 3600 ;
            if ($timeBicycle>=10) {
                $numberChartDataDay1[11] += 1;
            }
        }

        $dataDay1 = [
            "label" => "1日間",
            "backgroundColor" => "#f87979",
            "data" => $numberChartDataDay1,
        ];
        $dataWeek1 = [
            "label" => "1週間",
            "backgroundColor" => "#f87979",
            "data" => $numberChartDataWeek1,
        ];
        $dataMonth1 = [
            "label" => "1か月間",
            "backgroundColor" => "#f87979",
            "data" => $numberChartDataMonth1,
        ];
        $dataMonth3 = [
            "label" => "３か月間",
            "backgroundColor" => "#f87979",
            "data" => $numberChartDataMonth3,
        ];       
        $dataAll2 = [
            $dataDay1,
            $dataWeek1,
            $dataMonth1,
            $dataMonth3
        ];

        return response()->json([
            'situationChartData' => $dataAll1,
            "numberChartData" => $dataAll2
        ], Response::HTTP_OK);
    }

    // カメラ情報
    public function cameraData($id) {
        $cameras  = Camera::where('spots_id', $id)->get([
            'cameras_id',
            'spots_id',
            'cameras_name',
            'cameras_url',
            'cameras_status',
            'cameras_count'
        ]);

        return response()->json($cameras, Response::HTTP_OK);
    }

    //全情報
    public function homeData($id) {
        $spots = Spot::where('users_id', $id)->get();
        $spotsDataAll = [];
        $spotsId = [];
        $monthLabelsData = [];

        for ($i = 0; $i < 30; $i++) {
            $objDateTime = date('Y-m-d', strtotime("-$i day"));
            array_push($monthLabelsData, $objDateTime);
        }

        $monthLabelsData = array_reverse($monthLabelsData);

        for ($i = 0; $i < count($spots); $i++) {
            array_push($spotsId, $spots[$i]['spots_id']);
        }

        $cameraAll = Camera::where('spots_id', $spotsId)->get(['cameras_id', 'spots_id', 'cameras_name', 'cameras_url']);
        $bicycleAll = Bicycle::where('spots_id', $spotsId)->whereIn('bicycles_status', ['None', '違反'])->get();

        for ($j = 0; $j < count($spots); $j++){
            $day1Str = explode(",",$spots[$j]["spots_count_day1"]);
            $day1Int = array_map('intval', $day1Str);
            $violationStr = explode(",",$spots[$j]["spots_violations"]);
            $violationInt = array_map('intval', $violationStr);
            // グラフの色を指定
            $r = rand(130, 255);
            $g = rand(130, 255);
            $b = rand(180, 255);
            $borderColor = "rgba(". (string)$r . ", " . (string)$g . ", " . (string)$b . ", " . "1)";

            //cameraの項目
            $cameraNew = [];
            $cameras = [];

            for ($i = 0; $i < count($cameraAll); $i++) {
                if ($cameraAll[$i]['spots_id'] === $spots[$j]['spots_id']) {
                    array_push($cameras, $cameraAll[$i]);
                }
            }

            if (count($cameras) == 0) {
                for ($i=0; $i <= count($cameras); $i++) {
                    $cameraNew[$i]= [
                        'id' => [],
                        'name' => [],
                        'url' => [],
                    ];
                }
            } else {
                for ($i = 0; $i < count($cameras); $i++){
                    $cameraNew[$i]= [
                        'id' => $cameras[$i]['cameras_id'],
                        'name' => $cameras[$i]['cameras_name'],
                        'url' => $cameras[$i]['cameras_url'],
                    ];
                }
            }

            //situationの項目
            $labelNames = [];
            $bicycles = [];

            for ($i = 0; $i < count($bicycleAll); $i++) {
                if ($bicycleAll[$i]['spots_id'] === $spots[$j]['spots_id']) {
                    array_push($bicycles, $bicycleAll[$i]);                    
                }
            }

            for ($i = 0; $i < count($bicycles); $i++) {
                if (!(in_array($bicycles[$i]['labels_name'], $labelNames))) {
                    array_push($labelNames, $bicycles[$i]['labels_name']); 
                }
            }

            $labelNamesNew = array_unique($labelNames);

            if (count($labelNamesNew) == 0) {
                for ($i = 0; $i <= count($labelNamesNew); $i++) {
                    $situation[$i]= [
                        'row' => [],
                        'bicycle' => [],
                    ];
                }
            } else {
                for($i = 0; $i < count($labelNamesNew); $i++) {
                    $inLabelBicycles = [];

                    for ($k = 0; $k < count($bicycles); $k++) {
                        if ($bicycles[$k]['labels_name'] === $labelNamesNew[$i]) {
                            array_push($inLabelBicycles, $bicycles[$k]);
                        }
                    }

                    for ($k = 0; $k < count($inLabelBicycles); $k++) {
                        if ($inLabelBicycles[$k]['bicycles_status'] == 'None'){
                            $inLabelBicycles[$k]['bicycles_status'] = false;
                        } elseif ($inLabelBicycles[$k]['bicycles_status'] == '違反'){
                            $inLabelBicycles[$k]['bicycles_status'] = true;
                        } else {
                            $inLabelBicycles[$k]['bicycles_status'] = false;
                        }
                        $time = strtotime($inLabelBicycles[$k]['updated_at']) - strtotime($inLabelBicycles[$k]['created_at']);
                        $bicycleNew[$k]= [
                            'id' => $inLabelBicycles[$k]['get_id'],
                            'cameras_id' => $inLabelBicycles[$k]['cameras_id'],
                            'time' => $time,
                            'violatin_status' => $inLabelBicycles[$k]['bicycles_status'],
                            'violatin_img' => "None",
                        ];
                    }

                    $situation[$i]= [
                        'row' => $labelNamesNew[$i],
                        'bicycle' => $bicycleNew,
                    ];
                }
            }

            $allData = [
                'id' => $spots[$j]['spots_id'],
                'name' => $spots[$j]['spots_name'],
                'address' => $spots[$j]['spots_address'],
                'latitude' => $spots[$j]['spots_latitude'],
                'longitude' => $spots[$j]['spots_longitude'],
                'border_color' => $borderColor,
                'max' => $spots[$j]['spots_max'],
                'count' => end($day1Int),
                'count_day1' => $day1Int,
                'count_violations'=> $violationInt,
                'overtime' => $spots[$j]['spots_over_time'],
                'url'=> $spots[$j]['spots_url'],
                'camera' => $cameraNew,
                'situation' => $situation,
                'month_labels' => $monthLabelsData
            ];
            array_push($spotsDataAll,$allData);
            unset($allData);
            unset($situation);
            unset($cameraNew);
        }

        return response()->json($spotsDataAll, Response::HTTP_OK);
    }
}
