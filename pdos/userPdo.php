<?php

function isValidId($id)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select idx ,id, pw
from(
select idx ,id, pw
from((select idx, userId as id, password as pw
from user 
where userId is not null and isDeleted ='N')  union (select idx, email as id, password as pw
from user 
where email is not null and isDeleted ='N') ) as userT union (select idx, mobile as id, password as pw
from user 
where mobile is not null and isDeleted ='N')) as unionT
where id=?
) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function getUserProfile($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select if(isnull(profileImg),'-',profileImg) as profileImg from user where isDeleted = 'N' and idx=?";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['profileImg'];
}

function isValidPw($id,$pw)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select idx ,id, pw
from(
select idx ,id, pw
from((select idx, userId as id, password as pw
from user 
where userId is not null and isDeleted ='N')  union (select idx, email as id, password as pw
from user 
where email is not null and isDeleted ='N') ) as userT union (select idx, mobile as id, password as pw
from user 
where mobile is not null and isDeleted ='N')) as unionT
where id=? and pw=?
) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id, $pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isValidUser($idx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from user where idx=? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isValidUserId($userId)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from user where userId=? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isDupUserId($userId)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from user where userId=? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function getUserIdx($id,$pw)
{
    $pdo = pdoSqlConnect();
    $query = "select idx
from(
select idx ,id, pw
from((select idx, userId as id, password as pw
from user 
where userId is not null and isDeleted ='N')  union (select idx, email as id, password as pw
from user 
where email is not null and isDeleted ='N') ) as userT union (select idx, mobile as id, password as pw
from user 
where mobile is not null and isDeleted ='N')) as unionT
where id=? and pw=?;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id,$pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['idx']);
}


function createPhoneUser($id,$pw,$mobile,$birthday)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO user (`userId`, `password`, `mobile`,`birthDay`) VALUES (?, ?, ?, ?);";

    $st = $pdo->prepare($query);

    $st->execute([$id,$pw,$mobile,$birthday]);


    $st = null;
    $pdo = null;

}
function createEmailUser($id,$pw,$email,$birthday)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO user (`userId`, `password`, `email`,  `birthDay`) VALUES (?, ?,  ?, ?);";

    $st = $pdo->prepare($query);

    $st->execute([$id,$pw,$email,$birthday]);


    $st = null;
    $pdo = null;

}


// 필요없을 듯 하다 (클라와 논의)
function getMyPageMentionedListExist($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select idx as postIdx
from post as A
inner join linkMention as B
on B.typeIdx=A.idx and B.userIdx=?
inner join (select COUNT(*) as counts,parentIdx from postFile group by parentIdx) as C
on C.parentIdx = A.idx
where A.isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return intval($res[0]['exist']);
}

function getMyPageMentionedPosts($userIdx,$reqPageNumber)
{
    $pdo = pdoSqlConnect();
    $query = "select idx as postIndex ,C.counts,C.type, C.file
from post as A
inner join linkMention as B
on B.typeIdx=A.idx and B.userIdx=? and B.type='post'
inner join (select COUNT(*) as counts,parentIdx,type,file from postFile group by parentIdx) as C
on C.parentIdx = A.idx
where A.isDeleted='N' order by A.createdTime desc limit $reqPageNumber ,12;";

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
function getMyPageUser($userIdx){


        $pdo = pdoSqlConnect();
        $query = "select A.idx as userIndex, A.userId , IFNULL(A.website,'') as website, IFNULL(A.email,'') as email, IFNULL(A.mobile,'') as mobile, IFNULL(A.name,'') as name, IFNULL(A.profileImg,'') as profileImg,IFNULL(A.introduction,'') as introduction,IF(A.professional = 'Y', 1, IF(A.professional='N',0 , 0)) AS professional,IF(A.private = 'Y', 1, IF(A.private='N',0 , 0)) AS private, IFNULL(A.sex,'') as sex, IFNULL(IF(A.birthDay = '0000-00-00', '', IF(A.birthDay=null,'', A.birthday)),'') AS birthday , IF(A.isSubUser = 'Y', 1, IF(A.isSubUser='N',0 , 0)) AS isSubUser ,(select COUNT(*) as postCount from post where userIdx=? and isDeleted='N') as postCount,IFNULL((select COUNT(*) as follower from following where isDeleted='N' and followingIdx = ?),0) as follower,IFNULL((select COUNT(*) as following from following where isDeleted='N' and followerIdx = ?),0) as following 
from user as A
inner join (SELECT EXISTS(select * from story where userIdx= ? and (TIMESTAMPDIFF(DAY, createdTime, now()))<1 and isDeleted='N') as exist) as B
inner join (SELECT EXISTS(select idx from story where idx not in (select storyIdx from storyViews where userIdx=?) and isDeleted='N' and userIdx=? and (TIMESTAMPDIFF(DAY, createdTime, now()))<1) as exist) as C
where A.idx=?;";
        $var = [$userIdx,$userIdx,$userIdx,$userIdx,$userIdx,$userIdx,$userIdx];
        $st = $pdo->prepare($query);
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;
        return $res[0];



}
function getMyPageHighlight($userIdx){

    $pdo = pdoSqlConnect();
    $query = "select idx as highlightIndex ,highlightName, IFNULL(coverImg,'') as coverImg from highlight where userIdx = ? and isDeleted='N';";
    $var = [$userIdx];
    $st = $pdo->prepare($query);
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res;

}





function getMyPageMyPosts($userIdx,$reqPageNumber,$otherUserIdx){


    function one ($userIdx,$reqPageNumber,$otherUserIdx)
    {
        $pdo = pdoSqlConnect();
        $query = "
select A.idx as postIndex, B.counts ,B.type, B.file
from post as A
inner join (select COUNT(*) as counts,parentIdx,type,file from postFile group by parentIdx) as B
on A.idx= B.parentIdx
where A.userIdx= ? and A.isDeleted='N' order by A.createdTime desc limit $reqPageNumber , 12;";

        $st = $pdo->prepare($query);
        $var = [$otherUserIdx];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $count=$st->rowCount();
        if($userIdx==$otherUserIdx){
            $st = null;
            $pdo = null;

            return $res;
        }
        else{
            three($userIdx,$count,$res);
            $st = null;
            $pdo = null;

            return $res;

        }

    }
//    return one($userIdx,$reqPageNumber);


    function checkAlreadyViewedPost($userIdx,$postIdx){
        $pdo3= pdoSqlConnect();
        $query3 = "select EXISTS(select * from postViews where userIdx=? and postIdx=?) as exist";
        $st3 = $pdo3->prepare($query3);
        $var3 = [$userIdx,$postIdx];
        $st3->execute($var3);
        $st3->setFetchMode(PDO::FETCH_ASSOC);
        $res3 = $st3->fetchAll();

        $st3 = null;
        $pdo3 = null;
        return $res3[0]['exist'];

    }
    function three($userIdx, $count,$oneRes)
    {

        for ($i = 0; $i < $count; $i = $i + 1) {

            $postIdx = $oneRes[$i]['postIndex'];



            if (!checkAlreadyViewedPost($userIdx, $postIdx)) {
                $pdo2 = pdoSqlConnect();
                $query2 = "insert into postViews(userIdx,postIdx) values(?,?);";

                $st2 = $pdo2->prepare($query2);
                $var2 = [$userIdx, $postIdx];
                $st2->execute($var2);

                $st2 = null;
                $pdo2 = null;

            }
            $postIdx = null;


        }
    }

    return one($userIdx,$reqPageNumber,$otherUserIdx);
}


function isPrivateUser($otherUserIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select private from user where idx= ? and private='Y' and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$otherUserIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return intval($res[0]['exist']);
}
function isFollowedUser($userIdx, $otherUserIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from following where followerIdx = ? and followingIdx = ? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$otherUserIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return intval($res[0]['exist']);
}
function isFollowRequestedUser($userIdx, $otherUserIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from followRequest where userIdx = ? and followerIdx = ? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$otherUserIdx,$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return intval($res[0]['exist']);
}
function isFollowingWith($userIdx, $otherUserIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
SELECT

	CASE
	    WHEN (C.counts = 0 ) THEN ''
		WHEN (C.counts = 1 ) THEN CONCAT(C.userId,'님이 팔로우합니다')

        ELSE CONCAT(C.userId,'님 외 ',C.counts,'명이 팔로우 합니다')
	END AS isFollowingWith
FROM (select idx as userIdx,userId,COUNT(*) as counts from user where idx in (

select IFNULL(A.followerIdx,0) as followerIdx
# 누구를
from following as A
# 내가
inner join following as B
on A.followerIdx=B.followingIdx
where A.followingIdx = ? and B.followerIdx = ? and A.isDeleted='N') and user.isDeleted='N') as C;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$otherUserIdx,$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['isFollowingWith'];
}
function pagingPostsCount($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select CEIL(COUNT(*)/12) as counts from post where userIdx=? and isDeleted='N';";

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
function pagingMentionedPostsCount($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select CEIL(COUNT(C.postIdx)/12) as counts
from (
select idx as postIdx
from post as A
inner join linkMention as B
on B.typeIdx=A.idx and B.userIdx=?
inner join (select COUNT(*) as counts,parentIdx,type,file from postFile group by parentIdx) as C
on C.parentIdx = A.idx
where A.isDeleted='N') as C;";

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
function profileChange($var,$userIdx){
    $profileImg='profileImg';
    $name = 'name';
    $userId ='userId';
    $website ='website';
    $introduction = 'introduction';
    $email = 'email';
    $mobile = 'mobile';
    $sex = 'sex';
    $birthday ='birthday';
    $professional='professional';
    $namings = [$profileImg,$name,$userId,$website,$introduction,$email,$mobile,$sex,$birthday,$professional];

    for($i=0;$i<10;$i=$i+1){
        if($var[$i]!==0){

            $pdo = pdoSqlConnect();
            $query = "UPDATE user

SET  $namings[$i] = :result

WHERE idx = :userIdx;";
            $st = $pdo->prepare($query);

            $st->bindParam(':result',$var[$i]);
            $st->bindParam(':userIdx',$userIdx);

            $st->execute();
            $st = null;
            $pdo = null;
            $result =null;

        }
    }
}
function isSamePhone($mobile){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from user where mobile =? ) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$mobile];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['exist'];

}
function isSameUserId($userId){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from user where userId =? ) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userId];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['exist'];

}



function getMyFollowerList($userIdx, $otherUserIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select A.followerIdx as userIdx, B.userId,IFNULL(B.name,0) as name, IFNULL(B.profileImg,0) as profileImg, (C.idx IS NOT NULL) AS storyNotViewed, (D.followingIdx IS NOT NULL) AS isFollowed, (E.userIdx IS NOT NULL) AS followRequested
from following as A
inner join user as B
on A.followerIdx = B.idx and B.isDeleted='N'
# userIdx = 나자신
left join (select * from story where idx not in (select storyIdx from storyViews where userIdx=?) and timeOut='N' and isDeleted='N' group by userIdx ) as C
on C.userIdx = B.idx
# 나 자신
left join (select followingIdx from following where followerIdx= ?) as D
on  D.followingIdx = A.followerIdx
left join (select * from followRequest where isDeleted='N') as E
# 나 자신
on E.userIdx = A.followerIdx and E.followerIdx =?

# 상대
where A.followingIdx= ? and A.isDeleted='N';";
    $st = $pdo->prepare($query);
    $var = [$userIdx, $userIdx, $userIdx, $otherUserIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;


    return $res;
}

function isDupFollowedUser($userIdx, $otherUserIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from following where followerIdx = ? and followingIdx = ?) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$otherUserIdx];

    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return intval($res[0]['exist']);



}

function followRequestedList($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select CONCAT(A.userId,'님 외 ',(select count(*) from followRequest where userIdx=?),'명') as followRequested
from user as A
where A.idx in (select userIdx from followRequest where userIdx=?);";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$userIdx];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['followRequested'];

}

function isDupFollowRequestedUser($userIdx, $otherUserIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from followRequest where userIdx = ? and followerIdx = ?) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$otherUserIdx];

    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;


    return $res[0]['exist'];

}


function getMyFollowingList($userIdx,$otherUserIdx,$sortNumber){
//    기본 정렬
    $result = array();
    function one ($userIdx,$otherUserIdx)
    {
        $pdo = pdoSqlConnect();
        $query = "select A.followingIdx as userIdx, B.userId,IFNULL(B.name,0) as name, IFNULL(B.profileImg,0) as profileImg, (C.idx IS NOT NULL) AS storyNotViewed, (D.followingIdx IS NOT NULL) AS isFollowed, (E.userIdx IS NOT NULL) AS followRequested
from following as A
inner join user as B
on A.followingIdx=B.idx and B.isDeleted='N' and A.isDeleted='N'

left join (select * from story where idx not in (select storyIdx from storyViews where userIdx=?) and timeOut='N' and isDeleted='N' group by userIdx) as C
on C.userIdx= B.idx

left join (select followingIdx from following where followerIdx= ?) as D
on  D.followingIdx = A.followingIdx

left join (select * from followRequest where isDeleted='N') as E
on E.userIdx = A.followingIdx and E.followerIdx =?

where A.followerIdx= ?;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$userIdx,$userIdx,$userIdx, $otherUserIdx];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;

        return $res;
    }
//    최신 순 정렬
    function two ($userIdx,$otherUserIdx)
    {
        $pdo = pdoSqlConnect();
        $query = "select A.followingIdx as userIdx, B.userId,IFNULL(B.name,0) as name, IFNULL(B.profileImg,0) as profileImg, (C.idx IS NOT NULL) AS storyNotViewed, (D.followingIdx IS NOT NULL) AS isFollowed, (E.userIdx IS NOT NULL) AS followRequested
from following as A
inner join user as B
on A.followingIdx=B.idx and B.isDeleted='N' and A.isDeleted='N'

left join (select * from story where idx not in (select storyIdx from storyViews where userIdx=?) and timeOut='N' and isDeleted='N' group by userIdx) as C
on C.userIdx= B.idx

left join (select followingIdx from following where followerIdx= ?) as D
on  D.followingIdx = A.followingIdx

left join (select * from followRequest where isDeleted='N') as E
on E.userIdx = A.followingIdx and E.followerIdx =?

where A.followerIdx= ? order by A.createdTime desc;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$userIdx,$userIdx,$userIdx, $otherUserIdx];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;

        return $res;
    }
//    오래된 순 정렬
    function three ($userIdx,$otherUserIdx)
    {
        $pdo = pdoSqlConnect();
        $query = "select A.followingIdx as userIdx, B.userId,IFNULL(B.name,0) as name, IFNULL(B.profileImg,0) as profileImg, (C.idx IS NOT NULL) AS storyNotViewed, (D.followingIdx IS NOT NULL) AS isFollowed, (E.userIdx IS NOT NULL) AS followRequested
from following as A
inner join user as B
on A.followingIdx=B.idx and B.isDeleted='N' and A.isDeleted='N'

left join (select * from story where idx not in (select storyIdx from storyViews where userIdx=?) and timeOut='N' and isDeleted='N' group by userIdx) as C
on C.userIdx= B.idx

left join (select followingIdx from following where followerIdx= ?) as D
on  D.followingIdx = A.followingIdx

left join (select * from followRequest where isDeleted='N') as E
on E.userIdx = A.followingIdx and E.followerIdx =?

where A.followerIdx= ? order by A.createdTime;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $var = [$userIdx,$userIdx,$userIdx, $otherUserIdx];
        $st->execute($var);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st = null;
        $pdo = null;

        return $res;
    }
    if($sortNumber==1){
        $result = one($userIdx,$otherUserIdx);
        return $result;
    }
    if($sortNumber==2){
        $result = two($userIdx,$otherUserIdx);
        return $result;
    }
    if($sortNumber==3){
        $result = three($userIdx,$otherUserIdx);
        return $result;
    }


}



function createFollow($userIdx,$followIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `following` (`followerIdx`, `followingIdx`) VALUES (?, ?);";

    $st = $pdo->prepare($query);


    $st->execute([$userIdx,$followIdx]);
    $st = null;
    $pdo = null;
}

function updateFollow($userIdx,$followIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `following` SET `isDeleted` = 'N' WHERE (`followerIdx` = ?) and (`followingIdx` = ?);";

    $st = $pdo->prepare($query);

    $st->execute([$userIdx,$followIdx]);
    $st = null;
    $pdo = null;
}


function createFollowRequest($userIdx,$followIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `followRequest` (`userIdx`, `followerIdx`) VALUES (?, ?);";

    $st = $pdo->prepare($query);


    $st->execute([$userIdx,$followIdx]);
    $st = null;
    $pdo = null;
}

function updateFollowRequest($userIdx,$followIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `followRequest` SET `isDeleted` = 'N' WHERE (`userIdx` = ?) and (`followerIdx` = ?);";

    $st = $pdo->prepare($query);

    $st->execute([$userIdx,$followIdx]);
    $st = null;
    $pdo = null;
}

function deleteFollowRequest($userIdx,$followIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `followRequest` SET `isDeleted` = 'Y' WHERE (`userIdx` = ?) and (`followerIdx` = ?);";

    $st = $pdo->prepare($query);

    $st->execute([$userIdx,$followIdx]);
    $st = null;
    $pdo = null;
}


function deleteFollow($userIdx,$followIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `following` SET `isDeleted` = 'Y' WHERE (`followerIdx` = ?) and (`followingIdx` = ?);";

    $st = $pdo->prepare($query);

    $st->execute([$userIdx,$followIdx]);
    $st = null;
    $pdo = null;
}

function getFollowRequest($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select userIdx, profileImg, userId, name , if(isnull(followingStatusMsg),'',followingStatusMsg) as followingStatusMsg
from(select A.userIdx, if(isnull(profileImg),'',profileImg) as profileImg,userId,if(isnull(name),'',name) as name
from(select userIdx
from followRequest 
where isDeleted = 'N' and followerIdx =?) as A join (select idx as userIdx, profileImg,userId,name
from user
where isDeleted ='N') as B using (userIdx)) as info left join (select A.userIdx,
case
when comFollowCnt = 0
then concat(userId, '님이 팔로우 합니다')
else concat(userId, '님 외 ',comFollowCnt, '명이 팔로우 합니다')
end as followingStatusMsg
from (select followingIdx as userIdx, count(followerIdx)-1 as comFollowCnt 
from following
where isDeleted ='N' and
followerIdx in (select followingIdx as followerIdx
				from following
				where isDeleted='N' and followerIdx =?)
group by followingIdx) as A join (select followingIdx as userIdx, max(followerIdx) as mainIdx
from following
where isDeleted ='N' and
followerIdx in (select followingIdx as followerIdx
				from following
				where isDeleted='N' and followerIdx =?)
group by (followingIdx)) as B using(userIdx)
join (select idx as mainIdx, userId from user where isDeleted='N') as C using (mainIdx)) as msg using (userIdx)";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$userIdx,$userIdx];

    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;


    return $res;

}


function getMySearchedFollowerList($userIdx,$otherUserIdx,$keyword){

    $pdo = pdoSqlConnect();
    $query = "select A.followerIdx as userIdx, B.userId,IFNULL(B.name,0) as name, IFNULL(B.profileImg,0) as profileImg, (C.idx IS NOT NULL) AS storyNotViewed, (D.followingIdx IS NOT NULL) AS isFollowed, (E.userIdx IS NOT NULL) AS followRequested
from following as A
inner join user as B
on A.followerIdx = B.idx and B.isDeleted='N'


left join (select * from story where idx not in (select storyIdx from storyViews where userIdx=?) and timeOut='N' and isDeleted='N' group by userIdx ) as C
on C.userIdx = B.idx

left join (select followingIdx from following where followerIdx= ?) as D
on  D.followingIdx = A.followerIdx
left join (select * from followRequest where isDeleted='N') as E

on E.userIdx = A.followerIdx and E.followerIdx =?


where A.followingIdx= ? and A.isDeleted='N' and (B.name like(concat('%',?,'%')) or B.userId like(concat('%',?,'%')));";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$userIdx,$userIdx, $otherUserIdx,$keyword,$keyword];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;


    return $res;



}
function getMySearchedFollowingList($userIdx,$otherUserIdx,$keyword){

    $pdo = pdoSqlConnect();
    $query = "select A.followingIdx as userIdx, B.userId,IFNULL(B.name,0) as name, IFNULL(B.profileImg,0) as profileImg, (C.idx IS NOT NULL) AS storyNotViewed, (D.followingIdx IS NOT NULL) AS isFollowed, (E.userIdx IS NOT NULL) AS followRequested
from following as A
inner join user as B
on A.followingIdx=B.idx and B.isDeleted='N' and A.isDeleted='N'

left join (select * from story where idx not in (select storyIdx from storyViews where userIdx=?) and timeOut='N' and isDeleted='N' group by userIdx) as C
on C.userIdx= B.idx

left join (select followingIdx from following where followerIdx= ?) as D
on  D.followingIdx = A.followingIdx

left join (select * from followRequest where isDeleted='N') as E
on E.userIdx = A.followingIdx and E.followerIdx =?

where A.followerIdx= ? and (B.name like(concat('%',?,'%')) or B.userId like(concat('%',?,'%')));";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $var = [$userIdx,$userIdx,$userIdx, $otherUserIdx,$keyword,$keyword];
    $st->execute($var);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;


    return $res;

}

function getUserId($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select userId from user where idx= ? and isDeleted = 'N'";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['userId'];
}

function getPostedUserIdx($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select userIdx from post where idx= ? and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['userIdx'];
}


function myPageViewHistory($userIdx,$otherUserIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into myPageView(userIdx,myUserIdx) values(?,?);";

    $st = $pdo->prepare($query);
    $var = [$otherUserIdx,$userIdx];
    $st->execute($var);
    $st = null;
    $pdo = null;
}

function getStoryStatus($postedIdx,$userIdx)
{
    //postedIdx: 스토리 상태를 알고싶은 유저의 index
    //userIdx: 나, 토큰으로 받아온 user의 index
    $storyStatus=0;
    if(!isHaveStory($postedIdx)){
        $storyStatus=1;
    }
    else{
        if(isBF($postedIdx,$userIdx)||$postedIdx==$userIdx) {
            if(!isViewedAllByBF($postedIdx,$userIdx)){
                $storyStatus=4;
            }
            else if(!isHaveBFView($postedIdx,$userIdx)){

                $storyStatus=2;
            }
            else{
                $storyStatus=3;
            }
        }
        else {
            if(!isHaveNoBFStory($postedIdx)){
                $storyStatus=1;
            }
            else{
                if(!isViewedAll($postedIdx,$userIdx)){
                    $storyStatus=4;
                }
                else{
                    $storyStatus=2;
                }
            }
        }

    }
    return $storyStatus;
}


//
//function isValidHighlight($userIdx){
//    $pdo = pdoSqlConnect();
//    $query = "select EXISTS(select idx as highlightIdx ,highlightName, IFNULL(coverImg,'') as coverImg from highlight where userIdx = ? and isDeleted='N') as exist;";
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute([$userIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0]['exist'];
//
//
//}
function getRecommendUser($userIdx,$pageNumber)
{
    $pdo = pdoSqlConnect();
    $query = "select userIdx,userId,name as userName, profileImg,message,if(followerCnt>30,true,false) as isBlueBadge
from(select distinct(userIdx),message
from
((select followerIdx as userIdx, concat('회원님을 팔로우 합니다') as message
from following
where isDeleted='N' and followingIdx=? and followerIdx not in
(select followingIdx as followerIdx
from following
where followerIdx=? and isDeleted='N'))
 union 
 (select followingIdx as userIdx,
 case
 when Cnt>1
 then concat(userId,'님 외 ',Cnt-1,'명이 팔로우합니다')
 else concat(userId,'님이 팔로우합니다')
 end as message
from(select followingIdx, min(followerIdx) as followerIdx, count(followerIdx) as Cnt
from following
where isdeleted='N' and followerIdx in
(select followingIdx as followerIdx
from following
where isDeleted='N' and followerIdx=?)
and followingIdx not in 
(select followingIdx
from following
where isDeleted='N' and followerIdx=?) and
followingIdx not in(select followerIdx as followingIdx
from following
where isDeleted='N' and followingIdx=? and followerIdx not in
(select followingIdx as followerIdx
from following
where followerIdx=? and isDeleted='N'))
group by followingIdx) as a join (select idx as followerIdx, userId
from user
where isDeleted='N') as b using(followerIdx))
union
(select followingIdx as userIdx, concat('인기') as message
from following
where isDeleted='N' and followingIdx not in 
(select followingIdx
from following
where isDeleted='N' and followerIdx=?)
and followingIdx not in(select followingIdx
from following
where isdeleted='N' and followerIdx in
(select followingIdx as followerIdx
from following
where isDeleted='N' and followerIdx=?)
and followingIdx not in 
(select followingIdx
from following
where isDeleted='N' and followerIdx=?)
)
and followingIdx not in(select followerIdx as followingIdx
from following
where isDeleted='N' and followingIdx=? and followerIdx not in
(select followingIdx as followerIdx
from following
where followerIdx=? and isDeleted='N'))
group by(followingIdx)
having (count(followerIdx) >6)) ) as recommendU)as recommedUser 
join (select idx as userIdx, userId, if(isnull(name),'',name) as name, if(isnull(profileImg),'',profileImg) as profileImg
from user
where isDeleted='N') as userInfo using(userIdx)
left join(select followingIdx as userIdx,count(followerIdx) as followerCnt
from following
where isDeleted='N'
group by(followingIdx)) as follower using(userIdx)
where userIdx!=?
limit $pageNumber , 12;
";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx,$userIdx,$userIdx,$userIdx,$userIdx,$userIdx,$userIdx,$userIdx,$userIdx,$userIdx,$userIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

