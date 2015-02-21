<?php require_once( '../admin/cms.php' ); ?>
<cms:template title='Login' hidden='1' />
<cms:member_check_login />
<cms:if k_member_logged_in>
    <cms:set action="<cms:gpc 'act' method='get'/>" />
    <cms:if action='logout'>
        <cms:member_process_logout />
        <cms:else />
        <cms:redirect k_site_link />
    </cms:if>
    <cms:else />


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
        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <!--<link href="css/bootstrap-theme.css" rel="stylesheet">-->
        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
        <link href='http://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="../css/normalize.css">
        <link rel="stylesheet" href="../css/main.css">
        <script src="../js/vendor/modernizr-2.6.2.min.js"></script>
    </head>

    <body role="document">
        <!--[if lt IE 7]>
              <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
          <![endif]-->



        <header class="masthead">
            <div class="container">
                <div class="row">
                    <h1 id="jumbo-title"><x class="bold">sync</x><x class="light">MEMORI.ES</x></h1>
                    <p id="jumbo-subtitle">Always keep your memories backed up,</p>
                    <p id="jumbo-sub">You never know when the Alzheimer's starts kicking in.</p>
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
                        <a class="navbar-brand" href="/">
                            <x class="bold">sync</x>MEMORI.ES</a>
                    </div>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav" id="filters">
                            <li><a data-filter="*">Show All </a>
                            </li>
                            <li><a data-filter=".sign-up">Sign up</a>
                            </li>
                            <li><a data-filter=".log-in">Log in</a>
                            </li>
                            <li><a data-filter=".forgot-pwd">Forgot Password?</a>
                            </li>
                            <li><a data-filter=".about-us">About notes</a>
                            </li>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">

                        </ul>
                    </div>
                    <!-- /.navbar-collapse -->
                </div>
                <!-- /.container-fluid -->
            </nav>
        </div>


        <div class="container" id="notes-container">
            <div class="isotope">


                <!--ABOUT NOTES PART-->
                <div class="item w2 about-us sign-up log-in forgot-pwd">
                    <div class="item-inner">
                        <h1>About syncMEMORIES</h1> I awake to the perfect darkness of my room when I feel my cat resting on my chest, and have since been frozen in terror from the shocking lack of fur when I pet her. After half an hour, my eyes have adjusted to see the gargantuan insect with a fuzzy tail between its mandibles.
                    </div>
                </div>


                <!--REGISTER PART-->
                <div class="item w2 sign-up">
                    <div class="item-inner">
                        <cms:set success_msg="<cms:get_flash 'success_msg' />" />
                        <cms:if success_msg>
                            <div class="notice">
                                <cms:if success_msg='1'>
                                    Your account has been created successfully and we have sent you an email.
                                    <br /> Please click the verification link within that mail to activate your account.
                                    <cms:else /> Activation was successful! You can now log in!
                                    <br />
                                    <a href="<cms:member_login_link />">Login</a>
                                </cms:if>
                            </div>
                            <cms:else />
                            <cms:set action="<cms:gpc 'act' method='get'/>" />
                            <cms:if action='activate'>
                                <h1>Activate account</h1>
                                <cms:member_process_activation />
                                <cms:if k_success>
                                    <cms:set_flash name='success_msg' value='2' />
                                    <cms:redirect k_page_link />
                                    <cms:else />
                                    <cms:show k_error />
                                </cms:if>
                                <cms:else />
                                <h1>Create an account</h1>
                                <cms:form enctype="multipart/form-data" method='post' anchor='0' id='registerForm'>
                                    <cms:if k_success>
                                        <cms:member_process_registration_form />
                                        <cms:if k_success>
                                            <cms:set_flash name='success_msg' value='1' />
                                            <cms:redirect k_page_link />
                                        </cms:if>
                                    </cms:if>
                                    <cms:if k_error>
                                        <font color='red'><cms:each k_error ><cms:show item /><br /></cms:each></font>
                                    </cms:if>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <cms:input class="form-control" placeholder="Full Name" name='member_displayname' type='text' />
                                        </div>
                                        <div class="form-group">
                                            <cms:input class="form-control" placeholder="Email ID" name='member_email' type='text' />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <cms:input class="form-control" placeholder="Password" name='member_password' type='password' />
                                        </div>
                                        <div class="form-group">
                                            <cms:input class="form-control" placeholder="Repeat Password" name='member_password_repeat' type='password' />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="submit" class="btn btn-default" name="submit" value="Create account" />
                                    </div>
                                </cms:form>
                            </cms:if>
                        </cms:if>
                    </div>
                </div>

                <!--LOGIN PART -->
                <div class="item log-in">
                    <div class="item-inner">
                        <h1>Login</h1>
                        <cms:form method="post" anchor='0' id='loginForm'>
                            <cms:if k_success>
                                <cms:member_process_login_form />
                            </cms:if>
                            <cms:if k_error>
                                <h3><font color='red'><cms:show k_error /></font></h3>
                            </cms:if>
                            <div class="form-group">
                                <cms:input class="form-control" placeholder="Email ID" type='text' name='member_name' />
                            </div>
                            <div class="form-group">
                                <cms:input class="form-control" placeholder="Password" type='password' name='member_password' />
                            </div>
                            <div class="checkbox">
                                <cms:input type='checkbox' name="member_remember" opt_values=' &nbsp;Remember me=1' />
                            </div>
                            <div class="form-group">
                                <input type="submit" class="btn btn-default" value="Login" name="submit" />
                            </div>
                        </cms:form>
                    </div>
                </div>



                <!--FORGOT PASSWORD PART-->
                <div class="item w2 forgot-pwd">
                    <div class="item-inner">
                        <cms:set success_msg2="<cms:get_flash 'success_msg2' />" />
                        <cms:if success_msg2>
                            <div class="notice">
                                <cms:if success_msg2='1'>
                                    A confirmation email has been sent to you
                                    <br /> Please check your email.
                                    <cms:else /> Your password has been reset
                                    <br /> Please check your email for the new password.
                                </cms:if>
                            </div>
                            <cms:else />
                            <cms:set action="<cms:gpc 'act' method='get'/>" />
                            <cms:if action='resetpwd'>
                                <h1>Reset Password</h1>
                                <cms:member_process_reset_password />
                                <cms:if k_success>
                                    <cms:set_flash name='success_msg2' value='2' />
                                    <cms:redirect k_page_link />
                                    <cms:else />
                                    <cms:show k_error />
                                </cms:if>
                                <cms:else />
                                <h1>Forgot Password?</h1> Dont worry, We got you covered. Just type in your email id in the box below, and we will send you an email containing a link to reset your password.
                                <br />
                                <br/>
                                <cms:form method="post" anchor='0' id='forgotForm'>
                                    <cms:if k_success>
                                        <cms:member_process_forgot_password_form />
                                        <cms:if k_success>
                                            <cms:set_flash name='success_msg2' value='1' />
                                            <cms:redirect k_page_link />
                                        </cms:if>
                                    </cms:if>
                                    <cms:if k_error>
                                        <h3><font color='red'><cms:show k_error /></font></h3>
                                    </cms:if>
                                    <div class="form-group">
                                        <cms:input class="form-control" placeholder="Email ID" type='text' name='member_email' />
                                    </div>
                                    <input type="submit" class="btn btn-default" name="submit" value="Send reset mail" />
                                </cms:form>
                            </cms:if>
                        </cms:if>
                    </div>
                </div>



                <!--ABOUT NOTES PART-->
                <div class="item about-us sign-up log-in forgot-pwd">
                    <div class="item-inner">
                        <h1>Its quick and easy</h1> I awake to the perfect darkness of my room when I feel my cat resting on my chest, and have since been frozen in terror from the shocking lack of fur when I pet her. After half an hour, my eyes have adjusted to see the gargantuan insect with a fuzzy tail between its mandibles.
                    </div>
                </div>






            </div>
        </div>


        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script>
            window.jQuery || document.write('<script src="../js/vendor/jquery-1.10.2.min.js"><\/script>')
        </script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/isotope.pkgd.min.js"></script>
        <script src="../js/plugins.js"></script>
        <script src="../js/main.js"></script>
        <!--<script src="../js/formValidation.min.js"></script>
        <script src="../js/validbootstrap.min.js"></script>
        <script>
            $(document).ready(function () {

                $('#registerForm').formValidation({
                    message: 'This value is not valid',
                    icon: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        member_displayname: {
                            validators: {
                                notEmpty: {
                                    message: 'The first name is required'
                                }
                            }
                        },
                        member_email: {
                            validators: {
                                notEmpty: {
                                    message: 'The email address is required'
                                },
                                emailAddress: {
                                    message: 'The input is not a valid email address'
                                }
                            }
                        },
                        member_password: {
                            validators: {
                                notEmpty: {
                                    message: 'The password is required'
                                },
                                different: {
                                    field: 'username',
                                    message: 'The password cannot be the same as username'
                                }
                            }
                        },
                        member_password_repeat: {
                            validators: {
                                notEmpty: {
                                    message: 'The password is required'
                                },
                                identical: {
                                    field: 'member_password',
                                    message: 'Passwords do not match'
                                }
                            }
                        }

                    }
                });






                $('#loginForm').formValidation({
                    message: 'This value is not valid',
                    icon: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        member_name: {
                            validators: {
                                notEmpty: {
                                    message: 'The email address is required'
                                },
                                emailAddress: {
                                    message: 'The input is not a valid email address'
                                }
                            }
                        },
                        member_password: {
                            validators: {
                                notEmpty: {
                                    message: 'The password is required'
                                },
                                different: {
                                    field: 'username',
                                    message: 'The password cannot be the same as username'
                                }
                            }
                        }
                    }
                });





                $('#forgotForm').formValidation({
                    message: 'This value is not valid',
                    icon: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        member_email: {
                            validators: {
                                notEmpty: {
                                    message: 'The email address is required'
                                },
                                emailAddress: {
                                    message: 'The input is not a valid email address'
                                }
                            }
                        }
                    }
                });



            });
        </script>-->

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
    </body>

    </html>
</cms:if>
<?php COUCH::invoke(); ?>