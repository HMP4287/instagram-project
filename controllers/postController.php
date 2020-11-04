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

        case "getPostLikeDetail":
            http_response_code(200);


            $postIdx = intval($vars['postIdx']);

            if (gettype($postIdx) != integer) {
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "postIdx의 type이 잘못되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            if (!isValidPost($postIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 게시글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;


            $res->result = getPostLikeDetail($userIdx, $postIdx);
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getHome":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
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

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;
            $pageNum = ($pageNumber - 1) * 12;

            $res->result = getHomePost($userIdx, $pageNum);


            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getUserPostDetail":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


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

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;
            $pageNum = ($pageNumber - 1) * 6;
            $postedIdx = $vars['userIdx'];

            if (!isValidUser($postedIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "존재하지 않는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            $res->result = getUserPostDetail($userIdx, $postedIdx, $pageNum);
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "조회 성공";


            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createPostLike":
            http_response_code(200);


            $postIdx = $req->postIdx;

            if (gettype($postIdx) != integer) {
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "postIdx의 type이 잘못되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            if (!isValidPost($postIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 게시글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            if (isValidPostLike($userIdx, $postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "이미 존재하는 좋아요입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if (isDupPostLike($userIdx, $postIdx)) {
                updatePostLike($userIdx, $postIdx);
            } else {

                createPostLike($userIdx, $postIdx);
                $postedIdx = getPostedUserIdx($postIdx);
                if($postedIdx != $userIdx) {
                    createActivity($postedIdx, $userIdx, 3, $postIdx);
                }
            }
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "좋아요 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "deletePostLike":
            http_response_code(200);


            $postIdx = $vars['postIdx'];


            if (!isValidPost($postIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 게시글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            if (!isValidPostLike($userIdx, $postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "존재하지 않는 좋아요입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            deletePostLike($userIdx, $postIdx);

            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "좋아요 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createPostComment":
            http_response_code(200);

            $postIdx = $req->postIdx;

            if (!isValidPost($postIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 게시글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $context = $req->context;

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            createPostComment($userIdx, $postIdx, $context);
            $postedIdx = getPostedUserIdx($postIdx);
            $commentIdx = getCommentIdx($userIdx);
            if($postedIdx != $userIdx) {
                createActivity($postedIdx, $userIdx, 4, $commentIdx);
            }
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "댓글 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "deletePostComment":
            http_response_code(200);


            $commentIdx = $vars['commentIdx'];

            if (!isValidComment($commentIdx)) {
                $res->is_success = FALSE;
                $res->code = 302;
                $res->message = "존재하지 않는 댓글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            if (!isValidUserComment($commentIdx, $userIdx)) {
                $res->is_success = FALSE;
                $res->code = 303;
                $res->message = "삭제 권한이 없는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            deletePostComment($commentIdx);
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "댓글 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "createReComment":
            http_response_code(200);

            $commentIdx = $req->commentIdx;

            if (!isValidComment($commentIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 댓글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $context = $req->context;

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            createReComment($userIdx, $commentIdx, $context);
            $postIdx = getPostIdxByComment($commentIdx);
            $postedIdx = getPostedUserIdx($postIdx);
            $reCommentIdx = getReCommentIdx($userIdx);
            if($postedIdx != $userIdx) {
                createActivity($postedIdx, $userIdx, 5, $reCommentIdx);
            }
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "대댓글 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "deleteReComment":
            http_response_code(200);


            $reCommentIdx = $vars['reCommentIdx'];

            if (!isValidreComment($reCommentIdx)) {
                $res->is_success = FALSE;
                $res->code = 302;
                $res->message = "존재하지 않는 대댓글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            if (!isValidUserReComment($reCommentIdx, $userIdx)) {
                $res->is_success = FALSE;
                $res->code = 303;
                $res->message = "삭제 권한이 없는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            deleteReComment($reCommentIdx);
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "대댓글 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createPostStored":
            http_response_code(200);

            $postIdx = $req->postIdx;

            if (!isValidPost($postIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 게시글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            if (isValidCollection($userIdx, $postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "이미 저장된 게시글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if (isDupPostStored($userIdx, $postIdx)) {
                updatePostStored($userIdx, $postIdx);
            } else {
                createPostStored($userIdx, $postIdx);
            }
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "저장 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "deletePostStored":
            http_response_code(200);


            $postIdx = $vars['postIdx'];


            if (!isValidPost($postIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 게시글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            if (!isValidCollection($userIdx, $postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "존재하지 않는 컬렉션 정보입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            deletePostStored($userIdx, $postIdx);
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "저장 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getPostCommentDetail":
            http_response_code(200);


            $postIdx = $vars['postIdx'];

            if (!isValidPost($postIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 게시글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;


            $res->result = getPostCommentDetail($userIdx, $postIdx);
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createCommentLike":
            http_response_code(200);


            $commentIdx = $req->commentIdx;


            if (!isValidComment($commentIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 댓글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            if (isValidCommentLike($userIdx, $commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "이미 존재하는 좋아요입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if (isDupCommentLike($userIdx, $commentIdx)) {
                updateCommentLike($userIdx, $commentIdx);
            } else {
                createCommentLike($userIdx, $commentIdx);
                $postedIdx = getCommentUserIdx($commentIdx);
                if($postedIdx != $userIdx) {
                    createActivity($postedIdx, $userIdx, 7, $commentIdx);
                }
            }
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "좋아요 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "deleteCommentLike":
            http_response_code(200);


            $commentIdx = $vars['commentIdx'];


            if (!isValidComment($commentIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 댓글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            if (!isValidCommentLike($userIdx, $commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "존재하지 않는 좋아요입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            deleteCommentLike($userIdx, $commentIdx);

            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "좋아요 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "createReCommentLike":
            http_response_code(200);


            $reCommentIdx = $req->reCommentIdx;


            if (!isValidreComment($reCommentIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 대댓글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            if (isValidreCommentLike($userIdx, $reCommentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "이미 존재하는 좋아요입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if (isDupreCommentLike($userIdx, $reCommentIdx)) {
                updatereCommentLike($userIdx, $reCommentIdx);
            } else {
                createreCommentLike($userIdx, $reCommentIdx);
                $postedIdx = getReCommentUserIdx($reCommentIdx);
                if($postedIdx != $userIdx) {
                    createActivity($postedIdx, $userIdx, 8, $reCommentIdx);
                }
            }
            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "좋아요 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "deleteReCommentLike":
            http_response_code(200);


            $reCommentIdx = $vars['reCommentIdx'];


            if (!isValidreComment($reCommentIdx)) {
                $res->is_success = FALSE;
                $res->code = 301;
                $res->message = "존재하지 않는 대댓글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;

            if (!isValidreCommentLike($userIdx, $reCommentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "존재하지 않는 좋아요입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            deletereCommentLike($userIdx, $reCommentIdx);

            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "좋아요 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "createPost":
            http_response_code(200);


            $file = $req->file;
            $mention = $req->mention;
            $hashTag = $req->hashTag;
            $commentBlock = $req->commentBlock;
            $location = $req->location;
            $context = $req->context;


            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            if(sizeof($file)==0||$commentBlock==null){
                $res->is_success = false;
                $res->code = 401;
                $res->message = "Body의 형식이 잘못되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = $data->userIdx;
            $num = sizeof($file);

            if ($commentBlock != "N" && $commentBlock != "Y") {
                $res->is_success = TRUE;
                $res->code = 301;
                $res->message = "commentBlock은 N 또는 Y 만 가능합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            for ($i = 0; $i < $num; $i++) {
                $type = $file[$i]->type;

                if ($type != "image" && $type != "video") {
                    $res->is_success = FALSE;
                    $res->code = 300;
                    $res->message = "파일의 type은 image 또는 video만 가능합니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
            }

            if ($req->isMention) {

                $men_num = sizeof($mention);
                for ($i = 0; $i < $men_num; $i++) {
                    if (!isValidUserId($mention[$i]->userId)) {
                        $res->is_success = FALSE;
                        $res->code = 302;
                        $res->message = "존재하지않는 유저의 ID 입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                }
            }

            createPost($userIdx, $context, $location, $commentBlock);

            $postIdx = getPostIdx($userIdx);

            for ($i = 0; $i < $num; $i++) {
                $type = $file[$i]->type;
                $fileUrl = $file[$i]->fileUrl;

                createPostFile($postIdx, $type, $fileUrl);
            }

            if ($req->isMention) {
                $men_num = sizeof($mention);
                for ($i = 0; $i < $men_num; $i++) {
                    $NewUserIdx = getUserIdxfromId($mention[$i]->userId);
                    createMentionLink($NewUserIdx, "post", $postIdx);
                    if($NewUserIdx != $userIdx) {
                        createActivity($NewUserIdx, $userIdx, 6, $postIdx);
                    }
                }
            }

            if ($req->isHashTag) {
                $has_num = sizeof($hashTag);
                for ($i = 0; $i < $has_num; $i++) {
                    $tagName = $hashTag[$i]->tagName;
                    if (!isValidTagName($tagName)) {
                        createHashTag($tagName);
                    }
                    $tagIdx = getHashTagIdx($tagName);

                    createHashTagLink($postIdx, "post", $tagIdx);
                }
            }

            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "게시글 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "deletePost":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $userIdx = $data->userIdx;
            $postIdx = $vars['postIdx'];

            if (!isValidPost($postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "존재하지 않는 게시글입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if (!isValidPostUser($userIdx, $postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "게시글의 삭제권한이 없는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            deletePost($postIdx);
            deletePostFile($postIdx);
            deletelinkmention($postIdx, "post");
            deletelinkHashTag($postIdx, "post");


            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "게시글 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "updatePost":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $userIdx = $data->userIdx;
            $postIdx = $vars['postIdx'];

            if (!isValidPost($postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "존재하지 않는 게시글입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if (!isValidPostUser($userIdx, $postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "게시글의 수정 권한이 없는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            deletelinkmention($postIdx, "post");
            deletelinkHashTag($postIdx, "post");


            $mention = $req->mention;
            $hashTag = $req->hashTag;
            $location = $req->location;
            $context = $req->context;


            if ($req->isMention) {
                $men_num = sizeof($mention);
                for ($i = 0; $i < $men_num; $i++) {
                    if (!isValidUserId($mention[$i]->userId)) {
                        $res->is_success = FALSE;
                        $res->code = 302;
                        $res->message = "존재하지않는 유저의 ID 입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }
                }
            }

            updatePost($postIdx, $context, $location);

            if ($req->isMention) {
                $men_num = sizeof($mention);
                for ($i = 0; $i < $men_num; $i++) {
                    $NewUserIdx = getUserIdxfromId($mention[$i]->userId);
                    if (!isDupMentionLink($NewUserIdx, "post", $postIdx)) {
                        createMentionLink($NewUserIdx, "post", $postIdx);
                    } else {
                        updatelinkMention($NewUserIdx, "post", $postIdx);
                    }
                }
            }

            if ($req->isHashTag) {
                $has_num = sizeof($hashTag);
                for ($i = 0; $i < $has_num; $i++) {
                    $tagName = $hashTag[$i]->tagName;
                    if (!isValidTagName($tagName)) {
                        createHashTag($tagName);
                    }
                    $tagIdx = getHashTagIdx($tagName);
                    if (!isDupHashTagLink($postIdx, "post", $tagIdx)) {
                        createHashTagLink($postIdx, "post", $tagIdx);
                    } else {
                        updatelinkHashTag($postIdx, "post", $tagIdx);
                    }
                }
            }


            $res->is_success = TRUE;
            $res->code = 200;
            $res->message = "수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }


} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}

