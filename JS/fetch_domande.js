function fetch_questions()
{
  // costruzione elenco risposte attualmente selezionate
  var s = $('[data-side-effects=true]:checked,'
           +'[data-side-effects=true] option:selected').map(function()
           {
              return this.value? this.value : null;
           }).get().join();

  // esecuzione get
  $.get(
      'fetch_questions_list.php',

      // seleziono parametri inviati con la richiesta get
      {q: s},

      // funzione di callback
      function(data)
      {
        // parse JSON dell'elenco delle domande da mostrare
        var questions_to_show = JSON.parse(data); 
        questions_to_show = $.map(questions_to_show,
                                  function(id) { return id.toString() });

        // per ciascuna domanda
        $('.question').each(function()
        {
            // controllo se va mostrata e se è già visibile
            var to_show = $.inArray($(this).attr('data-question_id'),
                                    questions_to_show) >= 0;
            var visible = $(this).is(':visible');

            if(to_show && (!visible))
            {
              // se la domanda va mostrata ed è nascosta la mostro e abilito
              $(this).slideDown('slow');
              $(this).find('input:not([data-disabled=true]),'+
                           'select:not([data-disabled=true])').removeAttr('disabled');
            }
            else if((!to_show) && visible)
            {
              // se la domanda non va mostrata ed è visibile la nascondo e disabilito
              $(this).slideUp('slow');
              $(this).find('input,select').attr('disabled', 'disabled');
            }
          });

        // caricamento delle domande da mostrare terminato
        // reimposto il cursore allo stato di default
        $('body').css('cursor', 'auto');
      }
  );

  // imposto il cursore come "in progress"
  $('body').css('cursor', 'progress');
}