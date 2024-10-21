//Raju : Org-reg
$(document).on('click', "[name='downloadapprovalletter']", function(e){

       e.preventDefault();
       // Convert all the form elements values to a serialised string.
       var form = $(this).parents().find('form');

       var formData = $(this).parents().find('form').serialize();

       var request = new XMLHttpRequest();
       request.open('POST',M.cfg.wwwroot + "/auth/registration/approvalletter.php", true);
       request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
       request.responseType = 'blob';

       request.onload = function() {
         // Only handle status code 200
         if(request.status === 200) {
           // Try to find out the filename from the content disposition `filename` value
           var disposition = request.getResponseHeader('content-disposition');
           var matches = /"([^"]*)"/.exec(disposition);
           var filename = (matches != null && matches[1] ? matches[1] : 'file.pdf');

           // The actual download
           var blob = new Blob([request.response], { type: 'application/vnd.pdf' });
           var link = document.createElement('a');

           // IE doesn’t allow using a blob object directly as link href
            // instead it is necessary to use msSaveOrOpenBlob
            if (window.navigator && window.navigator.msSaveOrOpenBlob) {
                window.navigator.msSaveOrOpenBlob(blob, filename);
                return;
            }

            // For other browsers:
            // Create a link pointing to the ObjectURL containing the blob.

           document.body.appendChild(link);
           link.setAttribute("type", "hidden"); // make it hidden if needed
           link.download = filename;
           link.href = window.URL.createObjectURL(blob);

           link.click();

         }else if(request.status === 405){

            // console.log(form);

            // form.submit();

            $('#id_submit').trigger('click');

         }
         
         // some error handling should be done here...
       };

       request.send('formData=' + formData);

});
$(document).on('click', "#licensekeyvalidate_btn", function(e){
  $(this).parents().find('form').submit();
});

//Dynamic dropdown
$( document ).ready(function() {
   
  if(typeof "select[name='sectors[]']" != 'undefined'){
                    var sectors = $("select[name='sectors[]']").val();
                    if(typeof $('#el_segmentlist') != 'undefined'){
                        $('select#el_segmentlist').data('sectorid',sectors);
                    }
                 
                    if(typeof $('#el_jobfamily') != 'undefined'){
                        $('#el_jobfamily').data('sectorid',sectors);
                    }
                }
                $("select[name='sectors[]']").on('change', function(e){
                       var sectors = $(this).val();
                       var segments = $(this).closest("form").find("select[name='segments[]']");
                       segments.val('');
                       segments.attr('data-sectorid',sectors);
                       var targetgroup = $(this).closest("form").find("select[name='targetgroup[]']");
                       targetgroup.val('');
                       targetgroup.attr('data-sectorid',sectors);
                });

                if(typeof "select[name='sectors']" != 'undefined'){
                    var sectors = $("select[name='sectors']").val();
                    if(typeof $('#el_segmentlist') != 'undefined'){
                        $('select#el_segmentlist').data('sectorid',sectors);
                    }
                 
                    if(typeof $('#el_jobfamily') != 'undefined'){
                        $('#el_jobfamily').data('sectorid',sectors);
                    }
                }
                $("select[name='sectors']").on('change', function(e){
                       var sectors = $(this).val();
                       var segments = $(this).closest("form").find("select[name='segments[]']");
                       var segment = $(this).closest("form").find("select[name='segment']");
                       segments.val('');
                       segments.attr('data-sectorid',sectors);
                       segment.val('');
                       segment.attr('data-sectorid',sectors);
                       var targetgroup = $(this).closest("form").find("select[name='targetgroup[]']");
                       targetgroup.val('');
                       targetgroup.attr('data-sectorid',sectors);
                });
                //Job family
                if(typeof $('#el_segmentlist') != 'undefined'){
                    if(typeof $('#el_jobfamily') != 'undefined'){
                        var sectors = $('.el_sectorlist').val();
                        $('select#el_jobfamily').data('sectorid',sectors);
                        var segments = $('#el_segmentlist').val();
                        $('select#el_jobfamily').data('segmentid',segments);
                    }
                }
                $('#el_segmentlist').on('change', function(e){
                       var segments = $('#el_segmentlist').val();
                       $('select#el_jobfamily').val('');
                       $('select#el_jobfamily').attr('data-segmentid',segments);//data('segmentid',segments);
                });

                //job role
                if(typeof $('#el_jobfamily') != 'undefined'){
                    if(typeof $('#el_jobroles') != 'undefined'){
                        var jobfamily = $('#el_jobfamily').val();
                        $('select#el_jobroles').data('jobfamilyid',jobfamily);
                    }
                }
                $('#el_jobfamily').on('change', function(e){
                       var jobfamily = $('#el_jobfamily').val();
                       $('select#el_jobroles').val('');
                       $('select#el_jobroles').attr('data-jobfamilyid',jobfamily);//data('jobfamilyid',jobfamily);
                });
});

    $(document).ready(function(){
        $('body').on('change','input[name="regtype"]',function(){
            var selected_type = $(this).val();
           

            $('.tagscontainer').addClass('invisible1');        
            if(selected_type == 0){
              $('.tagscontainer[data-tagtype="0"]').removeClass('invisible1');
            }
            if(selected_type == 1){
                $('.tagscontainer[data-tagtype="1"]').removeClass('invisible1');
              }
          });
    });


