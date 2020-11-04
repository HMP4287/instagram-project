<?php

//function getHighlightFiles($userIdx, $highlightIdx){
//    $result = array();
//
//    function first($userIdx,$highlightIdx){
//        $pdo = pdoSqlConnect();
//        $query = "";
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $var = [$userIdx, $highlightIdx];
//        $st->execute($var);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//        $st = null;
//        $pdo = null;
//        if($res[0]['exist']==1) {
//            return $res[0]['exist'];
//        }
//        else{
//            return 0;
//        }
//
//
//    }
//    function second($highlightIdx){
//        $pdo = pdoSqlConnect();
//        $query = "";
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $var = [$highlightIdx];
//        $st->execute($var);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//        $st = null;
//        $pdo = null;
//
//        return $res[0]['counts'];
//
//    }
//    function third($userIdx,$highlightIdx){
//        $pdo = pdoSqlConnect();
//        $query = "";
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $var = [$userIdx,$highlightIdx];
//        $st->execute($var);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//        $st = null;
//        $pdo = null;
//        if(!empty($res)){
//            return $res;
//        }
//        else{
//            return 0;
//
//        }
//
//
//    }
//    try {
//        $pdo = pdoSqlConnect();
//        $pdo->beginTransaction();
//        $result["isMyHighlight"] = first($userIdx,$highlightIdx);
//        $result["totalCounts"] = second($highlightIdx);
//        $result["highlights"]= third($userIdx,$highlightIdx);
//
//        $pdo-> commit();
//    }
//    catch(\Execption $e){
//
//        if ($pdo->inTransaction()) {
//            $pdo->rollback();
//            // If we got here our two data updates are not in the database
//        }
//        throw $e;
//
//    }
//
//    return $result;
//
//}

function countGetSearchListType($userIdx, $keyword){

    $pdo = pdoSqlConnect();
    $query = "select CEIL(COUNT(counts.postIdx)/18) as dataCounts from (
select A.idx as postIdx, C.fileCounts as fileCounts , C.type ,C.file
from post as A
inner join user as B
on A.userIdx = B.idx and A.isDeleted='N' and B.isDeleted='N' and (private='N' or A.userIdx in (select followingIdx from following where followerIdx=?))
inner join (select COUNT(idx) as fileCounts, parentIdx, type, isDeleted,file from postFile where isDeleted='N' group by parentIdx) as C
on C.parentIdx = A.idx
where A.context like(concat('%',?,'%'))
order by A.createdTime desc) as counts;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$keyword];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['dataCounts'];


}

function getSearchListType($userIdx, $keyword,$number){

    $pdo = pdoSqlConnect();
    $query = "select A.idx as postIdx, C.fileCounts as fileCounts , C.type ,C.file
from post as A
inner join user as B
on A.userIdx = B.idx and A.isDeleted='N' and B.isDeleted='N' and (private='N' or A.userIdx in (select followingIdx from following where followerIdx=?))
inner join (select COUNT(idx) as fileCounts, parentIdx, type, isDeleted,file from postFile where isDeleted='N' group by parentIdx) as C
on C.parentIdx = A.idx
where A.context like(concat('%',?,'%'))
order by A.createdTime desc limit $number,18;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$keyword];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;


}

function getSearchList($userIdx, $keyword,$sortNumber){
//

    function one ($userIdx,$keyword){

        function first($userIdx,$keyword){
            $pdo = pdoSqlConnect();
            $query = "select A.idx as userIdx,A.userId,IFNULL(A.name,'') as name,IFNULL(A.profileImg,'') as profileImg, (B.idx is not null) as storyNotViewed,(C.followingIdx is not null) as following, (
    SELECT
           CASE
               WHEN D.counts < 1
                   THEN 0
               WHEN D.counts > 1
                   THEN CONCAT('새로운 게시물 ',D.counts,'개')
               ELSE
                   0
               END AS newPostsCount) as newPostCount


from user as A

left join (select idx,userIdx from story where idx not in (select storyIdx from storyViews where userIdx = ? ) and isDeleted='N' and (TIMESTAMPDIFF(DAY, createdTime, now()))<1 group by userIdx) as B
on B.userIdx= A.idx
left join (select followingIdx from following where followerIdx= ? and isDeleted='N') as C
on C.followingIdx= A.idx
left join (select idx, userIdx, COUNT(idx) as counts from post where idx not in (select postIdx from postViews where userIdx= ? ) and isDeleted='N' and (TIMESTAMPDIFF(DAY, createdTime, now()))<1 group by userIdx) as D
on D.userIdx= A.idx
left join (select userIdx,myUserIdx, COUNT(userIdx) as counts from myPageView where myUserIdx=? group by userIdx) as E
on E.userIdx = A.idx

where A.userId like(concat('%',?,'%')) or A.name like(concat('%',?,'%')) and A.isDeleted='N'
order by E.counts desc limit 20;";
            $st = $pdo->prepare($query);
            //    $st->execute([$param,$param]);
            $var = [$userIdx,$userIdx,$userIdx,$userIdx,$keyword,$keyword];
            $st->execute($var);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll();
//            $count=$st->rowCount();

            $st = null;
            $pdo = null;
            return $res;
        }

        function second($keyword){
            $pdo = pdoSqlConnect();
            $query = "
select idx as hashTagIdx , tagName ,(
    SELECT
           CASE
               WHEN counts = 0
                   THEN 0
               WHEN counts = 1
                   THEN CONCAT('게시물 ',counts,'개')
               ELSE
                   CONCAT(FORMAT(counts,0),' 게시물')

               END AS postCounts) as postCounts
from hashTag as A
inner join (select hashTagIdx, COUNT(typeIdx) as counts from linkHashTag group by hashTagIdx) as B
on A.idx = B.hashTagIdx and A.isDeleted='N'
where A.tagName like(concat('%',?,'%')) order by B.counts desc limit 20;";
            $st = $pdo->prepare($query);
            //    $st->execute([$param,$param]);
            $var = [$keyword];
            $st->execute($var);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll();
            $st = null;
            $pdo = null;
            return $res;
        }
        function third($keyword){
            $pdo = pdoSqlConnect();
            $query = "select location
from post as A
where isDeleted='N' and location like(concat('%',?,'%')) group by location limit 60;";
            $st = $pdo->prepare($query);
            //    $st->execute([$param,$param]);
            $var = [$keyword];
            $st->execute($var);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll();
            $st = null;
            $pdo = null;
            return $res;

        }
        $result['popularUser'] = first($userIdx,$keyword);
        $result['popularTag'] =second($keyword);
        $result['popularLocation'] = third($keyword);
        return $result;


    }
//    게정 검색 60개
    function two ($userIdx,$keyword) {
        $pdo = pdoSqlConnect();
        $query = "select A.idx as userIdx,A.userId,IFNULL(A.name,'') as name,IFNULL(A.profileImg,'') as profileImg, (B.idx is not null) as storyNotViewed,(C.followingIdx is not null) as following, (
    SELECT
           CASE
               WHEN D.counts < 1
                   THEN 0
               WHEN D.counts > 1
                   THEN CONCAT('새로운 게시물 ',D.counts,'개')
               ELSE
                   0
               END AS newPostsCount) as newPostCount


from user as A

left join (select idx,userIdx from story where idx not in (select storyIdx from storyViews where userIdx = ? ) and isDeleted='N' and (TIMESTAMPDIFF(DAY, createdTime, now()))<1 group by userIdx) as B
on B.userIdx= A.idx
left join (select followingIdx from following where followerIdx= ? and isDeleted='N') as C
on C.followingIdx= A.idx
left join (select idx, userIdx, COUNT(idx) as counts from post where idx not in (select postIdx from postViews where userIdx= ? ) and isDeleted='N' and (TIMESTAMPDIFF(DAY, createdTime, now()))<1 group by userIdx) as D
on D.userIdx= A.idx
left join (select userIdx,myUserIdx, COUNT(userIdx) as counts from myPageView where myUserIdx=? group by userIdx) as E
on E.userIdx = A.idx

where A.userId like(concat('%',?,'%')) or A.name like(concat('%',?,'%')) and A.isDeleted='N'
order by E.counts desc limit 60;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$userIdx,$userIdx,$userIdx,$userIdx,$keyword,$keyword];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;
        return $res;
    }
    function three ($keyword) {
        $pdo = pdoSqlConnect();
        $query = "
select idx as hashTagIdx , tagName ,(
    SELECT
           CASE
               WHEN counts = 0
                   THEN 0
               WHEN counts = 1
                   THEN CONCAT('게시물 ',counts,'개')
               ELSE
                   CONCAT(FORMAT(counts,0),' 게시물')

               END AS postCounts) as postCounts
from hashTag as A
inner join (select hashTagIdx, COUNT(typeIdx) as counts from linkHashTag group by hashTagIdx) as B
on A.idx = B.hashTagIdx and A.isDeleted='N'
where A.tagName like(concat('%',?,'%')) order by B.counts desc limit 60;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$keyword];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;
        return $res;
    }
    function four($keyword){
        $pdo = pdoSqlConnect();
        $query = "select location
from post as A
where isDeleted='N' and location like(concat('%',?,'%')) group by location limit 60;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$keyword];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;
        return $res;
    }
    if($sortNumber==1){
        return one($userIdx,$keyword);
    }

    if($sortNumber==2){
        return two($userIdx, $keyword);
    }
    if($sortNumber==3) {
        return three($keyword);
    }
    if($sortNumber==4){
        return four($keyword);
    }



}
function countSortNumberThree($keyword){

    $pdo = pdoSqlConnect();
    $query = "select CEIL(COUNT(A.hashTagIdx)/21) as counts from (
select idx as hashTagIdx
from hashTag as A
inner join (select hashTagIdx, COUNT(typeIdx) as counts from linkHashTag group by hashTagIdx) as B
on A.idx = B.hashTagIdx and A.isDeleted='N'
where A.tagName like(concat('%',?,'%')) order by B.counts) as A ;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$keyword];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['counts'];

}

function makeRandCol($userIdx){
    function one ($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select A.idx as postIndex
from post as A
inner join user as B
on A.userIdx= B.idx and A.isDeleted='N'
inner join (select idx, parentIdx, type , isDeleted, file,COUNT(idx) as fileCounts from postFile group by parentIdx ) as C
on C.parentIdx = A.idx and C.isDeleted = 'N'
order by RAND();";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $count=$st->rowCount();
    two($count,$userIdx,$res);
    $st = null;
    $pdo = null;
    return 1;
    }
    function two ($count,$userIdx,$res){

        for($i=0;$i<$count;$i=$i+1){
            $randCol = $res[$i]['postIndex'];
            $pdo = pdoSqlConnect();
            $query = "insert into randCol(userIdx,randCol) values (?,?)";

            $st = $pdo->prepare($query);

            $st->execute([$userIdx,$randCol]);
            $st = null;
            $pdo = null;


        }
    }
    one($userIdx);

}
function delRandCol($userIdx){

    $pdo = pdoSqlConnect();
    $query = "delete from randCol where userIdx=?;";

    $st = $pdo->prepare($query);

    $st->execute([$userIdx]);
    $st = null;
    $pdo = null;

}
function showRandColPosts($userIdx,$pageNumber){
    $pdo = pdoSqlConnect();
    $query = "select A.idx as postIndex, C.type ,C.file , C.fileCounts as fileCount
from post as A
inner join user as B
on A.userIdx= B.idx and A.isDeleted='N'
inner join (select idx, parentIdx, type , isDeleted, file,COUNT(idx) as fileCounts from postFile group by parentIdx ) as C
on C.parentIdx = A.idx and C.isDeleted = 'N'
inner join (select idx, userIdx, randCol from randCol) as D
on D.randCol = A.idx and D.userIdx= ? order by D.idx limit $pageNumber,24;";
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
function countRandColPosts($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select CEIL(COUNT(A.idx)/24) as counts from (select * from randCol where userIdx=?) as A;";
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

function isValidHashTagIndex($hashTagIndex){
//SELECT EXISTS(select * from hashTag where idx=?) as exist;
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select * from hashTag where idx=? and isDeleted='N') as exist;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$hashTagIndex];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $st = null;
    $pdo = null;
    return $res[0]['exist'];

}
function countHashTagIndex($hashTagIndex){
//SELECT EXISTS(select * from hashTag where idx=?) as exist;
    $pdo = pdoSqlConnect();
    $query = "select CEIL(COUNT(A.typeIdx)/24) as counts from (select * from linkHashTag where hashTagIdx=? and type='post' group by typeIdx) as A";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$hashTagIndex];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $st = null;
    $pdo = null;
    return $res[0]['counts'];

}
function getSearchHashTag($hashTagIndex,$sortNumber,$number){
//SELECT EXISTS(select * from hashTag where idx=?) as exist;
    function one($hashTagIndex,$number)
    {
        $pdo = pdoSqlConnect();
        $query = "select A.idx, C.fileCounts ,C.type ,C.file
from post as A
inner join (select * from linkHashTag where hashTagIdx=? and isDeleted='N' and type='post' group by typeIdx) as B
on B.typeIdx = A.idx and A.isDeleted='N'
inner join (select COUNT(idx) as fileCounts, parentIdx, type, isDeleted,file from postFile where isDeleted='N' group by parentIdx) as C
on C.parentIdx = A.idx
left join (select count(userIdx) as counts, postIdx from postViews group by postIdx) as D
on D.postIdx= A.idx
order by D.counts desc limit $number,24;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$hashTagIndex];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();


        $st = null;
        $pdo = null;
        return $res;
    }
    function two($hashTagIndex,$number)
    {
        $pdo = pdoSqlConnect();
        $query = "select A.idx, C.fileCounts ,C.type ,C.file
from post as A
inner join (select * from linkHashTag where hashTagIdx=? and isDeleted='N' and type='post' group by typeIdx) as B
on B.typeIdx = A.idx
inner join (select COUNT(idx) as fileCounts, parentIdx, type, isDeleted,file from postFile where isDeleted='N' group by parentIdx) as C
on C.parentIdx = A.idx
left join (select count(userIdx) as counts, postIdx from postViews group by postIdx) as D
on D.postIdx= A.idx
order by A.createdTime desc limit $number,24;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$hashTagIndex];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();


        $st = null;
        $pdo = null;
        return $res;
    }
    if($sortNumber==1){
        return one($hashTagIndex,$number);
    }
    if($sortNumber==2){
        return two($hashTagIndex,$number);
    }

}
function isValidLocation($location){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select *
from post as A
where location like(concat('%',?,'%')) and isDeleted='N') as exist;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$location];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $st = null;
    $pdo = null;
    return $res[0]['exist'];

}
function countLocation($location){
//SELECT EXISTS(select * from hashTag where idx=?) as exist;
    $pdo = pdoSqlConnect();
    $query = "
select CEIL(COUNT(A.idx)/24) as counts
from post as A
where A.location like(concat('%',?,'%')) and A.isDeleted='N';";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$location];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $st = null;
    $pdo = null;
    return $res[0]['counts'];

}

function getSearchLocation($location,$sortNumber,$number){
//SELECT EXISTS(select * from hashTag where idx=?) as exist;
    function one($location,$number)
    {
        $pdo = pdoSqlConnect();
        $query = "select A.idx, B.fileCounts, B.type, B.file
from post as A
inner join (select COUNT(idx) as fileCounts, parentIdx, type, isDeleted,file from postFile where isDeleted='N' group by parentIdx) as B
on A.idx= B.parentIdx and A.isDeleted='N'
left join (select count(userIdx) as counts, postIdx from postViews group by postIdx) as C
on C.postIdx= A.idx
where location like(concat('%',?,'%'))
order by C.counts desc limit $number,24;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$location];
        $st->execute([$location]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();


        $st = null;
        $pdo = null;
        return $res;
    }
    function two($location,$number)
    {
        $pdo = pdoSqlConnect();
        $query = "select A.idx, B.fileCounts, B.type, B.file
from post as A
inner join (select COUNT(idx) as fileCounts, parentIdx, type, isDeleted,file from postFile where isDeleted='N' group by parentIdx) as B
on A.idx= B.parentIdx and A.isDeleted='N'
left join (select count(userIdx) as counts, postIdx from postViews group by postIdx) as C
on C.postIdx= A.idx
where location like(concat('%',?,'%'))
order by A.createdTime desc limit $number,24;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$location];
        $st->execute([$location]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();


        $st = null;
        $pdo = null;
        return $res;
    }
    if($sortNumber==1){
        return one($location,$number);
    }
    if($sortNumber==2){
        return two($location,$number);
    }

}