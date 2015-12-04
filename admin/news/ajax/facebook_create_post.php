<?php 
require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

/* Require Composer autoloader */
require_once '../../../vendor/autoload.php';

define("PAGE_NAME","Facebook: Create Post");
$app_id=trim($kaImpostazioni->getVar('facebook-config',1));
$app_secret=trim($kaImpostazioni->getVar('facebook-config',2));
$page_id=trim($kaImpostazioni->getVar('facebook-page',1));
$access_token=trim($kaImpostazioni->getVar('facebook-token',1)); // User Access Token
$page_token=trim($kaImpostazioni->getVar('facebook-token',2)); // Page Access Token
$my_url=SITE_URL.ADMINDIR."news/ajax/facebook_create_post.php";
$code=isset($_REQUEST["code"])?$_REQUEST["code"]:'';

/* Get news data to post */
if(isset($_POST['insert'])) {
    $_SESSION['facebook_tmp']=array();
    $_SESSION['facebook_tmp']['idnews']=intval($_GET['id']);
    $_SESSION['facebook_tmp']['content']=$_POST['fb_post_text'];
    $_SESSION['facebook_tmp']['link']=$_POST['link'];
}

/* Initialize FB SDK */
$fb = new Facebook\Facebook([
    'app_id' => $app_id,
    'app_secret' => $app_secret,
    'default_graph_version' => 'v2.5',
    ]);

/* Print page head */
function head(){
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
    <head>
        <title><?php echo ADMIN_NAME." - ".PAGE_NAME; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="author" content="Roberto Pasini - www.kalamun.org" />
        <meta name="copyright" content="no(c)" />
        <style type="text/css">
            @import "<?php echo ADMINDIR; ?>css/screen.css";
            @import "<?php echo ADMINDIR; ?>css/main.lib.css";
        </style>

        <script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
        <script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js"></script>
    </head>

    <body>

    <div id="iPopUpHeader">
        <h1><?= $kaTranslate->translate('News:Create facebook post'); ?></h1>
        <a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
    </div>

    <div style="padding:20px;">
    <?php
}

/* Print page tail and die */
function tail(){
    ?>
    </div>

    </body>
    </html>
    <?php
    exit;
}

/* FB Access Token round-trip (get access_token from auth code) */
function getNewAccessToken($fb) {
    $helper = $fb->getRedirectLoginHelper();
    try {
        $aToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        /* When Graph returns an error */
        echo '<h2>Graph returned an error: ' . $e->getMessage().'</h2>';;
        tail();
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        /* When validation fails or other local issues */
        echo '<h2>Facebook SDK returned an error: ' . $e->getMessage().'</h2>';;
        tail();
    }

    /* The user has not accepted the permission request */
    if (! isset($aToken)) {
        if ($helper->getError()) {
            echo "<h2>Error: " . $helper->getError() .'</h2>';
            echo "<h2>Error Code: " . $helper->getErrorCode() .'</h2>';
            echo "<h2>Error Reason: " . $helper->getErrorReason() .'</h2>';
            echo "<h2>Error Description: " . $helper->getErrorDescription() .'</h2>';
        } else {
            echo '<h2>Bad request</h2>';
        }
        tail();
    }

    /* Exchange a short-lived access token for a long-lived one */
    if (!$aToken->isLongLived()) {
        try {
            $aToken = $fb->getOAuth2Client()->getLongLivedAccessToken($aToken);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<h2>Error getting long-lived access token: " . $helper->getMessage().'</h2>';
            tail();
        }
    }
    return $aToken->getValue();
}

/* Get Page Token unsing Access Token */
function getNewPageToken($fb,$page_id) {
    try {
        $page_token = $fb->get('/'.$page_id.'?fields=access_token')->getGraphNode()['access_token'];
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        echo '<h2>Error getting page token, Graph returned an error: ' . $e->getMessage().'</h2>';
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo '<h2>Error getting page token, Facebook SDK returned an error: ' . $e->getMessage().'</h2>';
        exit;
    }
    return $page_token;
}


/* print head */
head();

/* If recevied a code, exchange it with new access tokens, this takes priority over anything else */
if (!empty($code)) {
    $kaImpostazioni->setParam('facebook-token','',''); //drop old token config from db
    $access_token = getNewAccessToken($fb);
    $fb->setDefaultAccessToken($access_token);
    $page_token = getNewPageToken($fb,$page_id);
    $fb->setDefaultAccessToken($page_token);
    $kaImpostazioni->setParam('facebook-token',$access_token,$page_token); //save token config in db

/* Check with facebook server if page_token from config is valid */
} elseif ($page_token && $fb->getOAuth2Client()->debugToken($page_token)->getIsValid()) {
    $fb->setDefaultAccessToken($page_token);

/* Uhm, page_token is not valid, let's see if we can renew it using access_token */
} elseif ($access_token && $fb->getOAuth2Client()->debugToken($access_token)->getIsValid()) {
    $fb->setDefaultAccessToken($access_token);
    $page_token = getNewPageToken($fb,$page_id);
    $fb->setDefaultAccessToken($page_token);
    $kaImpostazioni->setParam('facebook-token',$access_token,$page_token); //update token config in db

/* Sorry, no token, no party. We need a new authentication code */
} else {
    $kaImpostazioni->setParam('facebook-token','','');  //drop old token config from db
    $permissions = ['manage_pages','publish_pages'];
    $loginUrl = $fb->getRedirectLoginHelper()->getLoginUrl($my_url, $permissions);
    ?>
    <h2>Non hai ancora il permesso di pubblicare su questa pagina facebook</h2>
    <p>Per ottenere il permesso di farlo, clicca sul tasto qui sotto e concedi il permesso</p>
    <p><a class="button" onclick="window.open('<?= $loginUrl; ?>','Windows','width=960,height=700,toolbar=no,menubar=no,scrollbars=no,resizable=no,location=no,directories=no,status=no');window.parent.k_closeIframeWindow();return false;">Richiedi il permesso di pubblicazione</a></p>
    <?php
    tail();
    //header("Location: ".$loginUrl);
}

/* Try to post to FB page */
try {
    $linkData = [
        'link' => $_SESSION['facebook_tmp']['link'],
        'message' => $_SESSION['facebook_tmp']['content'],
    ];
    $response = $fb->post('/'.$page_id.'/feed', $linkData, $page_token);
    unset($_SESSION['facebook_tmp']);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo '<h2>Graph returned an error: ' . $e->getMessage().'</h2>';
    $kaImpostazioni->setParam('facebook-token','','');
    tail();
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo '<h2>Facebook SDK returned an error: ' . $e->getMessage().'</h2>';
    $kaImpostazioni->setParam('facebook-token','','');
    tail();
}

/* Hurrah! */
?>
    <h2><?= $kaTranslate->translate('News:Hurrah, the post was successfully created!'); ?></h2>
    <p><?= $kaTranslate->translate('News:You can view it here'); ?>:<br />
        <a href="https://www.facebook.com/<?= $response->getGraphNode()['id'] ?>" target="_blank">https://www.facebook.com/<?= $response->getGraphNode()['id']; ?></a>
    </p>
<?php
tail();
