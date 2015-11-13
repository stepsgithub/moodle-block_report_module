var initInformation = function(){
      $.ajax({
            type: "POST",
            url: trackUrl + 'view.php?dummy=' + new Date().getTime() + '&menuuser=' + userID,
            async: false,
            success: function(html){
                $("#courseInformation").append(html);
                clickCourseChoiseAction();
            }
      });
      $.ui.dialog.defaults.bgiframe = true;
      $.ui.dialog.defaults.hide = 'slide';
      //$.ui.dialog.defaults.show= 'slide';
      $.ui.dialog.defaults.height = 460;
      $.ui.dialog.defaults.width = 640;
      $("#editDialog").hide();
}

var clickCourseChoiseAction = function(){
    $('#courseClick .courseButtonClick').click(function() {
        showLoading();
        $.ajax({
            type: "POST",
            url: trackUrl + 'index.php?dummy=' + new Date().getTime(),
            data: "courseid=" + this.name,
            async: true,
            success: function(html){
                closeLoading();
                var htmlForm = '<div id="dialog" title="Course Report">' + html + "</div>";
                $("#editDialog").append(htmlForm);
                $("#dialog").dialog();
                //clickCourseChoiseAction();
            },
            error: function(html){
                closeLoading();
                var htmlForm = '<div id="dialog" title="System Dialog">' + "Connect Error!" + "</div>";
                $("#editDialog").append(htmlForm);
                $("#dialog").dialog();
                //clickCourseChoiseAction();
            }
        });
    });
}

var showLoading = function() {
      $("#loading").css("display","block");
}

var closeLoading = function() {
      $("#loading").css("display","none");
}

$(document).ready( function(){
    initInformation();
});
