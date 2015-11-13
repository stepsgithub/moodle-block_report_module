var initForm = function(){
      $.ajax({
            type: "POST",
            url: reportUrl + 'view.php?dummy=' + new Date().getTime(),
            async: false,
            success: function(html){
                  $("#selectcourseform").append(html);
                  $('#checkcoursescoreform').empty();
            }
      });
      $.ui.dialog.defaults.bgiframe = true;
      $.ui.dialog.defaults.hide = 'slide';
      //$.ui.dialog.defaults.show= 'slide';
      $.ui.dialog.defaults.height = 460;
      $.ui.dialog.defaults.width = 640;
      $("#editDialog").hide();
}

var selectCategoryAction = function(){
      $('#menucategory').change( function () {
            $.ajax({
                  type: "POST",
                  url: reportUrl + 'view.php?dummy=' + new Date().getTime(),
                  data: "menucategory=" + $('#menucategory').attr('value'),
                  async: false,
                  success: function(data){
                        $('#selectcourseform').html(data);
                        selectCategoryAction();
                        selectCourseAction();
                        $('#checkcoursescoreform').empty();
                  }
            });
      });
}

var selectCourseAction = function(){
      $('#menucourse').change( function () {
            $.ajax({
                  type: "POST",
                  url: reportUrl + 'view.php?dummy=' + new Date().getTime(),
                  data: "menucourse=" + $('#menucourse').attr('value') + "&menucategory=" + $('#menucategory').attr('value'),
                  async: false,
                  success: function(data){
                        $('#selectcourseform').html(data);
                        selectCategoryAction();
                        selectCourseAction();
                        checkCourseScoreAction();
                  }
            });
      });
}

var checkCourseScoreAction = function(){
      $.ajax({
            type: "POST",
            url: reportUrl + 'checkscore.php?dummy=' + new Date().getTime(),
            data: "menuaction=checkscore&menucourse=" + $('#menucourse').attr('value'),
            async: false,
            success: function(data){
                  $('#checkcoursescoreform').html(data);
                  clickAction();
            }
      });
}

var clickAction = function(){
      $("#sentCourseInfoButton").click( function () {
            switch( $('#menureportformat').attr('value') )
            {
                  case 'showashtml':
                        showLoading();
                        $.ajax({
                              type: "POST",
                              url: reportUrl + 'index.php?dummy=' + new Date().getTime(),
                              data: "category=" + $('#menucategory').attr('value') + "&course=" + $('#menucourse').attr('value')
                                    + "&user=" + $('#menuuser').attr('value') + "&date=" + $('#menudate').attr('value')
                                    + "&reportformat=" + $('#menureportformat').attr('value'),
                              async: true,
                              success: function(html){
                                    closeLoading();
                                    var htmlForm = '<div id="dialog" title="Course Report">' + html + "</div>";
                                    $("#editDialog").append(htmlForm);
                                    $("#dialog").dialog();
                              },
                              error: function(html){
                                    closeLoading();
                                    var htmlForm = '<div id="dialog" title="System Dialog">' + "Connect Error!" + "</div>";
                                    $("#editDialog").append(htmlForm);
                                    $("#dialog").dialog();
                              }
                        });
                  break;
                  case 'downloadasods':
                        var odsURL = reportUrl + 'index.php'
                        + "?category=" + $('#menucategory').attr('value') + "&course=" + $('#menucourse').attr('value')
                        + "&user=" + $('#menuuser').attr('value') + "&date=" + $('#menudate').attr('value')
                        + "&reportformat=" + $('#menureportformat').attr('value')
                        + "&dummy=" + new Date().getTime();
                        setTimeout( location.href=(odsURL), 50); //ms    
                  break;
                  default:
                        var htmlForm = '<div id="dialog" title="System Dialog">' + "Select Form Error!" + "</div>";
                        $("#editDialog").append(htmlForm);
                        $("#dialog").dialog();
                  break;
            } 
    });
}

var showLoading = function() {
      $("#loading").css("display","block");
}

var closeLoading = function() {
      $("#loading").css("display","none");
}

$(document).ready( function(){
      initForm();
      selectCategoryAction();
});
