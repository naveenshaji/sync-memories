<?php require_once( 'admin/cms.php' ); ?>
<cms:template title='Home'>
    <cms:editable name="demotitle" label="Demo Title" type='text' />
    <cms:editable type='textarea' name='demonote' height='200' label='Demo Note' />
</cms:template>
<cms:member_check_login />
<cms:if k_member_logged_in>
    <!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!-->
    <html class="no-js">
    <!--<![endif]-->

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>easyNOT.ES</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <!--<link href="css/bootstrap-theme.css" rel="stylesheet">-->
        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
        <link href='http://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
        <script src="js/vendor/modernizr-2.6.2.min.js"></script>
    </head>

    <body role="document" onload="checkFileAPI();">
        <!--[if lt IE 7]>
              <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
          <![endif]-->


        <header class="masthead">
            <div class="container">
                <div class="row" style="margin-top:40px;">
                    <div id="new-note-form-div" class="col-sm-8">
                        <cms:set submit_success="<cms:get_flash 'submit_success' />" />
                        <cms:if submit_success> success </cms:if>
                        <cms:form masterpage='notes.php' mode='create' enctype='multipart/form-data' method='post' anchor='0'>
                            <cms:if k_success>
                                <div id="db_persist_form_div">
                                    <cms:db_persist_form _invalidate_cache='0' _auto_title='0' author_id=k_member_id />
                                </div>
                                <cms:set_flash name='submit_success' value='1' />
                                <cms:redirect 'categories.php' />
                            </cms:if>
                            <cms:if k_error>
                                <div class="error">
                                    <cms:each k_error>
                                        <br>
                                        <cms:show item />
                                    </cms:each>
                                </div>
                            </cms:if>
                            <h4 class="bold" style="text-transform:uppercase; margin-bottom:20px;">Add New note</h4>
                            <div class="form-group">
                                <cms:input class="form-control" placeholder="New note title" name="k_page_title" type="bound" />
                            </div>
                            <div class="form-group">
                                <select class="form-control" id="note_cat_selector">
                                    <option>Uncategorized</option>
                                    <cms:pages masterpage="categories.php" custom_field="author_id=<cms:show k_member_id />">
                                        <option value="<cms:show k_page_name />">
                                            <cms:show k_page_title />
                                        </option>
                                    </cms:pages>
                                </select>
                            </div>
                            <div class="form-group">
                                <cms:input class="form-control display-none" name="note_cat" type="bound" />
                            </div>
                            <div class="form-group">
                                <cms:input class="form-control note-textarea" placeholder="New note" name="note" type="bound" />
                            </div>
                            <cms:if "<cms:not submit_success />">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-default" style="margin-right:0.5em;">Add Note</button>
                                </div>
                            </cms:if>
                        </cms:form>
                    </div>
                    <div class="col-sm-4">
                        <h4 class="bold" style="text-transform:uppercase; margin-bottom:20px;">Or just drag and drop a txt file</h4>
                        <input type="file" class="form-control" onchange='readText(this)' />
                        <br/> Contents of file will appear on the form, where you can add a title and choose a category before submitting.
                        <hr/> P.S. Notes once added can never be edited or deleted. If shared with others, they can never be unshared. Tread carefully. You have been warned.
                        <hr/> This website is currently in beta. All your notes will be retained in the full release also. Bugs can be reported by making a note with said bug/error and sharing with [ID=8] Naveen Shaji.
                    </div>
                </div>
            </div>
        </header>


        <div id="nav">
            <nav class="navbar navbar-default navbar-static" id="notes-navbar">
                <div class="container-fluid">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="index.php">
                            <x class="bold">easy</x>NOT.ES</a>
                    </div>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav" id="filters">

                            <li><a data-filter="*">Show All </a>
                            </li>
                            <li><a data-filter=".mynotesxdxd<cms:show k_member_name />">My Notes </a>
                            </li>
                            <li><a data-filter=".sharednotesxdxd<cms:show k_member_name />">Shared Notes </a>
                            </li>

                            <li class="dropdown">
                                <a href="" class="dropdown-toggle" data-toggle="dropdown">Categories <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <cms:pages masterpage='categories.php' custom_field="author_id=<cms:show k_member_id />">
                                        <li>
                                            <a data-filter=".<cms:show k_page_name />">
                                                <cms:show k_page_title />
                                            </a>
                                        </li>
                                    </cms:pages>
                                    <li class="divider"></li>
                                    <li><a data-toggle="modal" data-target="#add-category">Add Category</a>
                                    </li>
                                </ul>
                            </li>

                            <li class="dropdown">
                                <a href="" class="dropdown-toggle" data-toggle="dropdown">Friends <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <cms:pages masterpage='friends.php' custom_field="author_id=<cms:show k_member_id />">
                                        <li><a data-filter=".shared<cms:show friend_id />">
			<cms:pages masterpage="members/index.php" id="<cms:show friend_id />">
			<cms:show k_page_title />
			</cms:pages>
			[ ID = <cms:show friend_id /> ]
			</a>
                                        </li>
                                    </cms:pages>
                                    <li class="divider"></li>
                                    <li><a data-toggle="modal" data-target="#add-friend">Add Friends</a>
                                    </li>
                                </ul>
                            </li>

                        </ul>
                        <form class="navbar-form navbar-left" role="search">
                            <div class="form-group">
                                <input type="text" style="width:100%" class="form-control" placeholder="Search notes" id="discussion-search">
                            </div>
                        </form>
                        <ul class="nav navbar-nav navbar-right">

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">[ ID = <cms:show k_member_id /> ] <cms:show k_member_title /> <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a data-toggle="modal" data-target="#profile-modal">Settings</a>
                                    </li>
                                    <li><a data-toggle="modal" data-target="#profile-modal">Profile</a>
                                    </li>
                                    <li>
                                        <a data-toggle="modal" data-target="#profile-modal">
                                            <cms:show k_member_email />
                                        </a>
                                    </li>
                                    <li class="divider"></li>
                                    <li><a href="<cms:member_logout_link />">Logout</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <!-- /.navbar-collapse -->
                </div>
                <!-- /.container-fluid -->
            </nav>
        </div>


        <div class="container" id="notes-container">
            <div class="isotope">

                <!--NOTES-->
                <cms:pages masterpage='notes.php' custom_field="author_id=<cms:show k_member_id />">
                    <div class="item mynotesxdxd<cms:show k_member_name /> <cms:show note_cat /> non-search" data-toggle="modal" data-target="#<cms:show k_page_name />">
                        <div class="item-inner">
                            <h4 class="bold" style="text-transform:uppercase; margin-bottom:5px;"><cms:show k_page_title /></h4>
                            <h6 style="margin-top:5px;"><cms:date k_page_date format='jS M, Y' /></h6>
                            <cms:show note />
                        </div>
                    </div>
                </cms:pages>

                <!--SHARED NOTES-->
                <cms:pages masterpage='share.php' custom_field="friend_id=<cms:show k_member_id />">
                    <cms:pages masterpage='notes.php' page_name="<cms:show k_page_title />">
                        <div class="item sharednotesxdxd<cms:show k_member_name /> shared<cms:show author_id /> non-search" data-toggle="modal" data-target="#<cms:show k_page_name />">
                            <div class="item-inner">
                                <h4 class="bold" style="text-transform:uppercase; margin-bottom:5px;"> 
				<cms:pages masterpage="members/index.php" id="<cms:show author_id />">
				[<cms:show k_page_title />] 
				</cms:pages>
				<cms:show k_page_title /></h4>
                                <h6 style="margin-top:5px;"><cms:date k_page_date format='jS M, Y' /></h6>
                                <cms:show note />
                            </div>
                        </div>
                    </cms:pages>
                </cms:pages>

                <!--DEMO NOTE-->
                <div class="item mynotesxdxd<cms:show k_member_name /> non-search" data-toggle="modal" data-target="#demonote<cms:show k_member_name />">
                    <div class="item-inner">
                        <h4 class="bold" style="text-transform:uppercase; margin-bottom:5px;"><cms:show demotitle /></h4>
                        <h6 style="margin-top:5px;"><cms:date k_member_creation_date format='jS M, Y' /></h6>
                        <cms:show demonote />
                    </div>
                </div>

            </div>
        </div>

        <!--NOTES MODALS-->
        <cms:pages masterpage='notes.php' custom_field="author_id=<cms:show k_member_id />">
            <div class="modal fade" id="<cms:show k_page_name />" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                            </button>
                            <h2 class="modal-title bold" id="myModalLabel"><cms:show k_page_title /></h2>
                            <h5 style="margin-top:5px; margin-bottom:0px;">
		Added on <cms:date k_page_date format='jS M, Y' /><cms:pages masterpage="categories.php" page_name=note_cat>
            <cms:if k_total_records = '1'>
             in category "<cms:show k_page_title/>"
            </cms:if>
            </cms:pages>
		<br /><br />
		Shared with 
		<cms:pages masterpage="share.php" page_title="<cms:show k_page_name />">
		<cms:pages masterpage="members/index.php" id="<cms:show friend_id />">
			<cms:show k_page_title />, 
		</cms:pages>
		</cms:pages>
		</h5>
                        </div>
                        <div class="modal-body">
                            <cms:nl2br>
                                <cms:show note /></cms:nl2br>
                        </div>
                        <div class="modal-footer">

                            <cms:set submit_success="<cms:get_flash 'submit_success' />" />
                            <cms:if submit_success> success </cms:if>
                            <cms:form masterpage='share.php' mode='create' enctype='multipart/form-data' method='post' anchor='0'>

                                <cms:if k_success>

                                    <cms:db_persist_form _invalidate_cache='0' _auto_title='0' author_id=k_member_id />

                                    <cms:set_flash name='submit_success' value='1' />
                                    <cms:redirect 'share.php' />
                                </cms:if>

                                <cms:if k_error>
                                    <div class="error">
                                        <cms:each k_error>
                                            <br>
                                            <cms:show item />
                                        </cms:each>
                                    </div>
                                </cms:if>
                                <div class="form-group" style="float:left;">
                                    <div class="form-inline">
                                        Share with :
                                        <select id="share_friend" class="form-control">
                                            <option disabled="disabled" selected="selected">Choose Friend</option>
                                            <cms:pages masterpage='friends.php' custom_field="author_id=<cms:show k_member_id />">
                                                <cms:pages masterpage="members/index.php" id="<cms:show friend_id />">
                                                    <option value="<cms:show k_page_id />">
                                                        <cms:show k_page_title />
                                                    </option>
                                                </cms:pages>
                                            </cms:pages>
                                        </select>
                                    </div>
                                </div>
                                <cms:input name="friend_id" style="display:none" type="bound" />
                                <cms:input name="k_page_title" style="display:none" type="bound" />

                                <cms:if "<cms:not submit_success />">
                                    <button type="submit" class="btn btn-primary">Add Share</button>
                                </cms:if>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </cms:form>
                        </div>
                    </div>
                </div>
            </div>
        </cms:pages>

        <!--SHARED NOTES MODALS-->
        <cms:pages masterpage='share.php' custom_field="friend_id=<cms:show k_member_id />">
            <cms:pages masterpage='notes.php' page_name="<cms:show k_page_title />">
                <div class="modal fade" id="<cms:show k_page_name />" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                                </button>
                                <h2 class="modal-title bold" id="myModalLabel"><cms:show k_page_title /></h2>
                                <h5 style="margin-top:5px; margin-bottom:0px;">
		<cms:date k_page_date format='jS M, Y' />
		<br /><br />
		Shared by 
		<cms:pages masterpage="members/index.php" id="<cms:show author_id />">
				<cms:show k_page_title />
		</cms:pages>
		[ ID = <cms:show author_id /> ]
		</h5>
                            </div>
                            <div class="modal-body">
                                <cms:nl2br>
                                    <cms:show note /></cms:nl2br>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </cms:pages>
        </cms:pages>

        <!--        DEMO NOTE MODAL-->
        <div class="modal fade" id="demonote<cms:show k_member_name />" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                        </button>
                        <h2 class="modal-title bold" id="myModalLabel"><cms:show demotitle /></h2>
                        <h5 style="margin-top:5px; margin-bottom:0px;">
              <cms:date k_member_creation_date format='jS M, Y' />
          </h5>
                    </div>
                    <div class="modal-body">
                        <cms:nl2br>
                            <cms:show demonote/></cms:nl2br>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!--ADD CATEGORY MODAL-->
        <div class="modal fade" id="add-category" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                        </button>
                        <h2 class="modal-title bold" id="myModalLabel">Add a new category</h2>
                    </div>
                    <div class="modal-body">
                        <cms:set submit_success="<cms:get_flash 'submit_success' />" />
                        <cms:if submit_success> success </cms:if>
                        <cms:form masterpage='categories.php' mode='create' enctype='multipart/form-data' method='post' anchor='0'>

                            <cms:if k_success>

                                <cms:db_persist_form _invalidate_cache='0' _auto_title='0' author_id=k_member_id />

                                <cms:set_flash name='submit_success' value='1' />
                                <cms:redirect 'categories.php' />
                            </cms:if>

                            <cms:if k_error>
                                <div class="error">
                                    <cms:each k_error>
                                        <br>
                                        <cms:show item />
                                    </cms:each>
                                </div>
                            </cms:if>
                            <div class="form-group">
                                Categories once added cannot be removed. Tread carefully. You have been warned.
                            </div>
                            <div class="form-group">
                                <cms:input class="form-control" placeholder="New category name" name="k_page_title" type="bound" />
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <cms:if "<cms:not submit_success />">
                            <button type="submit" class="btn btn-primary">Add Category</button>
                        </cms:if>
                    </div>
                    </cms:form>
                </div>
            </div>
        </div>

        <!--ADD FRIEND MODAL-->
        <div class="modal fade" id="add-friend" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                        </button>
                        <h2 class="modal-title bold" id="myModalLabel">Add Friend</h2>
                    </div>
                    <div class="modal-body">
                        <cms:set submit_success="<cms:get_flash 'submit_success' />" />
                        <cms:if submit_success> success </cms:if>
                        <cms:form masterpage='friends.php' mode='create' enctype='multipart/form-data' method='post' anchor='0'>

                            <cms:if k_success>

                                <cms:db_persist_form _invalidate_cache='0' _auto_title='1' author_id=k_member_id />

                                <cms:set_flash name='submit_success' value='1' />
                                <cms:redirect 'friends.php' />
                            </cms:if>

                            <cms:if k_error>
                                <div class="error">
                                    <cms:each k_error>
                                        <br>
                                        <cms:show item />
                                    </cms:each>
                                </div>
                            </cms:if>
                            <div class="form-group">
                                Friends once added cannot be removed. Tread carefully. You have been warned.
                            </div>
                            <div class="form-group">
                                <cms:input class="form-control" placeholder="Friend's ID number" name="friend_id" type="bound" />
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <cms:if "<cms:not submit_success />">
                            <button type="submit" class="btn btn-primary">Add Friend</button>
                        </cms:if>
                    </div>
                    </cms:form>
                </div>
            </div>
        </div>

        <!--PROFILE MODAL-->
        <div class="modal fade" id="profile-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                        </button>
                        <h2 class="modal-title bold" id="myModalLabel">Edit Profile</h2>
                    </div>
                    <div class="modal-body">
                        <cms:set success_msg="<cms:get_flash 'success_msg' />" />
                        <cms:if success_msg>
                            <h4>Profile updated.</h4>
                        </cms:if>

                        <!-- this ia regular databound-form -->
                        <cms:form masterpage=k_member_template mode='edit' page_id=k_member_id enctype="multipart/form-data" method='post' anchor='0'>

                            <cms:if k_success>
                                <cms:db_persist_form />

                                <cms:set_flash name='success_msg' value='1' />
                                <cms:redirect k_page_link />
                            </cms:if>

                            <cms:if k_error>
                                <font color='red'><cms:each k_error ><cms:show item /><br /></cms:each></font>
                            </cms:if>
                            <div class="form-group">
                                Display Name:
                                <cms:input type="bound" placeholder="Display Name" name="k_page_title" class="form-control" />
                            </div>

                            <div class="form-group">
                                New Password:
                                <cms:input type="bound" placeholder="New Password" name="member_password" class="form-control" />
                            </div>
                            <div class="form-group">
                                Repeat Password:
                                <cms:input type="bound" placeholder="Repeat New Password" name="member_password_repeat" class="form-control" />
                            </div>

                            <!--<input type="submit" name="submit" value="Save"/>  -->

                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <cms:if "<cms:not submit_success />">
                                    <button type="submit" name="submit" value="Save" class="btn btn-primary">Save</button>
                                </cms:if>
                            </div>
                        </cms:form>
                    </div>
                </div>
            </div>

            <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
            <script>
                window.jQuery || document.write('<script src="js/vendor/jquery-1.10.2.min.js"><\/script>')
            </script>
            <script src="js/bootstrap.min.js"></script>
            <script src="js/isotope.pkgd.min.js"></script>
            <script src="js/plugins.js"></script>
            <script src="js/jquery.quicksearch.js"></script>
            <script src="js/main.js"></script>






            <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
            <script>
                (function (b, o, i, l, e, r) {
                    b.GoogleAnalyticsObject = l;
                    b[l] || (b[l] =
                        function () {
                            (b[l].q = b[l].q || []).push(arguments)
                        });
                    b[l].l = +new Date;
                    e = o.createElement(i);
                    r = o.getElementsByTagName(i)[0];
                    e.src = '//www.google-analytics.com/analytics.js';
                    r.parentNode.insertBefore(e, r)
                }(window, document, 'script', 'ga'));
                ga('create', 'UA-XXXXX-X');
                ga('send', 'pageview');
            </script>
            <script>
                //SEARCH
                $(function () {

                    $container = $('.isotope');
                    $('input#discussion-search').quicksearch('.isotope .item', {
                        'show': function () {
                            $(this).addClass('quicksearch-match');
                        },
                        'hide': function () {
                            $(this).removeClass('quicksearch-match');
                        }
                    }).keyup(function () {
                        setTimeout(function () {
                            $container.isotope({
                                filter: '.quicksearch-match'
                            }).isotope();
                        }, 100);
                        var enter = $('input#discussion-search').val();

                    });

                });
            </script>
            <script>
                var $container = $('.isotope');
                $('#note_cat_selector').change(function () {
                    $("#f_note_cat").val($(this).val());
                });
            </script>

            <script>
                $('.isotope').on('click', 'div.item', function () {
                    var note_name_modal = $(this).attr('data-target');
                    $('[id=f_k_page_title][style="display:none"]').val(note_name_modal.replace('#', ''));
                });

                $('[id=share_friend]').change(function () {
                    var share_this_friend = $(this).val();
                    $("[id=f_friend_id]").val(share_this_friend); //EDIT NEEDED HERE
                });
            </script>

            <!--files api check-->
            <script type="text/javascript">
                var reader;

                function checkFileAPI() {
                    if (window.File && window.FileReader && window.FileList && window.Blob) {
                        reader = new FileReader();
                        return true;
                    } else {
                        return false;
                    }
                }

                /**
                 * read text input
                 */
                function readText(filePath) {
                    var output = ""; //placeholder for text output
                    if (filePath.files && filePath.files[0]) {
                        reader.onload = function (e) {
                            output = e.target.result;
                            displayContents(output);
                        }; //end onload()
                        reader.readAsText(filePath.files[0]);
                    } //end if html5 filelist support
                    else if (ActiveXObject && filePath) { //fallback to IE 6-8 support via ActiveX
                        try {
                            reader = new ActiveXObject("Scripting.FileSystemObject");
                            var file = reader.OpenTextFile(filePath, 1); //ActiveX File Object
                            output = file.ReadAll(); //text contents of file
                            file.Close(); //close file "input stream"
                            displayContents(output);
                        } catch (e) {
                            if (e.number == -2146827859) {
                                alert('Unable to access local files due to browser security settings. ' +
                                    'To overcome this, go to Tools->Internet Options->Security->Custom Level. ' +
                                    'Find the setting for "Initialize and script ActiveX controls not marked as safe" and change it to "Enable" or "Prompt"');
                            }
                        }
                    } else {
                        return false;
                    }
                    return true;
                }

                function displayContents(txt) {
                    $('.note-textarea').val(txt);
                }
            </script>

    </body>

    </html>
    <cms:else />
    <cms:redirect 'members/login.php' />
</cms:if>

<?php COUCH::invoke(); ?>