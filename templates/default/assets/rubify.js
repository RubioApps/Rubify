jQuery.extend({

    tmpl:{
        container   : '.rbfy-main',
        header      : '.rbfy-header',
        footer      : '.rbfy-footer',
        aside       : '#queue',
        query       : '#query',
        menu        : '#mainmenu',
        toaster     : '#rbfy-toast',
        modal       : '#rbfy-modal',
        linkclass   : 'framed',
        queue       : {
            list        : '#queue-tracks',
            item        : '#queue-tracks .queue-item',
            itemlink    : '#queue-tracks .queue-link',
            button:{
                repeat  : '#repeat',
                shuffle : '#shuffle',
                empty   : '#queue-empty',
                play    : '#queue-play',
                save    : '#queue-save',
                export  : '#queue-export',
                remove  : '.btn-remove'
            },
        },
        track       : {
            selector:   '.track',
            image:      '.track-art img',
            play:       '.track-art button',
            icon:       '.track-art button i',
            duration:   '.track-duration',
            menu:       '#track-menu',
            menubutton: '#track-menu-btn',
            option:     '.track-menu-options li button',
            menutitle:  '.track-menu-title',            
            carousel:   '#track-carousel',
            links:      '#track-links',
            button:    {
                favorite:   '#track-to-favorites',
                play:       '#track-to-play',
                queue:      '#track-to-queue'
            },
        },
        playlist: {
            button: {
                create:     '#playlist-create',
                delete:     '.playlist-delete'
            },
            form: {
                submit:     '#playlist-submit',
                name:       '#playlist-name',
                select:     '#playlist-select',
                token:      '#token'
            }
        },
        profile: {
            button: {
                eye:        '#password-eye',
                update:     '#button-user-update',
                remove:     '#button-user-remove',
                add:        '#button-user-add',
                history:    '#button-history',
                upload:     '#button-upload-form',
                view:       '#button-upload-view',
                playlist:   '#button-playlists',
            },
            chart:          '#history-chart'
        },
        upload: {
            button: {
                form:       '#button-upload-form',
                view:       '#button-upload-view',             
            },            
            form:           '#upload-form',
            genre:          '#upload-genre',
            bar:            '#upload-bar'
        }
    },
    rbfy: {
        livesite   : '',
        labels     : {},
        cache      : {},
        logged   : false,

        init : function(url){
            this.livesite = url;
            $.ajaxSetup({timeout: 10000});
            $.getJSON(this.livesite+'/?task=labels',function(data){
                for (let key in data) {
                    if (data.hasOwnProperty(key)) {
                        $.rbfy.labels[key.toLocaleLowerCase()] = data[key];
                    }
                }
            });

            this.framed();
            this.setlayout();
            this.queue.init();
            this.info.init();          

            $($.tmpl.modal).on('hide.bs.modal',function(){
                $(this).find($.tmpl.playlist.form.submit).off('click');
                $(this).find('.modal-body').empty();
            });

            /*
            //Use the tooltips
            $('[data-bs-toggle="tooltip"]').map(function (element) {
                return new bootstrap.Tooltip($(this).get(0),{delay: {'show': 500, 'hide': 100 }})
            });
            */
        },
        qs: function (key , value = null) {
            key = key.replace(/[*+?^$.\[\]{}()|\\\/]/g, "\\$&"); // escape RegEx meta chars
            var match = location.search.match(new RegExp("[?&]"+key+"=([^&]+)(&|$)"));
            if(value === null){
                return match && decodeURIComponent(match[1].replace(/\+/g, " "));
            } else {
                let params = new URLSearchParams(location.href);
                params.set('layout', value);
                return decodeURIComponent(params.toString());
            }
        },
        search: function(q){
            let url     = $.rbfy.livesite+'/?task='+q.task+'.search&oid='+q.oid+':'+q.alias+'&format=json';
            $($.tmpl.query).autocomplete({
                autoFocus: true,
                minLength : 3,
                source:  function( request, response ) {
                    let term = request.term;
                    if ( term in $.rbfy.cache ) {
                        response( $.rbfy.cache[ term ] );
                        return;
                    }
                    $.getJSON(url,request, function( data, status, xhr ) {
                        $.rbfy.cache[ term ] = data;
                        response( data );
                    });
                },
                create: function() {
                    $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                        let href  = item.link;
                        let info    = item.artist ? '<div class="row ms-2 fs-6 text-white-50">' + item.artist + '</div>' : '';
                        ul.addClass("dropdown-menu");
                        let line = $('<li>')
                            .addClass('dropdown')
                            .appendTo(ul);
                        const link = $('<a>')
                            .addClass('btn')
                            .attr('href' , href)
                            .append('<div class="row ms-2 fs-5">'+item.title+'</div>')
                            .append(info);
                        line.append(link);

                        link.on('click',function(event){
                            event.preventDefault();
                            $.rbfy.go(href);
                            return false;
                        });
                        return line.append(link);
                    };
                }
            });
        },
        token: function(){
            $.get($.rbfy.livesite+'/?task=token').done(function (data) {
                let input = $('input#token');
                input.attr('name',data.token);
                input.val(data.sid);
            });
        },
        login: function(selector){
            $(selector).on('click', function (e) {
                e.preventDefault();
                const uid = $('input#uid').val();
                const pwd = $('input#pwd').val();
                const token = $('input#token').attr('name');
                const sid = $('input#token').val();

                data = { 'user': uid, 'password': pwd, [token]: sid };
                const posting = $.post($.rbfy.livesite + '/?task=login', data);
                posting.done(function (result) {
                    $.rbfy.toast(result.message, result.error);
                    if (result.error) {
                        $.rbfy.token();
                    } else {
                        setTimeout(1000, top.location = $.rbfy.livesite);
                    }
                });
            });
        },
        framed : function (className = null , container = null) {

            if(!className) className=$.tmpl.linkclass;
            if(!container) container=$.tmpl.container;

            $('.'+className).each(function () {
                let href = $(this).attr('href');
                $(this).removeClass(className);
                $(this).on('click', function (event) {
                    event.preventDefault();
                    $.rbfy.go(href , className , container);
                    return false;
                });
            });            
        },
        go : function (href, className = null , container = null) {

            if(!this.logged){
                top.document.location.href = $.rbfy.livesite+'/?task=login';
                return;
            }            

            if(!className) className=$.tmpl.linkclass;
            if(!container) container=$.tmpl.container;

            $(container).fadeOut('slow', function(){
                $(container).load(href, function(){

                    const menu = bootstrap.Collapse.getInstance($($.tmpl.menu).get(0));
                    if(menu) menu.hide();

                    const queue = bootstrap.Offcanvas.getInstance($($.tmpl.aside).get(0));
                    if(queue) queue.hide();

                    $(container).scrollTop();
                    $(container).show();
                    window.history.pushState({'href': href, 'container': container}, null , href);

                    $.rbfy.framed(className, container);
                    $.rbfy.setlayout();
                    
                    $('.scroll-box').on({
                        mouseover: function() {
                            $(this).removeClass('text-truncate');
                            var maxscroll = $(this).width();
                            var speed = maxscroll * 15;
                            $(this).animate({scrollLeft: maxscroll}, speed, 'linear');
                        },                    
                        mouseleave: function() {
                            $(this).stop();
                            $(this).addClass('text-truncate');
                            $(this).animate({scrollLeft: 0}, 'slow');
                        }
                    });
                });
            });
        },
        toast : function (text, error = false) {
            const wrapper = $($.tmpl.toaster);
            const toast = wrapper.find('.toast:first').clone();
            toast.addClass('temp');
            toast.find('.toast-body').html(text);
            toast.addClass(error ? 'bg-danger' : 'bg-success');
            toast.appendTo('body');
            const tbs = bootstrap.Toast.getOrCreateInstance(toast.get(0));
            tbs.show();
            setTimeout(function () { $('.toast.temp').remove() }, 2000);
        },
        setlayout: function(){
            $('button#layout-list').on('click',function(e){
                e.preventDefault();
                href = $.rbfy.qs('layout','list');
                $.rbfy.go(href);                        
            });
                    
            $('button#layout-grid').on('click',function(e){
                e.preventDefault();
                href = $.rbfy.qs('layout','grid');
                $.rbfy.go(href);
            });  
        },
        queue: {
            autoplay:   false,
            init: function(){

                //Button empty the queue
                $($.tmpl.queue.button.empty).on('click',function(event){
                    event.preventDefault();
                    $.getJSON($.rbfy.livesite+'/?task=queue.empty',function(data){
                        if(data.success){
                            $.rbfy.toast($.rbfy.labels['queue_track_emptied'],false);
                            $($.tmpl.queue.item).remove();
                            $.player.empty();
                        }
                    });
                    const bsqueue = bootstrap.Offcanvas.getInstance($($.tmpl.aside).get(0));
                    if(bsqueue) bsqueue.hide();
                });

                //Button play the queue
                $($.tmpl.queue.button.play).on('click',function(event){
                    event.preventDefault();
                    if($.player.queue.length){
                        $.player.reset();
                        $.player.song = $.player.queue[0];
                        $.player.audio.src = $.player.song.audio;
                        $.player.audio.load();
                        $.player.autoplay =  true;
                        $.player.start();
                        const bsqueue = bootstrap.Offcanvas.getInstance($($.tmpl.aside).get(0));
                        if(bsqueue) bsqueue.hide();
                    }
                });

                //Button save the queue
                $($.tmpl.queue.button.save).on('click',function(event){
                    event.preventDefault();
                    $.rbfy.playlist.select();
                });

                //Button export the queue
                $($.tmpl.queue.button.export).on('click',function(event){
                    event.preventDefault();
                    $.ajax({
                        url: $.rbfy.livesite+'/?task=queue.export',
                        method: 'GET',
                        xhrFields: {
                            responseType: 'blob'
                        },
                        success: function (data) {
                            const a = document.createElement('a');
                            const url = window.URL.createObjectURL(data);
                            a.href = url;
                            a.download = Date.now() + '.m3u';
                            document.body.append(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);                  
                        }
                    });
                }); 

                //Button repeat the queue
                $($.tmpl.queue.button.repeat).on('click', function (event) {
                    event.preventDefault();
                    $.player.repeat();
                    return false;
                });

                //Button shuffle the queue
                $($.tmpl.queue.button.shuffle).on('click', function (event) {
                    event.preventDefault();
                    $.player.shuffle();
                    return false;
                });

                //Load the queue;
                $.rbfy.queue.load();

            },
            load: function( callback = null ){

                $($.tmpl.queue.list).load($.rbfy.livesite+'/?task=queue&format=raw' , function(){
                    $.getJSON($.rbfy.livesite+'/?task=queue.json', function(data){
                        const list = {
                            source: $.rbfy.queue.json,
                            songs: data
                        };
                        //Initialize the player
                        if(!$.player.loaded){
                            $.player.load(list);
                        } else {
                            //Get the songs from the queue
                            if (list.songs) {
                                $.player.queue = [];
                                $.each(list.songs, function (key) {
                                    $.player.queue.push(list.songs[key]);
                                });
                                //Display
                                if(!$.player.queue.length){
                                    $.player.digest();
                                    return;
                                }
                            }
                        }
                        //Highlight the first
                        if($.player.queue.length) $.rbfy.queue.reorder();

                        //Callback
                        if(callback) callback();
                    });

                    //Button select a track
                    $($.tmpl.queue.itemlink).on('click',function(){

                        let oid     = $(this).parents($.tmpl.queue.item).attr('data-src');

                        //If the it is already at the player
                        if ($.player.song != null && oid == $.player.song.oid) {
                        if (!$.player.isplaying) $.player.play();
                            return;
                        }

                        //Pop & push to the 1st position
                        $.getJSON($.rbfy.livesite+'/?task=queue.popandunshift&oid='+oid+'&format=json', function (data) {
                            if(data.success){
                                $.player.pop(data.result);
                                $.player.unshift(data.result);
                                $.player.track = 0;
                                $.player.start();
                                $.player.play();
                                $($.tmpl.queue.item+'[data-src="'+oid+'"]').prependTo($($.tmpl.queue.list));
                                $.rbfy.queue.reorder();
                            }
                        });

                        /*
                        const bsqueue = bootstrap.Offcanvas.getInstance($($.tmpl.aside).get(0))
                        if(bsqueue) bsqueue.hide();
                        */
                    });

                    //Button remove a track
                    $($.tmpl.queue.item).find($.tmpl.queue.button.remove).on('click',function(event){
                        event.preventDefault();
                        let button = $(this);
                        let oid = $(this).attr('data-src');

                        $.getJSON($.rbfy.livesite+'/?task=queue.pop&oid='+oid,function(data){
                            if(data.success)
                            {
                                $.player.pop(data.result);
                                button.parents($.tmpl.queue.item).fadeOut('slow',function(){ $(this).remove()});
                                if($.player.queue.length){
                                    if($.player.song && $.player.song.oid == oid){
                                        let wasplaying = $.player.isplaying;
                                        $.player.reset();
                                        $.player.start();
                                        if(wasplaying) $.player.play();
                                    }
                                } else {
                                    $.player.empty();
                                }
                                //Notify
                                $.rbfy.toast($.rbfy.labels['queue_track_removed']);
                            } else {
                                $.rbfy.toast($.rbfy.labels['queue_playlist_error'],true);
                            }
                        })
                    });

                    //Make the list sortable
                    $($.tmpl.queue.list).sortable({
                        items:  '.queue-item',
                        handle: '.queue-art',
                        update: function( event, ui ){
                            $.rbfy.queue.reorder();
                            let ids     = $(this).sortable('toArray' , { attribute: 'data-src' });
                            let postage = { 'ids' : ids};
                            $.post($.rbfy.livesite+'/?task=queue.sort',postage,function(data){
                                if(data.success){
                                    let result = [];
                                    for(k = 0 ; k < ids.length ; k++)
                                    {
                                        for(i = 0 ; i < $.player.queue.length ; i++)
                                        {
                                            if($.player.queue[i].oid == ids[k]) result.push($.player.queue[i]);
                                        }
                                    }
                                    $.player.queue = result;

                                    if ($.player.song.oid && ids[0] != $.player.song.oid){
                                        let wasplaying = $.player.isplaying;
                                        $.player.empty();
                                        $.player.queue = result;
                                        $.player.autoplay = wasplaying;
                                        $.player.start();
                                    }
                                }
                            });
                        }
                    });


                });
            },
            reorder: function() {
                let k=1;
                $($.tmpl.queue.item).each(function(i){
                    $(this).attr('data-order',k++);
                });
                $($.tmpl.queue.item).removeClass('queue-active');
                $($.tmpl.queue.item).first().addClass('queue-active');
            },
            pushandplay: function(oid){
                $.getJSON($.rbfy.livesite+'/?task=queue.push&oid='+oid,function(data){
                    if(data.success){
                        $.player.push(data.result);                        
                        $.rbfy.queue.load(function(){
                            if($.player.queue.length == 1) $.player.start();
                        });
                        //Notify
                        $.rbfy.toast($.rbfy.labels['queue_track_pushed']);
                    } else {
                        //Notify
                        $.rbfy.toast($.rbfy.labels['queue_track_error'],true);
                    }
                });
            },
            add: function(url){
                $.get(url,function(){
                    $.rbfy.toast($.rbfy.labels['queue_playlist_pushed']);
                    $.rbfy.queue.load();
                });
            },
            //Add the tracks of an album or playlist to the queue
            addandplay: function(url){
                $.get({
                    'url' : url,
                    'success': function(data){
                        //Notify
                        $.rbfy.toast($.rbfy.labels['queue_playlist_pushed']);
                        //Reload the content of the aside
                        $.rbfy.queue.load(function(){
                            $.player.track = 0;
                            $.player.start();
                            $.player.play();
                        });
                    },
                    'fail' : function() {
                        $.rbfy.toast($.rbfy.labels['queue_playlist_error'],true);
                    }
                });
            }
        },
        playlist: {
            create: function(){
                const header  = $($.tmpl.modal).find('.modal-title');
                const body    = $($.tmpl.modal).find('.modal-body');
                const wrapper = $('<div class="text-center w-100"></div>');

                header.text($.rbfy.labels['playlists']);
                wrapper.appendTo(body);
                const input   = $('<input type="text" class="form-control" id="playlist-name" name="name" value="" />');
                input.appendTo(wrapper);
                const token   = $('<input type="hidden" id="token" />');
                token.appendTo(wrapper);
                $.rbfy.token();

                //Bind the submit button
                $($.tmpl.playlist.form.submit).unbind();
                $($.tmpl.playlist.form.submit).on('click',function(){
                    if($($.tmpl.playlist.form.name).val().length > 0 && $($.tmpl.playlist.form.name).val() != 'favorites'){
                        let token = $($.tmpl.playlist.form.token).attr('name');
                        data = {};
                        data['name']= $($.tmpl.playlist.form.name).val().trim();
                        data[token] = $($.tmpl.playlist.form.token).val()

                        $.post($.rbfy.livesite+'/?task=playlist.create&format=json',data,function(result){
                            if(result.success){
                                $.rbfy.toast(result.message);
                                $.rbfy.go($.rbfy.livesite+'/?task=playlist&format=raw');
                                let modal = bootstrap.Modal.getOrCreateInstance($($.tmpl.modal).get(0));
                                modal.hide();
                            }else{
                                $.rbfy.toast(result.message,true);
                                $.rbfy.token();
                                $($.tmpl.playlist.form.name).focus();
                            }
                        });
                    } else {
                        $.rbfy.toast($.rbfy.labels['playlist_invalid_name'],true);
                        $($.tmpl.playlist.form.name).val('').focus();
                    }
                });

                const modal = bootstrap.Modal.getOrCreateInstance($($.tmpl.modal).get(0));
                if(modal) modal.show();
            },
            delete: function(){
                let id = $($.tmpl.playlist.button.delete).parents('tr').attr('data-id');
                $.get($.rbfy.livesite+'/?task=playlist.delete&id='+id+'&format=json',function(result){
                    if(result.success){
                        $.rbfy.toast(result.message);
                        $.rbfy.go($.rbfy.livesite+'/?task=playlist&format=raw');
                    }else{
                        $.rbfy.toast(result.message,true);
                    }
                });
            },
            select: function(oid = null){
                const header  = $($.tmpl.modal).find('.modal-title');
                const body    = $($.tmpl.modal).find('.modal-body');
                const wrapper = $('<div class="text-center w-100"></div>');

                header.text($.rbfy.labels['playlists']);
                wrapper.appendTo(body);

                wrapper.load($.rbfy.livesite+'/?task=playlist.select',function(){
                    const token   = $('<input type="hidden" id="token" />');
                    token.appendTo(wrapper);
                    $.rbfy.token();
                });

                //Bind the submit button
                $($.tmpl.playlist.form.submit).unbind();
                $($.tmpl.playlist.form.submit).on('click',function(){
                    if( ($($.tmpl.playlist.form.name).val().length > 0 && $($.tmpl.playlist.form.name).val() != 'favorites') ||
                        !$($.tmpl.playlist.form.name).val().length)
                    {
                        const token = $($.tmpl.playlist.form.token).attr('name');
                        data = {};
                        data['select']= $($.tmpl.playlist.form.select).val();
                        data['name']= $($.tmpl.playlist.form.name).val().trim();
                        data[token] = $($.tmpl.playlist.form.token).val()

                        let url = '';
                        if(!oid){
                            url = $.rbfy.livesite+'/?task=playlist.save&format=json';
                        } else {
                            url = $.rbfy.livesite+'/?task=playlist.push&oid='+oid+'&format=json';
                        }

                        $.post(url,data,function(result){
                            if(result.success){
                                $.rbfy.toast(result.message);
                                const modal = bootstrap.Modal.getOrCreateInstance($($.tmpl.modal).get(0));
                                if(modal) modal.hide();                                                            
                            }else{
                                $.rbfy.toast(result.message,true);
                                $.rbfy.token();
                                $($.tmpl.playlist.form.name).focus();
                            }
                        });
                    } else {
                        $.rbfy.toast($.rbfy.labels['playlist_invalid_name'],true);
                        $($.tmpl.playlist.form.name).val('').focus();
                    }
                });

                const modal = bootstrap.Modal.getOrCreateInstance($($.tmpl.modal).get(0));
                modal.show();
            },
            export: function(id){
                $.ajax({
                    url: $.rbfy.livesite+'/?task=playlist.export&id='+id,
                    method: 'GET',
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (data) {
                        if(data){
                            const a = document.createElement('a');
                            const url = window.URL.createObjectURL(data);
                            a.href = url;
                            a.download = Date.now() + '.m3u';
                            document.body.append(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);                  
                        }
                    }
                });                
            },
            download: function(id){
                $.ajax({
                    url: $.rbfy.livesite+'/?task=playlist.download&id='+id,
                    method: 'GET',
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (data) {
                        if(data){
                            const a = document.createElement('a');
                            const url = window.URL.createObjectURL(data);
                            a.href = url;
                            a.download = Date.now() + '.zip';
                            document.body.append(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);                  
                        }
                    }
                });                
            }                                   
        },
        history: {
            save: function(oid,time){
                $.getJSON($.rbfy.livesite+'/?task=history.push&oid='+oid+'&time='+time+'&format=json',function(data){
                    if(data.success){

                    };
                });
            }
        },
        track: {
            oid:        null,
            title:      '',
            setup: function(data){

                this.oid = data.oid;
                this.title = data.title;

                $.rbfy.track.isfavorite();
                $.rbfy.track.banner();
                $.rbfy.track.info();
                $.rbfy.track.toolbar();
            },
            bind : function (){
                const item     = $($.tmpl.track.selector);
                item.unbind();
                item.on({             
                    mouseover: function () {

                        const track = $(this);

                        if(track.is(':not(.selected)')){
                            track.addClass('bg-secondary');
                            track.find($.tmpl.track.icon).addClass('bi-play');
                        }

                        if(track.is(':not(.active)')){

                            const id      = track.attr('data-playlist');
                            const oid     = track.attr('data-src');
                            const menu    = $($.tmpl.track.menu);
                            const title   = track.find('.track-title').text();       
                            const source  = track.parents('.tracks-list').attr('data-source');   
                            const isfav   = (track.attr('data-favorite') == 'true');

                            track.addClass('active');
                            track.find($.tmpl.track.image).hide();
                            track.find($.tmpl.track.duration).hide();
                            track.find($.tmpl.track.play).removeClass('d-none');
                            $($.tmpl.track.menubutton).removeClass('d-none');

                            track.find('.track-title').removeClass('text-truncate');
                            var maxscroll = track.find('.track-title').width();
                            var speed = maxscroll * 15;
                            track.find('.track-title').animate({scrollLeft: maxscroll}, speed, 'linear');                            

                            menu.find($.tmpl.track.menutitle).html(title);
                            menu.attr('data-src',oid);
                            $($.tmpl.track.menubutton).appendTo(track);

                            //Hide all the optional buttons
                            menu.find('[data-option="true"]').parent().hide();

                            //Manage add/remove from the favorites
                            if(isfav){
                                menu.find('[data-action="fav-remove"]').parent().show();
                            } else {
                                menu.find('[data-action="fav-add"]').parent().show();
                            }                            

                            //Check if this is a track from a playlist or not                            
                            switch(source){
                                case 'album':
                                    menu.find('[data-action="playlist-add"]').parent().show(); 
                                    break;
                                case 'playlist':
                                    break;  
                                case 'favorites':                                                                  
                                    break;                                                                            
                                case 'history':
                                    menu.find('[data-action="history-remove"]').parent().show();
                                    break;      
                                case 'upload':                                 
                                    menu.find('[data-action="playlist-add"]').parent().show();  
                                    menu.find('[data-action="upload-remove"]').parent().show();
                                    break;                                                                    
                                default:
                                    break;
                            }
                            //For each eaction in the track menu
                            menu.find($.tmpl.track.option)
                                .unbind()
                                .attr('data-src',track.attr('data-src'))
                                .on('click',function(event){
                                    event.preventDefault();
                                    const action  = $(this).attr('data-action');
                                    const oid     = menu.attr('data-src');

                                    switch (action) {

                                        case 'play':
                                            track.find($.tmpl.track.play).trigger('click');
                                            break;

                                        case 'view':
                                            $.rbfy.go($.rbfy.livesite+'/?task=track&oid='+oid+'&format=raw');
                                            break;

                                        case 'queue':
                                            $.rbfy.queue.pushandplay(oid);
                                            break;

                                        case 'fav-add':
                                            $.getJSON($.rbfy.livesite+'/?task=favorites.push&oid='+oid,function(result){
                                                if(result.success){
                                                    track.attr('data-favorite','true');
                                                    track.find('.track-fav').removeClass('d-none').addClass('d-inline');
                                                    $.rbfy.toast($.rbfy.labels['fav_added_success']);
                                                } else {
                                                    $.rbfy.toast($.rbfy.labels['fav_added_error'],true);
                                                }
                                                if(id != '')
                                                    $.rbfy.go($.rbfy.livesite+'/?task=playlist.view&id='+id+'&format=raw');
                                            });
                                            break;

                                        case 'fav-remove':
                                            $.getJSON($.rbfy.livesite+'/?task=favorites.pop&oid='+oid,function(result){                                                
                                                if(result.success){
                                                    track.attr('data-favorite','false');
                                                    track.find('.track-fav').removeClass('d-inline').addClass('d-none');
                                                    $.rbfy.toast($.rbfy.labels['fav_removed_success']);
                                                } else {
                                                    $.rbfy.toast($.rbfy.labels['fav_removed_error'],true);
                                                }
                                                if(id != '')
                                                    $.rbfy.go($.rbfy.livesite+'/?task=playlist.view&id='+id+'&format=raw');
                                            });
                                            break;

                                        case 'playlist-remove':
                                            $.getJSON($.rbfy.livesite+'/?task=playlist.pop&id='+id+'&oid='+oid,function(result){
                                                if(result.success){
                                                    $.rbfy.toast($.rbfy.labels['playlist_removed_success']);
                                                } else {
                                                    $.rbfy.toast($.rbfy.labels['playlist_removed_error'],true);
                                                }
                                                if(id != '')
                                                    $.rbfy.go($.rbfy.livesite+'/?task=playlist.view&id='+id+'&format=raw');
                                            });
                                            break;  

                                        case 'playlist-add':
                                            $.rbfy.playlist.select(oid);
                                            break;

                                        case 'history-remove':
                                            $.getJSON($.rbfy.livesite+'/?task=history.pop&id='+id+'&oid='+oid,function(result){
                                                if(result.success){
                                                    $.rbfy.toast($.rbfy.labels['history_removed_success']);
                                                } else {
                                                    $.rbfy.toast($.rbfy.labels['history_removed_error'],true);
                                                }
                                                if(id != '')
                                                    $.rbfy.go($.rbfy.livesite+'/?task=history&format=raw');
                                            });
                                            break;    
                                        case 'upload-remove':
                                            const file = track.attr('data-file');
                                            $.get($.rbfy.livesite + '/?task=upload.remove&file='+file,function(success){    
                                                if(success){
                                                    $.rbfy.toast($.rbfy.labels['upload_removed_success']);                                  
                                                    track.parent().fadeOut('slow',function(){$(this).remove()});
                                                } else {
                                                    $.rbfy.toast($.rbfy.labels['upload_removed_error'],true);  
                                                }
                                            });                                            
                                            break;
                                    }
                                    track.trigger('mouseleave');

                                    const bsmenu = bootstrap.Offcanvas.getInstance($($.tmpl.track.menu).get(0))
                                    if(bsmenu) bsmenu.hide();
                                });
                        }
                    },
                    mouseleave: function () {
                        $($.tmpl.track.selector+':not(.selected)').removeClass('bg-secondary');
                        $(this).removeClass('active');
                        $(this).find($.tmpl.track.image).show();
                        $(this).find($.tmpl.track.duration).show();
                        $(this).find($.tmpl.track.play).addClass('d-none');
                        $($.tmpl.track.menubutton).addClass('d-none');
                        $($.tmpl.track.menubutton).prependTo($($.tmpl.track.menu))

                        $(this).find('.track-title').stop();
                        $(this).find('.track-title').addClass('text-truncate');
                        $(this).find('.track-title').animate({scrollLeft: 0}, 'slow');                        
                    }
                });
            
                $($.tmpl.track.play).on('click', function (event) {
                    event.preventDefault();
                    const elm = $(this).parents($.tmpl.track.selector);
                    $.rbfy.track.play(elm);
                });

                //Mobile Actions on track (swipe left & right)
                item.on('touchstart', function (e) {                   
                    let touch   = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                    let tstart  = touch.pageX;
                    let y1      = touch.pageY;
                    let maxwidth = $(this).parent().width();

                    let fleft = $('<div id="flyer-left"></div>')
                        .html('<div class="btn my-auto"><span class="bi bi-music-note-list"></span>'+$.rbfy.labels['queue_add']+'</div>')
                        .addClass('row m-0 p-0 bg-success text-nowrap')
                        .css({                                                                   
                            'width': '0px',
                            'height' : $(this).height() + 'px',
                            'float' : 'left',
                            'overflow': 'hidden',
                            'display' : 'none'
                        });  
                    fleft.insertBefore($(this));                        
                        
                    let fright = $('<div id="flyer-right"></div>')
                        .html('<div class="btn my-auto"><span class="bi bi-eye"></span>'+$.rbfy.labels['view']+'</div>')
                        .addClass('row m-0 p-0 bg-warning text-nowrap')                        
                        .css({          
                            'width': '0px',
                            'height' : $(this).height() + 'px',
                            'float' : 'left',
                            'overflow': 'hidden',
                            'display' : 'none'                         
                        });    
                    fright.insertAfter($(this));                                       

                    item.on('touchmove', function handler(ev) {
                        let touch   = ev.originalEvent.touches[0] || ev.originalEvent.changedTouches[0];
                        let tend    = touch.pageX;                        
                        let y2      = touch.pageY;

                        if(Math.abs(y1-y2)>10) return;

                        //Swipe left = View track                                                
                        if(tstart - tend > maxwidth/10 && $(this).width() >= maxwidth/5){                            
                            fleft.hide();
                            fright.show();
                            if(fright.width() < maxwidth/5){ 
                                $(this).removeClass('w-100');
                                $(this).animate({'width' : '-=' + 2*(tstart - tend)});
                                fright.animate({'width' : '+=' + 2*(tstart - tend)});
                                item.off('touchmove');
                                return;
                            }
                        }
                        //Swipe right = Add to queue
                        if(tend - tstart > maxwidth/10 && $(this).width() >= maxwidth/5){
                            fleft.show();
                            fright.hide();                            
                            if(fleft.width() < maxwidth/5){                     
                                $(this).removeClass('w-100');
                                $(this).animate({'width' : '-=' + 2*(tend - tstart)});                                           
                                fleft.animate({'width' : '+=' + 2*(tend - tstart)});
                                item.off('touchmove');
                                return;
                            }
                        }                                              
                    });
                });

                item.on('touchend', function () {                                    
                    const oid    = $(this).attr('data-src');
                    let maxwidth = $(this).parent().width();                  
                    let isfav    = $(this).attr('data-favorite');
                    item.off('touchmove');      

                    //View                
                    if($('#flyer-right').width() >= maxwidth/5){          
                        $.rbfy.go($.rbfy.livesite+'/?task=track&oid='+oid+'&format=raw');
                    }    

                    //Add to the queue                  
                    if($('#flyer-left').width() >= maxwidth/5){                                                  
                        $.rbfy.queue.add($.rbfy.livesite+'/?task=queue.push&oid='+oid+'&format=json');                            
                    }                                             
                    $('#flyer-left').remove();
                    $('#flyer-right').remove();                    
                    $(this).width(maxwidth);                      
                });                
            },
            play : function ( element ) {                

                let oid   = element.attr('data-src');
                let title = element.find('.track-title').html();

                element.addClass('bg-primary').addClass('selected');
                $($.tmpl.track.selector+'.selected').removeClass('selected').removeClass('bg-primary');
                $($.tmpl.track.selector+':not(.selected)').removeClass('bg-secondary');

                //if the track is already selected
                if($.player.queue.length && oid == $.player.song.oid){
                    if (!$.player.isplaying) $.player.play();
                    if($.player.isplaying){
                        element.find($.tmpl.track.icon).removeClass('bi-play').addClass('bi-pause');
                    } else {
                        element.find($.tmpl.track.icon).removeClass('bi-pause').addClass('bi-play');
                    }
                    return;
                }

                //If the track is already in the queue, pop&push at the 1st position and play
                if ($.player.select(oid)){
                    $.getJSON($.rbfy.livesite+'/?task=queue.popandunshift&oid='+oid, function (data) {
                        if(data.success){

                            $.player.pop(data.result);
                            $.player.unshift(data.result);

                            if($.player.isplaying){
                                element.find($.tmpl.track.icon).removeClass('bi-play').addClass('bi-pause');
                            } else {
                                element.find($.tmpl.track.icon).removeClass('bi-pause').addClass('bi-play');
                            }
                            $($.tmpl.queue.item+'[data-src="'+oid+'"]').prependTo($($.tmpl.queue.list));

                            $.rbfy.queue.reorder();
                            $.player.track = 0;
                            $.player.start();
                            if (!$.player.isplaying) $.player.play();

                            //Notify
                            $.rbfy.toast($.rbfy.labels['queue_playing_song'].replace(/%s/g, title));
                        }
                    });
                    return;
                }                

                //Otherwise, push it to the begining of the queue and play it
                $.getJSON($.rbfy.livesite+'/?task=queue.push&method=unshift&oid='+oid, function (data) {
                    if(data.success){
                        $.player.unshift(data.result);
                        //Reload the content of the aside
                        $.rbfy.queue.load(function(){
                            $.rbfy.queue.reorder();
                            $.player.track = 0;    
                            $.player.start();
                            if (!$.player.isplaying) $.player.play();
                            if($.player.isplaying){
                                element.find($.tmpl.track.icon).removeClass('bi-play').addClass('bi-pause');
                            } else {
                                element.find($.tmpl.track.icon).removeClass('bi-pause').addClass('bi-play');
                            }
                        });
                        //Notify
                        $.rbfy.toast($.rbfy.labels['queue_playing_song'].replace(/%s/g, title));
                    }
                });

            },
            tagfavorite: function(oid, isfavorite = false){
                if(isfavorite){
                    $($.tmpl.track.button.favorite).removeClass('btn-secondary').addClass('btn-danger');
                    $($.tmpl.track.selector+'[data-src="'+oid+'"]').find('.track-fav').removeClass('d-none').addClass('d-inline');
                } else {
                    $($.tmpl.track.button.favorite).removeClass('btn-danger').addClass('btn-secondary');
                    $($.tmpl.track.selector+'[data-src="'+oid+'"]').find('.track-fav').removeClass('d-inline').addClass('d-none');
                }
            },
            isfavorite: function(){
                let oid = $.rbfy.track.oid;
                $.getJSON($.rbfy.livesite+'/?task=favorites.find&oid='+oid,function(result){                    
                    if(result.success){
                        $($.tmpl.track.button.favorite).removeClass('btn-secondary').addClass('btn-danger');
                        $($.player.buttons.favorite).removeClass('bi-heart').addClass('bi-heart-fill');
                        $($.player.buttons.favorite).css({'color': 'red'});
                    } else {
                        $($.tmpl.track.button.favorite).removeClass('btn-danger').addClass('btn-secondary');
                        $($.player.buttons.favorite).removeClass('bi-heart-fill').addClass('bi-heart');
                        $($.player.buttons.favorite).css({'color': 'gray'});
                    }
                });
            },
            banner: function(){
                let oid = $.rbfy.track.oid;
                $.getJSON($.rbfy.livesite+'/?task=track.banner&oid='+oid, function (data) {
                    if (data.success) {
                        const model   = $($.tmpl.track.carousel).find('.carousel-item').first();
                        $.each(data.images, function (i, value) {
                            const div     = model.clone();
                            const img     = div.find('img').attr('src', value);
                            div.removeClass('active');
                            div.insertAfter(model);
                        });

                        model.fadeOut(500 , function(){
                            const carousel = new bootstrap.Carousel($($.tmpl.track.carousel).get(0),{
                                'keyboard': false,
                                'touch': true,
                                'interval': 2000
                            });
                            carousel.next();
                            $(this).remove();
                        });
                    }
                });
            },
            info: function(){
                let oid = $.rbfy.track.oid;
                $.getJSON($.rbfy.livesite+'/?task=track.info&oid='+oid, function (data) {
                    if (data.success) {
                        $.each(data.relations,function(i,value){
                            const div = $('<div class="track-rel text-truncate"></div>');
                            const link = $('<a></a>').attr({
                                'href' : value,
                                'target': '_blank'
                            });
                            link.html(value.host).appendTo(div)
                            div.appendTo($($.tmpl.track.links));
                        });
                    }
                });
            },
            toolbar: function(){
                let oid     = $.rbfy.track.oid;
                let title   = $.rbfy.track.title;

                //Button select a track                
                $($.tmpl.track.button.play).on('click', function (event) {
                    event.preventDefault();
                    //If the it is already at the player
                    if ($.player.song != null && oid == $.player.song.oid) {
                        if (!$.player.isplaying) $.player.play();
                            return;
                    }
                    //If the track is already in the queue, pop&push at the 1st position and play
                    if ($.player.select(oid)){
                        $.getJSON($.rbfy.livesite+'/?task=queue.popandunshift&oid='+oid, function (data) {
                            if(data.success){
                                $($.tmpl.queue.item+'[data-src="'+oid+'"]').prependTo($($.tmpl.queue.list));
                                $.rbfy.reorder();
                                $.player.pop(data.result);
                                $.player.unshift(data.result);
                                $.player.start();
                                $.player.play();
                                //Notify
                                $.rbfy.toast($.rbfy.labels['queue_playing_song'].replace(/%s/g, title));
                            }
                        });
                        return;
                    }

                    $.getJSON($.rbfy.livesite+'/?task=queue.push&method=unshift&oid='+oid, function (data) {
                        if(data.success){
                            $.player.push(data.result);
                            //Reload the content of the aside
                            $.rbfy.queue.load(function(){
                                $.player.track = 0;
                                $.player.start();
                                $.player.play();
                            });
                            //Notify
                            $.rbfy.toast($.rbfy.labels['queue_playing_song'].replace(/%s/g, title));
                        }
                    });
                    return false;
                });

                //Button add to queue
                $($.tmpl.track.button.queue).on('click', function (event) {
                    event.preventDefault();
                    $.getJSON($.rbfy.livesite+'/?task=queue.push&oid='+oid, function (data) {
                        if(data.success){
                            $.player.push(data.result);
                            $.rbfy.queue.load();
                            //Notify
                            $.rbfy.toast($.rbfy.labels['queue_track_pushed']);
                        }
                    });
                });

                //Button add to favorites
                $($.tmpl.track.button.favorite).on('click', function (event) {
                    event.preventDefault();
                    $.getJSON($.rbfy.livesite+'/?task=favorites.find&oid='+oid,function(result){
                        if(!result.success){
                            $.getJSON($.rbfy.livesite+'/?task=favorites.push&oid='+oid,function(result){
                                if(result){
                                    $.rbfy.toast($.rbfy.labels['fav_added_success']);
                                    $($.tmpl.track.button.favorite).removeClass('btn-secondary').addClass('btn-danger');
                                    $($.player.buttons.favorite).removeClass('bi-heart').addClass('bi-heart-fill');
                                    $($.player.buttons.favorite).css({'color': 'red'});
                                } else {
                                    $.rbfy.toast($.rbfy.labels['fav_added_error'],true);
                                }
                            });
                        } else {
                            $.getJSON($.rbfy.livesite+'/?task=favorites.pop&oid='+oid,function(result){
                                if(result.success){
                                    $.rbfy.toast($.rbfy.labels['fav_removed_success']);
                                    $($.tmpl.track.button.favorite).removeClass('btn-danger').addClass('btn-secondary');
                                    $($.player.buttons.favorite).removeClass('bi-heart-fill').addClass('bi-heart');
                                    $($.player.buttons.favorite).css({'color': 'gray'});
                                } else {
                                    $.rbfy.toast($.rbfy.labels['fav_removed_error'],true);
                                }
                            });
                        }
                    });
                });
            }
        },
        info: {
            container:  '#pane-track',
            content:    '.pane-content',
            tabs: {
                up:     '.pane-tab-up',
                down:   '.pane-tab-down'
            },
            buttons:    {
                view:   '#view-track',
                play:   '#queue-play',
                rewind: '#rewind',
                forward:'#forward',
                loop:   '#loop',
                volume: '#volume',
                fav:    '#favorite'
            },
            slider : function () {                
                if ($($.rbfy.info.tabs.up).hasClass('active')) {
                    const target = $(window).height();
                    const canvas = $($.player.labels.equalizer).get(0);

                    $($.rbfy.info.container).animate({ height: target }, 500);
                    $($.rbfy.info.content).height(target - $($.tmpl.footer).height());
                    canvas.height = target;
                    $.player.equalizer.height = target;

                    $($.rbfy.info.tabs.up).removeClass('active').hide();
                    $($.rbfy.info.tabs.down).addClass('active').show();
                } else {
                    const target = $($.tmpl.footer).height() + 30;
                    const canvas = $($.player.labels.equalizer).get(0);
                    
                    $($.rbfy.info.container).animate({ height: target }, 500);
                    $($.rbfy.info.content).height($($.rbfy.info.container).height());
                    canvas.height = target;
                    $.player.equalizer.height = target;
                    $($.rbfy.info.tabs.up).addClass('active').show();
                    $($.rbfy.info.tabs.down).removeClass('active').hide();
                }
            },
            init: function(){

                //Resize the info pane
                $($.rbfy.info.container).css({'height':($($.tmpl.footer).outerHeight() + $($.rbfy.info.tabs.up).outerHeight() - 1)+'px'});
                $($.rbfy.info.tabs.up+','+$.rbfy.info.tabs.down).on('click' ,function(){$.rbfy.info.slider();});

                //Unload the player if the document is refreshed
                $(window).on('beforeunload', function () {
                    if ($.player.call) $.player.call.abort();
                    if ($.player.timer) clearInterval($.player.timer);
                });

                // After a history back
                $(window).on('popstate', function(event){
                    if(event.originalEvent.state) {
                        let e = event.originalEvent.state;
                        $(e.container).load(e.href,function(){$.rbfy.framed();});
                    } else {
                        $($.tmpl.container).load($.rbfy.livesite+'/?task=home&format=raw' ,function(){
                            $.rbfy.framed();
                        });
                    }
                });

                // After orientationchange, add a one-time resize event
                $(window).on('orientationchange', function() {
                    $(window).one('resize', function() {
                        if($($.rbfy.info.container).hasClass('active')) $.rbfy.info.slider();
                        let hfooter = $($.tmpl.footer).outerHeight();
                        let htab    = $($.rbfy.info.tabs.up).outerHeight();
                        $($.rbfy.info.container).css({'height':(hfooter + htab)+'px'});
                    });
                });

                //Button rewind 5s
                $($.rbfy.info.buttons.rewind).on('click', function (event) {
                    event.preventDefault();
                    $.player.rewind();
                });

                //Button forward 5s
                $($.rbfy.info.buttons.forward).on('click', function (event) {
                    event.preventDefault();
                    $.player.forward();
                });

                //Button loop the song
                $($.rbfy.info.buttons.loop).on('click', function (event) {
                    event.preventDefault();
                    $.player.loop();
                });

                //Button toggle the volume
                $($.rbfy.info.buttons.volume).on('click', function (event) {
                    event.preventDefault();
                    $.player.mute();
                });

                //Button toggle favorite
                $($.rbfy.info.buttons.fav).on('click', function (event) {
                    event.preventDefault();
                    $.player.favorite();
                });

                //Button view info track
                $($.rbfy.info.buttons.view).on('click',function(event){
                    event.preventDefault();
                    if($.player.queue.length && $.player.song !== null){
                        $.rbfy.info.slider();
                        $.rbfy.go($.rbfy.livesite+'/?task=track&oid='+$.player.song.oid+'&format=raw');
                    }
                    return false;
                });
            }
        },
        upload: {
            reload: false,
            bind: function(){            
                $($.tmpl.upload.button.form).on('click',function(){            
                    const header    = $($.tmpl.modal).find('.modal-title');
                    const body    = $($.tmpl.modal).find('.modal-body');
                    const wrapper = $('<div class="text-start w-100"></div>');

                    header.text($.rbfy.labels['upload_button']);
                    wrapper.appendTo(body);

                    wrapper.load($.rbfy.livesite+'/?task=upload.form',function(){
                        const token   = $('<input type="hidden" id="token" />');
                        token.appendTo(wrapper);
                        $.rbfy.token();
                    });

                    const modal = bootstrap.Modal.getOrCreateInstance($($.tmpl.modal).get(0));
                    if(modal) modal.show();
                });

                $($.tmpl.upload.button.view).on('click',function(){   
                    $.rbfy.go($.rbfy.livesite+'/?task=upload');
                });
            },
            form: function(){          
                //Bind the new button
                const button = $($.tmpl.playlist.form.submit);
                button.unbind();        
                button.on('click',function(e){
                    e.preventDefault();
                    $($.tmpl.upload.form).submit();
                });

                //Load the genres
                $($.tmpl.upload.genre).autocomplete({
                    autoFocus: true,
                    minLength : 3,
                    source:  function( request, response ) {
                        let term = request.term;
                        if ( term in $.rbfy.cache ) {
                            response( $.rbfy.cache[ term ] );
                            return;
                        }
                        $.getJSON($.rbfy.livesite+'/?task=genre.json',request, function( data, status, xhr ) {
                            $.rbfy.cache[ term ] = data;
                            response( data );
                        });
                    },
                    create: function() {
                        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                            const line = $('<li class="dropdown">');
                            const span = $('<span class="btn">'+item.value+'</span>');
                            ul.addClass("dropdown-menu");                    
                            line.appendTo(ul);
                            return line.append(span);
                        };
                    },
                    select: function( event, ui ) {
                        $($.tmpl.upload.genre).val(ui.item.value);
                    }                                   
                });            

                //Bind the submit
                $($.tmpl.upload.form).bind('submit',function(e){
                    e.preventDefault();
                    let form = new FormData(this);
                    $.ajax({
                        xhr:function(){
                            let req = new XMLHttpRequest();
                            req.upload.addEventListener('progress',function(ele){
                                if (ele.lengthComputable) {
                                    let percentage=((ele.loaded / ele.total) * 100); 
                                    $($.tmpl.upload.bar).css('width',percentage+'%');
                                    $($.tmpl.upload.bar).html(Math.round(percentage)+'%');
                                }
                            });
                            return req;
                        },
                        url:    $.rbfy.livesite+'/?task=upload.form',
                        type:   'post',
                        contentType: false,
                        processData: false, 
                        data:   form,
                        complete: function(){
                            const modal = bootstrap.Modal.getOrCreateInstance($($.tmpl.modal).get(0));
                            if(modal) modal.hide();                            
                        },
                        beforeSend: function(){
                            $($.tmpl.upload.bar).css('width','0%');
                            $($.tmpl.upload.bar).html('0%');
                        },
                        success: function(data){
                            $.rbfy.toast(data.message  , data.error);
                            if($.rbfy.upload.reload){
                                $.rbfy.go($.rbfy.livesite+'/?task=upload');
                            }

                        },
                        error: function(xhr){
                            $.rbfy.toast($.rbfy.labels['upload_error'] + '(' + xhr.statusText + ')' , true);
                        }
                    });
                    return false;
                }); 
            }                    
        },
        profile: {
            init: function (labels , raw){
                this.eye();
                this.playlist();                
                this.history();
                this.chart.labels = labels;
                this.chart.raw = raw;
                this.chart.draw();        

                $.rbfy.upload.bind();                
            },
            eye: function(){
                $($.tmpl.profile.button.eye).on('click',function(e){
                    e.preventDefault();
                    if(!$(this).hasClass('show')){
                        $('input#pwd').attr('type', 'text');
                        $(this).removeClass('bi-eye-slash').addClass('bi-eye');
                        $(this).addClass('show');                        
                    } else {
                        $('input#pwd').attr('type', 'password');
                        $(this).removeClass('bi-eye').addClass('bi-eye-slash');
                        $(this).removeClass('show');                    
                    }                    
                    return false;
                });                  
            },
            update: function(){
                const uid = $('input#uid').val();
                const pwd = $('input#pwd').val();
                const lvl = $('select#lvl').find(':selected').val();
                const token = $('input#token').attr('name');
                const sid = $('input#token').val();

                data = { 'user': uid, 'password': pwd, 'level': lvl , [token]: sid };
                const posting = $.post($.rbfy.livesite + '/?task=user.update', data);
                posting.done(function (result) {
                    $.rbfy.toast(result.message, result.error);
                    if (result.error) {
                        $.rbfy.token();
                    } 
                });                
            },
            remove: function(){
                const uid = $('input#uid').val();
                const token = $('input#token').attr('name');
                const sid = $('input#token').val();

                data = { 'user': uid, [token]: sid };
                const posting = $.post($.rbfy.livesite + '/?task=user.remove', data);
                posting.done(function (result) {                    
                    if (result.error) {
                        $.rbfy.toast(result.message, result.error);
                        $.rbfy.token();
                    } else {
                        $.rbfy.go($.rbfy.livesite+'/?task=user.list');
                    }
                });            
            },
            add: function(){
                const uid = $('input#uid').val();
                const pwd = $('input#pwd').val();
                const lvl = $('select#lvl').find(':selected').val();
                const token = $('input#token').attr('name');
                const sid = $('input#token').val();

                data = { 'user': uid, 'password': pwd, 'level': lvl , [token]: sid };
                const posting = $.post($.rbfy.livesite + '/?task=user.add', data);
                posting.done(function (result) {
                    $.rbfy.toast(result.message, result.error);
                    if (result.error) {
                        $.rbfy.token();
                    } else {
                        $.rbfy.go($.rbfy.livesite+'/?task=user.list');
                    }
                });     
            },
            playlist: function(){            
                $($.tmpl.profile.button.playlist).on('click',function(){
                    $.rbfy.go($.rbfy.livesite+'/?task=playlist');
                });
            },            
            history: function(){
                $($.tmpl.profile.button.history).on('click',function(){
                    $.rbfy.go($.rbfy.livesite+'/?task=history');
                });
            },
            chart: {
                labels: [],
                raw: [],
                draw: function(){
                    $.getScript('https://cdn.jsdelivr.net/npm/chart.js',function(){
                        const ctx = $($.tmpl.profile.chart).get(0);
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: $.rbfy.profile.chart.labels,
                                datasets: [{
                                    label: $.rbfy.labels['tracks'],
                                    data: $.rbfy.profile.chart.raw,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                },                   
                            }                    
                        });                                            
                    });    
                }
            }
        }
    }
});

