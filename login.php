<?php include 'controllers/authController.php' ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/css/bootstrap.min.css" />
  <link rel="stylesheet" href="main.css">
  <title>User verification system PHP - Login</title>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-md-4 offset-md-4 form-wrapper auth login">
        <h3 class="text-center form-title">Login</h3>
        <!-- form title -->

<?php if (count($errors) > 0): ?>
  <div class="alert alert-danger">
    <?php foreach ($errors as $error): ?>
    <li>
      <?php echo $error; ?>
    </li>
    <?php endforeach;?>
  </div>
<?php endif;?>
        <form action="login.php" method="post">
          <div class="form-group">
            <label>Username or Email</label>
            <input type="text" name="username" class="form-control form-control-lg" value="<?php echo $username; ?>">
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control form-control-lg">
          </div>
          <div class="form-group">
            <button type="submit" name="login-btn" class="btn btn-lg btn-block">Login</button>
          </div>
        </form>

        <?php
// Include configuration file
require_once 'config.php';

// Include User class
require_once 'User.class.php';

if(isset($accessToken)){
    if(isset($_SESSION['facebook_access_token'])){
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }else{
        // Put short-lived access token in session
        $_SESSION['facebook_access_token'] = (string) $accessToken;
        
          // OAuth 2.0 client handler helps to manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();
        
        // Exchanges a short-lived access token for a long-lived one
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
        $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
           // Set default access token to be used in script
           $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
        }
        
        // Redirect the user back to the same page if url has "code" parameter in query string
        if(isset($_GET['code'])){
            header('Location: ./');
        }
        
        // Getting user's profile info from Facebook
        try {
            $graphResponse = $fb->get('/me?fields=name,first_name,last_name,email,link,gender,picture');
            $fbUser = $graphResponse->getGraphUser();
        } catch(FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            session_destroy();
            // Redirect user back to app login page
            header("Location: ./");
            exit;
        } catch(FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

          
    // Initialize User class
    $user = new User();
    
    // Getting user's profile data
    $fbUserData = array();
    $fbUserData['oauth_uid']  = !empty($fbUser['id'])?$fbUser['id']:'';
    $fbUserData['first_name'] = !empty($fbUser['first_name'])?$fbUser['first_name']:'';
    $fbUserData['last_name']  = !empty($fbUser['last_name'])?$fbUser['last_name']:'';
    $fbUserData['email']      = !empty($fbUser['email'])?$fbUser['email']:'';
    $fbUserData['gender']     = !empty($fbUser['gender'])?$fbUser['gender']:'';
    $fbUserData['picture']    = !empty($fbUser['picture']['url'])?$fbUser['picture']['url']:'';
    $fbUserData['link']       = !empty($fbUser['link'])?$fbUser['link']:'';
    
    // Insert or update user data to the database
    $fbUserData['oauth_provider'] = 'facebook';
    $userData = $user->checkUser($fbUserData);
    
    // Storing user data in the session
    $_SESSION['userData'] = $userData;
    
    // Get logout url
    $logoutURL = $helper->getLogoutUrl($accessToken, FB_REDIRECT_URL.'logout.php');

     
    // Render Facebook profile data
    if(!empty($userData)){
        $output  = '<h2>Facebook Profile Details</h2>';
        $output .= '<div class="ac-data">';
        $output .= '<img src="'.$userData['picture'].'"/>';
        $output .= '<p><b>Facebook ID:</b> '.$userData['oauth_uid'].'</p>';
        $output .= '<p><b>Name:</b> '.$userData['first_name'].' '.$userData['last_name'].'</p>';
        $output .= '<p><b>Email:</b> '.$userData['email'].'</p>';
        $output .= '<p><b>Gender:</b> '.$userData['gender'].'</p>';
        $output .= '<p><b>Logged in with:</b> Facebook</p>';
        $output .= '<p><b>Profile Link:</b> <a href="'.$userData['link'].'" target="_blank">Click to visit Facebook page</a></p>';
        $output .= '<p><b>Logout from <a href="'.$logoutURL.'">Facebook</a></p>';
        $output .= '</div>';
    }else{
        $output = '<h3 style="color:red">Some problem occurred, please try again.</h3>';
    }
}else{
    // Get login url
    $permissions = ['email']; // Optional permissions
    $loginURL = $helper->getLoginUrl(FB_REDIRECT_URL, $permissions);
    
    // Render Facebook login button
    $output = '<a href="'.htmlspecialchars($loginURL).'">  <button type="submit" class="btn btn-lg btn-block" style="background-color:#3b5998">Cotinue with FaceBook</button></a>';
}
?>



<!DOCTYPE html>
<html lang="en-US">
<head>
<title>Login with Facebook using PHP by CodexWorld</title>
<meta charset="utf-8">
</head>
<body>
<div class="container">
    <div class="fb-box">
        <!-- Display login button / Facebook profile information -->
        <?php echo $output; ?>
    </div>
</div>
</body>
</html>
<br>
        <p>Don't yet have an account? <a href="signup.php">Sign up</a></p>
      </div>
    </div>
  </div>
</body>