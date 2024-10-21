define(['jquery', 'core/ajax', 'core/notification', 'datatables.net',
'datatables.net-bs4',
'datatables.net-buttons',
'datatables.net-buttons-bs4',
'datatables.net-buttons-colvis',
'datatables.net-buttons-print',
'datatables.net-buttons-html',
'datatables.net-buttons-flash',
'datatables.net-responsive',
'datatables.net-responsive-bs4',
'datatables.net-select-bs4',
'datatables.net-fixedcolumn-bs4',
'jquery-datatables-checkboxes',
'pdfmake',
'jszip'], function($, ajax, notification) {
return {
    init: function() {

      $('#apiname11').change(function () {

           
               // console.log($("#fromdate").val());
               // alert($("#fromdate").val());
            var apiname = $("#apiname").find(':selected').val();
                     alert(apiname);
            var promises = ajax.call([
                {
                    methodname: 'local_lmsws_fetch_payload',
                    args: {
                        apiname : apiname,
                        fromdate : 0,
                        todate : 0
                    }
                }
            ]);
            promises[0].done(function(response) {
               
                 $("#tbl").remove();
                $("tbody").remove();
                $("#tbl_wrapper").remove();
                $('.table').append('<table id="tbl" class="table"   width="100%" cellspacing="0" >'+
                '<thead>'+
                '<tr>'+
                    '<th scope="col">SL.No</th>'+
                    '<th scope="col">API Name</th>'+
                    '<th scope="col">FA Id</th>'+
                    '<th scope="col">FA Request</th>'+
                    '<th scope="col">Ref Id</th>'+
                    '<th scope="col">Institute Id</th>'+
                    '<th scope="col">Created At</th>'+
                   /* '<th scope="col">Response time</th>'+*/
                    '<th scope="col">Status</th>'+
                    '<th scope="col">FA Response</th>'+
                '</tr>'+
                '</thead>'+
                '<tbody>'+
                '</tbody>'+
                '</table>');
                for (var i = 0; i < response.length; i++) {

                var dt = new Date(response[i].crat);

                var DD = ("0" + dt.getDate()).slice(-2);
                // getMonth returns month from 0
                var MM = ("0" + (dt.getMonth() + 1)).slice(-2);
                var YYYY = dt.getFullYear();
                var hh = ("0" + dt.getHours()).slice(-2);
                var mm = ("0" + dt.getMinutes()).slice(-2);
                var ss = ("0" + dt.getSeconds()).slice(-2);

                var date_string = YYYY + "-" + MM + "-" + DD + " " + hh + ":" + mm + ":" + ss;

                    $('tbody').append('<tr>'+
                    '<td>' + (i+1) + '</td>'+
                    '<td>' + response[i].apiname + '</td>'+
                    '<td>' + response[i].faid + '</td>'+
                    '<td>' + response[i].req + '</td>'+
                    '<td>' + response[i].refid + '</td>'+
                    '<td>' + response[i].inid + '</td>'+
                    '<td>' + new Date(response[i].crat) + '</td>'+
                   /* '<td>' + response[i].restm + '</td>'+*/
                    '<td>' + response[i].sts + '</td>'+
                    '<td>' + response[i].res + '</td>'+
                    '</tr>');
                }
                $('#tbl').DataTable({
                     dom: 'Bfrtip',
                     buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                    fixedColumns:   {leftColumns: 1}
                });

               
            }).fail(function(response) {
                notification.addNotification({
                    type: 'error',
                    message: response.message.replace('Exception - ', '')
                });
            });
        });

       $('#submit-btn').on('click', function(e) {

        var today = new Date();
        var dd = today.getDate(); 
        var mm = today.getMonth() + 1;
        var yyyy = today.getFullYear();
        if (dd < 10) { dd = '0' + dd; } 
        if (mm < 10) { mm = '0' + mm; }

        var today =   yyyy +'-'+ mm + '-'+dd ;

        if ($("#apiname").val() == '') {
                e.preventDefault();
                $(document).scrollTop(0);
                notification.addNotification({
                    type: 'error',
                    message: 'Select Valid API Name'
                }); 
        }else if($("#fromdate").val()!='' && $("#fromdate").val()>=today){
                e.preventDefault();
                $(document).scrollTop(0);
                notification.addNotification({
                    type: 'error',
                    message: 'Fromdate should be less than Current Date'
                }); 
        }else if($("#todate").val()!='' && $("#todate").val()>today){
                e.preventDefault();
                $(document).scrollTop(0);
                notification.addNotification({
                    type: 'error',
                    message: 'Todate should be less than Current Date'
                }); 
        }else if($("#fromdate").val()!='' && $("#todate").val()!='' 
                 && $("#fromdate").val() >= $("#todate").val()){
                e.preventDefault();
                $(document).scrollTop(0);
                notification.addNotification({
                    type: 'error',
                    message: 'Fromdate should be less than todate'
                }); 
        }
         
         var apiname = $("#apiname").find(':selected').val();
         var fromdate=0;
         if($("#fromdate").val() != ''){
            var fromdate = Math.floor(new Date($("#fromdate").val()).valueOf()/1000); 
        }
        var todate=0;
         if($("#fromdate").val() != ''){
            var todate = Math.floor(new Date($("#todate").val()).valueOf()/1000); 
        }
       
         var promises = ajax.call([
                {
                    methodname: 'local_lmsws_fetch_payload',
                    args: {
                        apiname : apiname,
                        fromdate : fromdate,
                        todate : todate

                    }
                }
            ]);
            promises[0].done(function(response) {
               
                 $("#tbl").remove();
                $("tbody").remove();
                $("#tbl_wrapper").remove();
                $('.table').append('<table id="tbl" class="table"   width="100%" cellspacing="0" >'+
                '<thead>'+
                '<tr>'+
                    '<th scope="col">SL.No</th>'+
                    '<th scope="col">API Name</th>'+
                    '<th scope="col">FA Id</th>'+
                    '<th scope="col">FA Request</th>'+
                    '<th scope="col">Ref Id</th>'+
                    '<th scope="col">Institute Id</th>'+
                    '<th scope="col">Created At(YYYY-MM-DD)</th>'+
                   /* '<th scope="col">Response time</th>'+*/
                    '<th scope="col">Status</th>'+
                    '<th scope="col">FA Response</th>'+
                '</tr>'+
                '</thead>'+
                '<tbody>'+
                '</tbody>'+
                '</table>');
                for (var i = 0; i < response.length; i++) {
/*                    var dt = new Date(response[i].crat);

                var DD = ("0" + dt.getDate()).slice(-2);
                // getMonth returns month from 0
                var MM = ("0" + (dt.getMonth() + 1)).slice(-2);
                var YYYY = dt.getFullYear();
                var hh = ("0" + dt.getHours()).slice(-2);
                var mm = ("0" + dt.getMinutes()).slice(-2);
                var ss = ("0" + dt.getSeconds()).slice(-2);

                var date_string = YYYY + "-" + MM + "-" + DD + " " + hh + ":" + mm + ":" + ss;*/
                    $('tbody').append('<tr>'+
                    '<td>' + (i+1) + '</td>'+
                    '<td>' + response[i].apiname + '</td>'+
                    '<td>' + response[i].faid + '</td>'+
                    '<td>' + response[i].req + '</td>'+
                    '<td>' + response[i].refid + '</td>'+
                    '<td>' + response[i].inid + '</td>'+
                    '<td>' + new Date(response[i].crat*1000) + '</td>'+
                   /* '<td>' + response[i].restm + '</td>'+*/
                    '<td>' + response[i].sts + '</td>'+
                    '<td>' + response[i].res + '</td>'+
                    '</tr>');
                }
                $('#tbl').DataTable({
                     dom: 'Bfrtip',
                     buttons: ['copy', 'csv', 'print'],
                    fixedColumns:   {leftColumns: 1}
                });

               
            }).fail(function(response) {
                notification.addNotification({
                    type: 'error',
                    message: response.message.replace('Exception - ', '')
                });
            });

         
       });


    }
};
});
