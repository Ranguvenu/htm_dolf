$(document).ready(function(){
  $(".el_quesubmit #id_submitbutton").click(function(e){ 
        //console.log( $(".el_quesubmit #id_submitbutton").serialize());
        var form = $(".el_quesubmit #id_submitbutton").submit();
        e.preventDefault();// Submit the form
         console.log( $(this).serialize() );
        $(this).parent('form').submit(); 
        $('.singlebutton').children('form').find('.btn-secondary').trigger('click');
    });
  // if (/question\/bank\/editquestion\/addquestion\.php/.test(node.getAttribute('action'))) {
  //               node.on('submit', this.displayQuestionChooser, this);
  // }
  //    displayQuestionChooser: function(e) {
  //       alert('gfjhgfjgjgfjg');
  //       $(".el_quesubmit #id_submitbutton").submit();
  //    }
})