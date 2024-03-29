<?php

namespace App\Http\Controllers\Spot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Spot;
use App\Models\Camera;
use App\Models\Bicycle;
use App\Jobs\CreateSpotJob;
use App\Jobs\DeleteSpotJob;
use Illuminate\Support\Facades\Log;

class SpotController extends Controller
{
    public function editSpot($id){
        $cameras = Spot::where('users_id', $id)->get();

        return response()->json($cameras, Response::HTTP_OK);
    }

    public function storeSpot(Request $request, $id)
    {
        $data = $request->all();
        $query = $data['spots_address'];
        
        // 外部のAPIから位置情報を取得
        $coordinateData= $this->getCoordinate($query);

        $spotStatus = 0;

        if ($data['spots_status'] === '自転車') {
            $spotStatus = 1;
        } else if ($data['spots_status'] === 'バイク') {
            $spotStatus = 2;
        } else if ($data['spots_status'] === '全てを管理') {
            $spotStatus = 3;
        }

        // XSS対策（攻撃そのものの対策ではなく、HTMLタグをDBやレスポンスに含めないようにする）
        if ($this->htmlValidation($data)) {
            return response()->json('使用できない文字が含まれています', Response::HTTP_OK);
        }

        $spotId = Spot::insertGetId([
             'spots_name' => $data['spots_name'],
             'users_id' => $id, 
             'spots_longitude' => $coordinateData['spots_longitude'], 
             'spots_latitude' => $coordinateData['spots_latitude'],
             'spots_address' => $data['spots_address'],
             'spots_status' => $spotStatus,
             'spots_count_day1' => 'None',
             'spots_count_week1' => 'None',
             'spots_count_month1' => 'None',
             'spots_count_month3' => 'None',
             'spots_violations' => 'None',
             'spots_over_time' => $data['spots_over_time'] * 3600,
             'spots_max' => $data['spots_max'],
             'spots_url' => $data['spots_url'],
        ]);      

        $this->createSpotLog($data);

        return response()->json($data, Response::HTTP_OK);
    }

    public function deleteSpot($id)
    {
        $deleteSpotName = Spot::where('spots_id', $id)->get(['spots_name']);
        $this->deleteSpotLog($deleteSpotName);
        Spot::where('spots_id', $id)->delete();
        Camera::where('spots_id', $id)->delete();

        return response()->json(['message' => '削除しました'], Response::HTTP_OK);
    }

    private function htmlValidation($data)
    {
        $xssData = array_values($data);

        for ($i = 0; $i < count($xssData); $i++) {
            if (preg_match('/<.*>/', $xssData[$i])) {
                return true;
            }
        }

        return false;
    }

    private function getCoordinate($query)
    {
        for ($i =0; $i <= 5; $i++) {
            try {
                $query = urlencode($query);
                $url = "http://www.geocoding.jp/api/";
                $url .= "?v=1.1&q=" . $query;
                $line = "";
                $fp = fopen($url, "r");
        
                while(!feof($fp)) {
                    $line .= fgets($fp);
                }
        
                fclose($fp);
                $xml = simplexml_load_string($line);
                $insertLong = (string) $xml->coordinate->lng;
                $insertLat = (string) $xml->coordinate->lat;

                if (!is_null($insertLong) || !is_null($insertlat)) {
                    Log::info("座標の取得処理の処理が成功しました。");
                    $coordinateData = [
                        'spots_longitude' => $insertLong, 
                        'spots_latitude' => $insertLat,
                    ];
            
                    return $coordinateData;                
                }

                Log::info("座標の取得処理が正常に終了しなかったためリトライします。");
                throw new \Exception();
            } catch (\Exception $e) {
                Log::info("座標の取得処理が正常に終了しなかったためリトライします。");
                
                if ($i >= 5) {
                    Log::error("座標の取得処理でエラーが発生したため強制終了しました。");

                    return;
                }
            }            
        }
    }

    private function createSpotLog($data)
    {
        CreateSpotJob::dispatch($data);
    }

    private function deleteSpotLog($deleteSpotName)
    {
        DeleteSpotJob::dispatch($deleteSpotName);
    } 
}
