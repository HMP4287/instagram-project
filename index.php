<?php
require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './pdos/activityPdo.php';
require './pdos/dmPdo.php';
require './pdos/postPdo.php';
require './pdos/searchPdo.php';
require './pdos/storyPdo.php';
require './pdos/userPdo.php';
require './vendor/autoload.php';



use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
//error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {

    

    $r->addRoute('POST', '/login', ['MainController', 'createJwt']);
    $r->addRoute('POST', '/login/facebook', ['MainController', 'createJwt']);
    $r->addRoute('GET', '/auto-login', ['MainController', 'getValidJwt']);
    
    $r->addRoute('POST', '/user', ['userController', 'createUser']);
    $r->addRoute('GET', '/post/{postIdx}/like', ['postController', 'getPostLikeDetail']);


    $r->addRoute('GET', '/home', ['postController', 'getHome']);
    $r->addRoute('GET', '/post/user/{userIdx}', ['postController', 'getUserPostDetail']);

    $r->addRoute('POST', '/post/like', ['postController', 'createPostLike']);
    $r->addRoute('DELETE', '/post/{postIdx}/like', ['postController', 'deletePostLike']);

    $r->addRoute('POST', '/post/comment', ['postController', 'createPostComment']);
    $r->addRoute('DELETE', '/post/comment/{commentIdx}', ['postController', 'deletePostComment']);

    $r->addRoute('POST', '/recomment', ['postController', 'createReComment']);
    $r->addRoute('DELETE', '/recomment/{reCommentIdx}', ['postController', 'deleteReComment']);

    $r->addRoute('POST', '/post/stored', ['postController', 'createPostStored']);
    $r->addRoute('DELETE', '/post/{postIdx}/stored', ['postController', 'deletePostStored']);

    $r->addRoute('GET', '/post/{postIdx}/comment', ['postController', 'getPostCommentDetail']);

    $r->addRoute('POST', '/comment/like', ['postController', 'createCommentLike']);
    $r->addRoute('DELETE', '/comment/{commentIdx}/like', ['postController', 'deleteCommentLike']);

    $r->addRoute('POST', '/recomment/like', ['postController', 'createReCommentLike']);
    $r->addRoute('DELETE', '/recomment/{reCommentIdx}/like', ['postController', 'deleteReCommentLike']);

    $r->addRoute('POST', '/post', ['postController', 'createPost']);
    $r->addRoute('DELETE', '/post/{postIdx}', ['postController', 'deletePost']);
    $r->addRoute('PUT', '/post/{postIdx}', ['postController', 'updatePost']);

    $r->addRoute('POST', '/follow/user', ['userController', 'createFollow']);
    $r->addRoute('DELETE', '/follow/user/{userIdx}', ['userController', 'deleteFollow']);

    $r->addRoute('GET', '/follow/request', ['userController', 'getFollowRequest']);

    $r->addRoute('DELETE', '/follow/request/accept/{userIdx}', ['userController', 'acceptFollowRequest']);

    $r->addRoute('DELETE', '/follow/request/deny/{userIdx}', ['userController', 'denyFollowRequest']);

    $r->addRoute('GET', '/activity', ['activityController', 'getActivity']);

    $r->addRoute('GET', '/recommend/user', ['userController', 'getRecommendUser']);


// 19

    $r->addRoute('GET', '/my-page', ['userController', 'getMyPage']);
// 19.1
    $r->addRoute('GET', '/my-page/mentioned-list', ['userController', 'getMyPageMentionedList']);
// 19.2
    $r->addRoute('GET', '/my-page/posts', ['userController', 'getMyPageMyPosts']);
// 22
    $r->addRoute('POST', '/my-page', ['userController', 'postMyPage']);
// 20
    $r->addRoute('GET', '/my-page/follower-list', ['userController', 'getMyFollowerList']);
// 20.1
    $r->addRoute('GET', '/my-page/follower-list/searched', ['userController', 'getMySearchedFollowerList']);
// 21
    $r->addRoute('GET', '/my-page/following-list', ['userController', 'getMyFollowingList']);
// 21.1
    $r->addRoute('GET', '/my-page/following-list/searched', ['userController', 'getMySearchedFollowingList']);

// 23
    $r->addRoute('GET', '/my-page/highlight/{highlightIdx}', ['storyController', 'getMyHighlight']);
// 24
    $r->addRoute('DELETE', '/my-page/highlight/{highlightIdx}', ['storyController', 'delMyHighlight']);

// 25
    $r->addRoute('GET', '/search-list/recommended', ['searchController', 'getSearchRecommended']);

// 26
    $r->addRoute('GET', '/search-list/type', ['searchController', 'getSearchListType']);
// 27
    $r->addRoute('GET', '/search-list', ['searchController', 'getSearchList']);
// 28.1
    $r->addRoute('GET', '/search-list/tag/{hashTagIndex}', ['searchController', 'getSearchHashTagList']);
// 28.2
    $r->addRoute('GET', '/search-list/location', ['searchController', 'getSearchLocationList']);
// 37
    $r->addRoute('POST', '/story', ['storyController', 'postStory']);
// 38
    $r->addRoute('GET', '/story-other/{userIndex}', ['storyController', 'getStoryAllOther']);
// 38.1
    $r->addRoute('GET', '/story-self', ['storyController', 'getStoryAllSelf']);
// 39
    $r->addRoute('DELETE', '/story/{storyIdx}', ['storyController', 'delStoryIdx']);

// 40
    $r->addRoute('GET', '/story-list', ['storyController', 'getStoryList']);

// 40.1
    $r->addRoute('GET', '/story/viewed/{storyIdx}', ['storyController', 'getStoryViewedList']);
//  41
    $r->addRoute('POST', '/story/{storyIdx}/highlight/{highlightIdx}', ['storyController', 'postStoryToHighlight']);
//  42
    $r->addRoute('GET', '/story/{storyIdx}/highlight-list', ['storyController', 'getHighlightList']);
//   43  /story/highlight
//    $r->addRoute('POST', '/story/highlight', ['storyController', 'postMyPageHighlight']);



    // $r->addRoute('POST', '/test', ['IndexController', 'test']);


//    $r->addRoute('GET', '/test/{testNo}', ['IndexController', 'testDetail']);
//    $r->addRoute('POST', '/test', ['IndexController', 'testPost']);
//    $r->addRoute('GET', '/jwt', ['MainController', 'validateJwt']);
//    $r->addRoute('POST', '/jwt', ['MainController', 'createJwt']);



//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'MainController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/MainController.php';
                break;
            case 'activityController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/activityController.php';
                break;
            case 'dmController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/dmController.php';
                break;
            case 'postController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/postController.php';
                break;
            case 'searchController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/searchController.php';
                break;
            case 'storyController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/storyController.php';
                break;
            case 'userController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/userController.php';
                break;


        }

        break;
}
