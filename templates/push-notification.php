
<div class="wrap">
    <h2>Send Push Notification</h2>
<?php
        $status = '';

      $sDir = dirname(__FILE__);
      $sDir = rtrim($sDir, '/');
      // $sDir = str_replace('/mstoreapp-mobile-app/admin','',$sDir); // myplugin was folder name of current plugin
      $sDir = rtrim($sDir, '/');
    


        if (isset($_REQUEST['save_all'])) {
            update_option('url_sendPush', trim(strip_tags($_REQUEST['url_sendPush'])));
            $someFields = [];
            $someFields['title'] = trim(strip_tags($_REQUEST['title']));
            $someFields['body'] = trim(strip_tags($_REQUEST['body']));
            update_option('listapp_push', $someFields);
        }

        if (isset($_REQUEST['push_all'])) {

            $values = array();

            if(isset($_REQUEST['title'])){
                $values['title'] = trim(strip_tags($_REQUEST['title']));
            }else {
                $values['title'] = '';
            }

            if(isset($_REQUEST['body'])){
                $values['body'] = trim(strip_tags($_REQUEST['body']));
            }else {
                $values['body'] = '';
            }

            // if(isset($_REQUEST['filter'])){
            //     $values['filter'] = trim(strip_tags($_REQUEST['filter']));
            // }else {
            //     $values['filter'] = '';
            // }

            // if(isset($_REQUEST['option'])){
            //     $values['option'] = trim(strip_tags($_REQUEST['option']));
            // }else {
            //     $values['option'] = '';
            // }

            // if(isset($_REQUEST['isAndroid']) && $values['isAndroid'] == 1){
            //     $values['isAndroid'] = true;
            // }else {
            //     $values['isAndroid'] = false;
            // }

            // if(isset($_REQUEST['isIos']) && $values['isIos'] == 1){
            //     $values['isIos'] = true;
            // }else {
            //     $values['isIos'] = false;
            // }
            
            // $values['isIos'] = trim(strip_tags($_REQUEST['isIos']));
            //$values['url'] = trim(strip_tags($_REQUEST['url']));
           // $fields['api_key'] = get_option('authorization_key');
            update_option('listapp_push', $values );

            $fields = array();

            // if($values['option'] == "email"){
            //     $fields['filters'] = array(array("field" => "tag", "key" => "email", "relation" => "=", "value" => $values['filter']));
            // }
            // if($values['option'] == "pincode"){
            //     $fields['filters'] = array(array("field" => "tag", "key" => "pincode", "relation" => "=", "value" => $values['filter']));
            // }
            // if($values['option'] == "city"){
            //     $fields['filters'] = array(array("field" => "tag", "key" => "city", "relation" => "=", "value" => $values['filter']));
            // }
            // if($values['option'] == "state"){
            //     $fields['filters'] = array(array("field" => "tag", "key" => "state", "relation" => "=", "value" => $values['filter']));
            // }
            // if($values['option'] == "country"){
            //     $fields['filters'] = array(array("field" => "tag", "key" => "country", "relation" => "=", "value" => $values['filter']));
            // }
            // if($values['option'] == "topic"){
            //     $fields['filters'] = array(array("field" => "tag", "key" => "topic", "relation" => "=", "value" => $values['filter']));
            // }



            // $fields['included_segments'] = array("All");

            $fields['title'] = trim(strip_tags($_REQUEST['title']));
            $fields['body'] = trim(strip_tags($_REQUEST['body']));

            // if($values['isAndroid'] == 1)
            // $fields['isAndroid'] = true;
            // else $fields['isAndroid'] = false;
            // if($values['isIos'] == 1)
            // $fields['isIos'] = true;
            // else $fields['isIos'] = false;

            // $fields['isAnyWeb'] = false;
            // $fields['isWP'] = false;
            // $fields['isAdm'] = false;
            // $fields['isChrome'] = false;
            //$fields['data'] = array(
              //  "myappurl" => $fields['url']
            //);

           // unset($fields['url']);
            /* Send another notification via cURL */
            $ch = curl_init();
            $listapp_post_url = trim(strip_tags($_REQUEST['url_sendPush']));
            /* Hopefully OneSignal::get_onesignal_settings(); can be called outside of the plugin */
            // $onesignal_wp_settings = OneSignal::get_onesignal_settings();
            // $onesignal_auth_key = $onesignal_wp_settings['app_rest_api_key'];
            // $fields['app_id'] = $onesignal_wp_settings['app_id'];

            curl_setopt($ch, CURLOPT_URL, $listapp_post_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                // 'Authorization: Basic ' . $onesignal_auth_key
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // Optional: Turn off host verification if SSL errors for local testing
            // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            /* Optional: cURL settings to help log cURL output response
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
            curl_setopt($ch, CURLOPT_HTTP200ALIASES, array(400));
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_STDERR, $out);
            */
            $response = curl_exec($ch);
            
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = json_decode(substr($response, $header_size), true);

            if(isset($body['ok']))
            $status = 'success';
            if($body['errors'][0])
            $status = 'errors';

            curl_close($ch);


        }









        ?>

<p>Please enter title and message to send all registred devices.</p>

    <?php if($status == 'success'){ ?>
        <div class="notice notice-success is-dismissible"> 
        <p><strong>Notification Sent. </strong></p>
            <!-- Total Recipients <?php //echo $body['recipients'] ?></strong></p> -->
        <!-- <button type="button" class="notice-dismiss">
            <span class="screen-reader-text">Dismiss this notice.</span>
        </button> -->
    </div>
    <?php } if($status == 'errors'){ ?>
            <div class="notice notice-error is-dismissible"> 
        <p><strong><?php echo $body['errors'][0] ?></strong></p>
        <button type="button" class="notice-dismiss">
            <span class="screen-reader-text">Dismiss this notice.</span>
        </button>
    </div>
    <?php } ?>

<?php
     $options = get_option( 'listapp_push' ); 
     $urlSendPush = get_option('url_sendPush');
?>
        <form action="" method="post">


            <table class="form-table">
                <tr>
                    <th style="width:100px;"><label for="title">FireBase Url</label></th>
                    <td ><input class="regular-text" type="text" id="url_sendPush" name="url_sendPush"  value="<?php echo $urlSendPush ?>" ></td>
                </tr>

                <tr>
                    <th style="width:100px;"><label for="title">Title</label></th>
                    <td ><input class="regular-text" type="text" id="title" name="title"  value="<?php echo $options['title'] ?>" ></td>
                </tr>
                <tr>
                    <th style="width:100px;"><label for="body">Message</label></th>
                    <td ><textarea class="regular-text" rows="15" col="15" type="text" id="body" name="body"><?php echo $options['body']; ?></textarea></td>
                </tr>


            </table>
            <p class="submit">
                <input type="submit" name="save_all" value="Save" class="button-primary" />
                <input type="submit" name="push_all" value="Send Now" class="button-primary" />

            </p>
                
        </form>
      
</div>