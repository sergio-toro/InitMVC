<!DOCTYPE html>
<html lang="<?=$lang?>">
    <head>
        <!-- start: Meta -->
        <meta charset="utf-8">
        <title>animet.org</title> 
        <meta name="description" content="Greate Bootstrap Theme"/>
        <meta name="keywords" content="Template, Theme, web, html5, css3, Bootstrap" />
        <meta name="author" content="Łukasz Holeczek from creativeLabs"/>
        <!-- end: Meta -->
        
        <!-- start: Mobile Specific -->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <!-- end: Mobile Specific -->
        
        <!-- start: Facebook Open Graph -->
        <meta property="og:title" content=""/>
        <meta property="og:description" content=""/>
        <meta property="og:type" content=""/>
        <meta property="og:url" content=""/>
        <meta property="og:image" content=""/>
        <!-- end: Facebook Open Graph -->

        <!-- start: CSS -->
        <link rel="stylesheet" type="text/css" href="/css/<?=$css?>"/>
        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Droid+Sans:400,700|Signika">
        <!--link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Signika"-->
        <!--link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Droid+Serif"-->
        <!--link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Economica:700,400italic"-->
        <!-- end: CSS -->

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <script>
            /* jQuery hook to load at the end of the document */
            var onReadyProto = function() {
                this.callbacks = [];
                this.call = function(fn) {
                    if (window.jQuery) fn();
                    else this.callbacks.push(fn);
                };
                this.ready = function() {
                    if (!this.callbacks.length) return;
                    // Execute pending callbacks
                    for (var i = 0; i<onReady.callbacks.length; i++) {
                        try {
                            onReady.callbacks[i]();
                        } catch(e) { }
                    };
                    this.callbacks = [];
                }
            };
            window.onReady = new onReadyProto();
        </script>
    </head>
    <body>
        

        <!-- start: main content -->
        <div id="ajaxSend">
            <?php Init_Template::display($include_file, $data); ?>
        </div>
        <!-- end: main content -->

        

        <!-- start: JS includes: Placed at the end of the document so the pages load faster -->
        <!-- Link to Google CDN's jQuery; fall back to local -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/js/example/jquery.min.js"><\/script>')</script>
        <!-- Compressed Init::JS files -->
        <script type="text/javascript" src="/js/<?=$js?>"></script>
        <!-- end: JS includes -->
        <script>
            $(function(){
                // Call queued scripts
                onReady.ready();
            });
        </script>


    </body>
</html>