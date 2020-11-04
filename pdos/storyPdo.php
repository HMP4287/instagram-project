<?php

function isHaveStory($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(SELECT 
        userIdx
    FROM
        story
    WHERE
        timeOut = 'N' AND isDeleted = 'N' and userIdx = ? ) as exist;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isHaveNoBFStory($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(SELECT 
        userIdx
    FROM
        story
    WHERE
        timeOut = 'N' AND isDeleted = 'N' and userIdx = ? and isBFView ='N') as exist;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}


function isViewedAll($postedIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(SELECT 
        userIdx
    FROM
        story
    WHERE
        timeOut = 'N' AND isDeleted = 'N' and userIdx =? and isBFView='N'AND 
idx NOT IN (SELECT storyIdx AS idx
            FROM
                storyViews
            WHERE
                userIdx = ?)) as exist;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$postedIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}


function isViewedAllByBF($postedIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(SELECT 
        userIdx
    FROM
        story
    WHERE
        timeOut = 'N' AND isDeleted = 'N' and userIdx =? AND 
idx NOT IN (SELECT storyIdx AS idx
            FROM
                storyViews
            WHERE
                userIdx = ?)) as exist;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$postedIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isHaveBFView($postedIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(SELECT 
        userIdx
    FROM
        story
    WHERE
        timeOut = 'N' AND isDeleted = 'N' and userIdx =? AND 
idx and isBFView = 'Y'and idx NOT IN (SELECT storyIdx AS idx
            FROM
                storyViews
            WHERE
                userIdx = ?)) as exist;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$postedIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}


function isBF($postedIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(SELECT 
        userIdx
    FROM
        bestFriend
    WHERE
        isDeleted = 'N' and userIdx =? AND friendIdx =?)as exist;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$postedIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isValidHighlight($highlightIdx){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from highlight where idx = ? and isDeleted='N') as exist;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$highlightIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['exist'];

}
function isValidHighlightToDel($highlightIdx,$userIdx){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from highlight where idx = ? and userIdx=? and isDeleted='N') as exist;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$highlightIdx,$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['exist'];

}
function getHighlightFiles($userIdx, $highlightIdx){
    $result = array();

    function first($userIdx,$highlightIdx){
        $pdo = pdoSqlConnect();
        $query = "select EXISTS(select * from highlight
where userIdx=? and idx= ? and isDeleted='N') as exist;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$userIdx, $highlightIdx];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;
        if($res[0]['exist']==1) {
            return $res[0]['exist'];
        }
        else{
            return 0;
        }


    }
    function second($highlightIdx){
        $pdo = pdoSqlConnect();
        $query = "select COUNT(*) as counts
from highlight as A
inner join highlightStory as B
on A.isDeleted='N' and B.isDeleted='N' and A.idx = B.highlightIdx
where A.idx= ?;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$highlightIdx];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;

        return $res[0]['counts'];

    }
    function third($userIdx,$highlightIdx){
        $pdo = pdoSqlConnect();
        $query = "
select (select EXISTS(select * from story where idx=C.idx and context like(CONCAT('@', (select userId from user where idx =?)) ) ) as exist) as isMentionedStory, A.idx highlightIdx , A.highlightName, A.coverImg as highlightCoverImg, C.file , C.type , IFNULL(C.context,0) as context,C.idx as storyIdx, C.isBFview,(
    SELECT
           CASE

               WHEN TIMESTAMPDIFF(SECOND, C.createdTime, NOW()) < 60
                   THEN CONCAT(TIMESTAMPDIFF(SECOND, C.createdTime, NOW()), '초')
               WHEN TIMESTAMPDIFF(MINUTE, C.createdTime, NOW()) < 60
                   THEN CONCAT(TIMESTAMPDIFF(MINUTE, C.createdTime, NOW()), '분')
               WHEN TIMESTAMPDIFF(HOUR, C.createdTime, NOW()) < 24
                   THEN CONCAT(TIMESTAMPDIFF(HOUR, C.createdTime, NOW()), '시')
               WHEN TIMESTAMPDIFF(DAY, C.createdTime, NOW()) < 7
                   THEN CONCAT(TIMESTAMPDIFF(DAY, C.createdTime, NOW()), '일')
               ELSE CONCAT(TIMESTAMPDIFF(WEEK, C.createdTime, NOW()), '주')

               END AS createdTime) as createdTime

from highlight as A
inner join highlightStory as B
on A.isDeleted='N' and B.isDeleted='N' and A.idx = B.highlightIdx
inner join story as C
on B.storyFileIdx = C.idx
where A.idx = ? order by C.createdTime;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$userIdx,$highlightIdx];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;
        if(!empty($res)){
            return $res;
        }
        else{
            return 0;

        }


    }
    try {
        $pdo = pdoSqlConnect();
        $pdo->beginTransaction();
        $result["isMyHighlight"] = first($userIdx,$highlightIdx);
        $result["totalCounts"] = second($highlightIdx);
        $result["highlights"]= third($userIdx,$highlightIdx);

        $pdo-> commit();
    }
    catch(\Execption $e){

        if ($pdo->inTransaction()) {
            $pdo->rollback();
            // If we got here our two data updates are not in the database
        }
        throw $e;

    }

    return $result;

}

function delMyHighlight($userIdx, $highlightIdx){
    $pdo = pdoSqlConnect();
    $query = "update highlight SET isDeleted='Y' where userIdx=? and idx=?";

    $st = $pdo->prepare($query);

    $st->execute([$userIdx,$highlightIdx]);
    $st = null;
    $pdo = null;

}
function postNewStory($userIdx,$count,$reqStoryData){

    for($i=0;$i<$count;$i=$i+1) {

        $file = $reqStoryData->result[$i]->file;
        $type = $reqStoryData->result[$i]->type;
        $context = $reqStoryData->result[$i]->context;
        $isBFView = $reqStoryData->result[$i]->isBFView;
        $pdo = pdoSqlConnect();
        $query = "insert into story ( userIdx, file, type , context, isBFView) values (?,?,?,?,?)";

        $st = $pdo->prepare($query);

        $st->execute([$userIdx,$file,$type,$context,$isBFView]);
        $st = null;
        $pdo = null;
        $file = null;
        $type = null;
        $context= null;
        $isBFView = null;

    }


}



function getStoryList($userIdx){

    $pdo = pdoSqlConnect();
    $query =  "select A.userIdx, A.idx as storyIdx, (B.idx is not null) as isNotViewed ,D.userId , IFNULL(D.profileImg,'') as profileImg
from story as A
left join (select idx from story where idx not in (select storyIdx from storyViews where userIdx=?) and isDeleted='N' group by userIdx) as B
on A.idx = B.idx
inner join following as C
on C.followerIdx=? and C.followingIdx= A.userIdx and A.isDeleted='N'
inner join user as D
on D.idx= A.userIdx
where (TIMESTAMPDIFF(DAY, A.createdTime, now()))<1
group by A.userIdx order by isNotViewed desc , COUNT(B.idx);";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;

}
function getMyOnlyStory($userIdx){

    $pdo = pdoSqlConnect();
    $query =  "select A.idx as userIdx,IFNULL(B.idx,0) as storyIdx,(B.idx is not null) as isNotViewed, A.userId,  IFNULL(A.profileImg,'') as profileImg
from user as A
left join (select idx,userIdx from story where idx not in (select storyIdx from storyViews where userIdx = ? ) and isDeleted='N' and (TIMESTAMPDIFF(DAY, createdTime, now()))<1 group by userIdx) as B
on B.userIdx= A.idx
where A.idx= ? and A.isDeleted='N';";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;

}


function getOtherDetailStory($userIdx){

    $pdo = pdoSqlConnect();
    $query =  "select A.idx, A.file ,A.type, A.context,A.isBFView,(
    SELECT
           CASE

               WHEN TIMESTAMPDIFF(SECOND, A.createdTime, NOW()) < 60
                   THEN CONCAT(TIMESTAMPDIFF(SECOND, A.createdTime, NOW()), '초')
               WHEN TIMESTAMPDIFF(MINUTE, A.createdTime, NOW()) < 60
                   THEN CONCAT(TIMESTAMPDIFF(MINUTE, A.createdTime, NOW()), '분')
               WHEN TIMESTAMPDIFF(HOUR, A.createdTime, NOW()) < 24
                   THEN CONCAT(TIMESTAMPDIFF(HOUR, A.createdTime, NOW()), '시간')
               END AS createdTime) as createdTime 

from story as A

where A.userIdx=? and (TIMESTAMPDIFF(DAY, A.createdTime, now()))<1 and A.isDeleted='N';";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;

}
function countGetOtherDetailStory($userIdx){

    $pdo = pdoSqlConnect();
    $query =  "select COUNT(D.idx) as counts from (select A.idx as idx

from story as A

where A.userIdx=? and (TIMESTAMPDIFF(DAY, A.createdTime, now()))<1 and A.isDeleted='N') as D;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['counts'];

}
function checkStoryUserValid($userIdx){
    $pdo = pdoSqlConnect();
    $query =  "SELECT EXISTS(select * from story where userIdx=? and (TIMESTAMPDIFF(DAY, createdTime, now()))<1 and isDeleted='N') as exist;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getSelfDetailStory($userIdx){

    $pdo = pdoSqlConnect();
    $query =  "
select A.idx, A.file ,A.type,IFNULL(A.context,'') as context ,A.isBFView,(
    SELECT
           CASE

               WHEN TIMESTAMPDIFF(SECOND, A.createdTime, NOW()) < 60
                   THEN CONCAT(TIMESTAMPDIFF(SECOND, A.createdTime, NOW()), '초')
               WHEN TIMESTAMPDIFF(MINUTE, A.createdTime, NOW()) < 60
                   THEN CONCAT(TIMESTAMPDIFF(MINUTE, A.createdTime, NOW()), '분')
               WHEN TIMESTAMPDIFF(HOUR, A.createdTime, NOW()) < 24
                   THEN CONCAT(TIMESTAMPDIFF(HOUR, A.createdTime, NOW()), '시간')
               END AS createdTime) as createdTime , IFNULL(C.idx,0) as inHighlightIdx
     ,
       (
                    SELECT
           CASE

               WHEN IFNULL((select COUNT(*) as counts from storyViews where storyIdx=A.idx),0) < 1
                   THEN 0
               WHEN IFNULL((select COUNT(*) as counts from storyViews where storyIdx=A.idx),0) > 0
                   THEN
                        concat((select COUNT(*) as counts from storyViews where storyIdx=A.idx),'명 읽음')

               END AS storyViews) as storyViews , IFNULL(C.idx,0) as inHighlightIdx



from story as A
left join (select * from highlightStory) as B
on A.idx = B.storyFileIdx and B.isDeleted='N'
left join (select * from highlight) as C
on C.idx= B.highlightIdx and C.userIdx=A.userIdx
where A.userIdx=? and (TIMESTAMPDIFF(DAY, A.createdTime, now()))<1 and A.isDeleted='N';";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;

}
function countGetSelfDetailStory($userIdx){

    $pdo = pdoSqlConnect();
    $query =  "SELECT COUNT(*) as counts from (select A.idx



from story as A
left join (select * from highlightStory) as B
on A.idx = B.storyFileIdx and B.isDeleted='N'
left join (select * from highlight) as C
on C.idx= B.highlightIdx and C.userIdx=A.userIdx
where A.userIdx=? and (TIMESTAMPDIFF(DAY, A.createdTime, now()))<1 and A.isDeleted='N') as counts;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['counts'];

}

//function delStory($storyIdx){
//
//    $pdo = pdoSqlConnect();
//    $query =  "UPDATE story SET isDeleted = 'Y' WHERE idx= ?";
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $var = [$storyIdx];
//    $st->execute($var);
////    $st->setFetchMode(PDO::FETCH_ASSOC);
////    $res = $st->fetchAll();
//    $st = null;
//    $pdo = null;
//
////    return $res[0]['counts'];
//
//}
//function checkExistsHighlightWith($storyIdx){
//
//    $pdo = pdoSqlConnect();
//    $query =  "SELECT EXISTS(select * from highlightStory where storyFileIdx= ?) as exist;";
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $var = [$storyIdx];
//    $st->execute($var);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//    $st = null;
//    $pdo = null;
//
//    return $res[0]['exist'];
//
//}



function delHighlightStory($storyIdx){

    function zero ($storyIdx){
        $pdo = pdoSqlConnect();
        $query =  "UPDATE story SET isDeleted = 'Y' WHERE idx= ?";
        $st = $pdo->prepare($query);

        $var = [$storyIdx];
        $st->execute($var);


        $st = null;
        $pdo = null;
    }

    function one ($storyIdx)
    {
        $pdo = pdoSqlConnect();
        $query = "select highlightIdx from highlightStory where storyFileIdx=?";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$storyIdx];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;

        return $res[0]['highlightIdx'];
    }
//    이걸로 하이라이트 9안에 데이터가 1이면 highlight도 삭제
    function two ($storyIdx)
    {
        $pdo = pdoSqlConnect();
        $query = "UPDATE highlightStory SET isDeleted = 'Y' WHERE storyFileIdx=?";
        $st = $pdo->prepare($query);

        $var = [$storyIdx];
        $st->execute($var);


        $st = null;
        $pdo = null;

    }
    function checkExistsHighlightWith($storyIdx){

    $pdo = pdoSqlConnect();
    $query =  "SELECT EXISTS(select * from highlightStory where storyFileIdx= ?) as exist;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$storyIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['exist'];

}
    function three ($highlightIdx)
    {
        $pdo = pdoSqlConnect();
        $query = "select COUNT(*) as counts
from highlightStory
where highlightIdx=? and isDeleted='N';";
        $st = $pdo->prepare($query);

        $var = [$highlightIdx];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;

        return $res[0]['counts'];
    }
    zero($storyIdx);
    $checkNum = checkExistsHighlightWith($storyIdx);
    if($checkNum==1) {
        two($storyIdx);

        $highlightIdx = one($storyIdx);
        $number = three($highlightIdx);
        if ($number < 1) {
            $pdo = pdoSqlConnect();
            $query = "UPDATE highlight SET isDeleted = 'Y' WHERE idx=?;";
            $st = $pdo->prepare($query);

            $var = [$highlightIdx];
            $st->execute($var);

            $st = null;
            $pdo = null;
        }
        }


}

function getStoryViewedList($userIdx,$storyIdx){

    $pdo = pdoSqlConnect();
    $query =  "select A.userId,IFNULL(A.name,'') as name ,IFNULL(A.profileImg,'') as profileImg, (B.idx is not null) as storyNotViewed
from user as A
left join (select idx,userIdx from story where idx not in (select storyIdx from storyViews where userIdx = ? ) and isDeleted='N' and (TIMESTAMPDIFF(DAY, createdTime, now()))<1 group by userIdx) as B
on B.userIdx = A .idx and A.isDeleted='N'
inner join (select userIdx,createdTime from storyViews where storyIdx=?) as C
on C.userIdx= A.idx
group by A.idx order by C.createdTime desc;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$storyIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;

}

function checkHighlightStoryExistsN($highlightIdx,$storyIdx){

    $pdo = pdoSqlConnect();
    $query =  "select EXISTS(select * from highlightStory where highlightIdx = ? and storyFileIdx =?  and isDeleted='N') as exist;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$highlightIdx,$storyIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['exist'];

}

function checkHighlightStoryExistsY($highlightIdx,$storyIdx){

    $pdo = pdoSqlConnect();
    $query =  "select EXISTS(select * from highlightStory where highlightIdx = ? and storyFileIdx =?  and isDeleted='Y') as exist;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$highlightIdx,$storyIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['exist'];

}

function highlightInsert($highlightIdx,$storyIdx){
    $pdo = pdoSqlConnect();
    $query =  "insert into highlightStory(highlightIdx,storyFileIdx) values (?, ?);";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$highlightIdx,$storyIdx];
    $st->execute($var);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

//    return $res[0]['exist'];
}
function highlightToN($highlightIdx,$storyIdx){
    $pdo = pdoSqlConnect();
    $query =  "update highlightStory SET isDeleted='N' where highlightIdx=? and storyFileIdx=? and isDeleted='Y';";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$highlightIdx,$storyIdx];
    $st->execute($var);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

}
function highlightToY($highlightIdx,$storyIdx){
    $pdo = pdoSqlConnect();
    $query =  "update highlightStory SET isDeleted='Y' where highlightIdx=? and storyFileIdx=? and isDeleted='N';";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$highlightIdx,$storyIdx];
    $st->execute($var);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
    $st = null;
    $pdo = null;


}
function getHighlightLists($storyIdx,$userIdx){
    $pdo = pdoSqlConnect();
    $query =  "select A.idx ,A.highlightName, A.coverImg , (B.highlightIdx is not null) as isInHighlight
from highlight as A
left join (select * from highlightStory where storyFileIdx=?) as B
on A.idx = B.highlightIdx and B.isDeleted='N'
where userIdx=? and A.isDeleted='N';";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$storyIdx,$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}


//function insertNewHighlight($highlightName,$userIdx,$coverImg){
//    $pdo = pdoSqlConnect();
//    $query =  "insert into highlight(highlightName, userIdx, coverImg) values (?,?,?);";
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $var = [$highlightName,$userIdx,$coverImg];
//    $st->execute($var);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//    $st = null;
//    $pdo = null;
//
//    return $res;
//
//}

