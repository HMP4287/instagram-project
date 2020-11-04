<?php
require 'function.php';

const JWT_SECRET_KEY = "secretkeybimil";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
         * API No. 0
         * API Name : JWT 유효성 검사 테스트 API
         * 마지막 수정 날짜 : 19.04.25
         */
        case "validateJwt":
            // jwt 유효성 검사

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            http_response_code(200);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 19.04.25
         */
        case "createJwt":
            // jwt 유효성 검사
            http_response_code(200);

            if(gettype($req->id)!=string){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "파라미터 id의 타입이 잘못되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(gettype($req->pw)!=string){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "파라미터 password의 타입이 잘못되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidId($req->id)){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "존재하지 않는 계정입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(!isValidPw($req->id,$req->pw)){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "비밀번호가 일치하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            //페이로드에 맞게 다시 설정 요함
            $jwt = getJWToken($req->id, $req->pw, JWT_SECRET_KEY);
//            echo $jwt;
            $userIdx= getUserIdx($req->id,$req->pw);

            $res->result->jwt = $jwt;
            $res->result->userIdx =$userIdx;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "로그인 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 2
         * API Name : getUserData 유저 인증 확인용
         * 마지막 수정 날짜 : 20.08.24
         */
        case "getUserData":
            // jwt 유효성 검사
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];


            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //페이로드에 맞게 다시 설정 요함
            $data = getDataByJWToken($jwt,JWT_SECRET_KEY);
            $res->result=$data;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getValidJwt":
            // jwt 유효성 검사
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];


            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //페이로드에 맞게 다시 설정 요함
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "로그인 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
