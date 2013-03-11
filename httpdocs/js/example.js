$(function(){
    // ------ start: History magic ----------------------------------------------------
    History = window.History;
    History.Adapter.bind(window, 'statechange', function() {
        var State = History.getState();
        if (State.data && State.data.type) {
            switch(State.data.type) {
                case 'default':
                    // Example loading fade effect
                    $('#ajaxSend').fadeTo('fast', 0.3); 

                    // Load link with ajax
                    $('#ajaxSend').load(State.url, function() {
                        // Restore fade visibility
                        $('#ajaxSend').fadeTo(0, 1);
                    });
                    break;
            }
        }
    });

    // Load links with ajax
    $('body').on('click', 'a.ajaxSend',function(e) {
        e.preventDefault();
        var 
        wl = window.location,
        baseName = wl.protocol + '//' + wl.hostname,
        previousState = (History.getState()).url.replace(baseName, ''),
        nextState = $(this).attr('href').replace(baseName, '');

        History.pushState({
            type:'default',
            options: false
        }, document.title, nextState);
        // Reload page
        if (previousState == nextState) {
            History.Adapter.trigger(window, 'statechange');
        }
    });
    // ------ end: History magic ------------------------------------------------------
    
    // Example message
    if (console) {
        console.log('[##]Init MVC Loaded! If translate actived, you can use it here.[/##]');
    }
});