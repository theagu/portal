<?php
/* Orignally written by Andrew Niemantsverdriet 
 * email: andrewniemants@gmail.com
 * website: http://www.rimrockhosting.com
 *
 * This code is on github: https://github.com/kaptk2/portal
 *
 * Copyright (c) 2012, Andrew Niemantsverdriet
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met: 
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer. 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution. 
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies, 
 * either expressed or implied, of the FreeBSD Project.
 */

// Start the session to get access to the saved variables
session_start();

 // Inclued the config file
require_once("config.php");

function sendAuthorization($id, $minutes)
{
  global $unifiServer;
  global $unifiUser;
  global $unifiPass;

  // Start Curl for login
  $ch = curl_init();
  // We are posting data
  curl_setopt($ch, CURLOPT_POST, TRUE);
  // Set up cookies
  $cookie_file = "/tmp/unifi_cookie";
  curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
  // Allow Self Signed Certs
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
  // Force SSL3 only
  curl_setopt($ch, CURLOPT_SSLVERSION, 3);
  // Login to the UniFi controller
  curl_setopt($ch, CURLOPT_URL, "$unifiServer/login");
  curl_setopt($ch, CURLOPT_POSTFIELDS,
            "login=login&username=$unifiUser&password=$unifiPass");
  curl_exec ($ch);
  curl_close ($ch);

  // Send user to authorize and the time allowed
  $data = json_encode(array(
          'cmd'=>'authorize-guest',
          'mac'=>$id,
          'minutes'=>$minutes));

  $ch = curl_init();
  // We are posting data
  curl_setopt($ch, CURLOPT_POST, TRUE);
  // Set up cookies
  $cookie_file = "/tmp/unifi_cookie";
  curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
  // Allow Self Signed Certs
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
  // Force SSL3 only
  curl_setopt($ch, CURLOPT_SSLVERSION, 3);
  // Make the API Call
  curl_setopt($ch, CURLOPT_URL, $unifiServer.'/api/cmd/stamgr');
  curl_setopt($ch, CURLOPT_POSTFIELDS, 'json='.$data);
  curl_exec ($ch);
  curl_close ($ch);
  
  // Logout of the connection
  $ch = curl_init();
  // We are posting data
  curl_setopt($ch, CURLOPT_POST, TRUE);
  // Set up cookies
  $cookie_file = "/tmp/unifi_cookie";
  curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
  // Allow Self Signed Certs
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
  // Force SSL3 only
  curl_setopt($ch, CURLOPT_SSLVERSION, 3);
  // Make the API Call
  curl_setopt($ch, CURLOPT_URL, $unifiServer.'/logout');
  curl_exec ($ch);
  curl_close ($ch);
  echo "Login successful, go to <a href='".$_SESSION['url']"'>$_SESSION['url']</a>";
  //sleep(8); // Small sleep to allow controller time to authorize
  //header('Location: '.$_SESSION['url']);
}

if ($_POST) // Check to see if the form has been posted to
{
  // If the form has been posted allow them through.
  sendAuthorization($_SESSION['id'], '480');
} else {
  echo "A valid username or password was not found.";
}
?>
