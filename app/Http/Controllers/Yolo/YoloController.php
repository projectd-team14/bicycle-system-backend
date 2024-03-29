<?php

namespace App\Http\Controllers\Yolo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Camera;
use App\Models\Spot;
use App\Models\Label;
use App\Models\Bicycle;
use App\Jobs\YoloUpdateJob;
use App\Jobs\YoloDeleteJob;
use App\Jobs\YoloViolationJob;

class YoloController extends Controller
{
    public function getCameraAll()
    {
        $cameraAll = Camera::get(['spots_id', 'cameras_count']);

        return response()->json($cameraAll, Response::HTTP_OK);
    }

    public function getCameraStatus($id)
    {
        $cameraStatus = Camera::where('cameras_id', $id)->get('cameras_status');
        
        return response()->json($cameraStatus, Response::HTTP_OK);
    }

    public function getCameraStop($id)
    {
        Bicycle::where('cameras_id', $id)->delete();
        
        return response()->json("終了", Response::HTTP_OK);
    }

    public function getCameraCount($id, $count)
    {
        Camera::where('cameras_id', $id)->update(['cameras_count' => $count]);
        
        return response()->json($count, Response::HTTP_OK);
    }

    public function getUrl($id)
    {
        $cameraUrl = Camera::where('cameras_id', $id)->get('cameras_url');
        
        return response()->json($cameraUrl, Response::HTTP_OK);
    }

    public function overTime($id)
    {
        $spotId = Camera::where('cameras_id', $id)->get('spots_id');
        $overTime = Spot::where('spots_id', $spotId[0]["spots_id"])->get(['spots_id', 'spots_over_time', 'spots_status']);

        return response()->json($overTime, Response::HTTP_OK);
    }

    public function getLabel($id)
    {
        $labelJson = Label::where('cameras_id', $id)->get('labels_json');

        return response()->json($labelJson, Response::HTTP_OK);
    }

    public function getId($id)
    {
        $labelJson = Bicycle::where('cameras_id', $id)->get('get_id');
        $idList = [];
        for ($i=0; $i<count($labelJson); $i++) {
            array_push($idList,$labelJson[$i]['get_id']); 
        }

        return response()->json($idList, Response::HTTP_OK);
    }

    public function bicycleStatus($camera_id, $get_id)
    {
        $bicycleStatus = Bicycle::where('cameras_id', $camera_id)->where('get_id', $get_id)->get(['bicycles_id', 'bicycles_status', 'updated_at', 'created_at']);
        $bicycleList = [$bicycleStatus[0]['bicycles_id'], $bicycleStatus[0]['bicycles_status'], $bicycleStatus[0]['updated_at'], $bicycleStatus[0]['created_at']];

        return response()->json($bicycleList, Response::HTTP_OK);
    }

    public function bicycleUpdate(Request $request)
    {
        $inputs = $request->all();
        $data = $this->dispatch(new YoloUpdateJob($inputs));
        $job = new YoloUpdateJob($inputs);
        $this->dispatchSync($job);
        $result = $job->getResult(); 

        return response()->json($result, Response::HTTP_OK);
    }

    public function bicycleDelete(Request $request, $camera_id)
    {
        $inputs = $request->all();

        $job = new YoloDeleteJob($inputs, $camera_id);
        $this->dispatchSync($job);
        $result = $job->getResult();
        
        return response()->json($result, Response::HTTP_OK);
    }

    public function bicycleViolation(Request $request)
    {
        $inputs = $request->all();

        $job = new YoloViolationJob($inputs);
        $this->dispatchSync($job);
        $result = $job->getResult();
        
        return response()->json($result, Response::HTTP_OK);
    }


    public function getSpotDay1($id)
    {
        $spotDay1 = Spot::where('spots_id', $id)->get('spots_count_day1');

        return response()->json($spotDay1, Response::HTTP_OK);
    }

    public function getSpotDay1Update(Request $request, $id)
    {
        $inputs = $request->all();
        $spotDay1Update = Spot::where('spots_id', $id)->update(['spots_count_day1' => $inputs['spots_count_day1']]);

        return response()->json($spotDay1Update, Response::HTTP_OK);
    }

    public function getSpotWeek1($id)
    {
        $spotWeek1 = Spot::where('spots_id', $id)->get('spots_count_week1');

        return response()->json($spotWeek1, Response::HTTP_OK);
    }

    public function getSpotWeek1Update(Request $request, $id)
    {
        $inputs = $request->all();
        $spotWeek1Update = Spot::where('spots_id', $id)->update(['spots_count_week1' => $inputs['spots_count_week1']]);

        return response()->json($spotWeek1Update, Response::HTTP_OK);
    }

    public function getSpotMonth1($id)
    {
        $spotMonth1 = Spot::where('spots_id', $id)->get('spots_count_month1');

        return response()->json($spotMonth1, Response::HTTP_OK);
    }

    public function getSpotMonth1Update(Request $request, $id)
    {
        $inputs = $request->all();
        $spotMonth1Update = Spot::where('spots_id', $id)->update(['spots_count_month1' => $inputs['spots_count_month1']]);

        return response()->json($spotMonth1Update, Response::HTTP_OK);
    }

    public function getSpotMonth3($id)
    {
        $spotMonth1 = Spot::where('spots_id', $id)->get('spots_count_month3');

        return response()->json($spotMonth1, Response::HTTP_OK);
    }

    public function getSpotMonth3Update(Request $request, $id)
    {
        $inputs = $request->all();
        $spotMonth3Update = Spot::where('spots_id', $id)->update(['spots_count_month3' => $inputs['spots_count_month3']]);

        return response()->json($spotMonth3Update, Response::HTTP_OK);
    }

    public function serverCondition($id)
    {
        $serverCondition = Bicycle::where('cameras_id', $id)->exists();

        if ($serverCondition) {
            return response()->json(['condition' =>'false'], Response::HTTP_OK);
        } else {
            return response()->json(['condition' =>'true'], Response::HTTP_OK);
        }
    }

    public function serverUpdate(Request $request, $id)
    {
        $inputs = $request->all();
        $bicycleOld = Bicycle::where('cameras_id', $id)->get('get_id');

        $newIdList = [];
        $oldIdList = [];

        for ($i=0; $i < count($inputs); $i++) {
            $bicycleServerUpdate = Bicycle::where('cameras_id', $id)->where('get_id', $inputs[$i]['old'])->update(['get_id' => $inputs[$i]['new']]);
        }

        for ($i=0; $i < count($inputs); $i++) {
            array_push($newIdList, $inputs[$i]['old']);
        }

        for ($i=0; $i < count($bicycleOld); $i++) {
            array_push($oldIdList, $bicycleOld[$i]['get_id']);
        }

        for ($i=0; $i < count($bicycleOld); $i++) {
            if (!in_array($bicycleOld[$i]['get_id'], $newIdList)) {
                $bicycleDelete = Bicycle::where('cameras_id', $id)->where('get_id', $bicycleOld[$i]['get_id'])->delete();
            }
        }

        return response()->json($inputs, Response::HTTP_OK);
    }
}
