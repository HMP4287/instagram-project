<?php


function createActivity($userIdx,$actorIdx,$type,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `actHistory` (`userIdx`, `actorIdx`, `type`, `postIdx`) VALUES (?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx,$actorIdx,$type,$postIdx]);

    $st = null;
    $pdo = null;


}

function getCommentUserIdx($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select userIdx from comment where idx= ? and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['userIdx'];
}

function getReCommentUserIdx($reCommentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select userIdx from reComment where idx= ? and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$reCommentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['userIdx'];
}

function getCommentIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select idx from comment where userIdx= ? and isDeleted = 'N' order by createdTime desc limit 1;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['idx'];
}


function getReCommentIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select idx from reComment where userIdx= ? and isDeleted = 'N' order by createdTime desc limit 1;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['idx'];
}


function getPostIdxByComment($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select postIdx from comment where idx= ? and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['postIdx'];
}

function getFollowRequestMember($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT userIdx
FROM followRequest 
where isDeleted='N' and followerIdx=?
limit 1;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getComment($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT context
FROM comment
where isDeleted='N' and idx=?";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['context'];
}

function getReContext($reCommentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT context
FROM reComment 
where isDeleted='N' and idx=?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$reCommentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['context'];
}

function getCommentIdxByReComment($reCommentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT commentIdx
FROM reComment 
where isDeleted='N' and idx=?";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$reCommentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['commentIdx'];
}

function getPostContext($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT context
FROM post
where isDeleted='N' and idx=?";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['context'];
}


function getFollowRequestStatus($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select 
case
when requestCnt >1 
then concat(userId ,'님 외 ' ,requestCnt,'명')
else userId
end as statusMsg
from (select idx as userIdx, userId
from user
where isDeleted='N') as A join (select userIdx 
from followRequest
where isDeleted='N' and followerIdx=?
limit 1) as b using (userIdx)
join(SELECT count(userIdx)-1 as requestCnt
FROM followRequest 
where isDeleted='N' and followerIdx=?
group by(followerIdx))as c;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $qres = $st->fetchAll();
    $requestUserIdx=getFollowRequestMember($userIdx);
    $num=sizeof($requestUserIdx);
    if($num==0){
        $res[0]['isHaveRequest']=false;
    }
    else {
        $res[0]['isHaveRequest']=true;
        $res[0]['statusMsg'] = $qres[0]['statusMsg'];
        $res[0]['file'] = getUserProfile($requestUserIdx[0]['userIdx']);
    }

    $st = null;
    $pdo = null;

    return $res[0];
}


function getActivity($userIdx,$str)
{
    $pdo = pdoSqlConnect();
    $query = "select 
actorIdx as userIdx, type, postIdx,
CASE
WHEN TIMESTAMPDIFF(MINUTE,createdTime,NOW()) < 1
THEN CONCAT(TIMESTAMPDIFF(SECOND,createdTime,NOW()),'초')
WHEN TIMESTAMPDIFF(HOUR, createdTime, NOW()) < 1
THEN CONCAT(TIMESTAMPDIFF(MINUTE,createdTime,NOW()),'분')
WHEN TIMESTAMPDIFF(DAY, createdTime, NOW()) < 1
THEN CONCAT(TIMESTAMPDIFF(HOUR, createdTime, NOW()),'시간')
WHEN TIMESTAMPDIFF(day, createdTime, NOW()) < 7
THEN CONCAT(TIMESTAMPDIFF(DAY, createdTime, NOW()),'일')
else CONCAT(TIMESTAMPDIFF(week, createdTime, NOW()),'주')
END AS postTime
from actHistory
where isDeleted = 'N' and userIdx = ? and $str
order by createdTime desc;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $qres = $st->fetchAll();
    $num=sizeof($qres);

    $res=array();

    for($i=0;$i<$num;$i++){
        $type=$qres[$i]['type'];
        $actorIdx=$qres[$i]['userIdx'];
        $typeIdx=$qres[$i]['postIdx'];
        $profileImg=getUserProfile($actorIdx);
        $userId=getUserId($actorIdx);
        $res[$i]->userIdx=$actorIdx;
        $res[$i]->profileImg=$profileImg;
        $res[$i]->type=$type;
        if(!isHaveStory($actorIdx)){
            $res[$i]->storyStatus=1;
        }
        else{
            if(isBF($actorIdx,$userIdx)) {
                if(!isViewedAllByBF($actorIdx,$userIdx)){
                    $res[$i]->storyStatus=4;
                }
                else if(!isHaveBFView($actorIdx,$userIdx)){

                    $res[$i]->storyStatus=2;
                }
                else{
                    $res[$i]->storyStatus=3;
                }
            }
            else {
                if(!isHaveNoBFStory($actorIdx)){
                    $res[$i]->storyStatus=1;
                }
                else{
                    if(!isViewedAll($actorIdx,$userIdx)){
                        $res[$i]->storyStatus=4;
                    }
                    else{
                        $res[$i]->storyStatus=2;
                    }
                }
            }
        }
        if($type==1){

            $message=$userId."님이 팔로우하기 시작했습니다.";
            $res[$i]->message=$message;
            if(isPrivateUser($actorIdx)) {
                if(isFollowRequestedUser($userIdx,$actorIdx)){
                    $res[$i]->followStatus=1; //요청됨
                }
                else if(isFollowedUser($userIdx, $actorIdx)){
                    $res[$i]->followStatus=2; //팔로잉
                }
                else{
                    $res[$i]->followStatus=3; //팔로우
                }
            }
            else{
                if(isFollowedUser($userIdx, $actorIdx)){
                    $res[$i]->followStatus=2;
                }
                else{
                    $res[$i]->followStatus=3;
                }
            }
            $res[$i]->postTime=$qres[$i]['postTime'];
        }
        else if($type==2){
            $message=$userId."님이 팔로우를 요청했습니다.";
            $res[$i]->message=$message;
            $res[$i]->postTime=$qres[$i]['postTime'];
        }
        else if($type==3){
            $postFile=getPostImg($typeIdx);
            $cnt=sizeof($postFile);
            if($cnt>1){
                $message=$userId."님이 회원님의 게시물을 좋아합니다";
                $res[$i]->message=$message;
            }
            else{
                $message=$userId."님이 회원님의 사진을 좋아합니다";
                $res[$i]->message=$message;
            }
            $res[$i]->postIdx=$typeIdx;
            $res[$i]->postImg=$postFile[0]['url'];
            $res[$i]->postTime=$qres[$i]['postTime'];
        }
        else if($type==4){
            $comment=getComment($typeIdx);
            $message=$userId."님이 댓글을 남겼습니다: ".$comment;
            $res[$i]->message=$message;
            $res[$i]->commentIdx=$typeIdx;
            if(isCommentLike($userIdx,$typeIdx)){
                $res[$i]->isCommentLiked=true;
            }
            else{
                $res[$i]->isCommentLiked=false;
            }
            $postIdx=getPostIdxByComment($typeIdx);
            $res[$i]->postIdx=$postIdx;
            $postFile=getPostImg($postIdx);
            $res[$i]->postImg=$postFile[0]['url'];
            $res[$i]->postTime=$qres[$i]['postTime'];
        }
        else if($type==5){
            $comment=getReContext($typeIdx);
            $message=$userId."님이 댓글을 남겼습니다: ".$comment;
            $res[$i]->message=$message;
            $res[$i]->reCommentIdx=$typeIdx;
            $commentIdx=getCommentIdxByReComment($typeIdx);
            if(isReCommentLike($userIdx,$typeIdx)){
                $res[$i]->isReCommentLiked=true;
            }
            else{
                $res[$i]->isReCommentLiked=false;
            }
            $postIdx=getPostIdxByComment($commentIdx);
            $res[$i]->postIdx=$postIdx;
            $postFile=getPostImg($postIdx);
            $res[$i]->postImg=$postFile[0]['url'];
            $res[$i]->postTime=$qres[$i]['postTime'];
        }
        else if($type==6){
            $postFile=getPostImg($typeIdx);
            $cnt=sizeof($postFile);
            $context=getPostContext($typeIdx);
            $message=$userId."님이 회원님의 게시물에서 회원님을 언급했습니다: ".$context;
            $res[$i]->message=$message;
            $res[$i]->postImg=$typeIdx;
            $res[$i]->postImg=$postFile[0]['url'];
            $res[$i]->postTime=$qres[$i]['postTime'];
        }
        else if($type==7){
            $comment=getComment($typeIdx);
            $message=$userId."님이 회원님의 댓글을 좋아합니다: ".$comment;
            $res[$i]->message=$message;
            $res[$i]->commentIdx=$typeIdx;
            $postIdx=getPostIdxByComment($typeIdx);
            $res[$i]->postIdx=$postIdx;
            $postFile=getPostImg($postIdx);
            $res[$i]->postImg=$postFile[0]['url'];
            $res[$i]->postTime=$qres[$i]['postTime'];
        }
        else if($type==8){
            $comment=getReContext($typeIdx);
            $message=$userId."님이 댓글을 좋아합니다: ".$comment;
            $res[$i]->message=$message;
            $res[$i]->reCommentIdx=$typeIdx;
            $commentIdx=getCommentIdxByReComment($typeIdx);
            $postIdx=getPostIdxByComment($commentIdx);
            $res[$i]->postIdx=$postIdx;
            $postFile=getPostImg($postIdx);
            $res[$i]->postImg=$postFile[0]['url'];
            $res[$i]->postTime=$qres[$i]['postTime'];
        }
    }
    $st = null;
    $pdo = null;

    return $res;
}
