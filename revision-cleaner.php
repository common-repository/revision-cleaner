<?php
/**
 * @package revision-cleaner
 */
/*
Plugin Name: Revision Cleaner
Plugin URI: http://wordpress.org/extend/plugins/revision-cleaner/
Description: Auto clean your revisions while you don't needed any more
Version: 2.1.5
Author: Meng Zhuo
Author URI: http://mengzhuo.org
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
//definition goes here...
define('REVISION_CLEANER_VERSION', '2.1.5');
define('REVISION_CLEANER_NAME','revision-cleaner');
//definition end.

load_plugin_textdomain(REVISION_CLEANER_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/po/');

// Make sure we don't expose any info if called directly [FROM AKISMET]
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}
 if (!function_exists('array_implode')){
     /**
     * Implode an array with the key and value pair giving
     * a glue, a separator between pairs and the array
     * to implode.
     * @param string $glue The glue between key and value
     * @param string $separator Separator between pairs
     * @param array $array The array to implode
     * @return string The imploded array
     */
    function array_implode( $glue, $separator, $array ) {
        if ( ! is_array( $array ) ) return $array;
        $string = array();
        foreach ( $array as $key => $val ) {
            if ( is_array( $val ) )
                $val = implode( ',', $val );
            if ($val === TRUE)
                $val = 'TRUE';
            if ($val === FALSE)
                $val = 'FALSE';
            $string[] = "{$key}{$glue}{$val}";
            
        }
        return implode( $separator, $string );
        
    }
}
if (!function_exists('Revis_Cleaner')){
    
    function Revis_get_neo_id(){
        $Revis_Setting = get_option('Revis_Setting');
        
        if (!$Revis_Setting && !isset( $Revis_Setting['neo_id']) ){
            //get admin list for check
            $admin_list_query = new WP_User_Query(array('role'=>'administrator'));
            $admin_list = $admin_list_query->get_results();
            
            if ( !isset($admin_list[0]) ){
                Revis_crazy_type(7,__FUNCTION__);
            }
            
            $Revis_Setting["neo_id"] = $admin_list[0]->ID;
        }
		if ( isset($Revis_Setting["neo_id"]) && $Revis_Setting["neo_id"] > 0   ){
			return $Revis_Setting['neo_id'];
		} 
		else{
			return 1;
		}
        
    }
    
    function Revis_new_user_action($user_id){
        
        $neo_id = Revis_get_neo_id();
		
        $neo_setting = get_user_meta($neo_id,'Revis_Setting');
        if (isset($neo_setting[0])){
            $neo_setting = $neo_setting[0];
        }
        
        if (isset($neo_setting['interval'])  && $neo_setting['interval']  > 3600 ){
            if (update_user_meta( $user_id, 'Revis_Setting',$neo_setting )){
                return TRUE;
            }
        }
        else{
            $neo_setting = array(
                'interval' => 864003,
                'keep_draft_revision' => TRUE
                );
                
            if ($neo_id != $user_id){
                if ( update_user_meta( $neo_id, 'Revis_Setting', $neo_setting) &&
                     update_user_meta( $user_id, 'Revis_Setting', $neo_setting ) ){
                    return TRUE;
                }
            }
            else{
                if ( update_user_meta( $neo_id, 'Revis_Setting', $neo_setting) ){
						return TRUE;
					}
            }
        
        }
    }
    
    /*Oh Plugin go crazed
    * @param Int $code_id
    */
    function Revis_crazy_type($code_id,$addition_msg = FALSE){
        $Revis_Error_ID = array(
            1=>__("You might install an duplicate cleaner",REVISION_CLEANER_NAME),
            2=>__("Cleaned negative numeric, that's impossible",REVISION_CLEANER_NAME),
            3=>__("Is the plugin's code been modified?",REVISION_CLEANER_NAME),
            4=>__("You don't has authority to change this profile",REVISION_CLEANER_NAME),
            5=>__("No Neo is found",REVISION_CLEANER_NAME),
            6=>__("Data is missing",REVISION_CLEANER_NAME),
            7=>__("Oops...No administrator found. Call previous Administrator for help",REVISION_CLEANER_NAME)
        );
        //gathering data for debug
        $version = REVISION_CLEANER_VERSION;
        global $wp_version,$current_user;
        
        $active_plugin = get_option('active_plugins');
        if ($active_plugin[0]){
		    $plugin_path =  dirname( plugin_basename( __FILE__ ) );
		    foreach ($active_plugin as $key => $value)
		    {
		        if ($value!="$plugin_path/revision-cleaner.php"){
		            $keeped_plugin[] = $value;
		        }
		    }
		}
		else{
		    $msg .= __("No plugin found",REVISION_CLEANER_NAME);
		}
		
        
        $msg =  'Revision Cleaner CRAZED!<br/><strong>'
                .$Revis_Error_ID[$code_id].'</strong><br/>'
                .sprintf(__('copy this message and post on %s ',REVISION_CLEANER_NAME),
	                        "<a href='http://wordpress.org/tags/revision-cleaner?forum_id=10#postform'>Wordpress Plugin Forum</a>")
	            .'<br/><br/>-------The Log below for that stupid author--------<br/>'
	            ."Version : WP->$wp_version | RC->$version | MySQl-> <br/> "
	            .'Global Setting : '.array_implode('=>',',',get_option('Revis_Setting'))
	            ."<br/>Current User : roles->".array_implode('=>',',',$current_user->roles)." | level->".$current_user->wp_user_level.'<br/>'
	            .array_implode('=>',',',$current_user->Revis_Setting)
	            .'<br /> Activated plugins : <br/>&nbsp;&nbsp;&nbsp;'.array_implode('=>',',<br/>&nbsp;&nbsp;&nbsp;',$active_plugin);
        // try to fixed this problem
        $self_fix = FALSE;
        if ($code_id == 5){ 
                $msg .= "<br/>I'm trying to fix this ......";
                if (!Revis_check_setting(get_option('Revis_Setting'))){
                    $default_array = array('version'=>REVISION_CLEANER_VERSION,
                                            'neo_id'=>Revis_get_neo_id(),
                                            'multi_user'=>TRUE
                                            );
                    if (update_option('Revis_Setting',$default_array)){
                        $self_fix = TRUE;
                    }
                    else{
                        $msg .= '[ Failed ]<br/>';
                    };
                }
        }
        
        if ($self_fix){
                    
		    $msg .= 'Deactivating Revision cleaner....[';
		
	        if (update_option('active_plugins',$keeped_plugin)){
	            $msg .= 'Completed';
	        }
	        else{
	            $msg .= 'Failed';
	        }
		    $msg .= "]<br/>";
		}
		else{
		    $msg .= '[ Success ]<br/>';
		}
		
		$msg .= $addition_msg;
		
        wp_die($msg,__('Revision Cleaner CRASHED!',REVISION_CLEANER_NAME));
    }
    function Revis_save_user_profile_fields( $user_id ) {
        
        if ( current_user_can( 'edit_user', $user_id )) { 
            
            $user_setting_filters = array(
                'interval' => FILTER_VALIDATE_INT,
                'keep_draft_revision' => FILTER_VALIDATE_BOOLEAN
            );
            $update_setting =  filter_var_array($_POST, $user_setting_filters);
            
            if (current_user_can( 'edit_posts', $user_id )){
                if ( Revis_update_author( $user_id,$update_setting ) ){
                    return TRUE;
                }
            }
        }
    }
    /*
    * This is will clear author setting, use it with cautions
    * @param Int $user_id 
    * @param Array|FALSE $revis_setting_array default FALSE if FALSE, the admin's setting will replace user setting
    * @return Boolean to show whether operation is successes.
    */
    function Revis_update_author($user_id,$revis_setting_array = FALSE){
        if ( current_user_can( 'edit_user', $user_id ) ) {
            
            $neo_id = Revis_get_neo_id();
            
            
            $neo_setting = get_user_meta($neo_id, 'Revis_Setting');
            $neo_setting = $neo_setting[0];
            
            if ($neo_setting == NULL){
                //set to default is NULL, It maybe eaten by Mon
                $admin_setting = array(
                'interval' => 864001,
                'keep_draft_revision' => TRUE
                );
                update_user_meta($neo_id, 'Revis_Setting', $admin_setting);
            }
            
            $user_setting = get_user_meta($user_id, 'Revis_Setting');
            $user_setting = $user_setting[0];
            
            if ($user_setting == NULL ){
                //set to default is NULL, It maybe eaten by Mon
                update_user_meta($user_id,'Revis_Setting',$admin_setting);
            }
            
            $setting_to_set = ($revis_setting_array)?$revis_setting_array:$admin_setting;
           
            
            if (is_array($setting_to_set)){
            
                if (update_user_meta( $user_id, 'Revis_Setting', $setting_to_set)){
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    /*
    * 
    **/
    function Revis_execute( $user_id , $setting_array,$multi_user = TRUE) {
         global $wpdb;
         /*
         * will extract there variables.
         * 'interval' => FILTER_VALIDATE_INT,
         * 'keep_draft_revision' => FILTER_VALIDATE_BOOLEAN
         */
         if (!$setting_array){
         
            Revis_new_user_action($user_id);
            $setting_array = get_user_meta($user_id,'Revis_Setting');
            $setting_array = $setting_array[0];
         }

         extract($setting_array);
         //get Interval
         $current_gmt = time();
         $time_between = date("\"Y-m-d H:i:s\"",$current_gmt-$interval);
         
         $multi_user_query = ($multi_user)?"AND `post_author` = $user_id":NULL;
         
         //keep draft work around for mysql 4.* due to it can't delete with child query
         //FIXME fix this inefficient query
         $keep_draft_revision_query = NULL;
         
         if ($keep_draft_revision==1){
            
            $drafts_id = $wpdb->get_results("
                SELECT `ID` FROM $wpdb->posts
                WHERE `post_status` = 'draft'
                $multi_user_query
            ",ARRAY_N);
            
            if (isset($drafts_id[0])){
            
                foreach ($drafts_id as $item)
                {
                    $drafts_id_list[] = $item[0];
                }
                
                $drafts_id_list = implode(',',$drafts_id_list);
                $keep_draft_revision_query = "AND `post_parent` NOT IN (".$drafts_id_list.")";
            }
         }

         $clean_query = "
                DELETE FROM $wpdb->posts
                WHERE `post_type` = 'revision'
                $keep_draft_revision_query
                $multi_user_query
                AND `post_date_gmt` < $time_between
                ";
         $clean_time = $wpdb->query($clean_query);
         
         
         return $clean_time;
    }
    
	function Revis_check_setting($global_setting){
		if (is_array($global_setting)){
			extract($global_setting);
			if (isset($neo_id) && $neo_id > 0 ){
				if (isset($version) && $version == REVISION_CLEANER_VERSION){
				    return TRUE;
				}
			}
		}
		return FALSE;
	}
	
    function Revis_Cleaner() {
    
    //Initializing...
    $global_setting = get_option('Revis_Setting');
            
        if ( $global_setting && Revis_check_setting($global_setting ) ){

            global $wpdb,$current_user;
            

            //Pull setting            
            extract($global_setting);
            
            
            $user_setting = get_user_meta($current_user->data->ID, 'Revis_Setting');
            if (!isset($user_setting[0]['interval'])){
                
                Revis_new_user_action($current_user->data->ID);
                $user_setting = get_user_meta($current_user->data->ID, 'Revis_Setting');

            }
            $user_setting = $user_setting[0];
            

            
            if ($multi_user){
                $clean_time = Revis_execute($current_user->data->ID,$user_setting);
            }
            else{
                $neo_setting = get_user_meta($neo_id,'Revis_Setting');
                $neo_setting = $neo_setting[0];
                
                $clean_time = Revis_execute($neo_id,$neo_setting,FALSE);
            }
            
            //update clean counter
            $revis_cleaned =  get_option('Revis_Cleaned');
            if ($clean_time >= 0){
                $revis_cleaned = $revis_cleaned+$clean_time;
                update_option('Revis_Cleaned',$revis_cleaned);
                return TRUE;
            }
            Revis_crazy_type(2);
        }
        else{
            //Howdy new user! Initiating Data structure. 
            $neo_id = Revis_get_neo_id();
            $initial_array = array(
                    'multi_user' => TRUE,
                    'version' => REVISION_CLEANER_VERSION,
                    'neo_id' => $neo_id
                    );
		    delete_option('Revis_Setting');
			delete_option('Revis_Cleaned');
            add_option('Revis_Setting',$initial_array,'','no');
            add_option('Revis_Cleaned',0,'','no');
            return TRUE;
        }
        Revis_crazy_type(3,__FUNCTION__);
    }
    function Revis_Cleaner_callback(){
        $Revis_Cleaned = get_option('Revis_Cleaned');
        $Revis_Setting = get_option('Revis_Setting');
        
        global $current_user;

        //get admin list for check
        $admin_list_query = new WP_User_Query(array('role'=>'administrator'));
        $admin_list = $admin_list_query->get_results();

        foreach ($admin_list as $admin)
        {
            $admin_id_list[$admin->ID] = $admin->user_login;
        }
        
        
        //check Neo hum.. It's not economical.
        if (!isset($Revis_Setting["neo_id"])){
            $fist_admin = $admin_id_list ;
            $first_admin_id = array_keys(array_splice($fist_admin,0,1));
            $Revis_Setting["neo_id"] = $first_admin_id[0];
        }
        
        
        if (isset($_POST['submit']) && current_user_can('administrator')){
            $message = __('Success!');
            $msg_class = 'updated';
            
            $update_setting_filters = array(
                'multi_user'=>FILTER_VALIDATE_BOOLEAN,
                'neo_id' => FILTER_VALIDATE_INT
                );
            
            $update_setting = filter_var_array( $_POST, $update_setting_filters);
            
            if ($update_setting != $Revis_Setting){
                if (!isset($update_setting['neo_id'])){
                    $update_setting['neo_id'] = Revis_get_neo_id();
                }
                
                if (isset($admin_id_list[$update_setting['neo_id']])){
                
                    if(!update_option('Revis_Setting',$update_setting)){
                        $message = __('Save failed');
                        $msg_class = 'error';
                        
                    }
                    $Revis_Setting = $update_setting;
                }
                else{
                    $message = sprintf(__("USER-ID:%s can't be Neo"),$update_setting['neo_id']);
                    $msg_class = 'error';
                }
            }

            
            echo "<div class='$msg_class' id='revision_message'><strong><p>". $message . "</p></strong></div>"; 
        }
        
        extract($Revis_Setting,EXTR_SKIP);

        
        if (!$multi_user && current_user_can('administrator') && isset($_POST['interval']) && $_POST['interval'] != NULL){
            $admin_setting_filters = array(
                'interval' => FILTER_VALIDATE_INT,
                'keep_draft_revision' => FILTER_VALIDATE_BOOLEAN
                );
            $admin_setting =   filter_var_array( $_POST, $admin_setting_filters);
            $neo_setting = get_user_meta( $neo_id,'Revis_Setting' );
            $neo_setting = $neo_setting[0];
            
            if ($admin_setting != $neo_setting){
                update_user_meta( $neo_id, 'Revis_Setting', $admin_setting);
            }
        }
        
        //statistic
        global $wpdb;
        $Revis_revisions_now = $wpdb->get_var( $wpdb->prepare("
            SELECT COUNT(*) FROM %s
            WHERE `post_type` = 'revision'
        ", $wpdb->posts));
        
        
        
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br></div>
            <h2><?php _e('Revision Cleaner',REVISION_CLEANER_NAME); ?></h2>
            <form method='post' action=''>
            <?php if (!$multi_user && current_user_can('administrator')){
                    
                    //well they don't like everyone has their interval
                    if ($admin_list[0]->ID){
                        foreach ($admin_list as $key => $value)
                        {
                            if ($value->ID == $neo_id){
                                Revis_profile_fields($admin_list[$key]);
                                break;
                            }
                        }
                    }
                }
            ?>
            <h3><?php _e('Global Settings',REVISION_CLEANER_NAME);?></h3>
                <table class="form-table">
                    <tbody>
                    <tr><th><?php _e('Multi-Users',REVISION_CLEANER_NAME);?></th>
                    <td>
                    <label for="revis_multiuser"><input type="checkbox" name="multi_user" id="revis_multiuser" <?php checked($Revis_Setting['multi_user'], TRUE) ?> /><?php _e("Allow each user set their own interval",REVISION_CLEANER_NAME);?></label>
                    <?php
                    if ($multi_user && current_user_can('administrator')){
                    $blog_admin_profile_url = site_url().'/wp-admin/profile.php#revis_advanced';
                    echo '<p>';
                    printf(__("As Multi-users on, You can set your interval at <a href='%s'>your profile</a>",REVISION_CLEANER_NAME), $blog_admin_profile_url);
                    echo '</p>';} ?>
                    </td></tr>
                    <tr><th><?php _e('Neo',REVISION_CLEANER_NAME);?></th>
                    <td><select name="neo_id" <?php if($multi_user){echo "disabled='disabled'";}?> >
                        <?php foreach ($admin_id_list as $id => $login_name){
                            if ($neo_id <= 0){
                                    Revis_crazy_type(7,__FUNCTION__);
                            }
                            $select = ($id == $neo_id)?"selected='selected'":NULL;
                            printf("<option value='%s' %s >%s</option>",$id,$select,$login_name);
                       } ?>
                    </select>
                    <p><span class="description">
                    <?php _e("Neo's setting will be treat as Global Setting while Multi-user is off ",REVISION_CLEANER_NAME)?>
                    </span></p></td>
                    </tr>
                    </tbody>
                </table>
           <h3><?php _e('Statistic',REVISION_CLEANER_NAME);?></h3>
           <table class="form-table">
                    <tbody>
           <tr valign="top"><th scope="row"><?php _e('Extant Revision',REVISION_CLEANER_NAME);?></th><td><?php echo $Revis_revisions_now;?></td></tr>
           <tr valign="top"><th scope="row"><?php _e('Cleaned Revision',REVISION_CLEANER_NAME);?></th><td><?php echo $Revis_Cleaned;?></td></tr>
           </tbody>
          </table>
          <h3 id='revis_users_overview'>
        <?php _e('Users Setting Overview',REVISION_CLEANER_NAME);?>
            <a>▼</a><a class="hidden">▲</a>
          </h3>
        <div id="revis_users_overview_area" >
        <table class="wp-list-table widefat fixed">
        <thead><tr>
               <th class="manage-column column-username"><?php _e('Username');?></th>
               <th class="manage-column num" ><?php _e('Extant Revision',REVISION_CLEANER_NAME);?></th>
               <th class="manage-column num column-posts"><?php _e('Interval',REVISION_CLEANER_NAME);?></th>
               <th class="manage-column column-role"><?php _e("Keep draft's",REVISION_CLEANER_NAME);?></th>
        </tr></thead><tbody>
        <?php 
        /*FIXME I don't know why such code below malfunction
        * new WP_User_Query( array( 'orderby'=>'id','exclude'=>array('role'=>'subscriber') ))
        */
        $wp_user_search = new WP_User_Query( array( 'orderby'=>'id') );
        $all_user = $wp_user_search->get_results();
        $index = 0;
        foreach ($all_user as $user){
            
            $setting = get_user_meta($user->ID,'Revis_Setting');
            if (!$setting){
                Revis_new_user_action($user->ID);
                $setting = get_user_meta($user->ID,'Revis_Setting');
            }
            $setting = $setting[0];
            
            if ($setting['interval']){
                $even_odd = (($index++)%2 ==0)?'alternate':NULL;
                $user_id = $user->ID;
                $author_revisions_now = $wpdb->get_var( $wpdb->prepare("
                    SELECT COUNT(*) FROM %s
                    WHERE `post_type` = 'revision'
                    AND `post_author` = %d
                ", $wpdb->posts, $user_id));
                
                printf('<tr class="%s">
                <td class="manage-column" >%s</td>
                <td class="manage-column num column-posts" >%d</td>
                <td class="manage-column num column-posts" >%d</td>
                <td><input type="checkbox"  %s disabled="disabled" class="manage-column"/></td>
                </tr>',
                $even_odd,
                $user->user_login,
                $author_revisions_now,
                $setting['interval'],
                checked($setting['keep_draft_revision'], TRUE,FALSE)
                );
            }
        }
        ?>
            </tbody>
        </table>
        </div>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save Changes'); ?>"></p>
            </form>
        </div>
    <?php
    }
    function Revis_Cleaner_menu(){
        add_options_page( __('Revision Cleaner',REVISION_CLEANER_NAME), __('Revision Cleaner',REVISION_CLEANER_NAME), 'manage_options', 'revision-cleaner-control-menu', 'Revis_Cleaner_callback');
    }
    function Revis_js(){ 
        if (current_user_can('administrator')){
        $admin_list_query = new WP_User_Query(array('role'=>'administrator'));
        $admin_list = $admin_list_query->get_results();
    
        foreach ($admin_list as $admin)
        {
            $admin_setting = get_user_meta($admin->ID,'Revis_Setting');
            if ($admin_setting == NULL ||  $admin_setting[0]['interval'] < 3600 ){

                $neo_setting = array(
                'interval' => 3600,
                'keep_draft_revision' => TRUE
                );
                $admin_setting[0] = $neo_setting;
                
            }
            $admin_set_array[$admin->ID]['id'] = $admin->ID;
            $admin_set_array[$admin->ID]['setting'] = $admin_setting[0];
        }
        if(function_exists('json_encode')){
            printf('<script>var Neos = %s</script>',json_encode($admin_set_array));
        }
    }
    else{
        printf('<script>var Neos = false</script>');
    }
    //END OF JS
    
    ?>
<script type="text/javascript">
/*Revision Cleaner*/
function reviso_interval_change(){
        revis_interval = (jQuery("#revis_interval").val()==NaN)?0:jQuery("#revis_interval").val();
        revis_d = Math.round(revis_interval/86400);
        if (revis_interval-(revis_d*86400)>0){
            revis_h = Math.round((revis_interval-(revis_d*86400))/3600);
        }
        else{
            revis_h = 0;
        }
        jQuery("#revis_d").val(revis_d);
        jQuery("#revis_h").val(revis_h);
}
function reviso_neo(_this){

    if(Neos != false){
         var neo = Neos[_this.children(':selected').val()]
         jQuery("#revis_interval").val(neo.setting['interval'])
         reviso_interval_change();
         var check = '';
         if (neo.setting['keep_draft_revision'] == 1){
            var check = 'checked';
         }
         jQuery("input[name='keep_draft_revision']").attr('checked',check)
    }
}
jQuery(document).ready(function(){


    jQuery("#revis_advanced_area,#revis_no_js,#revis_users_overview_area").hide(0);
    reviso_interval_change();
    jQuery('#revis_advanced,#revis_users_overview').click(function(){

        jQuery('#'+jQuery(this).attr('id')+"_area").slideToggle('slow');
        jQuery(this).children('a').toggle()
    })
    jQuery("#revis_d,#revis_h").change(function(){
        jQuery('#revis_interval').val(jQuery("#revis_d").val()*86400+jQuery("#revis_h").val()*3600);
    })
    jQuery('#revis_interval').change(function(){
        if (jQuery(this).val() < 3600 || jQuery(this).val() ==""){
            jQuery(this).val(3600)
        }
        else if (jQuery(this).val() > 730*24*3600){
            jQuery(this).val(730*24*3600)
        }
        reviso_interval_change();
    })
    if (Neos != false && !(jQuery('select[name="neo_id"]').children(':selected').val() === undefined)){
        reviso_neo(jQuery('select[name="neo_id"]'));
    
    
        jQuery('select[name="neo_id"]').change(function(){
        reviso_neo(jQuery(this))
        });
    }
    
});
</script>
    <?php 
    }
    function Revis_profile_fields($user){
    if (current_user_can( 'edit_posts', $user_id )){
    $setting = get_user_meta($user->ID,'Revis_Setting');
    $setting = $setting[0];
    if(!$setting['interval']){
        Revis_new_user_action($user->ID);
        $setting =  get_user_meta($user->ID,'Revis_Setting');
        $setting = $setting[0];
    }
    extract($setting);
    ?>
      <h3><?php _e('Revision Cleaner',REVISION_CLEANER_NAME);?></h3>
      <div id="revis_no_js"><?php _e("Error:No Javascript support, can't show simple setting");?></div>
                <table class="form-table">
                    <tbody>
                    <tr><th><?php _e('Time Settings',REVISION_CLEANER_NAME);?></th><td><p><?php _e('Revisions Last',REVISION_CLEANER_NAME);?>
                    <input id='revis_d' type='text' class="small-text" /><?php _e('day',REVISION_CLEANER_NAME);?>
                    <input id='revis_h' type='text' class="small-text" /><?php _e('hour',REVISION_CLEANER_NAME);?></p></td></tr>
                    
                    <tr><th id='revis_advanced'><?php _e('Advanced Options',REVISION_CLEANER_NAME);?> 
                        <a>▼</a><a class="hidden">▲</a></th>
                    <td></td>
                    </tr>
                    </tbody>
                </table>
          <div id="revis_advanced_area" style="background:#f1f1f1;border:1px solid #ccc;
          -webkit-box-shadow: inset 0 2px 2px #ddd;
          -moz-box-shadow: inset 0 2px 2px #ddd;
          box-shadow: inset 0 2px 2px #ddd;">
                <table class="form-table" >
                    <tbody >
                    <tr valign="top">
                    <th scope="row"><?php _e('Time Settings',REVISION_CLEANER_NAME);?></th>
                    <td><label for="revis_interval">
                        <?php _e('Revisions Last',REVISION_CLEANER_NAME);?><input name="interval" type="text" id="revis_interval" 
                    value="<?php echo $interval; ?>" class="big-text"><?php _e('Seconds',REVISION_CLEANER_NAME); ?></label>
                    </td>
                    </tr>
                    <tr><th><?php _e("Misc",REVISION_CLEANER_NAME);?></th><td>
                    <label for="keep_draft_revision"><input type="checkbox" name="keep_draft_revision" id="keep_draft_revision" <?php checked($keep_draft_revision, TRUE) ?> /><?php _e("Always keep draft's revisions",REVISION_CLEANER_NAME);?></label>
                    </td></tr>
                    </tbody>
                </table>
           </div>
    
    <?php 
        }
    
    }
    

    
    add_action('admin_menu','Revis_Cleaner');
    add_action('admin_menu','Revis_Cleaner_menu');
    add_action('user_register','Revis_new_user_action');
    add_action('admin_footer','Revis_js');
    
    $Revis_Setting = get_option('Revis_Setting');
    if ( $Revis_Setting['multi_user']){
        add_action('show_user_profile', 'Revis_profile_fields');
        add_action('edit_user_profile', 'Revis_profile_fields');
        add_action('personal_options_update', 'Revis_save_user_profile_fields' );
        add_action('edit_user_profile_update', 'Revis_save_user_profile_fields' );
    }

}
else{ Revis_crazy_type(1);}
?>
