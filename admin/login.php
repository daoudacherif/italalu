
    </head>
    <body>
        <div id="loginbox">            
            <form id="loginform" class="form-vertical" method="post">
				 <div class="control-group normal_text"> <h3>Inventory</strong> <strong style="color: orange">System</strong></h3></div>
                <div class="control-group">
                    <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_lg"><i class="icon-user"> </i></span><input type="text" placeholder="Username" name="username" required="true" />
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_ly"><i class="icon-lock"></i></span><input type="password" placeholder="Password" name="password" required="true"/>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <span class="pull-left"><a href="#" class="flip-link btn btn-info" id="to-recover">Lost password?</a></span>
                    <span class="pull-right"><input type="submit" class="btn btn-success" name="login" value="Sign In"></span>

                </div>
            </form>
            <div style="padding-left: 180px;">
                    <a href="../index.php" class="flip-link btn btn-info" id="to-recover"><i class="icon-home"></i>  Back to Home</a>
                 
                </div>
                <br />
            <form id="recoverform" class="form-vertical" method="post" name="changepassword" onsubmit="return checkpass();">
				<p class="normal_text">Enter your e-mail address below and we will send you instructions how to recover a password.</p>
				
                    <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_lo"><i class="icon-envelope"></i></span><input type="text" placeholder="E-mail address" name="email" required="true" />
                        </div>
                    </div>
                    <br />
               <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_lo"><i class="icon-phone-sign"></i></span><input type="text" placeholder="Contact Number" name="contactno" required="true" />
                        </div>
                    </div>
                    <br />
                    <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_lo"><i class="icon-lock"></i></span><input type="password" name="newpassword" placeholder="New Password" required="true" />
                        </div>
                    </div>
                    <br />
                    <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_lo"><i class="icon-lock"></i></span><input type="password" name="confirmpassword" placeholder="Confirm Password" required="true" />
                        </div>
                    </div>
                <div class="form-actions">
                    <span class="pull-left"><a href="#" class="flip-link btn btn-success" id="to-login">&laquo; Back to login</a></span>
                    <span class="pull-right"><input type="submit" class="btn btn-success" name="submit" value="Reset"></span>

                </div>
            </form>
        </div>
        
        <script src="js/jquery.min.js"></script>  
        <script src="js/matrix.login.js"></script> 
    </body>

</html>
