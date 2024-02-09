/**********************************************************************************
jsWindow 0.34b - Javascript based window creator & manager
Copyright (C) 2012, 2013  MEHMET EMRAH TUNÇEL

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

WEB PAGE : www.jswindow.com
MAIL     : timemrah@gmail.com
**********************************************************************************/
//NOT: jsWindow BEKLEME ANİMASYONU İÇİN "SPIN" KÜTÜPHANESİNİ İÇERİR (JSWINDOW'DAN BAĞIMSI BİR KÜTÜPHANEDİR) | fgnass.github.com/spin.js#v1.2.8


//JSWINDOW KLASÖRÜNÜN SAYFALAR İLE AYNI DİZİNDE OLMADIĞI TAKTİRDE DÜZGÜN ÇALIŞABİLMESİ İÇİN KLASÖR KONUMUNUN BELİRTİLECEĞİ DEĞİŞKEN.
var jw_konum = "";
var jw_website_cerceve_id = "jswindow_website_cerceve";

//JSWINDOW'UN KULLANIMI İÇİN GEREKLİ ANA SINIFIMIZ
var jswindow = function()
{	var $ = window.jQuery;

	//JQUERY KÜTÜPHANESİ KONTROL EDİLİYOR.
	if(typeof($)=="undefined")
	{	window.onload = function()
		{	alert("jQuery kütüphanesi bulunamadı! \n\n JsWindow çalışmak için jQuery 1.8.1 veya üzeri sürümüne ihtiyaç duyar. \n\n Lütfen JsWindow'un bulunduğu sayfalara jQuery kütüphanesini ekleyiniz.");
		}
		return false;
	}
	
	//BAZI GEREKLİ GLOBAL DEĞİŞKENLER
	var s 					= new Object;
	s.pencere				= new Array;	//TÜM STATİK PENCERELERİ NO'SU İLE BARINDIRAN DİZİ
	s.kPNO					= new Array;	//KAPATILAN STATİK PENCERELERİN NO'LARININ TUTULDUĞU DİZİ
	
	var d 					= new Object;
	d.pencere				= new Array;	//TÜM DİNAMİK PENCERELERİ NO'SU İLE BARINDIRAN DİZİ
	d.kPNO					= new Array;	//KAPATILAN DİNAMİK PENCERELERİN NO'LARININ TUTULDUĞU DİZİ
	
	var bodyOverflow 		= new Object;
	var websiteFixProcess 	= new Object;
	var websiteHTML 		= new Object;
	
	//JSWINDOW İÇİN GEREKLİ TAGLAR YOKSA ONLOAD ZAMANI İLE OLUŞTURULUYOR
	$(function jwTagKontrol()
	{	if(!$("#jswindow").length)
		{	var jwAnaTag 	= $("<div>").attr("id","jswindow");
			var jwDP		= $("<div>").attr("id","jw-dinamik");
			var jwDPT		= $("<div>").attr("id","jw-dinamikT");
			var jwSP		= $("<div>").attr("id","jw-statik");
			
			$("body").append(jwAnaTag.append(jwDPT).append(jwSP));
			$("#"+jw_website_cerceve_id).append(jwDP);
		}	
	})

	//websiteHTML ORJİNAL BİLGİLERİ ---------------------------------------
	$(function()
	{	
		websiteHTML.position 	= $("#"+jw_website_cerceve_id).css("position");
		websiteHTML.overflowX 	= $("#"+jw_website_cerceve_id).css("overflow-x");
		websiteHTML.overflowY 	= $("#"+jw_website_cerceve_id).css("overflow-y");
		websiteHTML.width	 	= document.getElementById(jw_website_cerceve_id).style.width;		
		websiteHTML.top	 		= $("#"+jw_website_cerceve_id).css("top");
		websiteHTML.status		= false;
		
		bodyOverflow.X			= $("body").css("overflow-x");
		bodyOverflow.Y			= $("body").css("overflow-y");
		
	})
	
	//STATİK PENCERE ----------------------------------------------------------------------------
	this.statikPencere = function(kod,tur)
	{	
		//STATİK PENCERE DEĞİŞKENLER ------------------------------------------------------------
		var altNesne 	= new Object;	//PENCERE İŞLEMLERİ İÇİN ALT METOD ve ÖZELLİK YARATMAK ADINA KULLANILIYOR
		var akilliKapat = true;
		var secimTus=false , tamamTus=false , acDurum=null , efektTip=false , efektHiz=250 , no=false , kapaninca = false , icerikDurum = false , aniDurum = false , kodluPencereAcikmi=false;
		
		//KOLAY KULLANIM İÇİN ÖZELLİKLERİ(HTML TAGLARINI) GLOBALLEŞİREN DEĞİŞKENLER
		var cerceve , perde , pencere , govde , tusSatir;
	
		//STATİK PENCERE HTML TAGLARINI OLUŞTUR -------------------------------------------------
		altNesne.htmlYarat = function()
		{	
			//ÖZELLİKLER -> PENCERENİN HTML TAGLARINA ERİŞMENİN KISA YOLLARI
			this.cerceve		= $("<div>")	.addClass("jw-s-cerceve");		cerceve = this.cerceve;
			
			this.perde			= $("<div>")	.addClass("jw-s-perde"); 		perde 	= this.perde;		
			this.perdeTABLE		= $("<table>")	.addClass("jw-s-perdeTABLE");
			this.perdeTR		= $("<tr>");
			this.perdeTD		= $("<td>")		.addClass("jw-s-perdeTD");
			
			this.pencereTABLE	= $("<div>")	.addClass("jw-s-pencereTABLE");
			this.pencereTD		= $("<div>")	.addClass("jw-s-pencereTD");
			this.pencere 		= $("<div>")	.addClass("jw-s-pencere");		pencere 	= this.pencere;
			this.baslikSatir	= $("<div>")	.addClass("jw-s-baslikSatir"); 	baslikSatir	= this.baslikSatir;
			this.baslikDIV		= $("<div>")	.addClass("jw-s-baslikDIV");
			this.kapatTus		= $("<img>")	.addClass("jw-s-carpiImg").attr("src",jw_konum+"jswindow/resim/carpi.png").attr("title","Kapat");
			
			this.govde			= $("<div>")	.addClass("jw-s-govde");		govde = this.govde
			this.govdeTABLE		= $("<table>")	.addClass("jw-s-govdeTABLE");
			this.govdeTR		= $("<tr>")		.addClass("jw-s-govdeTR");
			this.icerikTD		= $("<td>")		.addClass("jw-s-icerikTD");
			this.icerikDIV1		= $("<div>")	.addClass("jw-s-icerikDIV1");
			this.icerikDIV2		= $("<div>")	.addClass("jw-s-icerikDIV2");
			
			this.tusSatir		= $("<div>")	.addClass("jw-s-tusDiv");		tusSatir = this.tusSatir;
			
			//AKILLI KAPAT OLAY
			this.akilliKapat = function(){	if(akilliKapat) altNesne.kapat()	}
			
			//OLAY VE ÖZNİTELİK ATAMA
			this.cerceve.attr("kod",kod).attr("tur",tur).mousedown(this.akilliKapat);
			this.pencere.on({"mousedown":function(){akilliKapat=false} , "mouseup":function(){akilliKapat=true}})
			this.kapatTus.click(altNesne.kapat)

			return this;
		}
	
		//STATİK PENCERE KULLANIMI SAĞLAYAN METODLAR --------------------------------------------
		altNesne.kod				= function(kod)
		{	kodluPencereAcikmi = statikPencereEris().kod(kod);
			if(!kodluPencereAcikmi)	this.cerceve.attr("kod",kod);	//BU KODA SAHİP BAŞKA BAŞKA BİR PENCERE AÇIK DEĞİLSE HTML TAGINA KOD ÖZNİTELİĞİNE KOD'U KAYDET

			return this;
		}
		altNesne.tur				= function(tur)	{ this.cerceve.attr("tur",tur); 	return this; }
		altNesne.en					= function(px)	{ this.pencere.css("width",px); 	return this; }
		altNesne.boy				= function(px)	{ this.govdeTABLE.css("height",px);	return this; }
		
		altNesne.baslik				= function(veri, css1, css2)
		{	var cssDurum = 0;
			if(typeof(css1)=="string" && typeof(css2)=="string"){	this.baslikDIV.css(css1,css2); 	cssDurum=1; }//İKİ DEĞİŞKEN STRİNG OLARAK BELİRTİLMİŞ İSE JQUERY İÇİN İKİ DEĞİŞKEN MODELİ CSS UYGULA
			else if(typeof(css1)=="object")						{	this.baslikDIV.css(css1); 	 	cssDurum=1; }//DEĞİLSE VE İLK DEĞİŞKEN NESNE İSE JQUERY İÇİN NESNESEL CSS UYGULA
			
			if(typeof(veri)=="string" || typeof(veri)=="number")	this.baslikDIV.html(veri);		//VERİ YAZI İSE HTML OLARAK EKLENİR
			else if(typeof(veri)=="object")							this.baslikDIV.append(veri);	//VERİ NESNE İSE APPEND İLE EKLENİR
			else if(!cssDurum)										return this.baslikDIV.html();	//VERİ VE CSS YOKSA İÇERİK GERİ DÖNDÜRÜLÜR
			
			return this;
		}
		altNesne.icerik 			= function(veri, css1, css2)
		{ 	var cssDurum = 0;
			if(typeof(css1)=="string" && typeof(css2)=="string"){	this.icerikTD.css(css1,css2); cssDurum=1; }	//İKİ DEĞİŞKEN STRİNG OLARAK BELİRTİLMİŞ İSE JQUERY İÇİN İKİ DEĞİŞKEN MODELİ CSS UYGULA
			else if(typeof(css1)=="object")						{	this.icerikTD.css(css1); 	  cssDurum=1; }	//DEĞİLSE VE İLK DEĞİŞKEN NESNE İSE JQUERY İÇİN NESNESEL CSS UYGULA
			
			if(typeof(veri)=="string" || typeof(veri)=="number")	this.icerikDIV2.html(veri);		//VERİ YAZI İSE HTML OLARAK OLARAK EKLENİR
			else if(typeof(veri)=="object")							this.icerikDIV2.append(veri);	//VERİ NESNE İSE APPEND İLE EKLENİR
			else if(!cssDurum)										return this.icerikDIV2.html();	//VERİ VE CSS YOKSA İÇERİK GERİ DÖNDÜRÜLÜR
			
			icerikDurum = true;
			this.icerikDIV2.css("display","block");
			if(aniDurum)	this.icerikDIV1.css("marginBottom","0");
			
			return this;
		}
		altNesne.bekleAnimasyon		= function(ayar)
		{	if(ayar==undefined)	this.icerikDIV1.spin(bekleAnimasyonAyar1);
			else			   	this.icerikDIV1.spin(ayar);
			
			this.icerikDIV1.find(".spinner").css("margin","auto");
			
			aniDurum = true;
			this.icerikDIV1.css("display","block");
			if(icerikDurum)	this.icerikDIV1.css("marginBottom","0");
			else			this.icerikDIV2.css("display","none");
			
			return this;
		}
		altNesne.baslikCss			= function(css1, css2)
		{	if(typeof(css1)=="string" && typeof(css2)=="string")	this.baslikDIV.css(css1,css2)	//İKİ DEĞİŞKEN STRİNG OLARAK BELİRTİLMİŞ İSE JQUERY İÇİN İKİ DEĞİŞKEN MODELİ CSS UYGULA
			else if(typeof(css1)=="object")							this.baslikDIV.css(css1)		//DEĞİLSE VE İLK DEĞİŞKEN NESNE İSE JQUERY İÇİN NESNESEL CSS UYGULA
			return this;
		}
		altNesne.icerikCss			= function(css1, css2)
		{	if(typeof(css1)=="string" && typeof(css2)=="string")	this.icerikTD.css(css1,css2)	//İKİ DEĞİŞKEN STRİNG OLARAK BELİRTİLMİŞ İSE JQUERY İÇİN İKİ DEĞİŞKEN MODELİ CSS UYGULA
			else if(typeof(css1)=="object")							this.icerikTD.css(css1)			//DEĞİLSE VE İLK DEĞİŞKEN NESNE İSE JQUERY İÇİN NESNESEL CSS UYGULA
			return this;
		}
		altNesne.css				= function(css1, css2)
		{	if(typeof(css1)=="string" && typeof(css2)=="string")	this.pencere.css(css1,css2)		//İKİ DEĞİŞKEN STRİNG OLARAK BELİRTİLMİŞ İSE JQUERY İÇİN İKİ DEĞİŞKEN MODELİ CSS UYGULA
			else if(typeof(css1)=="object")							this.pencere.css(css1)			//DEĞİLSE VE İLK DEĞİŞKEN NESNE İSE JQUERY İÇİN NESNESEL CSS UYGULA
			return this;
		}
		
		altNesne.sinifEkle			= function(cls)	{ this.pencere.addClass(cls); return this; }
		altNesne.sinifKaldir		= function(cls)	{ this.pencere.removeClass(cls); return this; }
		
		altNesne.govdeKapat 		= function(hiz)
		{	govde.slideUp(hiz, function(){ govde.css("display","none") });
			this.tusSatir.slideUp(hiz, function(){ tusSatir.css("display","none") });
			return this;
		}
		altNesne.govdeAc			= function(hiz)
		{	govde.slideDown(hiz);
			this.tusSatir.slideDown(hiz);
			return this;
		}
		
		altNesne.baslikSatirKapat 	= function(hiz)	{ this.baslikSatir.slideUp(hiz,function(){ baslikSatir.css("display","none") }); return this; }
		altNesne.baslikSatirAc	 	= function(hiz)	{ this.baslikSatir.slideDown(hiz); 	return this; }
		
		altNesne.akilliKapatPasif	= function()	{ this.cerceve.off("mousedown",this.akilliKapat); return this; } //PENCERE DIŞINA BASILINCA KAPANMAYI PASİFLEŞTİRİYOR.
		altNesne.akilliKapatAktif	= function()	{ this.cerceve.on("mousedown",this.akilliKapat);  return this;   } //PENCERE DIŞINA BASILINCA KAPANMAYI AKTİFLEŞTİRİLİYOR.

		altNesne.kapatPasif			= function()	{ var a = this.kapatTus; a.fadeOut(300,function(){ a.css("display","none") }); return this; }
		altNesne.kapatAktif 		= function()	{ this.kapatTus.fadeIn(300); return this; }
		
		altNesne.kilitle			= function()	{ altNesne.akilliKapatPasif(); altNesne.kapatPasif(); return this; }
		altNesne.kilitlePasif		= function()	{ altNesne.akilliKapatAktif(); altNesne.kapatAktif(); return this; }
		
		altNesne.tamamTus 			= function(txt,tetikle)
		{	if(!tamamTus) //PENCEREYE TAMAM TUŞU KONMAMIŞSA TAMAM TUŞU KONABİLİR
			{	tamamTus 	= true;    //BİR DAHA TAMAM TUŞ EKLENEMEMESİ İÇİN
				
				if(typeof(txt) == "function"){ var tetikle = txt; var txt=false; } //VERİLEN İLK PARAMETRE TETİKLENCEK BİR FONKSİYON İSE TETİKLEME YAPILACAK DEĞİŞKENE AKTARILIYOR.

				var tamam	= $("<button>").text("Tamam").addClass("jw-t-standart").click(altNesne.kapat).click(tetikle).css("display","none");;
				if(txt)	tamam.text(txt); //TUŞUN YAZISINI DEĞİŞTİR.
				this.tusSatir.append(tamam)
				
				tamam.fadeIn(300);
				if(acDurum)	this.tusSatir.show(300);
				else		this.tusSatir.css("display","block");
				
				
			}
			return this;
		}
		altNesne.secimTus 	= function(tetikle,tus1yazi,tus2yazi)
		{	if(!secimTus)	//PENCEREYE SEÇİM TUŞU KONMAMIŞSA SEÇİM TUŞU KONABİLİR
			{	secimTus 	= true;		//BİR DAHA SEÇİM TUŞ EKLENEMEMESİ İÇİN
				
				var evet 	= $("<button>").text("Evet").addClass("jw-t-standart").click(altNesne.kapat).click(function(){ tetikle(1) }).css("display","none");
				var hayir	= $("<button>").text("Hayır").addClass("jw-t-standart").click(altNesne.kapat).click(function(){ tetikle(0) }).css("display","none");;
				
				if(tus1yazi!=undefined)	evet.text(tus1yazi);
				if(tus2yazi!=undefined)	hayir.text(tus2yazi);
				
				this.tusSatir.append(evet);
				this.tusSatir.append(hayir);
				
				evet.fadeIn(300); hayir.fadeIn(300);
				if(acDurum){	this.tusSatir.show(300); }
				else	   {	this.tusSatir.css("display","block"); }		
			}
			return this;
		}
		altNesne.tusEkle 	= function(txt,tetikle)
		{	
			var tus	= $("<button>").text("Tamam").addClass("jw-t-standart").css("display","none");
			if(txt != undefined && txt != false) tus.text(txt);						//TUŞUN YAZISI DEĞİŞSİN
			if(typeof(tetikle)=="function") 	 tus.click(function(){ tetikle()}); //TAMAM TUŞUNA BASILDIĞINDA TETİKLENECEK BİR FONKSİYON VAR İSE
			this.tusSatir.append(tus);
			
			tus.fadeIn(300);
			if(acDurum)	{ this.tusSatir.show(300); }
			else		this.tusSatir.css("display","block");

			return this;
		}
		
		altNesne.efekt 				= function(tip,hiz)
		{	efektTip = tip;
			if(hiz!=undefined && hiz!=false){ efektHiz = hiz; }
			this.cerceve.attr("efekt",tip);
			return this;
		}
		
		altNesne.kapaninca			= function(x)
		{	kapaninca = x;
			return this;
		}

		//STATİK PENCERE FİZİKİ OLARAK KONUMLANDIR ----------------------------------------------
		altNesne.ac = function()
		{	 
			//KAPATILMIŞ VEYA AYNI NOYA SAHİP VEYA AYNI KODA SAHİP BİR PENCERE NESNESİ AÇILAMAZ
			if(cerceve.attr("no")==undefined && acDurum==null && kodluPencereAcikmi==false)
			{	//NO ÜRETİM VE ATAMALAR
				{	var acikSPenAdet	= $("#jw-statik .jw-s-cerceve[no]").length;
					if(count(s.kPNO)>0){ no = diziIlkElm(s.kPNO); delete(s.kPNO[no]); }	//EĞER DAHA ÖNCE KAPATILMIŞ PENCERE VARSA İLK KAYITLI PENCERENİN NO'SUNU AL VE KAPATILANLAR LİSTESİNDEN NO'YU SİL
					else				 no = acikSPenAdet;								//YOKSA ACIK PENCERE ADETİ'Nİ NO OLARAK KULLAN
					
					s.pencere[no] = this;		//PENCERENİN HER AN ERİŞİMİ İÇİN GEREKLİ DİZİYE KAYDI
					this.cerceve.attr("no",no);	//cerceve ALANINA no ÖZNİTELİK OLARAK EKLENİYOR
					this.no = no;				//PENCERENİN NO DEĞERİNİ DIŞARDAN ÖĞRENMEK İSTERSEK BU ÖZELLİKLE ÖĞRENEBİLİRİZ
				}
				
				//İLK AÇILAN PENBCERE İSE STATİK ALAN GÖRÜNÜR HALE GELİYOR!
				if(acikSPenAdet==0)	$("#jw-statik").css({display:"block"})
				
				//PENCERE FİZİKİ OLARAK KONUMLANDIRILIYOR
				{	
					$("#jw-statik").append(this.cerceve);
					
					//PERDE KONUMLANDIRMA	
					this.cerceve.append(this.perde.append(this.perdeTABLE.append(this.perdeTR.append(this.perdeTD))));
					
					//PENCERE KONUMLANDIRMA
					this.pencere
					.append(this.baslikSatir.append(this.baslikDIV).append(this.kapatTus))
					.append(this.govde.append(this.govdeTABLE.append(this.govdeTR.append(this.icerikTD.append(this.icerikDIV1).append(this.icerikDIV2)))))
					.append(this.tusSatir);
		
					//PENCERE PERDEDEN HEMEN SONRA KONUMLANDIRILIYOR
					this.cerceve.append(this.pencereTABLE.append(this.pencereTD.append(this.pencere)));
					
					//PENCERENİN AÇILMA DURUMUNU SAKLADIĞI GLOBAL DEĞİŞKEN
					acDurum = 1;
					
					//PENCERE VE PERDE EFEKT İLE GÖRÜNÜR OLUYOR
					this.cerceve.fadeIn(efektHiz);
					
					//PENCERE SEÇİLEN EFEKT İLE AÇILACAK
					switch(efektTip)
					{	case 1 : this.pencere.fadeIn(efektHiz);	  break;
						case 2 : this.pencere.show(efektHiz);	  break;
						case 3 : this.pencere.slideDown(efektHiz); break;
						default: this.pencere.slideDown(efektHiz); break;
					}
					
					//PENCERE AÇILDIĞI ANDA BODY ALANININ SCROLUNU PASİFLEŞTİREN FONKSİYON.
					websiteFixProcess.pasif();
				}
			}
			
			return this;
		}
	
		//STATİK PENCERE KAPAT ------------------------------------------------------------------
		altNesne.kapat = function()
		{			
			//PENCERELER TAM AÇILMADAN KAPATILIRSA AÇILMASI BOŞUNA BEKLENMESİN!
			cerceve.stop(); pencere.stop();

			//İLGİLİ GLOBALLERDE ve ÖZNİTELİKTE VERİ DÜZENLEMELERİ
			{	acDurum		= false;	//PENCERE AÇILMA DURUMU GLOBAL DEĞİŞKENİ
				s.kPNO[no]	= no;		//KAPATILAN PENCERELER ARASINA EKLE
				delete(s.pencere[no])	//PENCEREYİ ERİŞİM DİZİSİNDEN ÇIKART
				cerceve.removeAttr("no");	//HTML TAGINDAN no ÖZNİTELİĞİ PENCERE KAPANMA EFEKTİNE BAŞLADIĞI ANDA KALDIRILIYOR
										//BU KAPANMA EFEKTİ SIRASINDA YENİ AÇILAN PENCEREDE no ÜRETİMİNDE HESAPLAMA HATASI OLUŞMASINI ENGELLER.
			}
			
			//PENCERE EFEKT İLE GÖRÜNMEZ OLDUKTAN SONRA ÇALIŞTIRILACAK FONKSİYON.
			function kapat()
			{	cerceve.remove(); 															//cerceveYİ - PENCEREYİ FİZİKİ OLARAK KALDIR
				
				var acikSPenAdet 	= $("#jw-statik 	.jw-s-cerceve[no]")		 	 .length; 		//TOPLAM AÇIK STATİK PENCERE ADETİ
				var acikDPentAdet 	= $("#jw-dinamik 	.jw-d-pencere[no][tamekran]").length		//TOPLAM TAM EKRAN MODUNDA AÇIK DİNAMİK PENCERE ADETİ
				var acikDPentkAdet	= $("#jw-dinamik 	.jw-d-pencere[no][tamekran][kucuk]").length	//TOPLAM TAM EKRAN MODUNDA ve KÜÇÜK AÇIK DİNAMİK PENCERE ADETİ
				
				if(acikSPenAdet==0)										$("#jw-statik").css({display:"none"})	//EKRANDA DAHA STATİK PENCERE YOKSA STATİK ALANI GÖRÜNMEZ YAP Kİ ARKA PLANDAKİ ALANLA KULLANICI ETKİLEŞSİN
				if((acikSPenAdet + acikDPentAdet - acikDPentkAdet)<1) 	websiteFixProcess.aktif();				//EĞER HİÇ AÇIK STATİK ve TAMEKRAN MODUNDA DİNAMİK PENCERE YOKSA BODY ALANI SCROLUNU ESKİ HALİNE GETİR.

				
				if(kapaninca) kapaninca(); //PENCERE KAPANDIĞINDA ÇALIŞACAK FONKSİYON VARSA ÇALIŞTIRILIYOR.
			}
			
			cerceve.fadeOut(efektHiz,kapat);	//cerceve SAYDAMLAŞARAK YOK OLUYOR.
			switch(efektTip)					//PENCERE SEÇİLMİŞ EFEKT İLE YOK OLUYOR.
			{	case 1 : pencere.fadeOut(efektHiz); break;
				case 2 : pencere.hide(efektHiz);    break;
				case 3 : pencere.slideUp(efektHiz); break;
				default: pencere.slideUp(efektHiz); break;
			}	
		}
		
		return altNesne;
	}

	//STATİK PENCERE ERİŞİM ---------------------------------------------------------------------
	var statikPencereEris = this.statikPencereEris = function()
	{	var altNesne = new Object;
		
		altNesne.no = function(no)		//PENCERE NOSUYLA PENCEREYE ERİŞİLEBİLİR.
		{	if(s.pencere[no] != undefined)	return s.pencere[no];
			else 							return false;
		}
		
		altNesne.kod = function(kod)	//PENCERE AÇILIRKEN KOD VERİLMİŞ İSE BU BİLGİYLE PENCEREYE SEÇİLEBİLİR.
		{	var pencereSec 	= $("#jw-statik .jw-s-cerceve[kod='"+kod+"'][no]");
			var no 			= pencereSec.attr("no");

			if(no!=undefined) 	return s.pencere[no];
			else 				return false;
		}
		
		altNesne.sira = function(sira)	//PENCERENİN SIRASINA GÖRE SEÇİM YAPILABİLİR
		{	var pencereSec,no=false;
			switch(sira)
			{	case "max" 	: pencereSec = $("#jw-statik .jw-s-cerceve[no]").eq(-1); 	break;
				case "min" 	: pencereSec = $("#jw-statik .jw-s-cerceve[no]").eq(0); 	break;
				default		: pencereSec = $("#jw-statik .jw-s-cerceve[no]").eq(sira); 	break;
			}
			
			if(pencereSec)	no = pencereSec.attr("no");
			if(no)		return 	s.pencere[no];
			else		return false;
		}
		
		altNesne.tur = function(tur)	//TÜR BİLGİSİ PENCERE AÇILIRKEN VERİLMİŞ İSE ÇOĞUL BİR ŞEKİLDE PENCERELERE ERİŞİLEBİLİR.
		{	var pencerelerSec	= false;
			var pencere			= new Array();
			
			pencerelerSec = $("#jw-statik .jw-s-cerceve[no][tur='"+tur+"']");
			pencerelerSec.each(function()
			{	var no 		= $(this).attr("no")
				pencere[no] = s.pencere[no];
			})
			if(pencere.length>0) 	return pencere;
			else					return false;			
		}

		return altNesne;
	}

	//STATİK PENCERE KAPAT ----------------------------------------------------------------------
	this.statikPencereTumunuKapat = function()
	{	var pencere = $("#jw-statik .jw-s-cerceve[no]").each(function()
		{	var no = $(this).attr("no");
			s.pencere[no].kapat();
		})
	}	
	
	//DİNAMİK PENCERE ---------------------------------------------------------------------------
	this.dinamikPencere = function(kod,tur)
	{	
		//DİNAMİK PENCERE GLOBAL DEĞİŞKENLER ----------------------------------------------------
		var altNesne 			= new Object;	//PENCERE İŞLEMLERİ İÇİN ALT METOD ve ÖZELLİK YARATMAK ADINA KULLANILIYOR
		var teoKB 				= new Object;	//TAM EKRAN ÖNCESİ PENCERE KONUM BOYUT
		var skoKB				= new Object;	//SİMGE DURUMUNA KÜÇÜLTÜLME ÖNCESİ PENCERE KONUM BOYUT
		var olay				= new Object;	//OLAYLAR GLOBAL NESNE
		
		var izin				= new Object;	//BAZI İZİNLER İÇİN GLOBAL NESNE
			izin.kucult			= true;
			izin.temsil			= true;
		
		var limit				= new Object;	//PENCERENİN EN BOY LİMİTİ
			limit.min 			= new Object;	
				limit.min.en 	= 200;
				limit.min.boy 	= 120; 
			limit.max			= new Object;
				limit.max.en 	= false;
				limit.max.boy 	= false;
		
		var efektHiz				= new Object;
			efektHiz.ac				= 200;
			efektHiz.kapat			= 200;
			efektHiz.tamEkran		= 75;
			efektHiz.tamEkranTers	= 75;
			efektHiz.kucult			= 300;
			efektHiz.buyut			= 300;
		
		var efektTip				= new Object;
			efektTip.ac				= 1;
			efektTip.kapat			= 1;
			
	
		var no=false , acDurum=null , kapaninca=false , kodluPencereAcikmi=false;
		
		//KOLAY KULLANIM İÇİN ÖZELLİKLERİ(HTML TAGLARINI) GLOBALLEŞİREN DEĞİŞKENLER
		var pencere , temsilKutu , temsilDIV2 , govde;
	
		//DİNAMİK PENCERE HTML TAGLARINI OLUŞTUR ------------------------------------------------
		altNesne.htmlYarat = function()
		{	
			this.pencere					= $("<div>")	.addClass("jw-d-pencere"); 			pencere 	= this.pencere;
				this.baslikSatir			= $("<div>")	.addClass("jw-d-baslikSatir");		baslikSatir	= this.baslikSatir;
					this.baslikSatirTABLE	= $("<table>")	.addClass("jw-d-baslikSatirTABLE");
					this.baslikSatirTR		= $("<tr>");
					this.baslikTD			= $("<td>")		.addClass("jw-d-baslikTD");
					this.baslikDIV			= $("<div>")	.addClass("jw-d-baslikDIV");
					this.tusTD				= $("<td>")		.addClass("jw-d-tusTD");
					this.tusDiv				= $("<div>")	.addClass("jw-d-tusDIV");
					this.kucultTus			= $("<img>")	.addClass("jw-d-tus1").attr("src",jw_konum+"jswindow/resim/kucult.png").attr("title","Küçült");
					this.tamEkranTus		= $("<img>")	.addClass("jw-d-tus1").attr("src",jw_konum+"jswindow/resim/tamekran.png").attr("title","Tam Ekran");
					this.kapatTus			= $("<img>")	.addClass("jw-d-tus1").attr("src",jw_konum+"jswindow/resim/carpi.png").attr("title","Kapat");
				
				this.govdeCerceve			= $("<div>")	.addClass("jw-d-govdeCerceve");
				this.govde					= $("<div>")	.addClass("jw-d-govde"); 			govde = this.govde;
					this.govdePerde			= $("<div>")	.addClass("jw-d-govdePerde");
					this.govdeTABLE			= $("<table>")	.addClass("jw-d-govdeTABLE");
					this.govdeTR			= $("<tr>");
					this.icerikTD			= $("<td>")		.addClass("jw-d-icerikTD");

				this.boyutla				= $("<div>")	.addClass("jw-d-boyutla");
					this.boyutlaSag			= $("<div>")	.addClass("jw-d-boyutlaSag");
					this.boyutlaAlt			= $("<div>")	.addClass("jw-d-boyutlaAlt");
					this.boyutlaKose		= $("<div>")	.addClass("jw-d-boyutlaKose").html
					(	'<div class="jw-d-boyutlaKN" style="right:8px; 	bottom:8px;"></div>'+
						'<div class="jw-d-boyutlaKN" style="right:10px; bottom:8px;"></div>'+
						'<div class="jw-d-boyutlaKN" style="right:12px; bottom:8px;"></div>'+
						'<div class="jw-d-boyutlaKN" style="right:14px; bottom:8px;"></div>'+
						'<div class="jw-d-boyutlaKN" style="right:8px; 	bottom:10px;"></div>'+
						'<div class="jw-d-boyutlaKN" style="right:8px; 	bottom:12px;"></div>'+
						'<div class="jw-d-boyutlaKN" style="right:8px; 	bottom:14px;"></div>'+
						'<div class="jw-d-boyutlaKN" style="right:10px;	bottom:10px;"></div>'+
						'<div class="jw-d-boyutlaKN" style="right:10px; bottom:12px;"></div>'+
						'<div class="jw-d-boyutlaKN" style="right:12px; bottom:10px;"></div>'
					);

			//DİNAMİK PENCEREYİ TEMSİL EDEN KUTU ------------------------------------------------
			this.temsilKutu					= $("<div>").addClass("jw-d-temsilKutu"); temsilKutu = this.temsilKutu;
			{	this.temsilDIV2				= $("<div>").addClass("jw-d-temsilDIV2"); temsilDIV2 = this.temsilDIV2;
				this.temsilDIV1				= $("<div>").addClass("jw-d-temsilDIV1");
				this.temsilSimgeDIV 		= $("<div>").addClass("jw-d-temsilSimgeDIV").append(jw_d_Simge1());
				this.temsilBaslikDIV		= $("<div>").addClass("jw-d-temsilBaslikDIV");
				this.temsilKapatDIV			= $("<div>").addClass("jw-d-temsilKapatDIV").attr("title","Kapat");
				this.temsilKapatTus			= $("<img>").addClass("jw-d-temsilKapatTus").attr("src",jw_konum+"jswindow/resim/carpi2.png");
			}

			//OLAYLAR
			olay.surukle 		= function(){ surukle.mBas(pencere) }
			olay.tamEkran		= function(){ altNesne.tamEkranVeTers() }
			olay.kucult			= function(){ altNesne.kucult() }
			olay.temsilKutuBas	= function(){ altNesne.temsilKutuBas() }
			olay.oneAl			= function(){ altNesne.oneAl() }
			olay.ekranDisindaysaGetir = function(){ altNesne.ekranDisindaysaGetir(); }
			
			//OLAY VE ÖZNİTELİK ATAMA
			this.pencere.attr("kod",kod).attr("tur",tur).on("mousedown",olay.oneAl);
			this.baslikTD.mousedown(olay.surukle);
			this.baslikSatir.dblclick(olay.tamEkran);
			this.boyutlaKose.mousedown(function(){ boyutla.mBas(govde,undefined,limit) 	  });
			this.boyutlaSag.mousedown(function() { boyutla.mBas(govde,"x",limit) });
			this.boyutlaAlt.mousedown(function() { boyutla.mBas(govde,"y",limit) });
			this.kapatTus.click(altNesne.kapat);
			this.tamEkranTus.click(olay.tamEkran);
			this.kucultTus.click(olay.kucult);
			this.temsilKutu.click(olay.temsilKutuBas);
			this.temsilKapatDIV.click(altNesne.kapat);

			return this;
		}

		//DİNAMİK PENCERE KULLANIMI SAĞLAYAN METODLAR -------------------------------------------
		altNesne.kod				= function(kod)
		{	kodluPencereAcikmi = dinamikPencereEris().kod(kod);
			if(!kodluPencereAcikmi)	this.pencere.attr("kod",kod);	//BU KODA SAHİP BAŞKA BAŞKA BİR PENCERE AÇIK DEĞİLSE HTML TAGINA KOD ÖZNİTELİĞİNE KOD'U KAYDET

			return this;
		}
		altNesne.tur				= function(tur)	{ this.pencere.attr("tur",tur); return this; }
		altNesne.minEn				= function(px)	{ limit.min.en  = px;			return this; }
		altNesne.minBoy				= function(px)	{ limit.min.boy = px;			return this; }
		altNesne.maxEn				= function(px)	{ limit.max.en  = px;			return this; }
		altNesne.maxBoy				= function(px)	{ limit.max.boy = px; 		return this; }
		altNesne.en					= function(px)	{ var px = dpEnBoyLimitKontrol("en",px);  this.govde.css("width",px);   return this; }
		altNesne.boy				= function(px)	{ var px = dpEnBoyLimitKontrol("boy",px); this.govde.css("height",px);	return this; }
		altNesne.x					= function(px)	{ this.pencere.css("left",px);		return this; }
		altNesne.y					= function(px)	{ this.pencere.css("top",px);		return this; }
		
		altNesne.baslik				= function(veri, css1 , css2)
		{	var cssDurum = 0;
			if(typeof(css1)=="string" && typeof(css2)=="string"){	this.baslikDIV.css(css1,css2); 	cssDurum=1; }//İKİ DEĞİŞKEN STRİNG OLARAK BELİRTİLMİŞ İSE JQUERY İÇİN İKİ DEĞİŞKEN MODELİ CSS UYGULA
			else if(typeof(css1)=="object")						{	this.baslikDIV.css(css1); 	 	cssDurum=1; }//DEĞİLSE VE İLK DEĞİŞKEN NESNE İSE JQUERY İÇİN NESNESEL CSS UYGULA
			
			if(typeof(veri)=="string" || typeof(veri)=="number")	this.baslikDIV.html(veri);		//VERİ YAZI İSE HTML OLARAK OLARAK EKLENİR
			else if(typeof(veri)=="object")							this.baslikDIV.append(veri);	//VERİ NESNE İSE APPEND İLE EKLENİR
			else if(!cssDurum)										return this.baslikDIV.html();	//VERİ VE CSS YOKSA İÇERİK GERİ DÖNDÜRÜLÜR
			
			return this;
		}
		altNesne.icerik 			= function(veri, css1, css2)
		{ 	var cssDurum = 0;
			if(typeof(css1)=="string" && typeof(css2)=="string"){	this.icerikTD.css(css1,css2); cssDurum=1; }//İKİ DEĞİŞKEN STRİNG OLARAK BELİRTİLMİŞ İSE JQUERY İÇİN İKİ DEĞİŞKEN MODELİ CSS UYGULA
			else if(typeof(css1)=="object")						{	this.icerikTD.css(css1); 	  cssDurum=1;  }//DEĞİLSE VE İLK DEĞİŞKEN NESNE İSE JQUERY İÇİN NESNESEL CSS UYGULA
			
			if(typeof(veri)=="string" || typeof(veri)=="number")	this.icerikTD.html(veri);		//VERİ YAZI İSE HTML OLARAK OLARAK EKLENİR
			else if(typeof(veri)=="object")							this.icerikTD.append(veri);		//VERİ NESNE İSE APPEND İLE EKLENİR
			else if(!cssDurum)										return this.icerikTD.html();	//VERİ VE CSS YOKSA İÇERİK GERİ DÖNDÜRÜLÜR

			return this;
		}
		
		altNesne.baslikCss			= function(css1, css2)
		{	if(typeof(css1)=="string" && typeof(css2)=="string")	this.baslikDIV.css(css1,css2)	//İKİ DEĞİŞKEN STRİNG OLARAK BELİRTİLMİŞ İSE JQUERY İÇİN İKİ DEĞİŞKEN MODELİ CSS UYGULA
			else if(typeof(css1)=="object")							this.baslikDIV.css(css1)		//DEĞİLSE VE İLK DEĞİŞKEN NESNE İSE JQUERY İÇİN NESNESEL CSS UYGULA
			return this;
		}
		altNesne.icerikCss			= function(css1, css2)
		{	if(typeof(css1)=="string" && typeof(css2)=="string")	this.icerikTD.css(css1,css2)	//İKİ DEĞİŞKEN STRİNG OLARAK BELİRTİLMİŞ İSE JQUERY İÇİN İKİ DEĞİŞKEN MODELİ CSS UYGULA
			else if(typeof(css1)=="object")							this.icerikTD.css(css1)			//DEĞİLSE VE İLK DEĞİŞKEN NESNE İSE JQUERY İÇİN NESNESEL CSS UYGULA
			return this;
		}
		altNesne.css				= function(css1, css2)
		{	//PENCERE ANA TAGININ CSS İŞLEMLERİ
			if(typeof(css1)=="string" && typeof(css2)=="string")	this.pencere.css(css1,css2)	//İKİ DEĞİŞKEN STRİNG OLARAK BELİRTİLMİŞ İSE JQUERY İÇİN İKİ DEĞİŞKEN MODELİ CSS UYGULA
			else if(typeof(css1)=="object")							this.pencere.css(css1)		//DEĞİLSE VE İLK DEĞİŞKEN NESNE İSE JQUERY İÇİN NESNESEL CSS UYGULA
			return this;
		}
		
		//EFEKT TİP VE HIZ DEĞİŞİM ------------------------------------------------------------------------------------------
		altNesne.acEfekt			= function(tip,hiz)
		{	efektTip.ac = tip;
			if(hiz!=undefined){ efektHiz.ac = hiz; }
			return this;
		}
		altNesne.kapatEfekt			= function(tip,hiz)
		{	efektTip.kapat = tip;
			if(hiz!=undefined){ efektHiz.kapat = hiz; }
			return this;
		}
		altNesne.kucultEfekt		= function(hiz){	if(hiz!=undefined){ efektHiz.kucult = hiz; }		return this;	}
		altNesne.buyutEfekt			= function(hiz){	if(hiz!=undefined){ efektHiz.buyut = hiz; }			return this;	}
		altNesne.tamEkranEfekt		= function(hiz){	if(hiz!=undefined){ efektHiz.tamEkran = hiz; }		return this;	}
		altNesne.tamEkranTersEfekt	= function(hiz){	if(hiz!=undefined){ efektHiz.tamEkranTers = hiz; }	return this;	}
		//-------------------------------------------------------------------------------------------------------------------

		altNesne.sinifEkle			= function(cls)	{ this.pencere.addClass(cls); return this; }
		altNesne.sinifKaldir		= function(cls)	{ this.pencere.removeClass(cls); return this; }
		
		altNesne.baslikSatirKapat 	= function()	{ e = this.baslikSatir; e.slideUp(function(){ e.css("display","none") }); return this; }
		altNesne.baslikSatirAc	 	= function()	{ this.baslikSatir.slideDown(); return this; }
		
		altNesne.kapatPasif			= function()
		{	var e1 = this.kapatTus; var e2 = this.temsilKapatDIV;
			e1.hide(300,function(){ e1.css("display","none"); });
			e2.hide(300,function(){ e2.css("display","none"); });
			return this;
		}
		altNesne.kapatAktif 		= function(){ this.kapatTus.show(300); this.temsilKapatDIV.show(300); return this; }
		
		altNesne.tamEkranPasif		= function()
		{	var a = this.tamEkranTus;
			a.hide(300,function(){ a.css("display","none") });
			this.baslikSatir.off("dblclick",olay.tamEkran);
			return this;
		}
		altNesne.tamEkranAktif		= function()
		{	this.tamEkranTus.show(300);

			this.baslikSatir.on("dblclick",olay.tamEkran);
			return this;
		}
		
		altNesne.kucultPasif		= function()
		{	izin.kucult = false;
			var a = this.kucultTus;
			a.hide(300,function(){ a.css("display","none") })
			return this;
		}
		altNesne.kucultAktif		= function()
		{	izin.kucult = true;
			this.kucultTus.show(300)
			return this;
		}
		
		altNesne.temsilPasif		= function()
		{	altNesne.kucultPasif(); izin.temsil=false;
			if(acDurum)	temsilKutu.hide(efektHiz.kapat);
			return this;
		}
		altNesne.temsilAktif		= function()
		{	altNesne.kucultAktif(); izin.temsil=true;
			if(acDurum)	temsilKutu.show(efektHiz.ac);
			return this;
		}
		
		altNesne.boyutlaPasif		= function(a)
		{	if(a==undefined)
			{	var e = this.boyutlaKose; 	e.hide(300, function(){ e.css("display","none") });
				this.boyutlaSag.css("display","none");
				this.boyutlaAlt.css("display","none");
				return this;
			}
			else

			{	if(a=="x")
				{	var e = this.boyutlaSag;	e.hide(300, function(){ e.css("display","none") });
					var b = this.boyutlaKose;	b.hide(300, function(){ b.css("display","none") });
					return this;
				}
				else if(a=="y")
				{	var e = this.boyutlaAlt;	e.hide(300, function(){ e.css("display","none") });
					var b = this.boyutlaKose;	b.hide(300, function(){ b.css("display","none") });
					return this;
				}
			}
		}
		altNesne.boyutlaAktif		= function(a)
		{	if(a==undefined)
			{	this.boyutlaKose.show(300);
				this.boyutlaSag.css("display","block");
				this.boyutlaAlt.css("display","block");
				return this;
			}
			else
			{	if(a=="x")
				{	//DiĞER KENARDA AKTİF İSE KÖŞEYİ AKTİF ET
					if(this.boyutlaAlt.css("display")!="none")	this.boyutlaKose.show(300);
					this.boyutlaSag.css("display","block");
					return this;
				}
				else if(a=="y")
				{	//DiĞER KENARDA AKTİF İSE KÖŞEYİ AKTİF ET
					if(this.boyutlaSag.css("display")!="none")	this.boyutlaKose.show(300);
					this.boyutlaAlt.css("display","block");
					return this;
				}
			}
		}

		altNesne.ortala 			= function(hiz,yon)
		{	if(hiz==undefined) hiz=300;
			var w = new Object;
			var p = new Object;
			
			w.boy	= $(window).height();
			w.en	= $(window).width();
			w.st	= $(window).scrollTop();
			w.sl	= $(window).scrollLeft();
			p.en	= this.pencere.width();
			p.boy	= this.pencere.height();
			
			var fY	= p.boy - p.gBoy;
			var fX	= p.en - p.gEn;
			
			//EĞER WEBSİTE ANA TAGI JSWINDOW TARAFINDAN SABİTLENMİŞ İSE SCROLL DEĞERİ TOP DEĞERİNE - OLARAK YANSITILMIŞ DEMEKTİR
			//BU DURUMDA BU -TOP DEĞERİ SANKİ SCROLUN DEĞERİYMİŞ GİBİ GÖSTERELİM VE SCROL DEĞİŞKENİNE BU DEĞERİ ATAYALIM.
			var wsHT= parseInt($("#"+jw_website_cerceve_id).css("top"));
			if(!isNaN(wsHT))	w.st = w.st-wsHT;
			
			var top  = ((w.boy-p.boy)/2)+w.st;
			var left = ((w.en-p.en)/2)+w.sl;
			
			if(top<0)	top	 = 0; //ORTALANDIĞINDA PENCERE ÜSTTEN SIĞMIYORSA ÜSTE SIFIRLA
			if(left<0)	left = 0; //OTALANDIĞINDA PENCERE SOLDAN SIĞMIYORSA SOLA SIFIRLA
			
			var e = this.pencere;
			if(acDurum==null)
			{	if(yon==undefined)	e.css("top",top).css("left",left);
				else if(yon=="y")	e.css("top",top);
				else if(yon=="x")	e.css("left",left);
			}
			else if(acDurum)
			{	if(yon==undefined)	e.animate({ "top":top , "left":left },hiz);
				else if(yon=="y")	e.animate({ "top":top },hiz);
				else if(yon=="x")	e.animate({ "left":left },hiz);
			}

			return this;
		}
		
		altNesne.altaYapistir = function()
		{	
			var penH  		= pencere.height();
			var penWH 		= $(window).height();
			var docST 		= $(document).scrollTop();
			var wsHT  		= parseInt($("#"+jw_website_cerceve_id).css("top"));
			
			//EĞER WEBSİTE ANA TAGI JSWINDOW TARAFINDAN SABİTLENMİŞ İSE SCROLL DEĞERİ TOP DEĞERİNE - OLARAK YANSITILMIŞ DEMEKTİR
			//BU DURUMDA BU -TOP DEĞERİ SANKİ SCROLUN DEĞERİYMİŞ GİBİ GÖSTERELİM VE SCROL DEĞİŞKENİNE BU DEĞERİ ATAYALIM.
			if(!isNaN(wsHT))	docST = docST-wsHT;
			
			var penTargetTop = (penWH-penH)+docST;
			
			this.pencere.animate({top:penTargetTop},250);
		}
		
		altNesne.usteYapistir = function()
		{	var wsHT  		= parseInt($("#"+jw_website_cerceve_id).css("top"));
			
			//EĞER WEBSİTE ANA TAGI JSWINDOW TARAFINDAN SABİTLENMİŞ İSE SCROLL DEĞERİ TOP DEĞERİNE - OLARAK YANSITILMIŞ DEMEKTİR
			//BU DURUMDA BU -TOP DEĞERİ SANKİ SCROLUN DEĞERİYMİŞ GİBİ GÖSTERELİM VE SCROL DEĞİŞKENİNE BU DEĞERİ ATAYALIM.
			if(!isNaN(wsHT))	var target = -wsHT;
			else				var target = $(document).scrollTop();
			
			this.pencere.animate({top:target},250);
		}
		
		altNesne.ekranDisindaysaGetir	= function()
		{		
			//PENCERE KONUM DURUM DEĞERLENDİRMESİ İÇİN GEREKLİ DEĞERLERİ ALIYORUZ -----------------------------------------------
			var penPT = pencere.position().top;
			var penH  = pencere.height();
			var penWH = $(window).height();
			var docST = $(document).scrollTop();
			var wsHT  = parseInt($("#"+jw_website_cerceve_id).css("top")); //WEBSİTE ANA TAGININ TOP DEĞERİ
			
			//EĞER WEBSİTE ANA TAGI JSWINDOW TARAFINDAN SABİTLENMİŞ İSE SCROLL DEĞERİ TOP DEĞERİNE - OLARAK YANSITILMIŞ DEMEKTİR
			//BU DURUMDA BU -TOP DEĞERİ SANKİ SCROLUN DEĞERİYMİŞ GİBİ GÖSTERELİM VE SCROL DEĞİŞKENİNE BU DEĞERİ ATAYALIM.
			if(!isNaN(wsHT))	docST = docST-wsHT;
			//-------------------------------------------------------------------------------------------------------------------
					
			var usttenTasma 		= (penPT < docST);
			var alttanTasma 		= ((penPT-docST+penH) > penWH);
			
			var usteYapistir = function(){	pencere.animate({ top:docST }				 , 250);	}
			var altaYapistir = function(){	pencere.animate({ top:((penWH-penH)+docST) } , 250); 	}
			
			
			var result = false;
			
			//PENCERENİN KONUM DEĞERLENDİRMESİ VE UYGULANACAK DAVRANIŞLAR -------------------------------------------------------
			if(usttenTasma || alttanTasma)
			{	//PENCERE HERHANGİ BİR ŞEKİLDE EKRANDAN TAŞIYORSA
				
				if(usttenTasma && !alttanTasma)
				{	//PENCERE SADECE ÜSTTEN TAŞIYORSA
					
					var usttenGorunurAlan 	= penPT-docST+penH;
					var gorunurAlanYetersiz	= (usttenGorunurAlan<200);
					
					if(gorunurAlanYetersiz)
					{	//GÖRÜNÜR ALAN ÇOK AZ İSE
						
						if(penH>penWH)	altaYapistir();	//PENCERE EKRANDAN BÜYÜKSE
						else			usteYapistir();	//PENCERE EKRANDAN KÜÇÜKSE
						
						result = true;
					}
				}
				else if(!usttenTasma && alttanTasma)
				{	//PENCERE SADECE ALTTAN TAŞIYORSA
					
					var alttanGorunurAlan	= docST+penWH-penPT;
					var gorunurAlanYetersiz	= (alttanGorunurAlan<200);
					
					if(gorunurAlanYetersiz)
					{	//GÖRÜNÜR ALAN ÇOK AZ İSE
						if(penH>penWH)	usteYapistir();	//PENCERE EKRANDAN BÜYÜKSE
						else			altaYapistir();	//PENCERE EKRANDAN KÜÇÜKSE
						
						result = true;
					}

				}
				else
				{	/*PENCERE HEM ÜSTTEN HEM ALTTAN TAŞIYORSA! BU DURUMDA PENCERE EKRANDAN KESİN BÜYÜK OLMALI VE BİRŞEY 
					  YAPILMAMASI DAHA DOĞRU*/
				}
				
			}
			
			return result;
			//-------------------------------------------------------------------------------------------------------------------
				
		}
		altNesne.simge 	= function(smg)
		{	if(	typeof(smg) == "string" ) this.temsilSimgeDIV.html(smg);
			if( typeof(smg) == "object" )
			{	this.temsilSimgeDIV.html("");
				this.temsilSimgeDIV.append(smg);
			}
			return this;
		}
		altNesne.kapaninca			= function(x)
		{	kapaninca = x;
			return this;
		}
		
	
		//DİNAMİK PENCERE FİZİKİ OLARAK KONUMLANDIR ---------------------------------------------
		altNesne.ac = function()
		{	
			//KAPATILMIŞ VEYA AYNI NO'YA VEYA AYNI KODA SAHİP BİR PENCERE NESNESİ AÇILAMAZ
			if(pencere.attr("no")==undefined && acDurum==null && kodluPencereAcikmi==false)
			{	
				//PENCERE ILE İLGİLİ ÖZEL BİLGİLERİN OLUŞTURULMASI VE ATANMASI		
				var acikDPenAdet	= count(d.pencere);								//AÇIK PENCERE ADET
				var zIndex 			= acikDPenAdet;									//ZINDEX HER ZAMAN PENCERELER ARASINDA SIRALI BİÇİMDE KORUNUR. DOLAYISIYLA AÇIK PENCERE ADETİ ZINDEX'İ BELİRLEYEBİLİR
				
				if(count(d.kPNO)>0){ no = diziIlkElm(d.kPNO); delete(d.kPNO[no]); }	//EĞER DAHA ÖNCE KAPATILMIŞ PENCERE VARSA İLK KAYITLI PENCERENİN NO'SUNU AL VE KAPATILANLAR LİSTESİNDEN NO'YU SİL
				else				 no = acikDPenAdet;								//YOKSA ACIK PENCERE ADETİ'Nİ NO OLARAK KULLAN
				
				//PENCERE d.pencere[no] GLOBAL DİZİSİNE ATANMADAN ÖNCE, ÖNCEDEN AÇIK PENCERE VARSA EN ÜSTTE OLANA PASİF EFEKT UYGULANIYOR
				if(acikDPenAdet>0) dinamikPencereEris().zIndex("max").pasifPencereEfekt(); //ATANMADAN ÖNCE UYGULANMALI ÇÜNKÜ dinamikPencereEris() METODU d.pencere[no] GLOBALİNİ KULLANIR
				
				d.pencere[no] = this;								//PENCERENİN HER AN ERİŞİMİ İÇİN GEREKLİ GLOBAL DİZİYE KAYDI
				this.pencere.css("z-index",zIndex).attr("no",no);	//PENCERE ANA TAGINA no ÖZNİTELİK OLARAK EKLENİYOR
				this.no = no;										//DIŞARDA PENCERE NOSUNU ÖĞRENEBİLECEĞİMİZ ÖZELLİK
				
				//PENCERE FİZİKİ OLARAK KONUMLANDIRILIYOR
				{
					//EN ÜST BÖLÜM BAŞLIK SATIRI İÇ YAPISI KONUMLANDIRILIYOR
					this.baslikSatir.append(
						this.baslikSatirTABLE.append(
							this.baslikSatirTR.append(
								this.baslikTD.append(
									this.baslikDIV )).append(
								this.tusTD.append(
									this.tusDiv.append(
										this.kucultTus ).append(
										this.tamEkranTus ).append(
										this.kapatTus )))))
					
					//GÖVDE BÖLÜMÜ İÇ YAPISI KONUMLANDIRILIYOR
					this.govdeCerceve.append(this.govde.append(
						this.govdePerde ).append( 
						this.govdeTABLE.append(
							this.govdeTR.append(
								this.icerikTD ))))
					
					//PENCERE BOYUTLANDIRMA BÖLÜMÜ İÇ YAPISI KONUMLANDIRILIYOR
					this.boyutla.append( this.boyutlaSag  )
								.append( this.boyutlaAlt  )
								.append( this.boyutlaKose )
					
					//ÜSTTE YARATILAN TÜM BÖLÜMLER PENCERE TAGININ İÇİNE KONUMLANDIRILIYOR ve PENCERE FİZİKİ OLARAK VAR EDİLİYOR
					$("#jw-dinamik").append(this.pencere.append(this.baslikSatir)
														.append(this.govdeCerceve)
														.append(this.boyutla));
					
					//PENCERE SIMGE KUTU KONUMLANDIRILIYOR
					$("#jw-dinamikT").append(
						this.temsilKutu.append(
							this.temsilDIV2 ).append(
							this.temsilDIV1.append(
								this.temsilSimgeDIV).append(
								this.temsilBaslikDIV).append(
								this.temsilKapatDIV.append(
									this.temsilKapatTus ))))
				}	
					//PENCERE BOYUTU BELLİ DEĞİLSE MIN DEĞERLERİ VER
					if(parseInt(this.govde.css("width"))<1)	 this.govde.css("width",limit.min.en);
					if(parseInt(this.govde.css("height"))<1) this.govde.css("height",limit.min.boy);
					
					//PENCERE KONUMU BELİRTİLMEMİŞ İSE ORTALIYORUZ.
					var left = this.pencere.css("left"); var top = this.pencere.css("top");
					if((!left || left=="auto") && (!top || top=="auto")){	this.ortala(); 			} //KOORDİNAT BELİRTİLMEMİŞSE PENCEREYİ ORTALIYORUZ
					else if((!left || left=="auto"))					{	this.ortala(false,"x");	} //X BELİRTİLMEMİŞSE X ORTALIYORUZ
					else if((!top || top=="auto"))						{	this.ortala(false,"y");	} //Y BELİRTİLMEMİŞSE Y ORTALIYORUZ
					
					//PENCERE ÜST ÜSTE AÇILMASIN
					{	var penTop 	= parseInt(this.pencere.css("top"));
						var penLeft = parseInt(this.pencere.css("left"));
						
						for(var x in d.pencere)
						{	
							if(x!=no)
							{	var epTop 	= parseInt(d.pencere[x].pencere.css("top"));
								var epLeft 	= parseInt(d.pencere[x].pencere.css("left"));

								if(penTop==epTop)	{ this.pencere.css({top:penTop+20}); 	penTop+=20;  }
								if(penLeft==epLeft)	{ this.pencere.css({left:penLeft+20}); 	penLeft+=20;  }
							}
						}
					}
					
					//PENCERENİN AÇILMA DURUMUNU SAKLADIĞI GLOBAL DEĞİŞKEN
					acDurum = 1;
					
					//TEMSIL KUTU BAŞLIĞINA PENCERE BAŞLIĞI YAZILIYOR
					this.temsilBaslikDIV.html(this.baslikDIV.html())
					
					//PENCERE EFEKT İLE GÖRÜNÜR OLUYOR
					altNesne.aktifPencereEfekt();
					switch(efektTip.ac)
					{	case 1 : pencere.fadeIn(efektHiz.ac);		break;
						case 2 : pencere.show(efektHiz.ac);			break;
						case 3 : pencere.slideDown(efektHiz.ac);	break;
						default: pencere.fadeIn(efektHiz.ac); 		break;
					}
					if(izin.temsil)	temsilKutu.show(efektHiz.ac);	//TEMSİL KUTUSUNA İZİN VERİLMİŞ İSE EFEK İLE PENCEREYLE BERABER GÖRÜNÜR OLUYOR
				
			}
			//BELİRTİLEN KODLU PENCERE ZATEN AÇIK İSE PENCEREYİ AÇMA VE ÖNE AL
			else if(kodluPencereAcikmi)	kodluPencereAcikmi.oneAl();
			return this;
		}

		//DİNAMİK PENCERE KAPAT -----------------------------------------------------------------
		altNesne.kapat = function()
		{	var no			= pencere.attr("no");
			
			//PENCERE FİZİKİ OLARAK VARSA VE KAPANMA EYLEMİ İÇİNDE DEĞİLSE
			if(no)
			{	var acikPenAdet = $("#jw-dinamik .jw-d-pencere[no]").length;
				var maxZIndex	= acikPenAdet-1;
				var zIndex		= parseInt(pencere.css("z-index"));
				var basZIndex	= zIndex+1;	
				
				var pencereTamekranDurum = pencere.attr("tamekran");
				
				//İLGİLİ DİZİLERDE ÖZNİTELİKTE VERİ DÜZENLEMELERİ
				{	acDurum = false;
					delete(d.pencere[no])			//PENCEREYİ ERİŞİM DİZİSİNDEN ÇIKART
					d.kPNO[no] = no;				//KAPATILAN PENCERELER ARASINA EKLE
					pencere.removeAttr("no");		//HTML TAGINDAN no ÖZNİTELİĞİNİ KALDIR Kİ YENİ PENCERE AÇILIRKEN BU PENCERE YOK FARZEDİLSİN.
													//ÇÜNKÜ KAPANIRKEN GEÇİŞ EFEKTİ GECİKME YAŞATIR YENİ AÇILAN PENCEREDE HESAPLAMA HATASI OLUŞUR.
				}
	
				//PENCERE EN ÖNDE KAPATILMAZ İSE KENDİSİNDEN ÖNDEKİLERİN ZINDEX DEĞERİ 1 AZALTILIYOR Kİ ZINDEX ARASI BOSLUK OLMASIN
				for(var i=basZIndex; i<=maxZIndex; i++ )
				{	var zdPencere = $('#jw-dinamik [style*="z-index: '+i+'"][no]');
					zdPencere.css("z-index",(i-1));
				}
				
				//KAPANMA EFEKTİNDEN SONRA PENCEREYİ FİZİKİ OLARAK KALDIRAN FONKSİYON
				var kapat = function()
				{ 	pencere.remove(); 					//FİZİKİ OLARAK PENCEREYİ KALDIRILIYOR
					temsilKutu.remove();				//FİZİKİ OLARAK PENCERE TEMSIL KUTUSU KALDIRILIYOR
					//dpSecEfek(dpSecZIndex("max"));	//PENCERE KAPATILDIKTAN SONRA EN ÖNDEKİ PENCEREYİ SEÇİLMİŞ GÖSTER.
					
					if(kapaninca) kapaninca();			//PENCERE KAPANDIĞINDA ÇALIŞACAK FONKSİYON VARSA ÇALIŞTIRILIYOR.
				}
				
				//EFEKT İLE PENCERE KAPANIYOR
				switch(efektTip.kapat)
				{	case 1 : pencere.fadeOut(efektHiz.kapat,kapat); break;
					case 2 : pencere.hide(efektHiz.kapat,kapat);	  break;
					case 3 : pencere.slideUp(efektHiz.kapat,kapat); break;
					default: pencere.fadeOut(efektHiz.kapat,kapat); break;
				}
				temsilKutu.hide(efektHiz.kapat);
				
				var acikSPenAdet 	= $("#jw-statik 	.jw-s-perde[no]")		 	 .length; 		//TOPLAM AÇIK STATİK PENCERE ADETİ
				var acikDPentAdet 	= $("#jw-dinamik 	.jw-d-pencere[no][tamekran]").length		//TOPLAM TAM EKRAN MODUNDA AÇIK DİNAMİK PENCERE ADETİ
				var acikDPentkAdet	= $("#jw-dinamik 	.jw-d-pencere[no][tamekran][kucuk]").length	//TOPLAM TAM EKRAN MODUNDA ve KÜÇÜK AÇIK DİNAMİK PENCERE ADETİ
				if((acikSPenAdet + acikDPentAdet - acikDPentkAdet)<1 && pencereTamekranDurum==1)	//KAPATILAN PENERE TAM EKRAN İSE, HİÇ AÇIK STATİK ve TAMEKRAN MODUNDA DİNAMİK PENCERE YOKSA.
				{	websiteFixProcess.aktif(); /*process*/
				}
			}
			
			return this;
		}

		//DİNAMİK PENCERE ÖNE AL ----------------------------------------------------------------
		altNesne.oneAl = function()
		{	var acikmi		= pencere.attr("no");	//ÖNE ALINMAK İSTENEN PENCEREMİZ ŞU ANDA AÇIK MI? BELKİ KAPATILMIŞTIR.
			var acikPenAdet = $("#jw-dinamik .jw-d-pencere[no]").length;		
			var maxZIndex	= acikPenAdet-1;
			var zIndex		= parseInt(pencere.css("z-index"));
			
			var ekranDisindami	= this.ekranDisindaysaGetir();	//PENCERE EKRAN DIŞINDAYSA KÜÇÜLME OLMAZ VE PENCERE EKRANA GELİR
			
			if(acikmi!=undefined && zIndex<maxZIndex) //ÖNE ALINACAK PENCERE FİZİKİ OLARAK VARSA ve ZATEN EN ÖNDE DEĞİLSE İŞLEMLERİ YAP
			{	
				var basZIndex	= zIndex+1;
				
				dinamikPencereEris().zIndex("max").pasifPencereEfekt();
				
				for(var i=basZIndex; i<=maxZIndex; i++ )
				{	var zdPencere = $('#jw-dinamik [style*="z-index: '+i+'"][no]');
					zdPencere.css("z-index",(i-1));
				}
				pencere.css("z-index",maxZIndex);
			}
			altNesne.aktifPencereEfekt();
			
			return this;
		}

		//DİNAMİK PENCERE TAM EKRAN -------------------------------------------------------------
		altNesne.tamEkran = function()
		{	
			var pencereVarmi = pencere.attr("no"); //PENCERE FİZİKİ OLARAK VARMI ve AÇIKMI
			
			if(pencereVarmi!=undefined)
			{	
				var tamEkranDurum = pencere.attr("tamekran")
				
				if(tamEkranDurum!=1)
				{	
					//TAMEKRAN OLMADAN VE WINDOW BOYUTLARI ALINMADAN SAYFANIN SCROLU KAPATILIR
					websiteFixProcess.pasif(); /*process*/
					
					//this.pencere.off("mousedown",olay.ekranDisindaysaGetir);
					
					//WINDOW BOYUT VE SCROLL BİLGİLERİ
					var wHeight 	= $(window).height();
					var wWidth		= $(window).width();
					var wScrollT	= $(window).scrollTop();

					//PENCERE BOYUT VE KONUM
					var pH 	= pencere.height();
					var pW 	= pencere.width();
					teoKB.x	= parseInt(pencere.css("left"));
					teoKB.y = parseInt(pencere.css("top"));
					
					//GOVDE BOYUT
					var gH 	= teoKB.boy	= this.govde.height();
					var gW	= teoKB.en	= this.govde.width();
					
					//PENCERE İLE GOVDE ARASINDAKİ FARK
					var farkH	= pH - gH
					var farkW	= pW - gW;
					
					//PENCERENİN AYARLANACAĞI HİZA VE KONUM BİLGİLERİ
					var websiteTop 	= parseInt($("#"+jw_website_cerceve_id).css("top"))
					var hTop 		= -websiteTop+wScrollT;
					var hLeft		= 0;
					var hHeight		= wHeight-farkH-53;
					var hWidth		= wWidth-farkW;
					
					//TAMEKRAN YAPILMADAN ÖNCE ÖZNİTELİKLER DÜZENLENİYOR
					this.tamEkranTus.attr("title","Önceki Boyut");
					this.baslikTD.off("mousedown",olay.surukle);
					this.boyutla.css("display","none");
					this.pencere.attr("tamEkran",1);
					
					//TAM EKRAN ANİMASYONU
					
					var hiz = efektHiz.tamEkran;
					
					this.pencere.animate({top:hTop , left:hLeft} , (hiz*2) );
					govde.animate({ width:hWidth , height:hHeight} , (hiz*2) )
	
					this.pencere.animate({top:(hTop+10) , left:(hLeft+10) } , hiz );
					govde.animate({ width:(hWidth-20) , height:(hHeight-20)} , hiz )

					this.pencere.animate({top:hTop , left:hLeft} , hiz );
					govde.animate({ width:hWidth , height:hHeight} , hiz )

					this.pencere.animate({top:(hTop+3) , left:(hLeft+3) } , hiz );
					govde.animate({ width:(hWidth-6) , height:(hHeight-6)} , hiz )

					this.pencere.animate({top:hTop , left:0} , hiz );
					govde.animate({ width:hWidth , height:hHeight} , hiz )	
				}
			}
			return this;
		}
	
		//DİNAMİK PENCERE TAM EKRAN TERS --------------------------------------------------------
		altNesne.tamEkranTers = function()
		{	var pencereVarmi = pencere.attr("no"); //PENCERE FİZİKİ OLARAK VARMI ve AÇIKMI
			
			if(pencereVarmi!=undefined)
			{	
				var tamEkranDurum = pencere.attr("tamekran")
				
				if(tamEkranDurum==1)
				{	
					this.pencere.on("mousedown",olay.ekranDisindaysaGetir);
					
					var acikSPenAdet 	= $("#jw-statik 	.jw-s-perde[no]")		 	 .length; 		//TOPLAM AÇIK STATİK PENCERE ADETİ
					var acikDPentAdet 	= $("#jw-dinamik 	.jw-d-pencere[no][tamekran]").length		//TOPLAM TAM EKRAN MODUNDA AÇIK DİNAMİK PENCERE ADETİ
					var acikDPentkAdet	= $("#jw-dinamik 	.jw-d-pencere[no][tamekran][kucuk]").length	//TOPLAM TAM EKRAN MODUNDA ve KÜÇÜK AÇIK DİNAMİK PENCERE ADETİ
					if((acikSPenAdet + acikDPentAdet - acikDPentkAdet)<2) 								//EĞER HİÇ AÇIK STATİK ve TAMEKRAN MODUNDA DİNAMİK PENCERE YOKSA BODY ALANI SCROLUNU ESKİ HALİNE GETİR.
					{	websiteFixProcess.aktif(); /*process*/
					}
			
					//PENCERENİN ESKİ BOYUT BİLGİLERİ ALINIYOR
					var pb 	= new Object;
					pb.en	= teoKB.en;
					pb.boy	= teoKB.boy;
					pb.top	= teoKB.y;
					pb.left	= teoKB.x;
					
					//ESKİ BOYUTA GELMEDEN ÖNCE ÖZNİTELİKLER DÜZENLENİYOR
					this.tamEkranTus.attr("title","Tam Ekran"); 
					this.baslikTD.on("mousedown",olay.surukle);
					this.boyutla.css("display","block");
					this.pencere.removeAttr("tamEkran");
					
					//PENCEREMİZ ANİMASYON İLE ESKİ BOYUTUNA GELİYOR
					var hiz = efektHiz.tamEkranTers;
					
					this.pencere.animate({top:pb.top , left:pb.left} , (hiz*2) );
					this.govde.animate({ width:pb.en , height:pb.boy} , (hiz*2) );
		
					this.pencere.animate({top:(pb.top-10) , left:(pb.left-10)} , hiz );
					this.govde.animate({ width:(pb.en+20) , height:(pb.boy+20)} , hiz );
		
					this.pencere.animate({top:pb.top , left:pb.left} , hiz );
					this.govde.animate({ width:pb.en , height:pb.boy} , hiz);
		
					this.pencere.animate({top:(pb.top-3) , left:(pb.left-3)} , hiz );

					this.govde.animate({ width:(pb.en+6) , height:(pb.boy+6)} , hiz );
						
					this.pencere.animate({top:pb.top , left:pb.left} , hiz );
					this.govde.animate({ width:pb.en , height:pb.boy} , hiz ,function()
					{
						altNesne.ekranDisindaysaGetir();
					});

					teoKB = new Object; //PENCEREMEZİN BOYUT BİLGİLERİNİ HAFIZADAN SİLİYORUZ.
				}
			}
			return this;
		}
		
		altNesne.tamEkranVeTers = function()
		{	var pencereVarmi = this.pencere.attr("no"); 
			
			if(pencereVarmi!=undefined)
			{	//PENCERE FİZİKİ OLARAK VARMI ve AÇIKMI
			
				durum = this.pencere.attr("tamEkran");	
				if(durum==1)	altNesne.tamEkranTers()	//TAMEKRAN İSE TERSİNİ YAP
				else			altNesne.tamEkran()		//TAMEKRAN DEĞİL İSE TAMEKRAN YAP
			}
			return this;
		}
		
		altNesne.kucult = function()
		{	if(izin.kucult)
			{	
				var pencereVarmi 	= this.pencere.attr("no"); 		//PENCERE FİZİKİ OLARAK VARMI ve AÇIKMI
				var pencereKucukmu	= this.pencere.attr("kucuk");	//PENCERE KÜÇÜK DURUMU
				var ekranDisindami	= this.ekranDisindaysaGetir();	//PENCERE EKRAN DIŞINDAYSA KÜÇÜLME OLMAZ VE PENCERE EKRANA GELİR
				
				if(pencereVarmi!=undefined && pencereKucukmu==undefined && ekranDisindami==false)
				{	//PENCERE GERÇEKTEN VARSA VE KÜÇÜK DEĞİLSE
				
					var acikSPenAdet 	= $("#jw-statik 	.jw-s-perde[no]")		 	 .length; 		//TOPLAM AÇIK STATİK PENCERE ADETİ
					var acikDPentAdet 	= $("#jw-dinamik 	.jw-d-pencere[no][tamekran]").length		//TOPLAM TAM EKRAN MODUNDA AÇIK DİNAMİK PENCERE ADETİ
					var acikDPentkAdet	= $("#jw-dinamik 	.jw-d-pencere[no][tamekran][kucuk]").length	//TOPLAM TAM EKRAN MODUNDA ve KÜÇÜK AÇIK DİNAMİK PENCERE ADETİ
					if((acikSPenAdet + acikDPentAdet - acikDPentkAdet)<2) 								//EĞER HİÇ AÇIK STATİK ve TAMEKRAN MODUNDA DİNAMİK PENCERE YOKSA BODY ALANI SCROLUNU ESKİ HALİNE GETİR.
					{	if(this.pencere.attr("tamekran")){	websiteFixProcess.aktif() /*process*/ }
					}
					
					//KÜÇÜLÜRKEN BİR DAHA BASILMAMASI İÇİN temsilKutuBas ANİMASYON BİTENE KADAR PASİF EDİLİR
					temsilKutu.off("click",olay.temsilKutuBas);
					
					//TEMSİL KUTU ÇERÇEVE KONUM
					var temsilCerceve 	= $("#jw-dinamikT");
					var temsilKB 		= new Object;
					temsilKB.cerY 		= temsilCerceve.position().top;
					temsilKB.cerX 		= temsilCerceve.position().left;
					
					//TEMSİL KUTU KONUM BOYUT
					temsilKB.en		= this.temsilKutu.width();
					temsilKB.boy	= this.temsilKutu.height();
					temsilKB.x		= this.temsilKutu.position().left;
					temsilKB.y		= this.temsilKutu.position().top;
					
					//PENCERE ESKİ KONUM BOYUT KAYIT
					skoKB.en	= this.pencere.width();
					skoKB.boy	= this.pencere.height();
					skoKB.x		= parseInt(this.pencere.css("left"));
					skoKB.y		= parseInt(this.pencere.css("top"));
					
					function kucultEfektOK()
					{	//ANİMASYON BİTİNCE TEMSİL BAS AKTİF EDİLİR
						temsilKutu.on("click",olay.temsilKutuBas);
						
						pencere.attr("kucuk",1);
						pencere.css("visibility","hidden");
						
						//PENCERE KÜÇÜLÜNCE KÜÇÜK OLMAYAN EN ÜSTTEKİ PENCERE SEÇİLİYOR.
						var acikPenAdet = $("#jw-dinamik .jw-d-pencere[no]").length;
						while(acikPenAdet>0)
						{	
							var zIndex 		= acikPenAdet-1;
							var arananPen 	= dinamikPencereEris().zIndex(zIndex);
							
							if(arananPen.pencere.attr("kucuk")!=1)
							{	arananPen.oneAl();
								return;
							}
							
							acikPenAdet--;
						}
					}
					
					var bodyScrollTop = $(document).scrollTop();					
					this.pencere.animate
					(	{	width:temsilKB.en , height:temsilKB.boy  , top:(temsilKB.y+temsilKB.cerY+bodyScrollTop) , left:(temsilKB.x+temsilKB.cerX) , opacity:0  },
						efektHiz.kucult,
						kucultEfektOK
					)
				}
			}
			return this;
		}
		
		altNesne.buyut = function()
		{	var pencereVarmi 	= this.pencere.attr("no"); 		//PENCERE FİZİKİ OLARAK VARMI ve AÇIKMI
			var pencereKucukmu	= this.pencere.attr("kucuk");	//PENCERE KÜÇÜK DURUMDAMI
			
			if(pencereVarmi!=undefined && pencereKucukmu==1)
			{	
				if(this.pencere.attr("tamekran")){	websiteFixProcess.pasif(); /*process*/ }
				
				//TEMSİL KUTU ÇERÇEVE KONUM
				var temsilCerceve 	= $("#jw-dinamikT");
				var temsilKB 		= new Object;
				temsilKB.cerY 		= temsilCerceve.position().top;
				temsilKB.cerX 		= temsilCerceve.position().left;
				
				//TEMSİL KUTU KONUM BOYUT
				temsilKB.en		= this.temsilKutu.width();
				temsilKB.boy	= this.temsilKutu.height();
				temsilKB.x		= this.temsilKutu.position().left;
				temsilKB.y		= this.temsilKutu.position().top;
				
				var websiteTop 	= parseInt($("#"+jw_website_cerceve_id).css("top"));
				if(isNaN(websiteTop)) websiteTop=0;
				
				//TEMSİL KUTUNUN YERİ DEĞİŞMİŞ İSE PENCEREMİZİ BÜYÜTMEDEN ÖNCE GENE KONUMLANDIRIYORUZ
				var bodyScrollTop 	= $(document).scrollTop();
				var targetTop		= (temsilKB.y+temsilKB.cerY+bodyScrollTop-websiteTop);
				this.pencere.css({ top: targetTop, left:(temsilKB.x+temsilKB.cerX)});
			
				altNesne.oneAl();
				pencere.css("visibility","visible");
				
				if(this.pencere.attr("tamekran")){ var phTop = -websiteTop+$(document).scrollTop(); }	//EĞER BÜYÜYEN PENCERE TAM EKRAN İSE TOP KONUMU SCROL HESABA KATILARAK YAPILIYOR Kİ PENCERE EKRANIN EN ÜSTÜNE HİZALANSIN.		
				else							 { var phTop = skoKB.y;  } 								//EĞER BÜYÜYEN PENCERE TAMEKRAN DEĞİLSE TOP KONUMU ESKİ KONUMUNA HİZALANIYOR					
				
				var oneal = this.oneAl;

				this.pencere.animate({width:skoKB.en , height:skoKB.boy  , top:phTop , left:skoKB.x , opacity:1 } , efektHiz.buyut ,function()
				{	pencere.css({ width:"auto" , height:"auto" }); pencere.removeAttr("kucuk");
					
					altNesne.ekranDisindaysaGetir();
				})
				
				skoKB = new Object;
			}
			return this;
		}
		
		altNesne.temsilKutuBas = function()
		{	var kucukmu 		= this.pencere.attr("kucuk");
			var sinif			= this.pencere.attr("class");
			
			
			if(kucukmu==1)										altNesne.buyut();
			else if(sinif.indexOf("jw-d-pencereAktif") == -1)	altNesne.oneAl();
			else												altNesne.kucult();
			
			return this;
		}
	
		//DİNAMİK PENCERE AKTİF PASİF EFEKT -----------------------------------------------------
		altNesne.aktifPencereEfekt = function()
		{	pencere		.addClass("jw-d-pencereAktif");
			temsilKutu	.addClass("jw-d-temsilKutuAktif");
			temsilDIV2	.addClass("jw-d-temsilDIV2Aktif");
			return this;
		}

		altNesne.pasifPencereEfekt = function()
		{	pencere		.removeClass("jw-d-pencereAktif");
			temsilKutu	.removeClass("jw-d-temsilKutuAktif");
			temsilDIV2	.removeClass("jw-d-temsilDIV2Aktif");
			return this;
		}
		
		return altNesne;
	}

	//DİNAMİK PENCERE ERİŞİM --------------------------------------------------------------------
	var dinamikPencereEris = this.dinamikPencereEris = function()
	{	var altNesne = new Object;
		
		//PENCERE NOSUYLA PENCEREYE ERİŞİM
		altNesne.no = function(no)
		{	if(d.pencere[no] != undefined)	return d.pencere[no];
			else 							return false;
		}
		
		//PENCERE AÇILIRKEN KOD VERİLMİŞ İSE BU BİLGİYLE PENCEREYE ERİŞİM
		altNesne.kod = function(kod)
		{	var pencere 	= $("#jw-dinamik .jw-d-pencere[kod='"+kod+"'][no]");
			var no 			= pencere.attr("no");

			if(no!=undefined) 	return d.pencere[no];
			else 				return false;
		}
		
		//PENCERENİN ZINDEX DEĞERİNE GÖRE ERİŞİM
		altNesne.zIndex = function(zi)	
		{	switch(zi)
			{	case "max" 	:	//EN USTTEKİ PENCERE ERİŞİM
					var acikPenAdet 	= count(d.pencere);
				 	var pencere			= $('#jw-dinamik [style*="z-index: '+(acikPenAdet-1)+'"][no]');	break;

				case "min" 	:	//EN ALTTAKİ PENCERE ERİŞİM
					var pencere = $('#jw-dinamik [style*="z-index: 0"][no]'); break;

				default		: 	//BELİRTİLEN ZINDEX DEĞERİNDEKİ PENCERE ERİŞİM
					if(zi!=undefined) var pencere = $('#jw-dinamik [style*="z-index: '+zi+'"][no]');
			}

			if(pencere!=undefined)	var no = pencere.attr("no");
			if(no!=undefined) 		return d.pencere[no];
			else return false;
		}
		
		//TÜR BİLGİSİ PENCERE AÇILIRKEN VERİLMİŞ İSE ÇOĞUL BİR ŞEKİLDE PENCERELERE ERİŞİM
		altNesne.tur = function(tur)	
		{	var pencereler	= new Array;
					
			pencere = $("#jw-dinamik .jw-d-pencere[no][tur='"+tur+"']");
			pencere.each(function()
			{	var no 			= $(this).attr("no")
				pencereler[no] 	= d.pencere[no];
			})
			if(pencereler.length>0) return pencereler;
			else					return false;			
		}
		
		//TÜM PENCERELERE ERİŞİM
		altNesne.tumu = function()
		{	var pencereler	= new Array;
			
			pencere = $("#jw-dinamik .jw-d-pencere[no]");
			pencere.each(function()
			{	var no 			= $(this).attr("no")
				pencereler[no] 	= d.pencere[no];
			})
			if(pencereler.length>0) return pencereler;
			else					return false;
		}

		return altNesne;
	}

	//DİNAMİK PENCERE TÜMÜNÜ KAPAT --------------------------------------------------------------
	this.dinamikPencereTumunuKapat = function()
	{	for(var i in d.pencere)
		{	d.pencere[i].kapat();
		}
	}

	//DİNAMİK PENCERE SÜRÜKLE , BOYUTLANDIR VE MOUSE KONUM --------------------------------------
	//KULLANILACAK BAZI DEĞİŞKENLER
	var mouse 	  		= new Object; //MOUSE KONUMUNU ANLIK OLARAK TUTAN GLOBAL NESNE(mouse.x - mouse.y)
	var surukle   		= new Object; //SÜRÜKLE İŞLEMLERİ İLE ALAKALI BİLGİLERİ TUTAN GLOBAL NESNE
	  surukle.durum 	= false;
	
	var boyutla   		= new Object;	
	  boyutla.durum 	= false;
	  boyutla.min	  	= new Object;
	  boyutla.max	  	= new Object;
	    boyutla.min.en	= false;
	    boyutla.min.boy	= false;
	    boyutla.max.en	= false;
	    boyutla.max.boy	= false;

	//SÜRÜKLE -----------------------------------------------------------------------------------
	surukle.mBas = function(elm) //SÜRÜKLEME BAŞLAMADAN ÖNCE PENCERENİN BAŞLIK SATIRINA BASILDIĞINDA TETİKLENECEK FONKSİYON
	{	
		var ekX 		= elm.position().left;
		var ekY	 		= elm.position().top;
		surukle.elm 	= elm;
		surukle.rmeX	= mouse.x - ekX;
		surukle.rmeY	= mouse.y - ekY;
		surukle.durum 	= true;
		
		$(".jw-d-govdePerde").css("display","block");	//IFRAME İÇEREN PENCERELERİN SÜRÜKLENİRKEN VE BOYUTLANIRKEN OLUŞAN BUG'I ENGELLER
		disableSelection($(document)); 					//CHROME - IE TARAYICILARINDA PENCERENİN HAREKET HALİNDEYKEN İÇERİĞİNİN SEÇİLMESİNİ ENGELLER
		disableSelection(elm); 							//FIREFOX İÇİN
	}
	surukle.hareket = function() //SÜRÜKLEME HESAP MOTORU
	{	var mX = (mouse.x - surukle.rmeX);
		var mY = (mouse.y - surukle.rmeY);
		if(mY<0) mY = 0;
		surukle.elm.css("left", mX );
		surukle.elm.css("top" , mY );
	}
	surukle.mKaldir = function() //MOUSE KALDIRINCA MOTORU VE BAZI ÖZELLİKLERİ DEVRE DIŞI BIRAK.
	{	surukle.durum = false;
		
		//HAREKET BİTİNCE DEVRE DIŞI BIRAKILIYORLAR
		$(".jw-d-govdePerde").css("display","none");	//PENCERE İÇİ PERDE 
		enableSelection($(document));					//CHROME - IE İÇİN
		enableSelection(surukle.elm);					//FIREFOX İÇİN
	}

	//YENİDEN BOYUTLANDIRMA ---------------------------------------------------------------------
	boyutla.mBas = function(elm,yon,limit)
	{	var ebX			= elm.width();
		var ebY			= elm.height();		
		boyutla.elm 	= elm;
		boyutla.yon		= yon;
		boyutla.min		= limit.min;
	    boyutla.max		= limit.max;
		boyutla.rmbX	= mouse.x - ebX;
		boyutla.rmbY	= mouse.y - ebY;
		boyutla.durum	= true;
		
		$(".jw-d-govdePerde").css("display","block"); //IFRAME İÇEREN PENCERELERİN SÜRÜKLENİRKEN VE BOYUTLANIRKEN OLUŞAN BUG'I ENGELLER
		disableSelection($(document)); 	//CHROME - IE TARAYICILARINDA PENCERENİN HAREKET HALİNDEYKEN İÇERİĞİNİN SEÇİLMESİNİ ENGELLER
		disableSelection(elm); 			//FIREFOX İÇİN
	}
	boyutla.hareket = function() //PENCERENİN BOYUTLANDIRMA ALANLARINA BASILDIKTAN SONRA MOUSE HAREKETİ İZLENEREK BOYUT AYARLANIYOR.
	{	
		if(boyutla.yon==undefined || boyutla.yon=="x")	//YÖN BELİRTİLMEMEİŞSE VEYA SADECE X YÖNÜ BELİRTİLMİŞSE
		{	var bX	= (mouse.x - boyutla.rmbX);
			var bX	= dpEnBoyLimitKontrol("en",bX);		//MAX VE MIN SINIRLAR KONTROL EDİLİYOR
			boyutla.elm.css( "width" , bX );
		}
		
		if(boyutla.yon==undefined || boyutla.yon=="y")	//YÖN BELİRTİLMEMEİŞSE VEYA SADECE Y YÖNÜ BELİRTİLMİŞSE
		{	var bY 	= (mouse.y - boyutla.rmbY);
			bY		= dpEnBoyLimitKontrol("boy",bY);	//MAX VE MIN SINIRLAR KONTROL EDİLİYOR
			boyutla.elm.css( "height" , bY );
		} 

	}
	boyutla.mKaldir = function()
	{	boyutla.durum = false;
		
		//HAREKET BİTİNCE DEVRE DIŞI BIRAKILIYORLAR
		$(".jw-d-govdePerde").css("display","none"); //PENCERE İÇİ PERDE 
		enableSelection($(document)); 	//CHROME - IE İÇİN
		enableSelection(boyutla.elm); 	//FIREFOX İÇİN
	}

	//MOUSE KONUM, SÜRÜKLE VE BOYUTLANDIR OLAY ATAMALARI ----------------------------------------
	$(window).mousemove(function(e){ if(surukle.durum){ surukle.hareket(); } if(boyutla.durum){ boyutla.hareket(); }	mouse.x = e.pageX; mouse.y = e.pageY; })
	$(window).mouseup  (function() { if(surukle.durum){ surukle.mKaldir(); } if(boyutla.durum){ boyutla.mKaldir(); } })

	//BAZI KULLANILAN FONKSİYONLAR --------------------------------------------------------------
	var count 				= function(dizi){	var t=0; for(var i in dizi)	t++; return t; }
	var diziIlkElm 			= function(dizi){	for(var i in dizi)	return i; }
	var disableSelection	= function(elm) {	elm.attr('unselectable', 'on').css({'user-select':'none', 'MozUserSelect':'none'}).on('selectstart', false); }
	var enableSelection 	= function(elm) { 	elm.attr('unselectable', 'off').css({'user-select':'auto', 'MozUserSelect':'auto'}).off('selectstart', false); }
	var dpEnBoyLimitKontrol	= function(enBoy , px)
	{	if(enBoy == "en")
		{	if		( boyutla.min.en && px<boyutla.min.en )	return boyutla.min.en;
			else if	( boyutla.max.en && px>boyutla.max.en )	return boyutla.max.en;
		}
		else if(enBoy == "boy")
		{	if		( boyutla.min.boy && px<boyutla.min.boy ) return boyutla.min.boy;
			else if	( boyutla.max.boy && px>boyutla.max.boy ) return boyutla.max.boy;
		}
		return px;
	}
	
	websiteFixProcess.pasif		= function()
	{		
		if(websiteHTML.status != "fixed")
		{	
			var bodyHeight 	= $(document).height();
			var winHeight	= $(window).height();
			
			//alert(bodyHeight + " : " + winHeight)
			
			if(bodyHeight>winHeight && bodyOverflow.Y!="hidden")
			{	//BODY SCROLL GİZLENMEMİŞ İSE VE EKRANDA SCROL GÖZÜKÜYOR İSE
				$("body").css({overflowY:"scroll"})
			}
			
			var windowTOP	= $(window).scrollTop();
			$("#"+jw_website_cerceve_id).css({ position:"fixed" , width:"100%" , top:-windowTOP })
		}
		
		websiteHTML.status = "fixed";

	}
	websiteFixProcess.aktif		= function()
	{	
		var windowTOP 	= parseInt($("#"+jw_website_cerceve_id).css("top"));
		if(isNaN(windowTOP)) windowTOP=0;
		
		$("#"+jw_website_cerceve_id).css({ position:websiteHTML.position , width:websiteHTML.width , top:websiteHTML.top });

		var bodyHeight 	= $(document).height();
		var winHeight	= $(window).height();
		if(bodyHeight>winHeight && bodyOverflow.Y!="hidden")
		{	//BODY SCROLL GİZLENMEMİŞ İSE VE EKRANDA SCROL GÖZÜKÜYOR İSE
			$("body").css({overflowY:"visible"})
			$(window).scrollTop(-windowTOP);
		}
		
		websiteHTML.status = false;
	}

	//STANDART PENCERE SİMGESİ ÜRETİR -----------------------------------------------------------
	function jw_d_Simge1()
	{	var dpkSimge1 		= $("<div>").addClass("jw-d-simge1");
		var dpkSimge1C1		= $("<div>").addClass("jw-d-simge1-C1");
		var dpkSimge1P1		= $("<div>").addClass("jw-d-simge1-P1");
		var dpkSimge1C2		= $("<div>").addClass("jw-d-simge1-C2");
		var dpkSimge1P2		= $("<div>").addClass("jw-d-simge1-P2");
		
		var simge = dpkSimge1.append(dpkSimge1C1.append(dpkSimge1P1)).append(dpkSimge1C2.append(dpkSimge1P2));
		return simge;
	}	

	//BİLDİRİM KALIPLARI ------------------------------------------------------------------------
	this.bildirim = function(kod,tur)
	{	var altNesne 	= new Object;
		var sp			= this.statikPencere(kod,tur);
		
		
		altNesne.genel = function(tetikle)
		{	var pen = sp.htmlYarat().baslik("BİLDİRİM").tamamTus(tetikle);
			return pen;
		}
		
		altNesne.olumlu = function(tetikle)
		{	var pen = sp.htmlYarat().baslik("TAMAM").icerik("İşlem Başarıyla Tamamlandı!").sinifEkle("jw-ap1").tamamTus(tetikle);
			return pen;
		}
		
		altNesne.olumsuz = function(tetikle)
		{	var pen = sp.htmlYarat().baslik("OLUMSUZ!",{"color":"#EEE","textShadow":"-1px -1px 0 rgba(0,0,0,0.3)"}).icerik("İşlem Başarısız!",{"color":"#EEE","textShadow":"-1px -1px 0 rgba(0,0,0,0.3)"}).sinifEkle("jw-ap4").tamamTus(tetikle);
			return pen;
		}
		
		altNesne.secim = function(tetikle)
		{	var pen = sp.htmlYarat().baslik("SEÇİM").icerik("İşlemin yapılmasını onaylıyormusunuz?").sinifEkle("jw-ap3").secimTus(tetikle);
			return pen;
		}
		
		altNesne.bekle = function()
		{	var pen = sp.htmlYarat().en(160).icerik("Lütfen Bekleyiniz...").bekleAnimasyon();
			pen.baslikSatir.css({"backgroundColor":"rgba(0,0,0,0)" , "boxShadow":"0 0 0"});
			
			pen.icerikDIV1.css({"marginTop":"0"})
			pen.icerikDIV2.css({fontWeight:"bold" , textShadow:"none"})
			return pen;
		}
		
		return altNesne;
	}

	//HIZLI KULLANIM ----------------------------------------------------------------------------
	this.kisa = function(istek , kod , tur, tetikle)
	{	var islem = false;
		
		if(typeof(kod) == "function")
		{	var tetikle = kod;
			var kod 	= false;
			var tur		= false;
		}
		
		if(typeof(tur)== "function")
		{	var tetikle = tur;
			var tur		= false;
		}
		
		switch(istek)
		{	
			case "s"  	: islem = this.statikPencere(kod,tur).htmlYarat();			break;	//STATİK PENCERE YARAT
			case "s?" 	: islem = this.statikPencereEris();							break;	//VAR OLAN STATİK PENCEREYE ÖZEL BİLGİLER İLE ERİŞİM
			case "s!" 	: islem = this.statikPencereTumunuKapat();					break;	//TÜM STATİK PENCERELERİ KAPAT
			
			case "d"	: islem = this.dinamikPencere(kod,tur).htmlYarat();			break;	//DİNAMİK PENCERE YARAT
			case "d?"	: islem = this.dinamikPencereEris();						break;	//VAR OLAN DİNAMİK PENCEREYE ÖZEL BİLGİLER İLE ERİŞİM
			case "d!"	: islem = this.dinamikPencereTumunuKapat();					break;	//TÜM DİNAMİK PENCERELERİ KAPAT
			
			//BİLDİRİM KALIBINDA STATİK PENCERE YARATIR.
			case "b"			: islem = this.bildirim(kod,tur).genel(tetikle);	break;
			case "b olumlu"		: islem = this.bildirim(kod,tur).olumlu(tetikle);	break;	
			case "b olumsuz"	: islem = this.bildirim(kod,tur).olumsuz(tetikle);	break;
			case "b secim"		: islem = this.bildirim(kod,tur).secim(tetikle);	break;
			case "b bekle"		: islem = this.bildirim(kod,tur).bekle();			break;
			
			default		: islem = this.statikPencere(kod,tur).htmlYarat();			break;
		}
		return islem;
	}
	
	/*BEKLEME ANİMASYONU İÇİN "SPIN" KÜTÜPHANESİ (JSWINDOW'DAN BAĞIMSI BİR KÜTÜPHANEDİR) | fgnass.github.com/spin.js#v1.2.8
	**********************************************************************************************************************/
	!function(window, document, undefined) {
	
	  /**
	   * Copyright (c) 2011 Felix Gnass [fgnass at neteye dot de]
	   * Licensed under the MIT license
	   */
	
	  var prefixes = ['webkit', 'Moz', 'ms', 'O'] /* Vendor prefixes */
		, animations = {} /* Animation rules keyed by their name */
		, useCssAnimations
	
	  /**
	   * Utility function to create elements. If no tag name is given,
	   * a DIV is created. Optionally properties can be passed.
	   */
	  function createEl(tag, prop) {
		var el = document.createElement(tag || 'div')
		  , n
	
		for(n in prop) el[n] = prop[n]
		return el
	  }
	
	  /**
	   * Appends children and returns the parent.
	   */
	  function ins(parent /* child1, child2, ...*/) {
		for (var i=1, n=arguments.length; i<n; i++)
		  parent.appendChild(arguments[i])
	
		return parent
	  }
	
	  /**
	   * Insert a new stylesheet to hold the @keyframe or VML rules.
	   */
	  var sheet = function() {
		var el = createEl('style', {type : 'text/css'})
		ins(document.getElementsByTagName('head')[0], el)
		return el.sheet || el.styleSheet
	  }()
	
	  /**
	   * Creates an opacity keyframe animation rule and returns its name.
	   * Since most mobile Webkits have timing issues with animation-delay,
	   * we create separate rules for each line/segment.
	   */
	  function addAnimation(alpha, trail, i, lines) {
		var name = ['opacity', trail, ~~(alpha*100), i, lines].join('-')
		  , start = 0.01 + i/lines*100
		  , z = Math.max(1 - (1-alpha) / trail * (100-start), alpha)
		  , prefix = useCssAnimations.substring(0, useCssAnimations.indexOf('Animation')).toLowerCase()
		  , pre = prefix && '-'+prefix+'-' || ''
	
		if (!animations[name]) {
		  sheet.insertRule(
			'@' + pre + 'keyframes ' + name + '{' +
			'0%{opacity:' + z + '}' +
			start + '%{opacity:' + alpha + '}' +
			(start+0.01) + '%{opacity:1}' +
			(start+trail) % 100 + '%{opacity:' + alpha + '}' +
			'100%{opacity:' + z + '}' +
			'}', sheet.cssRules.length)
	
		  animations[name] = 1
		}
		return name
	  }
	
	  /**
	   * Tries various vendor prefixes and returns the first supported property.
	   **/
	  function vendor(el, prop) {
		var s = el.style
		  , pp
		  , i
	
		if(s[prop] !== undefined) return prop
		prop = prop.charAt(0).toUpperCase() + prop.slice(1)

		for(i=0; i<prefixes.length; i++) {
		  pp = prefixes[i]+prop
		  if(s[pp] !== undefined) return pp
		}
	  }
	
	  /**
	   * Sets multiple style properties at once.
	   */
	  function css(el, prop) {
		for (var n in prop)
		  el.style[vendor(el, n)||n] = prop[n]
	
		return el
	  }
	
	  /**
	   * Fills in default values.
	   */
	  function merge(obj) {
		for (var i=1; i < arguments.length; i++) {
		  var def = arguments[i]
		  for (var n in def)
			if (obj[n] === undefined) obj[n] = def[n]
		}
		return obj
	  }
	
	  /**
	   * Returns the absolute page-offset of the given element.
	   */
	  function pos(el) {
		var o = { x:el.offsetLeft, y:el.offsetTop }
		while((el = el.offsetParent))
		  o.x+=el.offsetLeft, o.y+=el.offsetTop
	
		return o
	  }
	
	  var defaults = {
		lines: 12,            // The number of lines to draw
		length: 7,            // The length of each line
		width: 5,             // The line thickness
		radius: 10,           // The radius of the inner circle
		rotate: 0,            // Rotation offset
		corners: 1,           // Roundness (0..1)
		color: '#FFF',        // #rgb or #rrggbb
		speed: 1,             // Rounds per second
		trail: 100,           // Afterglow percentage
		opacity: 1/4,         // Opacity of the lines
		fps: 20,              // Frames per second when using setTimeout()
		zIndex: 2e9,          // Use a high z-index by default
		className: 'spinner', // CSS class to assign to the element
		top: 'auto',          // center vertically
		left: 'auto',         // center horizontally
		position: 'relative'  // element position
	  }
	
	  /** The constructor */
	  function Spinner(o) {
		if (!this.spin) return new Spinner(o)
		this.opts = merge(o || {}, Spinner.defaults, defaults)
	  }
	
	  Spinner.defaults = {}
	
	  merge(Spinner.prototype, {
		spin: function(target) {
		  this.stop()
		  var self = this
			, o = self.opts
			, el = self.el = css(createEl(0, {className: o.className}), {position: o.position, width: 0, zIndex: o.zIndex})
			, mid = o.radius+o.length+o.width
			, ep // element position
			, tp // target position
	
		  if (target) {
			target.insertBefore(el, target.firstChild||null)
			tp = pos(target)
			ep = pos(el)
			css(el, {
			  left: (o.left == 'auto' ? tp.x-ep.x + (target.offsetWidth >> 1) : parseInt(o.left, 10) + mid) + 'px',
			  top: (o.top == 'auto' ? tp.y-ep.y + (target.offsetHeight >> 1) : parseInt(o.top, 10) + mid)  + 'px'
			})
		  }
	
		  el.setAttribute('aria-role', 'progressbar')
		  self.lines(el, self.opts)
	
		  if (!useCssAnimations) {
			// No CSS animation support, use setTimeout() instead
			var i = 0
			  , fps = o.fps
			  , f = fps/o.speed
			  , ostep = (1-o.opacity) / (f*o.trail / 100)
			  , astep = f/o.lines
	
			;(function anim() {
			  i++;
			  for (var s=o.lines; s; s--) {
				var alpha = Math.max(1-(i+s*astep)%f * ostep, o.opacity)
				self.opacity(el, o.lines-s, alpha, o)
			  }
			  self.timeout = self.el && setTimeout(anim, ~~(1000/fps))
			})()
		  }
		  return self
		},
	
		stop: function() {
		  var el = this.el
		  if (el) {
			clearTimeout(this.timeout)
			if (el.parentNode) el.parentNode.removeChild(el)
			this.el = undefined
		  }
		  return this
		},
	
		lines: function(el, o) {
		  var i = 0
			, seg
	
		  function fill(color, shadow) {
			return css(createEl(), {
			  position: 'absolute',
			  width: (o.length+o.width) + 'px',
			  height: o.width + 'px',
			  background: color,
			  boxShadow: shadow,
			  transformOrigin: 'left',
			  transform: 'rotate(' + ~~(360/o.lines*i+o.rotate) + 'deg) translate(' + o.radius+'px' +',0)',
			  borderRadius: (o.corners * o.width>>1) + 'px'
			})
		  }
	
		  for (; i < o.lines; i++) {
			seg = css(createEl(), {
			  position: 'absolute',
			  top: 1+~(o.width/2) + 'px',
			  transform: o.hwaccel ? 'translate3d(0,0,0)' : '',
			  opacity: o.opacity,
			  animation: useCssAnimations && addAnimation(o.opacity, o.trail, i, o.lines) + ' ' + 1/o.speed + 's linear infinite'
			})
	
			if (o.shadow) ins(seg, css(fill('#000', '0 0 4px ' + '#000'), {top: 2+'px'}))
	
			ins(el, ins(seg, fill(o.color, '0 0 1px rgba(0,0,0,.1)')))
		  }
		  return el
		},
	
		opacity: function(el, i, val) {
		  if (i < el.childNodes.length) el.childNodes[i].style.opacity = val
		}
	
	  })
	
	  /////////////////////////////////////////////////////////////////////////
	  // VML rendering for IE
	  /////////////////////////////////////////////////////////////////////////
	
	  /**
	   * Check and init VML support
	   */
	  ;(function() {
	
		function vml(tag, attr) {
		  return createEl('<' + tag + ' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">', attr)
		}
	
		var s = css(createEl('group'), {behavior: 'url(#default#VML)'})
	
		if (!vendor(s, 'transform') && s.adj) {
	
		  // VML support detected. Insert CSS rule ...
		  sheet.addRule('.spin-vml', 'behavior:url(#default#VML)')
	
		  Spinner.prototype.lines = function(el, o) {
			var r = o.length+o.width
			  , s = 2*r
	
			function grp() {
			  return css(
				vml('group', {
				  coordsize: s + ' ' + s,
				  coordorigin: -r + ' ' + -r
				}),
				{ width: s, height: s }
			  )
			}
	
			var margin = -(o.width+o.length)*2 + 'px'
			  , g = css(grp(), {position: 'absolute', top: margin, left: margin})
			  , i
	
			function seg(i, dx, filter) {
			  ins(g,
				ins(css(grp(), {rotation: 360 / o.lines * i + 'deg', left: ~~dx}),
				  ins(css(vml('roundrect', {arcsize: o.corners}), {
					  width: r,
					  height: o.width,
					  left: o.radius,
					  top: -o.width>>1,
					  filter: filter
					}),
					vml('fill', {color: o.color, opacity: o.opacity}),
					vml('stroke', {opacity: 0}) // transparent stroke to fix color bleeding upon opacity change
				  )
				)
			  )
			}
	
			if (o.shadow)
			  for (i = 1; i <= o.lines; i++)
				seg(i, -2, 'progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)')
	
			for (i = 1; i <= o.lines; i++) seg(i)
			return ins(el, g)
		  }
	
		  Spinner.prototype.opacity = function(el, i, val, o) {
			var c = el.firstChild
			o = o.shadow && o.lines || 0
			if (c && i+o < c.childNodes.length) {
			  c = c.childNodes[i+o]; c = c && c.firstChild; c = c && c.firstChild
			  if (c) c.opacity = val
			}
		  }
		}
		else
		  useCssAnimations = vendor(s, 'animation')
	  })()
	
	  if (typeof define == 'function' && define.amd)
		define(function() { return Spinner })
	  else
		window.Spinner = Spinner
	
	}(window, document);
	/*BEKLEME ANİMASYONU İÇİN "SPIN" KÜTÜPHANESİ SONU
	**********************************************************************************************************************/
	
	//SPIN KÜTÜPHANESİNİ JQUERY'E EKLİYORUZ
	$.fn.spin = function(opts)
	{	this.each(function()
		{	var $this = $(this),data = $this.data();
			
			if(data.spinner)
			{	data.spinner.stop();
				delete data.spinner;
			}
			
			if(opts !== false)
			{	data.spinner = new Spinner
								   (	$.extend
								   		(	{ color:$this.css('color') } , 
								     		opts
										)
									)
									.spin(this);
			}
		});
		
		return this;
	};
	
	//JSWINDOW'DA KULLANDIĞIMIZ STANDART SPIN EFEKT AYARI
	var bekleAnimasyonAyar1 =
	{	lines: 10,				// The number of lines to draw
		length: 4,				// The length of each line
		width: 6,				// The line thickness
		radius: 12,				// The radius of the inner circle
		corners: 0.7,			// Corner roundness (0..1)
		rotate: 0,				// The rotation offset
		color: '#FFFFFF',		// #rgb or #rrggbb
		speed: 1.2,				// Rounds per second
		trail: 40,				// Afterglow percentage
		shadow: true,			// Whether to render a shadow
		hwaccel: false,			// Whether to use hardware acceleration
		className: 'spinner',	// The CSS class to assign to the spinner
		zIndex: 2e9,			// The z-index (defaults to 2000000000)
		top: 'auto',			// Top position relative to parent in px
		left: 'auto'			// Left position relative to parent in px
	}
	
}

//HIZLI KULLANIMI BAŞLATIYORUZ
var jw;
var $jswindow 	= new jswindow;
jw 				= function(istek,kod,tur){ return $jswindow.kisa(istek,kod,tur); };
