
function isset(v) {
  if (v === 'undefined') {
    return false;
  }
  return true;
}

if (isset(typeof (bildirgoster))) {
} else {
  var bildirgoster = '';
}
if (isset(typeof (kategori))) {
} else {
  var kategori = '';
}

if (isset(typeof (firma))) {
} else {
  var firma = '';
}
if (isset(typeof (tarih))) {
} else {
  var tarih = '';
}

if (isset(typeof (query))) {
} else {
  var query = '';
}

if (isset(typeof (satir))) {
} else {
  var satir = '';
}

if (emails === '') {

} else {
  var emails = [];
}
if (secilenisec === '')
{
  var secilenisec = '';
} else {
  
}


load_data(1, 15, kategori, bildirgoster, satir, firma, tarih, query);

function load_data(page, per, kategori, bildirgoster, satir, firma, tarih, query = '') {

  $('.yukleniyor').addClass('loading');
  $.ajax({
    url: "load_data.php",
    method: "POST",
    dataType: "json",
    data: { page: page, query: query, per: per, kategori: kategori, bildirgoster: bildirgoster, urun: satir, firma, tarih: tarih, sayfala: 1 },
    success: function (data) {
      $('.yukleniyor').removeClass('loading');
      $('#satirlar').html(data.satirlar);
      $('#linkler').html(data.linkler);
      $('#teklifaksiyonu').find('option[value="' + secilenisec  + '"]').prop('selected', 'selected');
      for (let i = 0; i < emails.length; i++) {
        $('input[value="' + emails[i] + '"]').attr('checked', true);
      }
    }
  });
}

$(document).on('click', '.page-link', function () {
  var page = $(this).data('page_number');
  var per = $(this).data('per_number');
  var query = $('#search').val();
  var tarih = $('#tarih').val();
  load_data(page, per, kategori, bildirgoster, satir, firma, tarih, query);
});

// Sayfada kaç satır gösterilecek
$('#sayfada').change(function () {
  var per = $(this).val();
  var query = $('#search').val();
  var tarih = $('#tarih').val();
  load_data(1, per, kategori, bildirgoster, satir, firma, tarih, query);
});

$(document).on("keyup input", "#search", function () {
  var query = $('#search').val();
  var per = $('select[name=sayfada] option').filter(':selected').val();
  var tarih = $('#tarih').val();
  if (query != '""' && query != '"') {
    load_data(1, per, kategori, bildirgoster, satir, firma, tarih, query);
  }
});

$(document).on("keyup input", "#tarih", function () {
  var tarih = $('#tarih').val();
  var query = $('#search').val();
  var per = $('select[name=sayfada] option').filter(':selected').val();
  load_data(1, per, kategori, bildirgoster, satir, firma, tarih, query);
});


// ücret giriş alanlarında 0.00 göstermek içindir
$("#urun_birim_fiyati,#urun_montaj_ucreti,#elle_iskonto,#urun_iskonto").on('blur change input', function () {
  $(this).val(function (i, input) {
    input = input.replace(/\D/g, '');
    return (input / 100).toFixed(2);
  });
}).trigger('blur');

$(".input_para_formati").on('blur change input', function () {
  $(this).val(function (i, input) {
    input = input.replace(/\D/g, '');
    return (input / 100).toFixed(2);
  });
}).trigger('blur');