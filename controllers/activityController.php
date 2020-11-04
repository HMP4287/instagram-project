<?php
require 'function.php';

const JWT_SECRET_KEY = "secretkeybimil";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;




        case "getActivity":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);

            $userIdx =$jwtData->userIdx;


            $private=isPrivateUser($userIdx);

            if($private){
                $res->result->isPrivate=true;
                $res->result->followRequestStatus=getFollowRequestStatus($userIdx);
            }
            $res->result->activeInfo->today=getActivity($userIdx,"TIMESTAMPDIFF(DAY, createdTime, NOW())<1");
            $res->result->activeInfo->yesterDay=getActivity($userIdx,"TIMESTAMPDIFF(DAY, createdTime, NOW())>0 and TIMESTAMPDIFF(DAY, createdTime, NOW())<2");
            $res->result->activeInfo->thisWeek=getActivity($userIdx,"TIMESTAMPDIFF(DAY, createdTime, NOW())>1 and TIMESTAMPDIFF(DAY, createdTime, NOW())<8");
            $res->result->activeInfo->thisMonth=getActivity($userIdx,"TIMESTAMPDIFF(DAY, createdTime, NOW())>7 and TIMESTAMPDIFF(DAY, createdTime, NOW())<31");
            $res->result->activeInfo->previous=getActivity($userIdx,"TIMESTAMPDIFF(DAY, createdTime, NOW())>30");

            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }


} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}

