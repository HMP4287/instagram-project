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

        /*  API No. 6
        * API Name : 하이라이트 목록 상세 조회
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getMyHighlight":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $highlightIdx =$vars['highlightIdx'];



            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);

            $userIdx =$jwtData->userIdx;
            if(empty($highlightIdx)){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "highlightIdx에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(!isValidHighlight($highlightIdx)){

                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "잘못된 highlightIdx";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            $res->result = getHighlightFiles($userIdx,$highlightIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "하이라이트 내용 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*  API No. 7
        * API Name : 하이라이트 삭제
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "delMyHighlight":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $highlightIdx =$vars['highlightIdx'];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);

            $userIdx =$jwtData->userIdx;
            if(empty($highlightIdx)){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "highlightIdx에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(!isValidHighlight($highlightIdx)){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "잘못 된 highlightIdx";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
//            지울 권한이 있는 유저인가 ?
            if(!isValidHighlightToDel($highlightIdx,$userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "하이라이트를 삭제 할 수 있는 유저가 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

            delMyHighlight($userIdx, $highlightIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "하이라이트 삭제에 성공했습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*  API No. 8
        * API Name : 스토리 생성기
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "postStory":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];


            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);

            $userIdx =$jwtData->userIdx;

            $count = count($req->result);
            $tfNumber= 0;
//             file, type , context, isBFView validation 을 반복문으로,, ?
            for($i=0;$i<$count;$i=$i+1){
                if(!checkRemoteFile($req->result[$i]->file) or empty($req->result[$i]->file)){
                    $tfNumber= 1;
                    $res->isSuccess = FALSE;
                    $res->code = 301;
                    $res->message = "file 정보가 잘못 된 url 이거나 공백입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;


                }
                if(empty($req->result[$i]->type) or (($req->result[$i]->type)!=='img' and ($req->result[$i]->type)!=='video')){
                    $tfNumber= 1;
                    $res->isSuccess = FALSE;
                    $res->code = 302;
                    $res->message = "type 정보에 'img' 나 'video' 라고 입력 해 주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if(!is_string($req->result[$i]->context)){
                    $tfNumber= 1;
                    $res->isSuccess = FALSE;
                    $res->code = 303;
                    $res->message = "context에 문자를 입력 해 주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }
                if(($req->result[$i]->isBFView)!=='N' and ($req->result[$i]->isBFView)!=='Y'){
                    $tfNumber= 1;
                    $res->isSuccess = FALSE;
                    $res->code = 304;
                    $res->message = "isBFView에 'Y'나 'N'을 입력 해 주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }


            }
            if($tfNumber==0){
                $reqStoryData = $req;
                postNewStory($userIdx,$count,$reqStoryData);
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "스토리 생성 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                break;
            }


        /*  API No. 8
        * API Name : 스토리 목록 조회 기능
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getStoryList":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];


            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;

            }
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);

            $userIdx =$jwtData->userIdx;
//          하이라이트에 등록 됐는지 여부 필요함
            $result['myStory']=getMyOnlyStory($userIdx);
            $result['otherStory']=getStoryList($userIdx);

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "스토리 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

//        /*  API No. 8
//        * API Name : 리스트 스토리 목록 조회 기능
//         *
//        * 마지막 수정 날짜 : 20.09.01
//        */
//        case "getStoryAll":
//            http_response_code(200);
//            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
////            $ =$vars[''];
//
//            if (!isValidHeader($jwt, JWT_SECRET_KEY)){
//
//                $res->isSuccess = FALSE;
//                $res->code = 300;
//                $res->message = "유효하지 않은 토큰입니다";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                return;
//
//            }
//            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);
//
//            $userIdx =$jwtData->userIdx;
//            $result['myStory']=getMyOnlyStory($userIdx);
//            $result['otherStory']=getStoryList($userIdx);
//
//            $res->result = $result;
//            $res->isSuccess = TRUE;
//            $res->code = 200;
//            $res->message = "스토리 상세 조회 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;

        /*  API No. 8
        * API Name : 남 스토리 목록 조회 기능
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getStoryAllOther":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            echo 'hi';
//            print_r ("hi");


            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;

            }
            if(!checkStoryUserValid($vars['userIndex'])){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "잘못된 userIndex입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            $userIdx =$vars['userIndex'];
//            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);
//
//            $userIdx =$jwtData->userIdx;
            $result['storyCount']=countGetOtherDetailStory($userIdx);
            $result['storyInfo']=getOtherDetailStory($userIdx);

//            $result['otherStory']=getStoryList($userIdx);

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "스토리 상세 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*  API No. 8
        * API Name : 남 스토리 목록 조회 기능
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getStoryAllSelf":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            echo 'hi';
//            print_r ("hi");
//            $userIdx =$vars['userIndex'];
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);

            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;

            }
//            if(!checkStoryUserValid($vars['userIndex'])){
//                $res->isSuccess = FALSE;
//                $res->code = 301;
//                $res->message = "잘못된 userIndex입니다";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                return;
//            }
            $userIdx =$jwtData->userIdx;
//
//            $userIdx =$jwtData->userIdx;
            $result['storyCount']=countGetSelfDetailStory($userIdx);
            $result['storyInfo']=getSelfDetailStory($userIdx);

//            $result['otherStory']=getStoryList($userIdx);

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "스토리 상세 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*  API No. 8
        * API Name : 삭제 스토리 + 하이라이트에서도 제거
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "delStoryIdx":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            echo 'hi';
//            print_r ("hi");
            $storyIdx = $vars['storyIdx'];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;

            }
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx =$jwtData->userIdx;
//            지울게 있는가 ?

            if(!checkStoryUserValid($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "잘못된 userIndex입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


//         스토리와 하이라이트 둘 다 자동 판별 후 삭제
            delHighlightStory($storyIdx);
//            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "스토리 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*  API No. 8
        * API Name : 스토리 조회한사람 목록
         *
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getStoryViewedList":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            echo 'hi';
//            print_r ("hi");
            $storyIdx = $vars['storyIdx'];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;

            }
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx =$jwtData->userIdx;
//            지울게 있는가 ?

            if(!checkStoryUserValid($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "잘못된 userIndex입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }



//         스토리와 하이라이트 둘 다 자동 판별 후 삭제
//            delHighlightStory($storyIdx);
            $result['viewedList'] =  getStoryViewedList($userIdx,$storyIdx);

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "스토리 조회 기록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*  API No. 8
        * API Name : 스토리 조회한사람 목록
         *
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "postStoryToHighlight":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            echo 'hi';
//            print_r ("hi");
            $storyIdx = $vars['storyIdx'];
            $highlightIdx= $vars['highlightIdx'];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;

            }
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx =$jwtData->userIdx;
//            지울게 있는가 ?

            if(!checkStoryUserValid($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "잘못된 userIndex입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isValidHighlight($highlightIdx)){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "잘못된 highlightIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isValidHighlightToDel($highlightIdx,$userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "권한이 없는 유저이거나 잘못된 highlightIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
//          하이라이트에 이미 추가가 돼었나 ?
            $checkNum1 = checkHighlightStoryExistsN($highlightIdx,$storyIdx);
            $checkNum2 = checkHighlightStoryExistsY($highlightIdx,$storyIdx);
            if($checkNum1==1 and $checkNum2!==1){
                highlightToY($highlightIdx,$storyIdx);
            }
            if($checkNum1!==1 and $checkNum2==1){
                highlightToN($highlightIdx,$storyIdx);
            }
            if($checkNum1==0 and $checkNum2==0){
                highlightInsert($highlightIdx,$storyIdx);
            }







//         스토리와 하이라이트 둘 다 자동 판별 후 삭제
//            delHighlightStory($storyIdx);
//            $result['viewedList'] =  getStoryViewedList($userIdx,$storyIdx);

//            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "하이라이트에 저장 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*  API No. 8
        * API Name : 스토리 조회한사람 목록
         *
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getHighlightList":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            echo 'hi';
//            print_r ("hi");
            $storyIdx = $vars['storyIdx'];
//            $highlightIdx= $vars['highlightIdx'];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;

            }
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx =$jwtData->userIdx;
//            지울게 있는가 ?

            if(!checkStoryUserValid($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "잘못된 userIndex입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }



//         스토리와 하이라이트 둘 다 자동 판별 후 삭제
//            delHighlightStory($storyIdx);
            $result['highlightList'] =  getHighlightLists($storyIdx,$userIdx);

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "하이라이트 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*  API No. 8
        * API Name : 스토리 조회한사람 목록
         *
         *
        * 마지막 수정 날짜 : 20.09.01
        */
//        case "postMyPageHighlight":
//            http_response_code(200);
//            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
////            echo 'hi';
////            print_r ("hi");
////            $storyIdx = $vars['storyIdx'];
////            $highlightIdx= $vars['highlightIdx'];
//
//            if (!isValidHeader($jwt, JWT_SECRET_KEY)){
//
//                $res->isSuccess = FALSE;
//                $res->code = 300;
//                $res->message = "유효하지 않은 토큰입니다";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                return;
//
//            }
//            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);
//            $userIdx =$jwtData->userIdx;
//            $coverImg = $req->coverImg;
//            $highlightName = $req->highlightName;
////            echo $req->coverImg;
////            echo $req->highlightName;
//            insertNewHighlight($highlightName,$userIdx,$coverImg)
//            $count = count($req->result);
//            for($i=0;$i<$count;$i=$i+1){
//
//            }
//            echo $req->result[0]->storyIdx;
////            지울게 있는가 ?
////
////            if(!checkStoryUserValid($userIdx)){
////                $res->isSuccess = FALSE;
////                $res->code = 301;
////                $res->message = "잘못된 userIndex입니다";
////                echo json_encode($res, JSON_NUMERIC_CHECK);
////                addErrorLogs($errorLogs, $res, $req);
////                return;
////            }
//
//
//
////         스토리와 하이라이트 둘 다 자동 판별 후 삭제
////            delHighlightStory($storyIdx);
////            $result['highlightList'] =  getHighlightLists($storyIdx,$userIdx);
//
////            $res->result = $result;
//            $res->isSuccess = TRUE;
//            $res->code = 200;
//            $res->message = "하이라이트 생성 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;




    }


} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}

