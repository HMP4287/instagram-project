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

        /*  API No. 1
        * API Name : 키워드 버튼 누르면 나오는 게시글 목록 조
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getSearchListType":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            18페이지 씩 페이징


            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(empty($_GET['keyword'])){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "keyword에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(empty($_GET['pageNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "pageNumber에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(!preg_match( "/^[0-9]/i", $_GET['pageNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "pageNumber에 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
//            if(){} 페이지 검사
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);

            $userIdx =$jwtData->userIdx;
            $pageNumber = $_GET['pageNumber'];
            $keyword = $_GET['keyword'];
            $reqPageNumber = $_GET['pageNumber'];
            $resPageNumber = countGetSearchListType($userIdx,$keyword);

            $number = ($reqPageNumber-1)*18;

            if($reqPageNumber>$resPageNumber){
                $res->isSuccess = FALSE;
                $res->code = 304;
                $res->message = "데이터가 더 이상 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
//                더보여줄 데이터 없다 .
            }
            $result = getSearchListType($userIdx,$keyword,$number);
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "키워드 버튼으로 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*  API No. 2
        * API Name : 키워드 버튼 누르면 나오는 게시글 목록 조
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getSearchList":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            18페이지 씩 페이징


            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(empty($_GET['keyword'])){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "keyword에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(empty($_GET['sortNumber'])){

                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "sortNumber에 공백이 입력되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(!preg_match( "/^[0-9]/i", $_GET['sortNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "sortNumber에 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($_GET['sortNumber']>4 or $_GET['sortNumber']<1){
                $res->isSuccess = FALSE;
                $res->code = 304;
                $res->message = "sortNumber에 1부터 4까지의 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
//            if(){} 페이지 검사
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);

            $userIdx =$jwtData->userIdx;

            $keyword = $_GET['keyword'];
            $sortNumber = $_GET['sortNumber'];

//
//            if($sortNumber==1){}
//            if($sortNumber==2){}
//            if($sortNumber==3) {
//
//            }
//            if($sortNumber==4){}

            $result = getSearchList($userIdx,$keyword,$sortNumber);
//            $counts = $result->rowCount();
//            echo (count($result['result']['popularUser'], COUNT_RECURSIVE))/7;
//            $count1 = intval(((count($result['popularUser'], COUNT_RECURSIVE))-2)/7);
//            echo $count1;
//            $count2 = intval(((count($result['popularTag'], COUNT_RECURSIVE))-2)/3);
//            $count3 = intval(((count($result['popularLocation'], COUNT_RECURSIVE))-2));
//            if($count1<1){
//                $count1=0;
//            }
//            if($count2<1){
//                $count2=0;
//            }
//            if($count3<1){
//                $count3=0;
//            }
//            $totalCount = $count1+$count2+$count3;
//            echo $totalCount;


            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*  API No. 1
        * API Name : 키워드 버튼 누르면 나오는 게시글 목록 조
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getSearchRecommended":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            18페이지 씩 페이징


            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(empty($_GET['pageNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "pageNumber에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(!preg_match( "/^[0-9]/i", $_GET['pageNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "pageNumber에 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
//            if(){} 페이지 검사
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);

            $userIdx =$jwtData->userIdx;
            $reqPageNumber = $_GET['pageNumber'];



            $number = ($reqPageNumber-1)*24;


            if($reqPageNumber==1){
                delRandCol($userIdx);
                makeRandCol($userIdx);


            }
            $resPageNumber = countRandColPosts($userIdx);

            if($reqPageNumber>$resPageNumber){

                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "데이터가 더 이상 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
//                더보여줄 데이터 없다 .
            }
            $result= showRandColPosts($userIdx,$number);
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "추천 게시글 목록 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*  API No. 1
        * API Name : 해쉬태그 게시글 목록 조
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getSearchHashTagList":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            18페이지 씩 페이징


            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isValidHashTagIndex($vars['hashTagIndex'])){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "hashTagIndex가 잘못 되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(empty($_GET['pageNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "pageNumber에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(!preg_match( "/^[0-9]/i", $_GET['pageNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "pageNumber에 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(empty($_GET['sortNumber'])){

                $res->isSuccess = FALSE;
                $res->code = 304;
                $res->message = "sortNumber에 공백이 입력되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(!preg_match( "/^[0-9]/i", $_GET['sortNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 305;
                $res->message = "sortNumber에 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($_GET['sortNumber']>2 or $_GET['sortNumber']<1){
                $res->isSuccess = FALSE;
                $res->code = 306;
                $res->message = "sortNumber에 1부터 2까지의 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $hashTagIndex= $vars['hashTagIndex'];
//            if(){} 페이지 검사
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);
            $sortNumber= $_GET['sortNumber'];
            $userIdx =$jwtData->userIdx;
//            $pageNumber = $_GET['pageNumber'];

            $reqPageNumber = $_GET['pageNumber'];
            $resPageNumber = countHashTagIndex($hashTagIndex);

            $number = ($reqPageNumber-1)*24;

            if($reqPageNumber>$resPageNumber){
                $res->isSuccess = FALSE;
                $res->code = 307;
                $res->message = "데이터가 더 이상 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
//                더보여줄 데이터 없다 .
            }
            $result = getSearchHashTag($hashTagIndex,$sortNumber,$number);
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "해쉬태그 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*  API No. 1
        * API Name : 위 게시글 목록 조
         *
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getSearchLocationList":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            18페이지 씩 페이징


            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isValidLocation($_GET['location'])){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "location이 없거나 잘못 되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(empty($_GET['pageNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "pageNumber에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(!preg_match( "/^[0-9]/i", $_GET['pageNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "pageNumber에 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(empty($_GET['sortNumber'])){

                $res->isSuccess = FALSE;
                $res->code = 304;
                $res->message = "sortNumber에 공백이 입력되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(!preg_match( "/^[0-9]/i", $_GET['sortNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 305;
                $res->message = "sortNumber에 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($_GET['sortNumber']>2 or $_GET['sortNumber']<1){
                $res->isSuccess = FALSE;
                $res->code = 306;
                $res->message = "sortNumber에 1부터 2까지의 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $location= (string)$_GET['location'];
//            if(){} 페이지 검사
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);
            $sortNumber= $_GET['sortNumber'];
            $userIdx =$jwtData->userIdx;
//            $pageNumber = $_GET['pageNumber'];

            $reqPageNumber = $_GET['pageNumber'];
            $resPageNumber = countLocation($location);

            $number = ($reqPageNumber-1)*24;

            if($reqPageNumber>$resPageNumber){
                $res->isSuccess = FALSE;
                $res->code = 307;
                $res->message = "데이터가 더 이상 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
//                더보여줄 데이터 없다 .
            }
            $result = getSearchLocation($location,$sortNumber,$number);
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "장소 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;






    }


} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}

