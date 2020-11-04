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



        /*
         * API No. 15
         * API Name : 마이페이지 요청 시 조회 기능
         * 마지막 수정 날짜 : 20.09.01
         */


        case "getMyPage":
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

            if(empty($_GET['userIndex'])){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "userIndex에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

            if(is_string(['userIndex'])){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "userIndex에 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(!isValidUser($_GET['userIndex'])){

                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "없는 userIndex";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);

            $userIdx =$jwtData->userIdx;
            $otherUserIdx = $_GET['userIndex'];
//            echo $userIdx,$otherUserIdx;
            $isPrivate = isPrivateUser($otherUserIdx);
            $isFollowed = isFollowedUser($userIdx, $otherUserIdx);
            $isFollowRequest = isFollowRequestedUser($userIdx, $otherUserIdx);
            $isFollowingWith = isFollowingWith($userIdx, $otherUserIdx);
//            $isHighlight = isValidHighlight($userIdx);
            $followStatus = 0;
// isHighlight = 1 or 0 1팔로우 2요청됨 3팔로잉

            if($isFollowed!==1){
                $followStatus = 1;
            }
            if($isFollowRequest==1){
                $followStatus=2;

            }
            if($isFollowed==1){
                $followStatus=3;
            }




            if($userIdx==$otherUserIdx){

                $result["isMyPosts"] = 1;

                $result['isHighlight']= 1;
                $result["followStatus"]=0;
                $result['storyStatus']= getStoryStatus($otherUserIdx,$userIdx);
                $result['userInfo'] = getMyPageUser($userIdx);
                $result["followingWith"]= "";
                $result['highlightInfo'] = getMyPageHighlight($userIdx);

                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "자신의 마이페이지 조회 기능 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;




            }
            else {
//                $isPrivate = isPrivateUser($otherUserIdx);
//                $isFollowed = isFollowedUser($userIdx, $otherUserIdx);
//                $isFollowRequest = isFollowRequestedUser($userIdx, $otherUserIdx);
//                $isFollowingWith = isFollowingWith($userIdx, $otherUserIdx);


                if($isPrivate==1){
//                  비공개 계정이고 / 팔로우가 안되었고 / 팔로우 요청이 없다면
                    if($isFollowed!==1 and $isFollowRequest!==1){
                        myPageViewHistory($userIdx,$otherUserIdx);
                        $result["isMyPosts"] = 0;
                        $result['isHighlight']= 0;
                        $result["followStatus"]=$followStatus;
                        $result['storyStatus']= getStoryStatus($otherUserIdx,$userIdx);
                        $result['userInfo'] = getMyPageUser($otherUserIdx);
                        $result["followingWith"]= $isFollowingWith;
//                        $result['highlightInfo'] = 0;
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 201;
                        $res->message = "상대의 비공개, 팔로우 되지 않은 마이페이지 요청 시 조회 기능 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;



                    }
//                  비공개 계정이고 / 팔로우가 안되었고 / 팔로우 요청을 한 상태라면
                    else if($isFollowed!==1 and $isFollowRequest==1){
                        myPageViewHistory($userIdx,$otherUserIdx);
                        $result["isMyPosts"] = 0;
                        $result['isHighlight']= 0;
                        $result["followStatus"]=$followStatus;
                        $result['storyStatus']= getStoryStatus($otherUserIdx,$userIdx);
                        $result['userInfo'] = getMyPageUser($otherUserIdx);
                        $result["followingWith"]= $isFollowingWith;
//                        $result['highlightInfo'] = 0;
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 202;
                        $res->message = "상대의 비공개, 팔로우 안된 , 팔로우 요청을 한 상태인 마이페이지 요청 시 조회 기능 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
//                  비공개 계정이고 / 팔로우가 되어있고 / (팔로우 요청이 없다면 = 상관없)
                    else if($isFollowed==1){
                        myPageViewHistory($userIdx,$otherUserIdx);
                        $result["isMyPosts"] = 0;
                        $result['isHighlight']= 1;
                        $result["followStatus"]=$followStatus;
                        $result['storyStatus']= getStoryStatus($otherUserIdx,$userIdx);
                        $result['userInfo'] = getMyPageUser($otherUserIdx);
                        $result["followingWith"]= $isFollowingWith;
                        $result["highlightInfo"] = getMyPageHighlight($otherUserIdx);
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 203;
                        $res->message = "상대의 비공개, 팔로우 된 마이페이지 요청 시 조회 기능 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }

                }
                else{
//                  공개 계정이라면 ?
                    myPageViewHistory($userIdx,$otherUserIdx);
                    $result["isMyPosts"] = 0;
                    $result['isHighlight']= 1;
                    $result["followStatus"]=$followStatus;
                    $result['storyStatus']= getStoryStatus($otherUserIdx,$userIdx);
                    $result['userInfo'] = getMyPageUser($userIdx);
                    $result["followingWith"]= $isFollowingWith;
                    $result["highlightInfo"] = getMyPageHighlight($otherUserIdx);
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 204;
                    $res->message = "상대의 공개된 마이페이지 요청 시 조회 기능 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;


                }

            }






        /*
         * API No. 1
         * API Name : 마이페이지에서 자신이 맨션 당한 게시글 목록 조회 기능
         * 마지막 수정 날짜 : 20.09.01
         */
        case "getMyPageMentionedList":
            http_response_code(200);



            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx =$jwtData->userIdx;

            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(empty($_GET['userIndex'])){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "userIndex에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

            if(!isValidUser($_GET['userIndex'])){

                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "없는 userIndex";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(empty($_GET['pageNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "pageNumber에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

            if(!preg_match( "/^[0-9]/i", $_GET['pageNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 304;
                $res->message = "pageNumber에 숫자를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $otherUserIdx = $_GET['userIndex'];
            $reqPageNumber = ceil($_GET['pageNumber']);
            $resPageNumber = ceil(pagingMentionedPostsCount($otherUserIdx));
            $isPrivate = isPrivateUser($otherUserIdx);
            $isFollowed = isFollowedUser($userIdx, $otherUserIdx);
            $isFollowRequest = isFollowRequestedUser($userIdx, $otherUserIdx);
            $isFollowingWith = isFollowingWith($userIdx, $otherUserIdx);
            $followStatus = 0;
// isHighlight = 1 or 0 1팔로우 2요청됨 3팔로잉

            if($isFollowed!==1){
                $followStatus = 1;
            }
            if($isFollowRequest==1){
                $followStatus=2;

            }
            if($isFollowed==1){
                $followStatus=3;
            }
            $number = ($reqPageNumber-1)*12;


            if($userIdx==$otherUserIdx){

                if($resPageNumber==0){
                    $result["isMyPosts"] = 1;
                    $result["followStatus"]=0;
                    $result["myPosts"] = getMyPageMentionedPosts((int)$userIdx,(int)$number);
                    $res->result =$result;
                    $res->isSuccess = TRUE;
                    $res->code = 200;
                    $res->message = "맨션 된 게시글이 없습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }
                if($reqPageNumber>$resPageNumber){
                    $result["isMyPosts"] = 1;
                    $result["followStatus"]=0;
                    $result["myPosts"] = getMyPageMentionedPosts((int)$userIdx,(int)$number);
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 201;
                    $res->message = "잘못 된 pageNumber 이거나 이 pageNumber에는 더 이상 맨션 된 게시글 데이터가 존재하지 않습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }


                $result["isMyPosts"] = 1;
                $result["followStatus"]=0;
                $result["myPosts"] = getMyPageMentionedPosts((int)$userIdx,(int)$number);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 202;
                $res->message = "마이페이지에서 자신의 맨션된 게시글 목록 데이터 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            else {



                if($isPrivate==1){
//                  비공개 계정이고 / 팔로우가 안되었고 / 팔로우 요청이 없다면
                    if($isFollowed!==1 and $isFollowRequest!==1){


                        $result["isMyPosts"] = 0;
                        $result["followStatus"]=$followStatus;
                        $result["myPosts"] = 0;
//                        $res->result=getMyPageMentionedList($userIdx);
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 203;
                        $res->message = "상대의 마이페이지 비공개 , 팔로우가 안 된 , 팔로우 요청을 안한 맨션 된 게시글 목록 조회 기능";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;

                    }
//                  비공개 계정이고 / 팔로우가 안되었고 / 팔로우 요청을 한 상태라면
                    else if($isFollowed!==1 and $isFollowRequest==1){
                        $result["isMyPosts"] = 0;
                        $result["followStatus"]=$followStatus;
                        $result["myPosts"] = 0;
                        $res->result = $result;
//                        $res->result=getMyPageMentionedList($userIdx);
                        $res->isSuccess = TRUE;
                        $res->code = 204;
                        $res->message = "상대의 마이페이지가 비공개, 팔로우 안된 , 팔로우 요청을 한 상태인 맨션 된 게시글 목록 조회 기능";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;


                    }
//                  비공개 계정이고 / 팔로우가 되어있고 / (팔로우 요청이 없다면 = 상관없)
                    else if($isFollowed==1){
                        if($resPageNumber==0){
                            $result["isMyPosts"] = 0;
                            $result["followStatus"]=$followStatus;
                            $result["myPosts"] = getMyPageMentionedPosts((int)$otherUserIdx,(int)$number);
                            $res->result = $result;
                            $res->isSuccess = TRUE;
                            $res->code = 205;
                            $res->message = "맨션 된 게시글이 없습니다";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;

                        }
                        if($reqPageNumber>$resPageNumber){
                            $result["isMyPosts"] = 0;
                            $result["followStatus"]=$followStatus;
                            $result["myPosts"] = getMyPageMentionedPosts((int)$otherUserIdx,(int)$number);
                            $res->result = $result;
                            $res->code = 206;
                            $res->message = "잘못 된 pageNumber 이거나 이 pageNumber에는 더 이상 맨션된 게시글 데이터가 존재하지 않습니다";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;

                        }
                        $result["isMyPosts"] = 0;
                        $result["followStatus"]=$followStatus;
                        $result["myPosts"] = getMyPageMentionedPosts((int)$otherUserIdx,(int)$number);
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 207;
                        $res->message = "상대의 마이페이지가 비공개, 팔로우 되었고 맨션된 게시글 목록 조회 기능";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;

                    }

                }
                else{
                    if($resPageNumber==0){
                        
                        $result["isMyPosts"] = 0;
                        $result["followStatus"]=$followStatus;
                        $result["myPosts"] = getMyPageMentionedPosts((int)$otherUserIdx,(int)$number);
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 208;
                        $res->message = "맨션 된 게시글이 없습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;

                    }
                    if($reqPageNumber>$resPageNumber){
                        $result["isMyPosts"] = 0;
                        $result["followStatus"]=$followStatus;
                        $result["myPosts"] = getMyPageMentionedPosts((int)$otherUserIdx,(int)$number);
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 209;
                        $res->message = "잘못 된 pageNumber 이거나 이 pageNumber에는 더 이상 맨션 된 게시글 데이터가 존재하지 않습니";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;

                    }
//                  공개 계정이라면 ?
                    $result["isMyPosts"] = 0;
                    $result["followStatus"]=$followStatus;
//                  복구해  $result["myPagePosts"]=getMyPageMyPosts($otherUserIdx);
                    $result["myPosts"] = getMyPageMentionedPosts((int)$otherUserIdx,(int)$number);
                    $res->result = $result;
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 210;
                    $res->message = "상대의 공개 된 마이페이지 요청 시 상대의 맨션된 게시글 목록 조회 기능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;



                }

            }




        /*
         * API No. 2
         * API Name : 마이페이지에서 자신의 게시글 목록 조회 기능
         * 마지막 수정 날짜 : 20.09.01
         */
        case "getMyPageMyPosts":
            http_response_code(200);



            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $jwtData = getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx =$jwtData->userIdx;

            if (!isValidHeader($jwt, JWT_SECRET_KEY)){

                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(empty($_GET['userIndex'])){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "userIndex에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

            if(!isValidUser($_GET['userIndex'])){

                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "없는 userIndex";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(empty($_GET['pageNumber'])){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "pageNumber에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

            if(!preg_match( "/^[0-9]/i", $_GET['pageNumber'])){
                    $res->isSuccess = FALSE;
                    $res->code = 304;
                    $res->message = "pageNumber에 숫자를 입력하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
            }

            $otherUserIdx = $_GET['userIndex'];
            $reqPageNumber = ceil($_GET['pageNumber']);
            $resPageNumber = ceil(pagingPostsCount($otherUserIdx));
            $isPrivate = isPrivateUser($otherUserIdx);
            $isFollowed = isFollowedUser($userIdx, $otherUserIdx);
            $isFollowRequest = isFollowRequestedUser($userIdx, $otherUserIdx);
            $isFollowingWith = isFollowingWith($userIdx, $otherUserIdx);
            $followStatus = 0;
// isHighlight = 1 or 0 1팔로우 2요청됨 3팔로잉

            if($isFollowed!==1){
                $followStatus = 1;
            }
            if($isFollowRequest==1){
                $followStatus=2;

            }
            if($isFollowed==1){
                $followStatus=3;
            }
            $number = ($reqPageNumber-1)*12;


            if($userIdx==$otherUserIdx){

                if($resPageNumber==0){
                    $result["isMyPosts"] = 1;
                    $result["followStatus"]=0;
                    $result["myPosts"] = getMyPageMyPosts((int)$userIdx,(int)$number,$otherUserIdx);
                    $res->result =$result;
                    $res->isSuccess = TRUE;
                    $res->code = 200;
                    $res->message = "작성 된 게시글이 없습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }
                if($reqPageNumber>$resPageNumber){
                    $result["isMyPosts"] = 1;
                    $result["followStatus"]=0;
                    $result["myPosts"] = getMyPageMyPosts((int)$userIdx,(int)$number,$otherUserIdx);
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 201;
                    $res->message = "잘못 된 pageNumber 이거나 이 pageNumber에는 더 이상 게시글 데이터가 존재하지 않습니";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }


                $result["isMyPosts"] = 1;
                $result["followStatus"]=0;
                $result["myPosts"] = getMyPageMyPosts((int)$userIdx,(int)$number,$otherUserIdx);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 202;
                $res->message = "마이페이지에서 자신의 게시글 목록 데이터 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            else {



                if($isPrivate==1){
//                  비공개 계정이고 / 팔로우가 안되었고 / 팔로우 요청이 없다면
                    if($isFollowed!==1 and $isFollowRequest!==1){


                        $result["isMyPosts"] = 0;
                        $result["followStatus"]=$followStatus;
                        $result["myPosts"] = 0;
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 203;
                        $res->message = "상대의 마이페이지 비공개 , 팔로우가 안 된 , 팔로우 요청을 안한 상대의 게시글 목록 조회 기능";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;

                    }
//                  비공개 계정이고 / 팔로우가 안되었고 / 팔로우 요청을 한 상태라면
                    else if($isFollowed!==1 and $isFollowRequest==1){
                        $result["isMyPosts"] = 0;
                        $result["followStatus"]=$followStatus;
                        $result["myPosts"] = 0;
                        $res->result = $result;
//                        $res->result=getMyPageMentionedList($userIdx);
                        $res->isSuccess = TRUE;
                        $res->code = 204;
                        $res->message = "상대의 마이페이지가 비공개, 팔로우 안된 , 팔로우 요청을 한 상태인 게시글 목록 조회 기능";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;


                    }
//                  비공개 계정이고 / 팔로우가 되어있고 / (팔로우 요청이 없다면 = 상관없)
                    else if($isFollowed==1){
                        if($resPageNumber==0){
                            $result["isMyPosts"] = 0;
                            $result["followStatus"]=$followStatus;
                            $result["myPosts"] = getMyPageMyPosts((int)$userIdx,(int)$number,$otherUserIdx);
                            $res->result = $result;
                            $res->isSuccess = TRUE;
                            $res->code = 205;
                            $res->message = "작성 된 게시글이 없습니다.";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;

                        }
                        if($reqPageNumber>$resPageNumber){
                            $result["isMyPosts"] = 0;
                            $result["followStatus"]=$followStatus;
                            $result["myPosts"] = getMyPageMyPosts((int)$userIdx,(int)$number,$otherUserIdx);
                            $res->result = $result;
                            $res->code = 206;
                            $res->message = "잘못 된 pageNumber 이거나 이 pageNumber에는 더 이상 게시글 데이터가 존재하지 않습니";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;

                        }
                        $result["isMyPosts"] = 0;
                        $result["followStatus"]=$followStatus;
                        $result["myPosts"] = getMyPageMyPosts((int)$userIdx,(int)$number,$otherUserIdx);
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 207;
                        $res->message = "상대의 마이페이지가 비공개, 팔로우 된 게시글 목록 조회 기능";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;

                    }

                }
                else{
                    if($resPageNumber==0){
                        $result["isMyPosts"] = 0;
                        $result["followStatus"]=$followStatus;
                        $result["myPosts"] = getMyPageMyPosts((int)$userIdx,(int)$number,$otherUserIdx);
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 208;
                        $res->message = "작성 된 게시글이 없습니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;

                    }
                    if($reqPageNumber>$resPageNumber){
                        $result["isMyPosts"] = 0;
                        $result["followStatus"]=$followStatus;
                        $result["myPosts"] = getMyPageMyPosts((int)$userIdx,(int)$number,$otherUserIdx);
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 209;
                        $res->message = "잘못 된 pageNumber 이거나 이 pageNumber에는 더 이상 게시글 데이터가 존재하지 않습니";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;

                    }
//                  공개 계정이라면 ?
                    $result["isMyPosts"] = 0;
                    $result["followStatus"]=$followStatus;
//                  복구해  $result["myPagePosts"]=getMyPageMyPosts($otherUserIdx);
                    $result["myPosts"] = getMyPageMyPosts((int)$userIdx,(int)$number,$otherUserIdx);
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 210;
                    $res->message = "상대의 공개 된 마이페이지 요청 시 상대의 게시글 목록 조회 기능";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;



                }

            }
        /*
         * API No. 3
         * API Name : 마이페이지에서 프로필 정보 편집 기능
         * 마지막 수정 날짜 : 20.09.01
         */
        case "postMyPage":
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

            if(empty($req->profileImg) and empty($req->name) and empty($req->userId) and empty($req->website)
            and empty($req->introduction) and empty($req->email) and empty($req->mobile) and empty($req->sex) and empty($req->birthday) and empty($req->professional)){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "변경할 프로필 정보가 단 하나도 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;


            }
            //변수 0 으로 선언
            $profileImg = $name = $userId = $website = $introduction = $email =$mobile =$sex =$birthday =$professional =0;
//            이미지 validation check
            if(is_string($req->profileImg)){

                $profileImg = checkRemoteFile($req->profileImg);
                if($profileImg==true){
                    $profileImg= $req->profileImg;
                }
                if($profileImg==false){
                    $res->isSuccess = FALSE;
                    $res->code = 302;
                    $res->message = "잘못된 이미지 url 입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            if(is_string($req->name) and $req->name!==""){
                if(strlen($req->name)>30){
                    $res->isSuccess = FALSE;
                    $res->code = 303;
                    $res->message = "이름을 30자 미만으로 입력 해 주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $name = $req->name;
            }
            if(is_string($req->userId) and $req->userId!==""){
                if(strlen($req->userId)>30){
                    $res->isSuccess = FALSE;
                    $res->code = 304;
                    $res->message = "사용자 이름을 30자 미만으로 입력 해 주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if(isSameUserId($req->userId)){
                    $res->isSuccess = FALSE;
                    $res->code = 305;
                    $res->message = "중복 된 사용자 이름 입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }
                $userId = $req->userId;
            }
            if(is_string($req->website) and $req->website!==""){
                 $website = $req->website;
//                $website = checkRemoteWebsite($req->website);
            }
            if(is_string($req->introduction) and $req->introduction!==""){
                if(strlen($req->introduction)>150){
                    $res->isSuccess = FALSE;
                    $res->code = 306;
                    $res->message = "소개를 150자 미만으로 입력 해 주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $introduction =$req->introduction;
            }

            if(is_string($req->email) and $req->email!==""){
                $email = mailCheck($req->email);
                if($email==false){
                    $res->isSuccess = FALSE;
                    $res->code = 307;
                    $res->message = "잘못 된 이메일 형식입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $email = $req->email;
            }
            if(is_string($req->mobile) and $req->mobile!==""){
                $mobile = phoneCheck($req->mobile);
                if($mobile==false){
                    $res->isSuccess = FALSE;
                    $res->code = 308;
                    $res->message = "잘못 된 핸드폰 번호입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if(isSamePhone($req->mobile)){
                    $res->isSuccess = FALSE;
                    $res->code = 309;
                    $res->message = "중복 된 핸드폰 번호입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }
                $mobile = $req->mobile;
            }
            if(is_string($req->sex) and $req->sex!==""){
                if(strlen($req->sex)>150){
                    $res->isSuccess = FALSE;
                    $res->code = 310;
                    $res->message = "직접 설정 한 성별을 150자 미만으로 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $sex =$req->sex;
            }
            if(is_string($req->birthday) and $req->birthday!==""){
                $birthday = birthdayCheck($req->birthday);
                if($birthday==false){
                    $res->isSuccess = FALSE;
                    $res->code = 311;
                    $res->message = "잘못된 생년월일 입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $birthday = $req->birthday;
            }
            if(is_string($req->professional) and $req->professional!==""){
                if($req->professional!=="N" and $req->professional!=="Y"){
                    $res->isSuccess = FALSE;
                    $res->code = 312;
                    $res->message = "프로페셔널을 Y 나 N으로 입력 해 주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

            }
            $var = array($profileImg, $name, $userId, $website, $introduction, $email,$mobile, $sex, $birthday, $professional);
            profileChange($var, $userIdx);

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "유저 정보 수정에 성공했습니다";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*  API No. 4
        * API Name :
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getMyFollowerList":
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
            if(empty($_GET['userIndex'])){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "userIndex에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

            if(!isValidUser($_GET['userIndex'])){

                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "없는 userIndex";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            $otherUserIdx= $_GET['userIndex'];

            if($userIdx!==$otherUserIdx) {
                if (isPrivateUser($otherUserIdx)){
//                비공개이고 팔로우 안돼었따
                    if(!isFollowedUser($userIdx, $otherUserIdx)){
                        $result['followRequested'] = 0;
                        $result['getFollowerList'] = 0;
                        $res->result = $result;
                        $res->isSuccess = FALSE;
                        $res->code = 303;
                        $res->message = "팔로우 되지 않은 ,비공개 인 다른 유저의 팔로워 목록은 볼 수 없습니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;

                    } else {
                        $result['followRequested'] = 0;
                        $result['getFollowerList'] = getMyFollowerList($userIdx, $otherUserIdx);
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 200;
                        $res->message = "비공개 , 팔로우 된 다른 유저 팔로워 목록 조회 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
//                비공개지만 팔로우 돼었다면

                }
                else{
                    $result['followRequested'] = 0;
                    $result['getFollowerList'] = getMyFollowerList($userIdx, $otherUserIdx);
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 201;
                    $res->message = "공개 된 다른 유저 팔로워 목록 조회 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }

            }
            else {
                if (isPrivateUser($userIdx)){
                    $result['followRequested'] = followRequestedList($userIdx);
                    $result['getFollowerList'] = getMyFollowerList($userIdx, $otherUserIdx);
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 202;
                    $res->message = "자신의 계정이 비공개인 유저 자신의 팔로워 목록 조회 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;



                }
                else {
                    $result['followRequested'] = 0;
                    $result['getFollowerList'] = getMyFollowerList($userIdx, $otherUserIdx);
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 203;
                    $res->message = "자신의 계정이 공개인 유저 자신의 팔로워 목록 조회 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
        /*  API No. 5
        * API Name :
        * 마지막 수정 날짜 : 20.09.01
        */
        case "getMyFollowingList":
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
            if(empty($_GET['userIndex'])){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "userIndex에 공백이 입력되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

            if(!isValidUser($_GET['userIndex'])){

                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "없는 userIndex";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            $sortNumber =$_GET['sortNumber'] + 0;
            if(empty($sortNumber) or ($sortNumber!==3 and $sortNumber!==2 and $sortNumber!==1) or $sortNumber>3 or $sortNumber<1){

                $res->isSuccess = FALSE;
                $res->code = 304;
                $res->message = "잘못된 sortNumber";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            $sortNumber =$_GET['sortNumber'];
            $otherUserIdx= $_GET['userIndex'];
            if($userIdx!==$otherUserIdx) {
                if (isPrivateUser($otherUserIdx)){
//                비공개이고 팔로우 안돼었따
                    if(!isFollowedUser($userIdx, $otherUserIdx)){

                        $result['getFollowingList']= 0;
                        $res->result = $result;
                        $res->isSuccess = FALSE;
                        $res->code = 303;
                        $res->message = "팔로우 되지 않은 ,비공개 인 다른 유저의 팔로잉 목록은 볼 수 없습니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;

                    } else {
                        $result['getFollowingList']= getMyFollowingList($userIdx,$otherUserIdx,$sortNumber);
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 200;
                        $res->message = "비공개 , 팔로우 된 다른 유저 팔로잉 목록 조회 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
//                비공개지만 팔로우 돼었다면

                }
                else{
                    $result['getFollowingList']= getMyFollowingList($userIdx,$otherUserIdx,$sortNumber);
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 201;
                    $res->message = "공개 된 다른 유저 팔로잉 목록 조회 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }

            }
            else {

                    $result['getFollowingList']= getMyFollowingList($userIdx,$otherUserIdx,$sortNumber);
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 202;
                    $res->message = "자신의 계정 팔로잉 목록 조회 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;




            }











        /*
* API No. 0
* API Name : Jwt 로그인 기능
* 마지막 수정 날짜 : 20.08.13
*/

        case "createUser":
            http_response_code(200);


            if(gettype($req->password)!=string){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "password 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(gettype($req->birthday)!=string){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "birthday 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(gettype($req->userId)!=string){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "userId 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($req->type=="email") {
                if (gettype($req->email) != string) {
                    $res->isSuccess = FALSE;
                    $res->code = 304;
                    $res->message = "email 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            else if($req->type=="mobile"){
                if (gettype($req->mobile) != string) {
                    $res->isSuccess = FALSE;
                    $res->code = 304;
                    $res->message = "mobile 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            else{
                $res->isSuccess = FALSE;
                $res->code = 309;
                $res->message = "타입이 필요합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }
            if(!birthCheck($req->birthday)){
                $res->isSuccess = FALSE;
                $res->code = 305;
                $res->message = "birthday 형식이 올바르지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($req->type=="mobile") {
                if (!phoneCheck($req->mobile)) {
                    $res->isSuccess = FALSE;
                    $res->code = 306;
                    $res->message = "mobile 형식이 올바르지 않습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            else {
                if (!mailCheck($req->email)) {
                    $res->isSuccess = FALSE;
                    $res->code = 307;
                    $res->message = "email 형식이 올바르지 않습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            if(!passwordCheck($req->password)){
                $res->isSuccess = FALSE;
                $res->code = 308;
                $res->message = "비밀번호 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isAvailableAge($req->birthday)){
                $res->isSuccess = FALSE;
                $res->code = 310;
                $res->message = "만 13세 이상만 회원가입 가능합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $userId=IDCheck($req->userId);
            $num=0;
            $newUserId=$userId;
            while(isDupUserId($newUserId)){
                $newUserId=$userId.$num;
                $num=$num+1;
            }


            if($req->type=="email"){
                createEmailUser($newUserId,$req->password,$req->email,$req->birthday,$req->name);
            }
            else{
                createEmailUser($newUserId,$req->password,$req->mobile,$req->birthday,$req->name);
            }

            $jwt = getJWToken($newUserId, $req->password, JWT_SECRET_KEY);
            $userIdx = getUserIdx($newUserId,$req->password);

            $res->result->userId=$newUserId;
            $res->result->jwt=$jwt;
            $res->result->userIdx=$userIdx;

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "회원가입 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createFollow":
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

            $followIdx=$req->userIdx;

            $private=isPrivateUser($followIdx);

            if(!isValidUser($followIdx)){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "존재하지 않는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }




            if(isFollowedUser($userIdx, $followIdx)){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "이미 팔로우 중인 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(isFollowRequestedUser($userIdx, $followIdx)){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "이미 팔로우 요청중인 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if($userIdx==$followIdx){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "자신을 팔로우 할 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(!isPrivateUser($followIdx)){
                if(isDupFollowedUser($userIdx, $followIdx)){
                    updateFollow($userIdx,$followIdx);
                }
                else {
                    createFollow($userIdx, $followIdx);
                    $userId = getUserId($userIdx);
                    if($followIdx != $userIdx) {
                        createActivity($followIdx, $userIdx, 2, null);
                    }
                }
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "팔로잉";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                if(isDupFollowRequestedUser($userIdx, $followIdx)){
                    updateFollowRequest($userIdx,$followIdx);
                }
                else{
                    createFollowRequest($userIdx,$followIdx);
                    if($followIdx != $userIdx) {
                        createActivity($followIdx, $userIdx, 2, null);
                    }
                }
                $res->isSuccess = TRUE;
                $res->code = 201;
                $res->message = "요청됨";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        case "deleteFollow":
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

            $followIdx=$vars['userIdx'];


            if(!isValidUser($followIdx)){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "존재하지 않는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if(isFollowedUser($userIdx, $followIdx)){
                deleteFollow($userIdx,$followIdx);
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "팔로우 취소 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            else if(isFollowRequestedUser($userIdx, $followIdx)){
                deleteFollowRequest($userIdx,$followIdx);
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "팔로우 요청 취소 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            else{
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "팔로우/팔로우 요청한 유저가 아닙니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }



        case "getFollowRequest":
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

           if(!isPrivateUser($userIdx)){
               $res->isSuccess = FALSE;
               $res->code = 300;
               $res->message = "비공개 계정이 아닙니다.";
               echo json_encode($res, JSON_NUMERIC_CHECK);
               break;
           }


            $res->result=getFollowRequest($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;


        case "acceptFollowRequest":
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

            $reqUserIdx=$vars['userIdx'];


            if(!isValidUser($reqUserIdx)){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "존재하지 않는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(!isPrivateUser($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "비공개 계정이 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isFollowRequestedUser($reqUserIdx, $userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "요청목록에 없는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }



            deleteFollowRequest($reqUserIdx,$userIdx);
            if(isDupFollowedUser($reqUserIdx, $userIdx)){
                updateFollow($reqUserIdx,$userIdx);
            }
            else{
                createFollow($reqUserIdx,$userIdx);
                if($reqUserIdx != $userIdx) {
                    createActivity($userIdx, $reqUserIdx, 1, null);
                }
            }

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "팔로우 요청 승인 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;



        case "denyFollowRequest":
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

            $reqUserIdx=$vars['userIdx'];


            if(!isValidUser($reqUserIdx)){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "존재하지 않는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(!isPrivateUser($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "비공개 계정이 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            if(!isFollowRequestedUser($reqUserIdx, $userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "요청목록에 없는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }



            deleteFollowRequest($reqUserIdx,$userIdx);

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "팔로우 요청 거절 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;




        case "getRecommendUser":
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
            $pageNumber = $_GET['pageNumber'];
            if ($pageNumber == null) {
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "페이지 번호를 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            if ($pageNumber == 0) {
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "페이지 번호는 1부터 시작입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            $pageNum=($pageNumber-1)*20;


            $res->result=getRecommendUser($userIdx,$pageNum);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;


    }


} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}

