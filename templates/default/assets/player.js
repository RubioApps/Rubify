/*
There is 5 main objects in this scripts:
- song:     an object that contains the audio track, title, thumbnail, duration, etc.
- queue:    an array of songs [song-1, song-2, ..., song-N]
- track:    the index of the queue. queue[track] = song
- audio:   the HTML audio object
- display:  the group of HTML DOM objects that take the details of a song
*/
jQuery.extend({
    player: {
        loaded: false,      /* Toogle if the player is already loaded */
        list: null,         /* list */
        audio: null,        /* HTML audio object */
        source: null,       /* URL pointing to the JSON source of the queue */
        queue: [],          /* Array of songs */
        history: [],        /* History of played tracks */
        song: null,         /* Current song */
        track: 0,           /* Track index in the list of songs */
        call: null,         /* JSON song call */
        time: 0,            /* Current time of a song */
        duration: 0,        /* Duration of a song */
        timer: null,        /* Refresing timer */
        lastTime: null,     /* Last time recorded after a timer change */
        autoplay: true,     /* Force autoplay */
        isplaying: false,   /* Toogle play/pause */
        isrepeated: false,  /* Toogle repeat track */
        isshuffle: false,   /* Toggle shuffle */
        ismute: false,      /* Toggle volume on/off */
        isloop: false,      /* Toogle if the song is looped */
        nloop: 0,           /* Number of loops */
        isdragging: false,  /* Toogle if dragging the slider */
        lyrics: null,       /* Contains the lyrics */
        timeline: [],       /* Timestemps of each phrase of the lyrics */
        counter: 0,         /* Counter used for the timestamps of the lyrics */
        equalizer: {
            active: false,  /* Toggle if the visualization is active */
            context: null,  /* AudioContext */
            analyser: null, /* Frequencies analyzer for the visualisation */
            blength: 0,     /* Analyser Buffer length */
            data: null,     /* Data */
            ctx: null,      /* Graphical context for the canvas */
            height: 0,
            width: 0
        },
        labels: {
            info:       '#current-song',
            title:      '#current-song .song-title',
            artist:     '#current-song .song-artist',
            lyrics:     '#song-lyrics',
            equalizer:  '#equalizer',
            loading:    '#loading-song',
            art:        '.album-art',
        },
        buttons: {
            play:       '#play i',
            prev:       '#prev i',
            next:       '#next i',
            rewind:     '#rewind i',
            forward:    '#forward i',
            favorite:   '#favorite',
            loop:       '#loop',
            repeat:     '#repeat',
            shuffle:    '#shuffle',
            volume:     '#volume',
        },
        slider: {
            now:        '#currentTime',
            total:      '#duration',
            bar:        '#progress-bar',
            value:      '#progress-value',
            marker:     '#progress-marker'
        },

        //Load the queue and play the first song
        load: function (list, autoplay = false) {

            if(!this.loaded){
                this.loaded = true;
                this.isplaying = false;
                this.isrepeated = false;
                this.isloop = false;
            }
            this.list = list;
            this.source = list.source;
            this.autoplay = autoplay;
            this.track = 0;

            //Get the DOM element
            if(!this.audio){
                this.audio = new Audio();  
                $(this.audio).attr('id','audioFile');
                $(this.audio).attr('preload','none');
            }

            //Get the songs from the queue
            if (list.songs) {
                $(this.audio).find('source').remove(); 
                this.queue = [];                
                $.each(list.songs, function (key,song) {
                    $.player.push(song);
                });
                //Display
                if(!this.queue.length){
                    this.digest();
                    return;
                }
                //We have something in the queue
                if(!this.song){
                    this.song = this.queue[0];
                    this.start();
                }
            }
        },
        //Empty the queue and reload a new queue, while preserving the property autplay
        reload: function () {
            var wasAutoplay = this.autoplay;
            this.reset();
            this.queue = [];
            this.song = null;
            this.load(this.list, wasAutoplay);
        },
        //Push a song to the last position in the queue
        push: function (data) {
            if(data.audio){
                if(!$(this.audio).find('[src="'+ data.audio +'"]').length){
                    $('<source>').attr({
                        'src':  data.audio,
                        'type': data.mime
                    }).prependTo($(this.audio));
                }                
                this.queue.push(data);     
                this.reorder();
            }
        },
        pop: function(data){
            if(data.audio){
                $(this.audio).find('[src="'+ data.audio +'"]').remove();            
                let index = parseInt(data.order) - 1;
                let result = [];
                for(let i=0;i<this.queue.length && index>0;i++){
                    if(i != index) {
                        result.push(this.queue[i]);                    
                    }
                }
                this.queue = result;
                this.reorder();
            }
        },
        unshift: function(data){
            if(data.audio){
                if(!$(this.audio).find('[src="'+ data.audio +'"]').length){
                    $('<source>').attr({
                        'src':  data.audio,
                        'type': data.mime
                    }).prependTo($(this.audio));
                } 
                this.queue.unshift(data);
                this.reorder();
            }
        },
        reorder: function(){
            $.each(this.queue,function(index){
                $.player.queue[index].order = index+1;
            });
        },
        //Select a song in the queue
        select: function(oid){
            if (!this.queue.length) return false;
            let found = false;
            $.each(this.queue,function(index,song){
                if( oid === song.oid ) {
                    $.player.track = index;
                    found = true;
                }
            });
            return found;
        },
        //Empty the queue, the player and its display
        empty: function () {
            $(this.audio).find('source').remove();      
            this.queue = [];
            this.track = 0;
            this.song = null;
            this.reset();
            this.digest();
        },
        //Reset the current track in the player and its display, but do not change the queue
        reset: function () {
            //Abort any pending ajax call
            if (this.call) this.call.abort();

            //Reset the timer if exists
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }

            //Pause the audio, put it to the zero and empty the content
            if (this.audio || this.isplaying) {
                this.audio.src='';                
                this.isplaying = false;
            }

            //Reset the properties of the song
            this.duration = 0;
            this.time = 0;
            this.lastTime = null;

            //Reset the content of the lyrics
            this.lyrics = null;
            this.counter = 0;
            this.timeline = [];
        },
        //Play the track loaded in the player
        play: function () {
            if (!this.song) this.start();
            if (!this.song) return;
            if (!this.isplaying) {
                this.audio.play();

                if ('mediaSession' in navigator) {
                    navigator.mediaSession.metadata = new MediaMetadata({
                      title: this.song.title,
                      artist:  this.song.artist,
                      album:  this.song.album,
                      genre:  this.song.genre,
                      artwork: [{ src:  this.song.thumbnail, sizes: '150x150', type: 'image/jpg' }]
                    });                 

                    navigator.mediaSession.setActionHandler('previoustrack', function() {
                        $.player.previous();
                    });
                      
                    navigator.mediaSession.setActionHandler('nexttrack', function() {
                        $.player.next();
                    });    
                                        
                    navigator.mediaSession.setActionHandler("seekto", (data) => {                                                
                        $.player.audio.currentTime = parseInt(data.seekTime);                       
                        $.player.time = parseInt($.player.audio.currentTime * 1000);
                        let percent = parseInt(100 * $.player.time / $.player.duration); 
                        $($.player.slider.value).css('width', percent + '%');
                    });
                    navigator.mediaSession.setActionHandler('play', function() {
                        $.player.isplaying = false;
                        $.player.play();
                    });

                    navigator.mediaSession.setActionHandler('pause', function() {
                        $.player.isplaying = true;
                        $.player.play();
                    });    
                    
                    navigator.mediaSession.setActionHandler('seekbackward', function() {});
                    navigator.mediaSession.setActionHandler('seekforward', function() {});                    
                }                   

                var promise = this.audio.play();                
                if (promise !== undefined) {
                  promise.then(_ => {
                    // Autoplay started!
                  }).catch(error => {
                    // Autoplay was prevented
                    $(this.buttons.play).removeClass('bi-pause');
                    $(this.buttons.play).addClass('bi-play');
                    $.rbfy.toast('Chrome blocks to play a track on this way. Please , click on the button play',true);
                  });
                }

                this.visualization();                                                                
                
                if(!this.lyrics) {
                    $(this.labels.equalizer).removeClass('d-none');
                    $(this.labels.lyrics).addClass('d-none');
                } else {
                    $(this.labels.equalizer).addClass('d-none');
                    $(this.labels.lyrics).removeClass('d-none');
                }
                $(this.buttons.play).removeClass('bi-play');
                $(this.buttons.play).addClass('bi-pause');
            } else {
                this.audio.pause();
                $(this.buttons.play).removeClass('bi-pause');
                $(this.buttons.play).addClass('bi-play');
            }
            this.isplaying = !this.isplaying;
        },
        //Rewind the song 5s
        rewind: function () {
            if (!this.queue.length) return;
            if (this.time > 5000)
                this.time = this.time - 5000;
            else
                this.time = 0;

            this.audio.currentTime = parseInt(this.time / 1000);
        },
        //Forward the song 5s
        forward: function () {
            if (!this.queue.length) return;
            if ((this.time + 5000) < this.duration)
                this.time = this.time + 5000;
            else
                this.time = this.duration;

            this.audio.currentTime = parseInt(this.time / 1000);
        },
        //Toggle the option repeat the queue
        repeat: function () {
            this.isrepeated = !this.isrepeated;
            if (!this.isrepeated) {
                $(this.buttons.repeat).css('color', 'grey');
            } else {
                $(this.buttons.repeat).css('color', 'white');
            }
        },
        //Toggle the option shuffle the queue
        shuffle: function () {
            this.isshuffle = !this.isshuffle;
            if (!this.isshuffle) {
                $(this.buttons.shuffle).css('color', 'grey');
            } else {
                $(this.buttons.shuffle).css('color', 'white');
            }
        },
        //Toggle the option to play the song in a loop
        loop: function () {
            if(!this.isloop && this.nloop==0){
                this.isloop = true;
                $(this.buttons.loop).css('color', 'white');
            } else if(this.isloop && this.nloop==0){
                this.nloop = 1;
                $(this.buttons.loop).removeClass('bi-repeat').addClass('bi-repeat-1');
            } else if(this.isloop && this.nloop == 1) {
                this.isloop = false;
                this.nloop = 0;
                $(this.buttons.loop).css('color', 'grey');
                $(this.buttons.loop).removeClass('bi-repeat-1').addClass('bi-repeat');
            }
            if(this.audio) this.audio.loop = this.isloop;
        },
        //Toggle the volume of the player
        mute: function () {
            if (!this.ismute) {
                this.audio.volume = false;
                $(this.buttons.volume).removeClass('bi-volume-up').addClass('bi-volume-mute');
            } else {
                this.audio.volume = true;
                $(this.buttons.volume).removeClass('bi-volume-mute').addClass('bi-volume-up');
            }
            this.ismute = !this.ismute;
            if(this.audio) this.audio.mute = this.ismute;
        },
        //Restore the lastest track in the history to the queue
        previous: function () {

            //Use the previous to go to time zero of the current song
            if(this.time>3000){
                let wasplaying = this.isplaying;
                this.start();
                if(wasplaying) this.play();
                return;
            }

            if (!this.history.length) return;

            let wasPlaying = this.isplaying;
            let last = this.history.length - 1;
            let newsong = this.history[last];

            $.getJSON($.rbfy.livesite+'/?task=queue.unshift&oid='+newsong.oid+'&format=json', function (data) {
                if(data.success){
                    $.player.unshift(data.result);
                    $.player.history.pop();
                    $.rbfy.queue.load(function(){
                        $.player.track = 0;
                        $.player.autoplay = wasPlaying;
                        $.player.start();
                        $.player.play();
                    });
                }
            });
        },
        //Play the next track in the queue
        next: function () {
            
            let wasPlaying = this.isplaying;
            let wasTrack = this.track;
            this.track++;

            //Let's pause the audio            
            if(this.isplaying) this.audio.pause();
            this.audio.src='';   

            //Shuffle the queue
            if(this.isshuffle){
                this.track = Math.floor(Math.random() * (this.queue.length-1));
                if(this.track == wasTrack) this.track++;
            }
            //Avoid overflow
            if (this.track > this.queue.length -1) this.track = 0;

            //Save the old and new songs
            let oldsong = this.queue[wasTrack];
            let newsong = this.queue[this.track];

            //Depending on the status of isrepeat, we keep the old track in the queue or not
            if (this.isrepeated){
                $.getJSON($.rbfy.livesite+'/?task=queue.popandpush&oid='+oldsong.oid+'&format=json',function(data){
                    if(data.success){
                        $.player.pop(oldsong);
                        $.player.push(oldsong);
                    }
                });
            } else {
                $.getJSON($.rbfy.livesite+'/?task=queue.pop&oid='+oldsong.oid+'&format=json',function(data){
                    if(data.success){
                        $.player.pop(oldsong);
                    }
                });
            }

            //The new song goes to the 1st position
            $.getJSON($.rbfy.livesite+'/?task=queue.popandunshift&oid='+newsong.oid+'&format=json', function (data) {
                if(data.success){
                    $.player.pop(newsong);
                    $.player.unshift(newsong);

                    //Refresh the HTML
                    $.rbfy.queue.load(function(){
                        $.player.track = 0;
                        $.player.autoplay = wasPlaying;
                        $.player.start();
                    });
                }
            });
        },
        //Push or Remove a song from the favorites
        favorite: function(){
            $.getJSON($.rbfy.livesite+'/?task=favorites.find&oid='+this.song.oid,function(result)
            {
                if(!result.success){
                    $.getJSON($.rbfy.livesite+'/?task=favorites.push&oid='+$.player.song.oid,function(result){
                        if(result.success){
                            $.rbfy.toast($.rbfy.labels['fav_added_success']);
                            $.player.song.isfavorite = true;
                            $.player.tagfavorite();
                        } else {
                            $.rbfy.toast($.rbfy.labels['fav_added_error'],false);
                        }
                    });
                } else {
                    $.getJSON($.rbfy.livesite+'/?task=favorites.pop&oid='+$.player.song.oid,function(result){
                        if(result.success){
                            $.rbfy.toast($.rbfy.labels['fav_removed_success']);
                            $.player.song.isfavorite = false;
                            $.player.tagfavorite();
                            $($.player.buttons.favorite).css({'color': 'gray'});
                        } else {
                            $.rbfy.toast($.rbfy.labels['fav_removed_error'],false);
                        }
                    });
                }
            });
        },
        //Check whether a song is in the favorites
        tagfavorite: function(){                        
            if(this.song.isfavorite){
                $($.player.buttons.favorite).removeClass('bi-heart').addClass('bi-heart-fill');                
                $($.player.buttons.favorite).css({'color': 'red'});
                $.rbfy.track.tagfavorite(this.song.oid,true);
            } else {                
                $($.player.buttons.favorite).removeClass('bi-heart-fill').addClass('bi-heart');
                $($.player.buttons.favorite).css({'color': 'gray'});                
                $.rbfy.track.tagfavorite(this.song.oid,false);
            }                       
        },
        //Load the current track from the queue to the player
        start: function () {

            //If we come from another song, put the current one to the history before switching
            if( this.audio.src != '' && this.song){
                this.history.push(this.song);
                //Truncate the history : 100 songs max.
                if(this.history.length>100){
                    this.history.slice(this.history.length-100);
                }
                //Save the history
                $.rbfy.history.save(this.song.oid ,this.time);
            }

            //Reset the current player
            this.reset();

            //If there is nothing in the queue, exit
            if(!this.queue.length) {
                this.song = null;
                return false;
            }

            //Select the song from the track
            this.song = this.queue[this.track];
            
            //Load the audio source  
            if( this.audio.src != this.song.audio){
                this.audio.src = this.song.audio;
                this.audio.type = this.song.mime;
                this.audio.preload = 'none';
                this.audio.load();
                $(this.audio).on("loadedmetadata", function() {
                    if($.player.audio.duration == 'Infinity')
                        $.player.audio.currentTime = 24*60*60;
                });
            }

            //Refresh the digest
            this.digest();

            //Tag if favorite
            this.tagfavorite()

            //Get the lyrics in asynchronous mode
            this.call = $.getJSON(this.song.extra['lyrics'], function (data) {
                //Prepare the lyrics
                if(data.success){
                    $.player.lyrics = data.lyrics;
                    $('<div class="lyrics-content"></div>').appendTo($($.player.labels.lyrics));
                } else {
                    $($.player.labels.lyrics).empty();
                }
                //Parse the content
                $.player.roll();
            });
            if(this.timer) clearInterval(this.timer);
            this.timer = setInterval(function () { $.player.refresh(); }, 1000);
        },
        //Refresh the current status of the player each 1s.
        refresh: function () {
            if (!this.song) return;

            if (this.duration == 0 || isNaN(this.duration)) {
                if(!this.audio){
                    this.duration = 0;
                } else {
                    this.duration = parseInt(this.audio.duration * 1000);
                }
                this.roll();
            }

            //for the end of the song
            if (this.time >= this.duration) {
                //If the song is looped
                if(this.isloop)
                {
                    if(this.nloop>0){
                        this.nloop = 0;
                        $(this.buttons.loop).css('color', 'white');
                        $(this.buttons.loop).removeClass('bi-repeat-1').addClass('bi-repeat');
                    } else {
                        $(this.buttons.loop).css('color', 'grey');
                        this.isloop = false;
                    }
                    this.autoplay = true;
                    this.start();

                } else {
                    $(this.buttons.loop).css('color', 'grey');
                    $(this.buttons.loop).removeClass('bi-repeat-1').addClass('bi-repeat');

                    //If there is a next track in the queue
                    if (this.track < this.queue.length - 1) {
                        //If the queue is repeated, push the old track to the end of the list
                        if (this.isrepeated) {
                            $.getJSON($.rbfy.livesite+'/?task=queue.popandpush&oid='+this.song.oid+'&format=json',function(data){
                                if(data.success){
                                    $.player.pop(data.result);
                                    $.player.push(data.result);
                                    $.rbfy.queue.load(function(){
                                        $.player.track = 0;
                                        $.player.autoplay = true;
                                        $.player.start();
                                    });
                                }
                            });
                        } else {
                            this.next();
                            this.play();
                        }
                    //This was the last song of the queue. Stop
                    } else {
                       this.empty();
                    }
                }
                return;
            }
            //update timer
            if (this.isplaying) {
                this.time = this.time + 1000;
            }
            //update time on the progress bar
            if (this.audio.currentTime != this.lastTime) {
                this.lastTime = this.audio.currentTime;
                $(this.slider.now).html(this.format(this.time));
                var percent = this.time / this.duration * 100;
                $($.player.slider.value).css('width', percent + '%');
            } else {
                this.time = parseInt(this.audio.currentTime * 1000);
                if (this.time > 100) this.time = this.time - 100;
                if(this.autoplay && this.audio.readyState < 4){
                    $(this.labels.info).addClass('d-none');
                    $(this.labels.loading).removeClass('d-none');
                } else {
                    $(this.labels.loading).addClass('d-none');
                    $(this.labels.info).removeClass('d-none');
                }
                if (this.autoplay && this.audio.readyState== 4) {
                    this.audio.pause();
                    this.play();
                    this.autoplay = false;
                }
            }

            var safeKill = 0;
            while (this.timeline.length) {
                safeKill += 1;
                if (safeKill >= 100) break;

                if (this.counter == 0 && (this.time < this.timeline[this.counter])){
                    this.nextphrase();
                    break;
                }

                if ((this.counter == this.timeline.length) && (this.time <= this.timeline[this.counter - 1])){
                    this.counter--;
                    this.prevphrase();
                }

                if (this.time >= this.timeline[this.counter]) {
                    if (this.counter <= this.timeline.length) this.counter++;
                    this.nextphrase();
                }
                else if (this.time < this.timeline[this.counter - 1]) {
                    this.counter--;
                    this.prevphrase();
                } else {
                    if(this.isplaying && !this.audio.paused && !this.audio.ended) this.centerize();
                    break;
                }
            }
        },
        //Display the details of the current track from the player
        digest: function () {

            $(this.labels.loading).addClass('d-none');
            $(this.labels.info).removeClass('d-none');

            if (!this.song || isNaN(this.duration)) {
                $(this.slider.now).html(this.format(0));
                $(this.slider.total).html(this.format(0));
                $(this.labels.title).html($(this.labels.title).attr('data-default'));
                $(this.labels.artist).html($(this.labels.artist).attr('data-default'));             
                $(this.labels.art).attr('src',$(this.labels.art).attr('data-default'));
                $(this.buttons.play).removeClass('bi-pause');
                $(this.buttons.play).addClass('bi-play');
                $($.player.slider.value).css('width', '0%');
                return;
            }

            if(this.song.title == '') this.song.title = $(this.labels.title).attr('data-default');
            if(this.song.artist == '') this.song.artist = $(this.labels.artist).attr('data-default');
            if(this.song.thumbnail == '') this.song.thumbnail = $(this.labels.art).attr('data-default');

            $(this.labels.title).html(this.song.title);
            $(this.labels.artist).html(this.song.artist);
            $(this.labels.art).attr('src',this.song.thumbnail);

            this.roll();
        },
        roll: function(){

            //Move the slider
            if (this.song && this.duration) {
                $(this.slider.now).html(this.format(this.time));
                $(this.slider.total).html(this.format(this.duration));
                var percent = 100 * this.time / this.duration;
                $(this.slider.value).css('width', percent + '%');
            }

            //Reset the lyrics and reload its content
            this.timeline = [];
            var html = '';
            if (this.lyrics) {
                for (var i = 0; i < this.lyrics.length; i++) {
                    this.timeline.push(this.lyrics[i].time);
                    html = html + '<p>' + this.lyrics[i].text + '</p>';
                }
                $(this.labels.lyrics).find('.lyrics-content').html(html);
            }
        },
        //Creates a visualisation from the current track in the player
        visualization: function()
        {
            if(!this.equalizer.active) {
                this.equalizer.context  = new AudioContext();
                var source   =  this.equalizer.context.createMediaElementSource(this.audio);
                this.equalizer.analyser =  this.equalizer.context.createAnalyser();
                source.connect(this.equalizer.analyser);
            }
            this.equalizer.active = true;
            this.equalizer.analyser.connect(this.equalizer.context.destination);
            this.equalizer.analyser.fftSize = 256;
            this.equalizer.blength  = this.equalizer.analyser.frequencyBinCount;
            this.equalizer.data     = new Uint8Array(this.equalizer.blength);

            const canvas = $(this.labels.equalizer).get(0);
            canvas.width = $(canvas).parent().width();
            canvas.height = $(canvas).parent().height();

            this.equalizer.ctx      = canvas.getContext('2d');
            this.equalizer.width    = canvas.width;
            this.equalizer.height   = canvas.height;

            this.renderFrame();
        },
        renderFrame: function()
        {
            requestAnimationFrame($.player.renderFrame);

            $.player.equalizer.analyser.getByteFrequencyData($.player.equalizer.data);

            $.player.equalizer.ctx.fillStyle =  '#000';
            $.player.equalizer.ctx.fillRect(0, 0, $.player.equalizer.width , $.player.equalizer.height);

            for (var i = 0 , x=0 , barWidth=0 , barHeight=0; i < $.player.equalizer.blength; i++) {

                barWidth    = ($.player.equalizer.width / $.player.equalizer.blength) * 2.5;
                barHeight   = $.player.equalizer.data[i];

                var r = barHeight + (25 * ( i / $.player.equalizer.blength ));
                var g = 250 * (i / $.player.equalizer.blength);
                var b = 50;

                $.player.equalizer.ctx.fillStyle = 'rgb(' + r + ',' + g + ',' + b + ')';
                $.player.equalizer.ctx.fillRect( x , $.player.equalizer.height - barHeight , barWidth , barHeight);
                x += barWidth + 1;
            }
        },
        nextphrase: function(){
            var phrase = $(this.labels.lyrics).find('.phrase');
            if(!phrase.length){
                $(this.labels.lyrics).find('p:nth-child(1)').addClass('phrase');
                return;
            }
            phrase.removeClass('phrase');
            phrase.next().addClass('phrase');
        },
        prevphrase: function() {
            var phrase = $(this.labels.lyrics).find('.phrase');
            if(!phrase.length) return;
            var first = $(this.labels.lyrics).find('p:nth-child(1)');
            phrase.removeClass('phrase');
            if(phrase === first) return;
            phrase.prev().addClass('phrase');
        },
        centerize: function() {
            if(!this.isplaying) return;

            var phrase = $(this.labels.lyrics).find('.phrase');
            if(!phrase.length) return;
            var a = $(this.labels.lyrics).find('.phrase').height();
            var c = $(this.labels.lyrics).height();
            var d = $('.phrase').offset().top - $('.phrase').parent().offset().top;
            var e = d + (a/2) - (c*1/4);
            $(this.labels.lyrics).animate(
                {scrollTop: e + 'px'}, {easing: 'swing', duration: 500}
            );
        },
        progress: function () {
            dragHandler = (event) => {
                event.preventDefault();
                if (event.offsetY > 10 || event.offsetY < 1) return;
                var width = $(this.slider.container).css('width');
                var percent = parseInt(event.offsetX) / parseInt(width) * 100;
                $(this.slider.value).css('width', percent + '%');                
                this.time = parseInt(this.duration * (percent / 100));
                if(isNaN(this.time)) this.time = this.currentTime;                
                this.audio.currentTime = parseInt(this.time / 1000);
            }
        },
        format: function (a) {
            var b = parseInt(a / 60000);
            var c = parseInt((a % 60000) / 1000);
            if (c < 10) { c = '0' + c; }
            return b + ':' + c;
        }
    }
});

jQuery(document).ready(function($) {

    $('#prev').on('click tap', function (event) {
        event.preventDefault();
        $.player.previous();
    });
    $('#next').on('click tap', function (event) {
        event.preventDefault();
        $.player.next();
    });
    $('#play').on('click tap', function (event) {
        event.preventDefault();
        $.player.play();
    });

    //Slider for Mobile
    $($.player.slider.bar).on('touchstart', function (e) {
        $($.player.slider.bar).on('touchmove', function handler(ev) {
            e.preventDefault();
            var touch = ev.originalEvent.touches[0] || ev.originalEvent.changedTouches[0];
            var elm = $(this).offset();
            var pos = touch.pageX - elm.left;
            var max = $($.player.slider.bar).width();
            if(pos > 0 && pos < max)
            {
                var percent = parseInt(pos) / parseInt(max) * 100;
                $($.player.slider.value).css('width', percent + '%');
                $.player.time = parseInt($.player.duration * (percent / 100));                
                $.player.audio.currentTime = parseInt($.player.time/1000);
            }
        });
    });
    $($.player.slider.bar).on('touchend', function () {           
        $.player.audio.currentTime = parseInt($.player.time/1000);
        $($.player.slider.bar).off('touchmove');
    });
    $($.player.slider.marker).on('touchstart', function () {
        $.player.progress();
    });   
    $($.player.slider.marker).on('touchend', function () {
        $.player.audio.currentTime = parseInt($.player.time/1000);
        $($.player.slider.bar).off('touchmove');
    });      

    //Slider for PC
    $($.player.slider.bar).on('mousedown', function (e) {
        $($.player.slider.bar).on('mousemove', function handler(ev){
            if(ev.offsetY > 5 || ev.offsetY < 1) return;
            var width = $($.player.slider.bar).css('width');
            var percent = parseInt(ev.offsetX) / parseInt(width) * 100;
            $($.player.slider.value).css('width', percent + '%');
            $.player.time = parseInt($.player.duration * (percent / 100));
            $.player.audio.currentTime = parseInt($.player.time/1000);
        });
    });    
    $($.player.slider.bar).on('mouseup', function () { 
        $($.player.slider.bar).off('mousemove');  
    });
    $($.player.slider.marker).on('mousedown', function () {
        $.player.progress();
    });    
    $($.player.slider.marker).on('mouseup', function () {
        $($.player.slider.bar).off('mousemove');
    });


});