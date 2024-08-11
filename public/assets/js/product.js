$( document ).ready(function() {
    var data;
    var errors;

    $(".btn-get-report").click(function(){
        data = [];
        errors = false;
        $(".product-row").removeClass("red-border");
        $(".product-row").removeClass("green-border");
        $(".product-row").each(function() {
            var manufacturedQuantity = $(this).find("input[name=manufactured_quantity]").val() ?? 0 ;
            var soldQuantity = $(this).find("input[name=sold_quantity]").val() ?? 0;
            if( manufacturedQuantity && soldQuantity){
                console.log(soldQuantity,manufacturedQuantity)
                data.push({
                    manufactured_quantity: manufacturedQuantity, 
                    sold_quantity: soldQuantity,
                    id: $(this).attr("data-key"),
                });
                $(this).addClass("green-border");
            }else{
                $(this).addClass("red-border");
                errors = true;
            }
        });
        if(errors){
            showWarningMessage();
        }else{
            sendDataForReport(data);
        }
    })
})

function showWarningMessage(){
    $(".warning-section").fadeIn(1000, function() {
        $(".warning-section").fadeOut(3000);
    });
}

function sendDataForReport(reportData){
    $(".download-report").removeAttr("href").slideUp(1000);
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });
    $.ajax({
        url: "/get-report",
            type: 'POST',
            data: {
                data:reportData
            },
            datatype: 'json',
        }).done(function(res){
            $(".download-report").attr("href",res.route).slideDown(1000);
        }).fail(function (res) {
            $(".errors").append("<p>Խնդրում ենք լրացնել բոլոր դաշտերը․</p>")
            setTimeout(() => {
                $(".errors").empty();
              }, 3000);
        });
}