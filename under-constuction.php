<?php
/*
Plugin Name: Under Construction for specific pages
Description: A simple plugin that allows you to set Under Construction for specific pages.
Version: 1.1.2
Author: Chirag
Author URI: https://chiragjdsofttech.blogspot.com/
License: GPL2
*/

/**
 * DEFINE PATHS
 */
define( 'UNDERCONSTRUCTIONPATH', plugin_dir_path( __FILE__ ) );


/**
 * FUNCTIONS
 */


//activate plugin and create table
register_activation_hook( __FILE__, 'underConstructionDbCreate');
function underConstructionDbCreate() {
  global $wpdb;
  global $table_name;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'underConstruct';
  $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
  `ucid` int(11) NOT NULL AUTO_INCREMENT,
  `pageid` varchar(220) DEFAULT NULL,
  `datetime` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `startdatetime` DATETIME DEFAULT NULL,
  `enddatetime` DATETIME DEFAULT NULL,
  `status` int(1) DEFAULT 1,
  `uc_text` TEXT DEFAULT NULL,
  PRIMARY KEY(ucid)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ";
  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
}

add_action("admin_menu", "underConstructionAdminMenu");

function underConstructionAdminMenu() {
    add_menu_page("Under Construction", "Under Construction", 0, "under-construction", "underConstructionSettings");
}

require_once(UNDERCONSTRUCTIONPATH.'/ajax_action.php');

function underConstructionSettings(){  
	global $wpdb;
	$table_name = $wpdb->prefix . 'underConstruct';
	
	echo "<h1>Select the page for set under maintenance</h1>";
 
  $sampletext = '<article>
                    <h1>We’ll be back soon!</h1>
                    <div>
                        <p>Sorry for the inconvenience but we’re performing some maintenance at the moment. If you need to you can always <a href="mailto:#">contact us</a>, otherwise we’ll be back online shortly!</p>
                        <p>— The Team</p>
                    </div>
                </article>';
	?>
  <hr>

<!-- Button trigger modal -->
<button type="button" id="openModalPopup" name="openModalPopup" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#insertDataModal">
  Add Page For UnderConstruction
</button>

<!-- Modal -->
<div class="modal fade" id="insertDataModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <?php 
      // insert data
      global $wpdb;
        $table_name = $wpdb->prefix . 'underConstruct';
        //save new data
        if (isset($_POST['savedata'])) {
            $pageid = sanitize_text_field($_POST['page_id']);
            $editorname = sanitize_text_field($_POST['editorname']);
            if (!empty($pageid)) {
              if ($wpdb->query(
                   $wpdb->prepare(
                      "INSERT INTO $table_name
                      ( pageid, uc_text )
                      VALUES ( %d, %s)",
                      $pageid,
                      $editorname
                   )
                  )) {
                $result='<div class="notice notice-success is-dismissible"><p>UnderConstruction Page Is Set!</p></div>';
              } else {
                $result='<div class="notice notice-error"><p>Sorry there was an error setting page! Please try again later..</p></div>';
              }
            }
            echo("<meta http-equiv='refresh' content='1'>"); //Refresh by HTTP 'meta'
        }
      //end

  ?>
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Add page for under construction</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div id="msg"><span class="wqsubmit_message"></span></div>
      <form name="setUnderconstruction" id="setUnderconstruction" method="post" accept-charset="utf-8">
        <div class="modal-body">
            
              <label for="">Select the page name</label>
              <?php wp_dropdown_pages(); ?> 
              &nbsp;
                <br>
                 <label for="editorname">Set Content</label>
                 <?php wp_editor($sampletext, 'editorname' ); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" id="savedata" name="savedata" class="btn btn-primary">Save changes</button> 
        </div>
      </form>
    </div>
  </div>
</div>
<!-- END Modal -->

<hr>
<div id="showalldatahere"></div>
<div class="wrap">
    <h2>List Of set under construction pages</h2>
    <div id="msg"> </div>
    <table class="wp-list-table widefat striped">
      <thead>
        <tr>
          <th width="5%">ID</th>
          <th width="25%">Page Name/ID</th>
          <th width="5%">Status</th>
          <th width="20%">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        //check status
        function status_function($status){
          if ($status == 1) {
            $status_display = "<div class='borderboxActive'>Active</div>";
          } else {
            $status_display = "<div class='borderboxDeactive'>Disabled</div>";
          }
          return $status_display ;
        }
        //display pages
        function displaypage($id){
          $pages = get_pages();
          foreach ( $pages as $page ) {
            $d = '<a href="' . get_page_link( $page->ID ) . '">' . $page->post_title . '</a>';
          }
          return $d;
        }
        global $wpdb;
          $table_name = $wpdb->prefix . 'underConstruct';
          $results = $wpdb->get_results(
                        $wpdb->prepare("SELECT *
                            FROM $table_name")
                      );
          $count = $wpdb->num_rows;
          if ($count > 0) {
            foreach ($results as $print) {
              $htmlmodal='';
              $pagename = get_the_title($print->pageid);
              $pageid = $print->pageid;
              $status = status_function($print->status);
              $active=$noactive='';
              if ($print->status == 1) {
                $active='checked';
              }else{
                $noactive='checked';
              }
                echo "<tr id='user_".$print->ucid."'>
                  <td width='5%'>$print->ucid</td>
                  <td width='25%'>$pagename ($pageid)</td>
                  
                  <td width='5%'>$status</td>
                  <td width='20%'>
                    <form name='formaction_".$print->ucid."' id='formaction_".$print->ucid."'> 
                        <input type='hidden' name='userid_delete' id='userid_delete' value='".$print->ucid."'>
                        <button type='button' data-ucid='".$print->ucid."' class='deletedata' >DELETE</button>
                       
                    </form>
                  </td>
                </tr>";
            }
          } else {
            echo "<tr><td colspan='4'>No page is set for under-construction..</td></tr>";
          }
          
        ?>
      </tbody>  
    </table>

 </div>

 	<?php
} // complete function underConstructionSettings

//display page
add_action( 'wp', 'underConstructionAnyPageCall' );
function underConstructionAnyPageCall()
{
  $pagid = get_the_ID();
  global $wpdb;
  $table_name = $wpdb->prefix . 'underConstruct';
  $results =$wpdb->get_results(
      $wpdb->prepare("SELECT *
                        FROM $table_name
                        WHERE status = %d", 1
      )
  );
  $pageid_array = array();

  foreach($results as $print){
    $pageid_array[] = $print->pageid;
  }

  if (in_array($pagid, $pageid_array)){  
    add_filter('the_content', 'getContentFunction');
  } else {
    //echo "NotFound";
  }
}

function getContentFunction($return_text){
  $pagid = get_the_ID();
  global $wpdb;
  $table_name = $wpdb->prefix . 'underConstruct';
  $results = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT *
            FROM $table_name
            WHERE status = %d AND pageid = %d",
          1, $pagid
        )
    );
  return $results->uc_text;
}

//ajax
add_action( 'admin_enqueue_scripts', 'underConstructionCssJsFiles' );
function underConstructionCssJsFiles(){
  wp_register_style( 'bootstrap-css', plugins_url( '/assets/bootstrap.min.css', __FILE__ ) );
    wp_enqueue_style( 'bootstrap-css' );
  // Register the script like this for a plugin: 
    wp_enqueue_script( 'myjs', plugins_url( 'script.js', __FILE__ ),array('jquery'));
  wp_localize_script( 'myjs', 'ajax_var', array( 'ajaxurl' => admin_url('admin-ajax.php') ));
  //custom css
  wp_register_style( 'custom-css', plugins_url( '/assets/customcss.css', __FILE__ ) );
    wp_enqueue_style( 'custom-css' );
    
    wp_enqueue_script( 'bootstrap-script', plugin_dir_url( __FILE__ ) . 'assets/bootstrap.min.js', array('jquery'), '4.6.2', true );
}
//end ajax

/**
 * Deactivation hook.
 */
function underConstructionDeActivate() {
  // Unregister the post type, so the rules are no longer in memory.
  global $wpdb;
  $table_name = $wpdb->prefix . 'underConstruct'; // table name
  $sql = "DROP TABLE IF EXISTS $table_name";
  $rslt=$wpdb->query($sql);

  // Clear the permalinks to remove our post type's rules from the database.
  flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'underConstructionDeActivate' );

?>