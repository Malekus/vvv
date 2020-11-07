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
                $('#workplace .row').append(data.chart1)
                $('#workplace .row').append(data.chart2 )
                $('#workplace .row').append(data.chart3)
                $('#workplaceArray .row').append(data.htmlArray)
            }
        });

       
    });
})