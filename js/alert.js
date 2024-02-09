
var close = document.getElementsByClassName("closebtn");
var i;

for (i = 0; i < close.length; i++) {
  close[i].onclick = function(){
    var div = this.parentElement;
    div.style.opacity = "0";
    setTimeout(function(){ div.style.display = "none"; }, 600);
  }
}


$(document).ready(function() {
  $(".success").fadeTo(2000, 1000).slideUp(1000, function(){
    $(".success").slideUp(1000);
});
});

$("#eklebuton").click(function() {
    const element = document.getElementById("tbliste");
    element.scrollIntoView();
    $("#ekle").toggle();
});

function reply_click(clicked_id)
{
    $("#ekle" + clicked_id ).toggle();
}

function cikis() {
  $(function () {
    jw('b secim', CIKIS).baslik("Çıkışı Onayla!").icerik("Çıkış yapmak istediğinizden emin misiniz?").kilitle().ac();
  })
  function CIKIS(x) {
    if (x == 1) {
      $.ajax({
        url: "logout.php",
        success: function () {
          window.location.href = "/";
        }
      });
    }
  }

}


