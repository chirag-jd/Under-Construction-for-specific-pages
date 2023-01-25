jQuery(document).ready(function($){
    jQuery("#savedata").click( function(e) {
        var page_id = $('#page_id').val();
        var editorname = $('#editorname').val();
        if(page_id != '' && editorname != '') { 
            $('.wqsubmit_message').html('');
            var fd = new FormData(document.getElementById("setUnderconstruction"));
            var action = 'underconstructionaction';
            fd.append("action", action);

            $.ajax({
                data: fd,
                type: 'POST',
                url: ajax_var.ajaxurl,
                contentType: false,
                   cache: false,
                   processData:false,
                success: function(response) {
                    if (response == 1) {
                        $('.wqsubmit_message').html('<span class="alert alert-success"> Data Has Inserted Successfully </span>');
                        setTimeout(function() { location.reload(); }, 3500);
                    } else if(response == 2){
                        $('.wqsubmit_message').html('<span class="alert alert-danger"> Data Not Saved </span>');
                    } else if(response == 3){
                        $('.wqsubmit_message').html('<span class="alert alert-info"> Data Has Already Exist </span>');
                    }
                    //$('.wqsubmit_message').html(response);
                }
            });
        } else {
          return false;
        }
    });

    //delete data
    //function DeleteData(d){ 
    jQuery(".deletedata").click( function(e) {
      var uid = this.getAttribute("data-ucid");
      if (confirm('Are you sure to delete this?')) {
        $('#msg').html('');
        var fd = new FormData(document.getElementById("formaction_"+uid));
        var action = 'delete_data_unid';
        fd.append("action", action);
        $.ajax({
              data: fd,
              type: 'POST',
              url: ajax_var.ajaxurl,
              contentType: false,
                 cache: false,
                 processData:false,
              success: function(res) {
                $('#msg').html('<span class="alert alert-success"> Under Construction Removed Successfully </span>');
                $('#user_'+uid).remove();
                setTimeout(function() { location.reload(); }, 3500);
              }
        });
      }
    //}
    });

    jQuery(".open-edit_user").click( function(e) {
      var uid = this.getAttribute("data-user_id");
        $('#msg').html('');
        $("#editid").val(uid); 
    });
});
