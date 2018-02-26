/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(function ($) {
        
    jQuery(document).ready(function($){
        
        countdown.setLabels(
	' milliseconde| Sekunde | Minute | Stunde | Tag | semaine| mois| année| décennie| siècle| millénaire',
	' millisecondes| Sekunden | Minuten | Stunden | Tage | semaines| mois| années| décennies| siècles| millénaires',
	' und ',
	', ',
	'');
        
        var nowDate = null;
        var timerDate = null;
        
        var timerId =
          countdown(
            new Date(registrationStartDate),
            function(ts) {
              nowDate = new Date();
              timerDate = new Date(ts.start);
              if(nowDate.getTime() >= timerDate.getTime()) {
                $('#counter-li').html('<a href="#" onclick="javascript:register(); return false;">Hier geht es zu den Anmeldungen</a>'); 
                window.clearInterval(timerId);
              } else {
                $('#counter-li').html('<span>Rennanmeldungen m&ouml;glich in: ' + ts.toLocaleString() + '</span>');
              }
            },
            countdown.DAYS | countdown.HOURS|countdown.MINUTES|countdown.SECONDS);
    });
});


