<?php

function getPostLikeDetail($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    userIdx,
    userId,
    if(isnull(profileImg),'-',profileImg)as profileImg,
    name,
    IF(ISNULL(following.userIdx),
        FALSE,
        TRUE) AS isFollowed
FROM
    (SELECT 
        userIdx
    FROM
        postLike
    WHERE
        postIdx = ? AND isDeleted = 'N'
            AND userIdx != ?) AS likedUser
        JOIN
    (SELECT 
        idx AS userIdx, userId, profileImg, name
    FROM
        user
    WHERE
        isDeleted = 'N') AS user USING (userIdx)
        LEFT JOIN
    (SELECT 
        followingIdx AS userIdx
    FROM
        following
    WHERE
        isDeleted = 'N' AND followerIdx = ?) AS following USING (userIdx)
ORDER BY (isFollowed) DESC;";

    $st = $pdo->prepare($query);



    $st->execute([$postIdx,$userIdx,$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $qres = $st->fetchAll();

    $st = null;
    $pdo = null;

    $num = sizeof($qres);
    $res=array();

    for($i=0; $i<$num; $i++){
        $res[$i]->info=$qres[$i];
        $postedIdx=$qres[$i]['userIdx'];

        $res[$i]->storyStatus=getStoryStatus($postedIdx,$userIdx);
    }

    return $res;
}

function isValidPost($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists (select * from post where idx=? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isValidPostUser($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists (select * from post where userIdx=? and idx=? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $postIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getPostImg($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select type, file as url
from postFile
where parentIdx=? and isDeleted='N';";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $st = null;
    $pdo = null;

    return $res;
}



function getPostFollowerLike($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select userIdx
from postLike 
where postIdx=? and isDeleted='N'and userIdx in (select followingIdx as userIdx from following where followerIdx=? and isDeleted ='N')
) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
function getPostLikeStatusMsg($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select idx as followingIdx, 
case
when likeCnt >1 
then concat(userId,'님 외 ' ,likeCnt,'명이 좋아합니다') 
else concat(userId,'님이 좋아합니다')
end as statusMsg ,profileImgCnt
from user 
join
(select userIdx ,postIdx
from postLike 
where postIdx = ? and isDeleted='N'and userIdx in (select followingIdx as userIdx from following where followerIdx=? and isDeleted ='N')
limit 1) as toUser 
on (idx= toUser.userIdx) 
join (select postIdx,count(userIdx)-1 as likeCnt
from postLike
where isDeleted='N'
group by postIdx) as likeCount 
using (postIdx)
join
(select
postIdx,
case
when count(userIdx)>3
then 3
else count(userIdx)
end as profileImgCnt
from postLike 
where isDeleted='N'and userIdx in (select followingIdx as userIdx from following where followerIdx=? and isDeleted ='N')
group by postIdx) as profileCountTable using(postIdx)
where isDeleted='N';
";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx,$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $st = null;
    $pdo = null;

    return $res[0];
}
function getPostStatusMsg($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select concat('좋아요 ',count(userIdx),'개') as statusMsg
from postLike
where postIdx=? and isDeleted='N';";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $st = null;
    $pdo = null;

    return $res[0];
}
function getPostLikeUserProfile($userIdx,$postIdx)
{
        $pdo = pdoSqlConnect();
        $query = "select if(isnull(profileImg),'-',profileImg)as ImgUrl
from (select userIdx
	from postLike 
	where postIdx=? and isDeleted='N'and userIdx in (select followingIdx as userIdx from following where followerIdx=? and isDeleted ='N')) as postLikeUser 
    join (select * from user where isDeleted='N') as userTable on(userIdx = userTable.idx)
limit 3;";

        $st = $pdo->prepare($query);
        $st->execute([$postIdx,$userIdx]);

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();


        $st = null;
        $pdo = null;

        return $res;
}

function getPostCommentCnt($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select
count(userIdx) as commentCnt
from comment
where postIdx=? and isDeleted='N';";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $st = null;
    $pdo = null;

    return $res[0]['commentCnt'];
}

function getPostCommentPre($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select userTable.userIdx, userId, comment.idx as commentIdx, context, if(isnull(isLikedComment.userIdx),0,1) as isLiked
from comment join (select idx as userIdx, userId from user where isDeleted='N') as userTable using (userIdx)
left join (select commentIdx,userIdx from commentLike where isDeleted='N' and userIdx=?) as isLikedComment on(comment.idx=isLikedComment.commentIdx)
where postIdx=? and isDeleted='N'
order by createdTime desc
limit 2;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $st = null;
    $pdo = null;

    return $res;
}


function getHomePost($userIdx,$pageNumber)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    idx AS postIdx,
    post.userIdx,
    userId,
    profileImg,
    IF(ISNULL(location), '-', location) AS location,
    IF(commentBlock='N',0,1) AS commentBlock,
    fileCount,
    IF(ISNULL(Liked.userIdx),FALSE, TRUE) AS isLiked,
    IF(ISNULL(Store.userIdx), FALSE, TRUE) AS isStored,
    IF(ISNULL(haveLike.postIdx), FALSE, TRUE) AS isHavedLiked,
    IF(ISNULL(context), '', context) AS context,
    IF(ISNULL(haveComment.postIdx), 0, 1) AS isHavedComment,
    CASE
        WHEN
            TIMESTAMPDIFF(MINUTE,
                createdTime,
                NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(SECOND,
                        createdTime,
                        NOW()),
                    '초 전')
        WHEN
            TIMESTAMPDIFF(HOUR, createdTime, NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(MINUTE,
                        createdTime,
                        NOW()),
                    '분 전')
        WHEN
            TIMESTAMPDIFF(DAY, createdTime, NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(HOUR, createdTime, NOW()),
                    '시간 전')
        WHEN
            TIMESTAMPDIFF(DAY, createdTime, NOW()) < 5
        THEN
            CONCAT(TIMESTAMPDIFF(DAY, createdTime, NOW()),
                    '일 전')
		WHEN
            TIMESTAMPDIFF(year, createdTime, NOW()) < 1
        THEN
            CONCAT(month(createdTime),'월 ',month(createdTime),'일')
		ELSE CONCAT(year(createdTime),'년 ',month(createdTime),'월 ',month(createdTime),'일')
    END AS postTime
FROM
    post
        JOIN
    (SELECT 
        idx AS userIdx,
            userId,
            IF(ISNULL(profileImg), '-', profileImg) AS profileImg
    FROM
        user
    WHERE
        idx = ?
            OR idx IN (SELECT 
                followingIdx AS userIdx
            FROM
                following
            WHERE
                followerIdx = ? AND isDeleted = 'N')
            AND isDeleted = 'N') AS FollowUser USING (userIdx)         
        left JOIN
    (SELECT 
        parentIdx AS postIdx, COUNT(idx) AS fileCount
    FROM
        postFile
    WHERE
        isDeleted = 'N'
    GROUP BY parentIdx) AS postImgCnt ON (post.idx = postImgCnt.postIdx)
        LEFT JOIN
    (SELECT 
        postIdx, userIdx
    FROM
        postLike
    WHERE
        userIdx = ? AND isDeleted = 'N') AS Liked ON (idx = Liked.postIdx)
        LEFT JOIN
    (SELECT 
        postIdx, userIdx
    FROM
        postStored
    WHERE
        userIdx = ? AND isDeleted = 'N') AS Store ON (idx = Store.postIdx)
        LEFT JOIN
    (SELECT DISTINCT
        postIdx
    FROM
        postLike
    WHERE
        isDeleted = 'N') AS haveLike ON (idx = haveLike.postIdx)
        LEFT JOIN
    (SELECT DISTINCT
        postIdx
    FROM
        comment
    WHERE
        isDeleted = 'N') AS haveComment ON (idx = haveComment.postIdx)
WHERE
    isDeleted = 'N'
ORDER BY createdTime DESC
limit $pageNumber , 12;
";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$userIdx,$userIdx,$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $qres = $st->fetchAll();

    $num =sizeof($qres);
    $res=array();
    for($i =0; $i<$num; $i++){
        $res[$i]->info=$qres[$i];
        $res[$i]->file=getPostImg($qres[$i]['postIdx']);
        if($qres[$i]['isHavedLiked']) {
            $res[$i]->likeStatus->isFollowedLike = getPostFollowerLike($userIdx, $qres[$i]['postIdx']);
            if (getPostFollowerLike($userIdx, $qres[$i]['postIdx'])) {
                $res[$i]->likeStatus->status = getPostLikeStatusMsg($userIdx, $qres[$i]['postIdx']);
                $res[$i]->likeStatus->profileImg = getPostLikeUserProfile($userIdx, $qres[$i]['postIdx']);
            } else {
                $res[$i]->likeStatus->status = getPostStatusMsg($qres[$i]['postIdx']);
            }
        }
        if($qres[$i]['isHavedComment']) {
            $res[$i]->commentStatus->commentCnt = getPostCommentCnt($qres[$i]['postIdx']);
            $res[$i]->commentStatus->commentPre=getPostCommentPre($userIdx,$qres[$i]['postIdx']);
        }
        $postedIdx=$qres[$i]['userIdx'];

        $res[$i]->storyStatus=getStoryStatus($postedIdx,$userIdx);


        $commentUrl = getUserProfile($userIdx);
        $res[$i]->commentUrl=$commentUrl;
    }

    $st = null;
    $pdo = null;

    return $res;
}

function isValidPostLike($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from postLike where userIdx=? and postIdx =? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isCommentLike($userIdx,$commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from commentLike where userIdx=? and commentIdx =? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$commentIdx]);


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isReCommentLike($userIdx,$reCommentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from reCommentLike where userIdx=? and reCommentIdx =? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$reCommentIdx]);


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isDupPostLike($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from postLike where userIdx=? and postIdx =?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isValidCommentLike($userIdx,$commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from commentLike where userIdx=? and commentIdx =? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$commentIdx]);


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isValidreCommentLike($userIdx,$reCommentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from reCommentLike where userIdx=? and reCommentIdx =? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$reCommentIdx]);


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isDupCommentLike($userIdx,$commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from commentLike where userIdx=? and commentIdx =?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$commentIdx]);


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isDupreCommentLike($userIdx,$reCommentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from reCommentLike where userIdx=? and reCommentIdx =?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$reCommentIdx]);


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}





function isDupPostStored($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from postStored where userIdx=? and postIdx =?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}


function isValidComment($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from comment where idx=? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isValidreComment($reCommentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from reComment where idx=? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$reCommentIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isValidCollection($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from postStored where postIdx=? and userIdx=? and isDeleted='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isValidUserComment($commentIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists (select commentUserIdx, userIdx
from (select postIdx, userIdx as commentUserIdx
from comment   
where idx= ? and isDeleted = 'N') as userComment join (select idx, userIdx
from post
where isDeleted ='N') as postUser on (postUser.idx = userComment.postIdx)
where commentUserIdx=? or userIdx=?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx,$userIdx,$userIdx]);


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isValidUserReComment($reCommentIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(
select userIdx, postedIdx
from(select postIdx, postedIdx
from (select idx as commentIdx, postIdx
from comment
where isDeleted='N'
) as A join (select commentIdx, userIdx as postedIdx 
from reComment 
where idx=? and isDeleted='N') as B using (commentIdx)) as C join (select idx as postIdx, userIdx
from post
where isDeleted='N') as D using (postIdx)
where userIdx =? or postedIdx=?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$reCommentIdx,$userIdx,$userIdx]);


    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function createPostLike($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO postLike (`postIdx`, `userIdx`) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function deletePostLike($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `postLike` SET `isDeleted` = 'Y' WHERE (`postIdx` = ?) and (`userIdx` = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function updatePostLike($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `postLike` SET `isDeleted` = 'N' WHERE (`postIdx` = ?) and (`userIdx` = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function createCommentLike($userIdx,$commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO commentLike (`commentIdx`, `userIdx`) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function deleteCommentLike($userIdx,$commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `commentLike` SET `isDeleted` = 'Y' WHERE (`commentIdx` = ?) and (`userIdx` = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function updateCommentLike($userIdx,$commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `commentLike` SET `isDeleted` = 'N' WHERE (`commentIdx` = ?) and (`userIdx` = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function createreCommentLike($userIdx,$reCommentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO reCommentLike (`reCommentIdx`, `userIdx`) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$reCommentIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function deletereCommentLike($userIdx,$reCommentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `reCommentLike` SET `isDeleted` = 'Y' WHERE (`reCommentIdx` = ?) and (`userIdx` = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$reCommentIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function updatereCommentLike($userIdx,$reCommentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `reCommentLike` SET `isDeleted` = 'N' WHERE (`reCommentIdx` = ?) and (`userIdx` = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$reCommentIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function createPostComment($userIdx,$postIdx,$context)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO comment (`postIdx`, `userIdx`, `context`) VALUES (?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx,$context]);

    $st = null;
    $pdo = null;

}

function deletePostComment($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `comment` SET `isDeleted` = 'Y' WHERE `idx`= ?;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);

    $st = null;
    $pdo = null;

}


function createReComment($userIdx,$commentIdx,$context)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO reComment (`commentIdx`, `userIdx`, `context`) VALUES (?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx,$userIdx,$context]);

    $st = null;
    $pdo = null;

}

function deleteReComment($reCommentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `reComment` SET `isDeleted` = 'Y' WHERE `idx`= ?;";

    $st = $pdo->prepare($query);
    $st->execute([$reCommentIdx]);

    $st = null;
    $pdo = null;

}


function createPostStored($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO postStored (`postIdx`, `userIdx`) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function deletePostStored($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE postStored SET isDeleted = 'Y' WHERE (`postIdx` = ?) and (`userIdx` = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function updatePostStored($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE postStored SET isDeleted = 'N' WHERE (`postIdx` = ?) and (`userIdx` = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);

    $st = null;
    $pdo = null;

}

function isRecomment($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists (select * from reComment where isDeleted ='N' and commentIdx=? ) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}

function getReComment($commentIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select reCommentIdx,userIdx,userId,profileImg,context, if(isnull(reCommentLikeT.recommentIdx),false,true) as isLike ,postedTime
from (SELECT idx as reCommentIdx, commentIdx, userIdx, context,
 CASE
        WHEN
            TIMESTAMPDIFF(MINUTE,
                createdTime,
                NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(SECOND,
                        createdTime,
                        NOW()),
                    '초')
        WHEN
            TIMESTAMPDIFF(HOUR, createdTime, NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(MINUTE,
                        createdTime,
                        NOW()),
                    '분')
        WHEN
            TIMESTAMPDIFF(DAY, createdTime, NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(HOUR, createdTime, NOW()),
                    '시간')
        WHEN
            TIMESTAMPDIFF(day, createdTime, NOW()) < 7
        THEN
            CONCAT(TIMESTAMPDIFF(DAY, createdTime, NOW()),
                    '일')
		else CONCAT(TIMESTAMPDIFF(week, createdTime, NOW()),
                    '주')
    END AS postedTime
FROM reComment 
where isDeleted='N' and commentIdx=?
order by createdTime
) as reCommentInfo join 
(select idx as userIdx, userId, profileImg
from user
where isDeleted ='N') as userT using (userIdx)
left join(select reCommentIdx
from reCommentLike
where userIdx=? and isDeleted ='N') as reCommentLikeT using(reCommentIdx);
";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}

function getPostCommentDetail($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select commentIdx,userIdx,userId,profileImg,context, if(isnull(CommentLikeT.commentIdx),false,true) as isLike ,postedTime
from (SELECT idx as commentIdx, postIdx, userIdx, context,
 CASE
        WHEN
            TIMESTAMPDIFF(MINUTE,
                createdTime,
                NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(SECOND,
                        createdTime,
                        NOW()),
                    '초')
        WHEN
            TIMESTAMPDIFF(HOUR, createdTime, NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(MINUTE,
                        createdTime,
                        NOW()),
                    '분')
        WHEN
            TIMESTAMPDIFF(DAY, createdTime, NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(HOUR, createdTime, NOW()),
                    '시간')
        WHEN
            TIMESTAMPDIFF(day, createdTime, NOW()) < 7
        THEN
            CONCAT(TIMESTAMPDIFF(DAY, createdTime, NOW()),
                    '일')
		else CONCAT(TIMESTAMPDIFF(week, createdTime, NOW()),
                    '주')
    END AS postedTime
FROM comment 
where isDeleted='N' and postIdx=?
order by createdTime
) as commentInfo join 
(select idx as userIdx, userId, profileImg
from user
where isDeleted ='N') as userT using (userIdx)
left join(select commentIdx
from commentLike
where userIdx=? and isDeleted ='N') as CommentLikeT using(commentIdx);
";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $qres = $st->fetchAll();

    $num=sizeof($qres);
    $res=array();

    for($i=0;$i<$num;$i++){
        $res[$i]->info=$qres[$i];
        $postedIdx=$qres[$i]['userIdx'];

        $res[$i]->storyStatus=getStoryStatus($postedIdx,$userIdx);


        if(isRecomment($qres[$i]['commentIdx'])){
            $res[$i]->isHaveRecomment=true;
            $res[$i]->reCommentInfo=getReComment($qres[$i]['commentIdx'],$userIdx);
        }
        else{
            $res[$i]->isHaveRecomment=false;
        }
    }

    $st = null;
    $pdo = null;

    return $res;
}

function  createPost($userIdx,$context,$location,$commentBlock)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `post` (`userIdx`, `context`, `location`, `commentBlock`) VALUES (?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$context,$location,$commentBlock]);

    $st = null;
    $pdo = null;
}


function  getPostIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select idx
from post
where userIdx = ?
order by createdTime desc
limit 1;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['idx'];
}

function getUserIdxfromId($userId)
{
    $pdo = pdoSqlConnect();
    $query = "select idx from user where isDeleted='N' and userId=?";

    $st = $pdo->prepare($query);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['idx'];
}

function createPostFile($postIdx,$type,$fileUrl)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `postFile` (`parentIdx`, `type`, `file`) VALUES (?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx, $type, $fileUrl]);

    $st = null;
    $pdo = null;
}

function isDupMentionLink($userIdx, $type, $postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from linkMention where userIdx = ? and type = ? and typeIdx= ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$type,$postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}

function createMentionLink($userIdx, $type, $postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `linkMention` (`userIdx`, `typeIdx`, `type`) VALUES (?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $postIdx, $type]);

    $st = null;
    $pdo = null;
}

function isValidTagName($tagName)
{
    $pdo = pdoSqlConnect();
    $query = "select exists (select * from hashTag where isDeleted='N' and tagName=?) as exist";

    $st = $pdo->prepare($query);
    $st->execute([$tagName]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}

function createHashTag($tagName)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `hashTag` (`tagName`) VALUES (?);";

    $st = $pdo->prepare($query);
    $st->execute([$tagName]);

    $st = null;
    $pdo = null;
}

function getHashTagIdx($tagName)
{
    $pdo = pdoSqlConnect();
    $query = "select idx from hashTag where isDeleted='N' and tagName=?";

    $st = $pdo->prepare($query);
    $st->execute([$tagName]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['idx'];
}

function isDupHashTagLink($postIdx, $type, $tagIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from linkHashTag where typeIdx = ? and type = ? and hashTagIdx= ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$type,$tagIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}



function createHashTagLink($postIdx, $type, $tagIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `linkHashTag` (`typeIdx`,`type`,`hashTagIdx`) VALUES (?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$type,$tagIdx]);

    $st = null;
    $pdo = null;
}

function deletePost($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `post` SET `isDeleted` = 'Y' WHERE `idx` = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);

    $st = null;
    $pdo = null;

}

function deletePostFile($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `postFile` SET `isDeleted` = 'Y' WHERE `parentIdx` = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);

    $st = null;
    $pdo = null;
}

function deletelinkMention($postIdx,$type)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `linkMention` SET `isDeleted` = 'Y' WHERE `typeIdx` = ? and `type` = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$type]);

    $st = null;
    $pdo = null;
}

function updatelinkMention($userIdx, $type, $postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `linkMention` SET `isDeleted` = 'N' WHERE `userIdx` = ? and `typeIdx` = ? and `type` = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx,$type]);

    $st = null;
    $pdo = null;
}

function deletelinkHashTag($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `linkHashTag` SET `isDeleted` = 'Y' WHERE `typeIdx` = ? and `type` = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,"post"]);

    $st = null;
    $pdo = null;
}

function updatelinkHashTag($postIdx, $type, $tagIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `linkHashTag` SET `isDeleted` = 'N' WHERE `typeIdx` = ? and `type` = ? and `hashTagIdx` = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$type,$tagIdx]);

    $st = null;
    $pdo = null;
}


function updatePost($postIdx,$context,$location)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `post` SET `context` = ? , `location` = ? WHERE `idx` = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$context,$location,$postIdx]);

    $st = null;
    $pdo = null;

}





function getUserPostDetail($userIdx,$postedIdx,$pageNumber)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    idx AS postIdx,
    post.userIdx,
    userId,
    profileImg,
    IF(ISNULL(location), '-', location) AS location,
    IF(commentBlock='N',0,1) AS commentBlock,
    fileCount,
    IF(ISNULL(Liked.userIdx),FALSE, TRUE) AS isLiked,
    IF(ISNULL(Store.userIdx), FALSE, TRUE) AS isStored,
    IF(ISNULL(haveLike.postIdx), FALSE, TRUE) AS isHavedLiked,
    IF(ISNULL(context), '', context) AS context,
    IF(ISNULL(haveComment.postIdx), 0, 1) AS isHavedComment,
    CASE
        WHEN
            TIMESTAMPDIFF(MINUTE,
                createdTime,
                NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(SECOND,
                        createdTime,
                        NOW()),
                    '초 전')
        WHEN
            TIMESTAMPDIFF(HOUR, createdTime, NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(MINUTE,
                        createdTime,
                        NOW()),
                    '분 전')
        WHEN
            TIMESTAMPDIFF(DAY, createdTime, NOW()) < 1
        THEN
            CONCAT(TIMESTAMPDIFF(HOUR, createdTime, NOW()),
                    '시간 전')
        WHEN
            TIMESTAMPDIFF(DAY, createdTime, NOW()) < 5
        THEN
            CONCAT(TIMESTAMPDIFF(DAY, createdTime, NOW()),
                    '일 전')
		WHEN
            TIMESTAMPDIFF(year, createdTime, NOW()) < 1
        THEN
            CONCAT(month(createdTime),'월 ',month(createdTime),'일')
		ELSE CONCAT(year(createdTime),'년 ',month(createdTime),'월 ',month(createdTime),'일')
    END AS postTime
FROM
    post
        JOIN
    (SELECT 
        idx AS userIdx,
            userId,
            IF(ISNULL(profileImg), '-', profileImg) AS profileImg
    FROM
        user
    WHERE
        idx = ? AND isDeleted = 'N') AS FollowUser USING (userIdx)         
        left JOIN
    (SELECT 
        parentIdx AS postIdx, COUNT(idx) AS fileCount
    FROM
        postFile
    WHERE
        isDeleted = 'N'
    GROUP BY parentIdx) AS postImgCnt ON (post.idx = postImgCnt.postIdx)
        LEFT JOIN
    (SELECT 
        postIdx, userIdx
    FROM
        postLike
    WHERE
        userIdx = ? AND isDeleted = 'N') AS Liked ON (idx = Liked.postIdx)
        LEFT JOIN
    (SELECT 
        postIdx, userIdx
    FROM
        postStored
    WHERE
        userIdx = ? AND isDeleted = 'N') AS Store ON (idx = Store.postIdx)
        LEFT JOIN
    (SELECT DISTINCT
        postIdx
    FROM
        postLike
    WHERE
        isDeleted = 'N') AS haveLike ON (idx = haveLike.postIdx)
        LEFT JOIN
    (SELECT DISTINCT
        postIdx
    FROM
        comment
    WHERE
        isDeleted = 'N') AS haveComment ON (idx = haveComment.postIdx)
WHERE
    isDeleted = 'N'
ORDER BY createdTime DESC
limit $pageNumber , 6;
";

    $st = $pdo->prepare($query);
    $st->execute([$postedIdx,$userIdx,$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $qres = $st->fetchAll();

    $num =sizeof($qres);
    $res=array();
    for($i =0; $i<$num; $i++){
        $res[$i]->info=$qres[$i];
        $res[$i]->file=getPostImg($qres[$i]['postIdx']);
        if($qres[$i]['isHavedLiked']) {
            $res[$i]->likeStatus->isFollowedLike = getPostFollowerLike($userIdx, $qres[$i]['postIdx']);
            if (getPostFollowerLike($userIdx, $qres[$i]['postIdx'])) {
                $res[$i]->likeStatus->status = getPostLikeStatusMsg($userIdx, $qres[$i]['postIdx']);
                $res[$i]->likeStatus->profileImg = getPostLikeUserProfile($userIdx, $qres[$i]['postIdx']);
            } else {
                $res[$i]->likeStatus->status = getPostStatusMsg($qres[$i]['postIdx']);
            }
        }
        if($qres[$i]['isHavedComment']) {
            $res[$i]->commentStatus->commentCnt = getPostCommentCnt($qres[$i]['postIdx']);
        }
        $postedIdx=$qres[$i]['userIdx'];

        $res[$i]->storyStatus=getStoryStatus($postedIdx,$userIdx);

    }

    $st = null;
    $pdo = null;

    return $res;
}