Dropzone.autoDiscover = false;

$(document).ready(function(){
    console.log("doc ready")
    var myDropzone = new Dropzone("#upload-widget", {url: "/file-upload"});
    myDropzone.on("addedfile", function(file) {

        $.ajax({
            type: "get",
            url: "/makeCharts",
            success: function (data) {
                $('#dropZonePlace').remove()
                $('#workplace').append('<div class="row"><div class="col-12 my-5">' + data.chart1 + '</div></div>')
                $('#workplace').append(data.chart2)
                $('#workplace').append(data.chart3)
            }
        });

       
    });
})