<?php
// the message
function sendClientData($RESERVATION_ID,$ADDS){
$sql="SELECT RES.RESERVATION_ID,US.USER_NAME AS RETAILER,LO.ZIP,LO.LOCATION_NAME AS LOCATION,CONCAT(PE3.NAME,' ',PE3.LAST_NAME) AS CLIENT_NAME,PE3.EMAIL AS CLIENT_EMAIL,SER.SERVICE_NAME,DATE_FORMAT(RES.DATE,'%m-%d-%Y') AS DATE,SCHE.HOUR ,CONCAT(CARME.MAKE_NAME,' ',CARM.MODEL_NAME,' ',VEH.YEAR) AS VEHICLE,VEH.PLATE,VEH.COLOR, RES.TOTAL,RES.TAX,RES.TRANSACCION  FROM RESERVATION RES INNER JOIN VEHICLE_DETAIL VEH ON VEH.VHE_DETAIL=RES.VEHICLE_DETAIL INNER JOIN CAR_MAKE CARME";
$sql.=" ON CARME.MAKE_ID=VEH.MAKE INNER JOIN CAR_MODEL CARM ON CARM.MODEL_ID=VEH.MODEL ";
$sql.=" INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RESERVATION_ID=".$RESERVATION_ID;
  try {

    $db=new db();
    $db=$db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();
    $resultado2 = $gsent->fetchAll();
    $NAME="";
    $DATE="";
    $TIME="";
    $SERVICE="";
    $PRICE="";
    $LOCATION="";
    $EMAIL="";
    foreach ($resultado2 as &$valor) {
        $NAME=$valor[4];
        $DATE=$valor[7];
        $TIME=$valor[8];
        $SERVICE=$valor[6];
        $PRICE=$valor[12];
        $LOCATION=$valor[3];
        $EMAIL=$valor[5];
    }

    $msg = "<html>";
    $msg .="<head>
    <link rel='stylesheet' type='text/css' href='http://ezcwash.com/email/stylesheets/email.css' />
    <style>
    /* ------------------------------------- GLOBAL ------------------------------------- */ * { margin:0; padding:0; } * { font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; }
    img { max-width: 100%; } .collapse { margin:0; padding:0; } body { -webkit-font-smoothing:antialiased; -webkit-text-size-adjust:none; width: 100%!important; height: 100%; }
    /* ------------------------------------- ELEMENTS ------------------------------------- */ a { color: #2BA6CB;}
    .btn { text-decoration:none; color: #FFF; background-color: #666; padding:10px 16px; font-weight:bold; margin-right:10px; text-align:center; cursor:pointer; display: inline-block; }
    p.callout { padding:15px; background-color:#ECF8FF; margin-bottom: 15px; } .callout a { font-weight:bold; color: #2BA6CB; }
    table.social { /* 	padding:15px; */ background-color: #ebebeb;
    } .social .soc-btn { padding: 3px 7px; font-size:12px; margin-bottom:10px; text-decoration:none; color: #FFF;font-weight:bold; display:block; text-align:center; } a.fb { background-color: #3B5998!important; } a.tw { background-color: #1daced!important; } a.gp { background-color: #DB4A39!important; } a.ms { background-color: #000!important; }
    .sidebar .soc-btn { display:block; width:100%; }
    /* ------------------------------------- HEADER ------------------------------------- */ table.head-wrap { width: 100%;}
    .header.container table td.logo { padding: 15px; } .header.container table td.label { padding: 15px; padding-left:0px;}
    /* ------------------------------------- BODY ------------------------------------- */ table.body-wrap { width: 100%;}
    /* ------------------------------------- FOOTER ------------------------------------- */ table.footer-wrap { width: 100%;	clear:both!important; } .footer-wrap .container td.content  p { border-top: 1px solid rgb(215,215,215); padding-top:15px;} .footer-wrap .container td.content p { font-size:10px; font-weight: bold;
    }
    /* ------------------------------------- TYPOGRAPHY ------------------------------------- */ h1,h2,h3,h4,h5,h6 { font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif; line-height: 1.1; margin-bottom:15px; color:#000; } h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }
    h1 { font-weight:200; font-size: 44px;} h2 { font-weight:200; font-size: 37px;} h3 { font-weight:500; font-size: 27px;} h4 { font-weight:500; font-size: 23px;} h5 { font-weight:900; font-size: 17px;} h6 { font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}
    .collapse { margin:0!important;}
    p, ul { margin-bottom: 10px; font-weight: normal; font-size:14px; line-height:1.6; } p.lead { font-size:17px; } p.last { margin-bottom:0px;}
    ul li { margin-left:5px; list-style-position: inside; }
    /* ------------------------------------- SIDEBAR ------------------------------------- */ ul.sidebar { background:#ebebeb; display:block; list-style-type: none; } ul.sidebar li { display: block; margin:0;} ul.sidebar li a { text-decoration:none; color: #666; padding:10px 16px; /* 	font-weight:bold; */ margin-right:10px; /* 	text-align:center; */ cursor:pointer; border-bottom: 1px solid #777777; border-top: 1px solid #FFFFFF; display:block; margin:0; } ul.sidebar li a.last { border-bottom-width:0px;} ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p { margin-bottom:0!important;}
    /* --------------------------------------------------- RESPONSIVENESS Nuke it from orbit. It's the only way to be sure. ------------------------------------------------------ */
    /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */ .container { display:block!important; max-width:600px!important; margin:0 auto!important; /* makes it centered */ clear:both!important; }
    /* This should also be a block element, so that it will fill 100% of the .container */ .content { padding:15px; max-width:600px; margin:0 auto; display:block; }
    /* Let's make sure tables in the content area are 100% wide */ .content table { width: 100%; }
    /* Odds and ends */ .column { width: 300px; float:left; } .column tr td { padding: 15px; } .column-wrap { padding:0!important; margin:0 auto; max-width:600px!important; } .column table { width:100%;} .social .column { width: 280px; min-width: 279px; float:left; }
    /* Be sure to place a .clear element after each set of columns, just to be safe */ .clear { display: block; clear: both; }
    /* ------------------------------------------- PHONE For clients that support media queries. Nothing fancy. -------------------------------------------- */ @media only screen and (max-width: 600px) {
    a[class='btn'] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}
    div[class='column'] { width: auto!important; float:none!important;}
    table.social div[class='column'] { width:auto!important; }
    }
    </style>
    </head>
    <body bgcolor='#FFFFFF'>
    <!-- HEADER --> <table class='head-wrap' bgcolor='#fff'> <tr> <td></td> <td class='header container'>
    <div class='content'> <table bgcolor='#fff'> <tr> <td><a href='http://ezcwash.com/'><img src='http://ezcwash.com/email/ezcwash_logo.png' /></a></td>
    </tr> </table> </div>
    </td> <td></td> </tr> </table><!-- /HEADER -->
    <!-- BODY --> <table class='body-wrap'> <tr> <td></td> <td class='container' bgcolor='#FFFFFF'>
    <div class='content'> <table> <tr> <td>
    <h3>".$NAME." thank you for choosing EZ CarWash.</h3> <p class='lead'>This is a confirmation that you have booked the following services:</p>
    <!-- A Real Hero (and a real human being) --> <p><img 'src='http://ezcwash.com/email/ezcwash_image.png' /></p><!-- /hero -->
    <!-- Callout Panel --> <p class='callout'> DATE: ".$DATE." <br> TIME: ".$TIME." <br> SERVICE: ".$SERVICE." <br>ADDITIONALS:".$ADDS." <br> PRICE: $".$PRICE."  <br> LOCATION: ".$LOCATION."<br></p>
	Your car will be ready 60 mins after your appointment.<BR>
	This service doesn't accept cancellations.  <br>
	If you need to reschedule, you have 12 hrs before your appointment<br> <a href='http://ezcwash.com/booking/production/'>Reschedule Here &raquo;</a> </p><!-- /Callout Panel -->
     <p class='callout'>REMEMBER: <br> We are not responsible for personal items left in your vehicle. Please double-check to see that you have your wallet, purse, camera, or any other personal items.<BR>
	 We don't accept CASH. Our policy of insurance is not responsible for any cash transactions; We ONLY accept credit cards payments.<BR>

	</p> <a href='tel:+19367038748'class='btn'>Call Us: +1 (936)703-8748</a>
    <br/> <br/>
	<!-- social & contact -->
						<table class='social' width='100%'>
							<tr>
								<td>

									<!--- column 1 -->
									<table align='left' class='column'>
										<tr>
											<td>

												<h5 class=''>Connect with Us:</h5>
												<p class=''>
												<a href='https://www.facebook.com/ezcwash/' class='soc-btn fb'>Facebook</a>
												<a href='https://twitter.com/@ezcwash' class='soc-btn tw'>Twitter</a>
												<a href='https://www.instagram.com/ezcwash/' class='soc-btn gp'>Instagram</a></p>


											</td>
										</tr>
									</table><!-- /column 1 -->

									<!--- column 2 -->
									<table align='left' class='column'>
										<tr>
											<td>

												<h5 class=''>EZ Carwash</h5>
												<p>Phone: <strong>+1 (936)703-8748</strong><br/>
												<p>Email: <strong><a href='emailto:ezcs@ezcwash.com'>ezcs@ezcwash.com</a></strong></p>
												<p>Website: <a href='http://ezcwash.com/'>www.ezcwash.com </a></p>

											</td>
										</tr>
									</table><!-- /column 2 -->

									<span class='clear'></span>

								</td>
							</tr>
						</table><!-- /social & contact -->

    </td> </tr> </table> </div>
    </td> <td></td> </tr> </table><!-- /BODY -->

    <!-- FOOTER --> <table class='footer-wrap'> <tr> <td></td> <td class='container'>
    <!-- content --> <div class='content'> <table> <tr> <td align='center'> <p> <a href='http://ezcwash.com/faqs.html'>Terms</a> | <a href='http://ezcwash.com/faqs.html'>Privacy</a> |
    </p> </td> </tr> </table> </div><!-- /content -->
    </td> <td></td> </tr> </table><!-- /FOOTER -->
    </body> </html>";

    $to = $EMAIL;

    $subject = 'Your Ez CarWash appointment ';

    $headers = "From: ezcs@ezcwash.com\r\n";
    $headers .= "Reply-To: ezcs@ezcwash.com\r\n";
    $headers .= "CC: ezcs@ezcwash.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    // use wordwrap() if lines are longer than 70 characters
    // send email
    mail($to, $subject, $msg, $headers);
    $db=null;
    return $to;

  } catch (PDOException $e) {
    return $e->getMessage();
  }
  return "hola";
}
function sendClientReschedule($RESERVATION_ID){
$sql="SELECT RES.RESERVATION_ID,US.USER_NAME AS RETAILER,LO.ZIP,LO.LOCATION_NAME AS LOCATION,CONCAT(PE3.NAME,' ',PE3.LAST_NAME) AS CLIENT_NAME,PE3.EMAIL AS CLIENT_EMAIL,SER.SERVICE_NAME,DATE_FORMAT(RES.DATE,'%m-%d-%Y') AS DATE,SCHE.HOUR ,CONCAT(CARME.MAKE_NAME,' ',CARM.MODEL_NAME,' ',VEH.YEAR) AS VEHICLE,VEH.PLATE,VEH.COLOR, RES.TOTAL,RES.TAX,RES.TRANSACCION  FROM RESERVATION RES INNER JOIN VEHICLE_DETAIL VEH ON VEH.VHE_DETAIL=RES.VEHICLE_DETAIL INNER JOIN CAR_MAKE CARME";
$sql.=" ON CARME.MAKE_ID=VEH.MAKE INNER JOIN CAR_MODEL CARM ON CARM.MODEL_ID=VEH.MODEL ";
$sql.=" INNER JOIN LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RESERVATION_ID=".$RESERVATION_ID;
  try {

    $db=new db();
    $db=$db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();
    $resultado2 = $gsent->fetchAll();
    $NAME="";
    $DATE="";
    $TIME="";
    $SERVICE="";
    $PRICE="";
    $LOCATION="";
    $EMAIL="";
    foreach ($resultado2 as &$valor) {
        $NAME=$valor[4];
        $DATE=$valor[7];
        $TIME=$valor[8];
        $SERVICE=$valor[6];
        $PRICE=$valor[12];
        $LOCATION=$valor[3];
        $EMAIL=$valor[5];
    }

    $msg = "<html>";
    $msg .="<head>
    <link rel='stylesheet' type='text/css' href='http://ezcwash.com/email/stylesheets/email.css' />
    <style>
    /* ------------------------------------- GLOBAL ------------------------------------- */ * { margin:0; padding:0; } * { font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; }
    img { max-width: 100%; } .collapse { margin:0; padding:0; } body { -webkit-font-smoothing:antialiased; -webkit-text-size-adjust:none; width: 100%!important; height: 100%; }
    /* ------------------------------------- ELEMENTS ------------------------------------- */ a { color: #2BA6CB;}
    .btn { text-decoration:none; color: #FFF; background-color: #666; padding:10px 16px; font-weight:bold; margin-right:10px; text-align:center; cursor:pointer; display: inline-block; }
    p.callout { padding:15px; background-color:#ECF8FF; margin-bottom: 15px; } .callout a { font-weight:bold; color: #2BA6CB; }
    table.social { /* 	padding:15px; */ background-color: #ebebeb;
    } .social .soc-btn { padding: 3px 7px; font-size:12px; margin-bottom:10px; text-decoration:none; color: #FFF;font-weight:bold; display:block; text-align:center; } a.fb { background-color: #3B5998!important; } a.tw { background-color: #1daced!important; } a.gp { background-color: #DB4A39!important; } a.ms { background-color: #000!important; }
    .sidebar .soc-btn { display:block; width:100%; }
    /* ------------------------------------- HEADER ------------------------------------- */ table.head-wrap { width: 100%;}
    .header.container table td.logo { padding: 15px; } .header.container table td.label { padding: 15px; padding-left:0px;}
    /* ------------------------------------- BODY ------------------------------------- */ table.body-wrap { width: 100%;}
    /* ------------------------------------- FOOTER ------------------------------------- */ table.footer-wrap { width: 100%;	clear:both!important; } .footer-wrap .container td.content  p { border-top: 1px solid rgb(215,215,215); padding-top:15px;} .footer-wrap .container td.content p { font-size:10px; font-weight: bold;
    }
    /* ------------------------------------- TYPOGRAPHY ------------------------------------- */ h1,h2,h3,h4,h5,h6 { font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif; line-height: 1.1; margin-bottom:15px; color:#000; } h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }
    h1 { font-weight:200; font-size: 44px;} h2 { font-weight:200; font-size: 37px;} h3 { font-weight:500; font-size: 27px;} h4 { font-weight:500; font-size: 23px;} h5 { font-weight:900; font-size: 17px;} h6 { font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}
    .collapse { margin:0!important;}
    p, ul { margin-bottom: 10px; font-weight: normal; font-size:14px; line-height:1.6; } p.lead { font-size:17px; } p.last { margin-bottom:0px;}
    ul li { margin-left:5px; list-style-position: inside; }
    /* ------------------------------------- SIDEBAR ------------------------------------- */ ul.sidebar { background:#ebebeb; display:block; list-style-type: none; } ul.sidebar li { display: block; margin:0;} ul.sidebar li a { text-decoration:none; color: #666; padding:10px 16px; /* 	font-weight:bold; */ margin-right:10px; /* 	text-align:center; */ cursor:pointer; border-bottom: 1px solid #777777; border-top: 1px solid #FFFFFF; display:block; margin:0; } ul.sidebar li a.last { border-bottom-width:0px;} ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p { margin-bottom:0!important;}
    /* --------------------------------------------------- RESPONSIVENESS Nuke it from orbit. It's the only way to be sure. ------------------------------------------------------ */
    /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */ .container { display:block!important; max-width:600px!important; margin:0 auto!important; /* makes it centered */ clear:both!important; }
    /* This should also be a block element, so that it will fill 100% of the .container */ .content { padding:15px; max-width:600px; margin:0 auto; display:block; }
    /* Let's make sure tables in the content area are 100% wide */ .content table { width: 100%; }
    /* Odds and ends */ .column { width: 300px; float:left; } .column tr td { padding: 15px; } .column-wrap { padding:0!important; margin:0 auto; max-width:600px!important; } .column table { width:100%;} .social .column { width: 280px; min-width: 279px; float:left; }
    /* Be sure to place a .clear element after each set of columns, just to be safe */ .clear { display: block; clear: both; }
    /* ------------------------------------------- PHONE For clients that support media queries. Nothing fancy. -------------------------------------------- */ @media only screen and (max-width: 600px) {
    a[class='btn'] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}
    div[class='column'] { width: auto!important; float:none!important;}
    table.social div[class='column'] { width:auto!important; }
    }
    </style>
    </head>
    <body bgcolor='#FFFFFF'>
    <!-- HEADER --> <table class='head-wrap' bgcolor='#fff'> <tr> <td></td> <td class='header container'>
    <div class='content'> <table bgcolor='#fff'> <tr> <td><a href='http://ezcwash.com/'><img src='http://ezcwash.com/email/ezcwash_logo.png' /></a></td>
    </tr> </table> </div>
    </td> <td></td> </tr> </table><!-- /HEADER -->
    <!-- BODY --> <table class='body-wrap'> <tr> <td></td> <td class='container' bgcolor='#FFFFFF'>
    <div class='content'> <table> <tr> <td>
    <h3>".$NAME." thank you for choosing EZ CarWash.</h3> <p class='lead'>This is a confirmation that you have rescheduled the following services:</p>
    <!-- A Real Hero (and a real human being) --> <p><img 'src='http://ezcwash.com/email/ezcwash_image.png' /></p><!-- /hero -->
    <!-- Callout Panel --> <p class='callout'> DATE: ".$DATE." <br> TIME: ".$TIME." <br> SERVICE: ".$SERVICE." <br> LOCATION: ".$LOCATION."<br></p>
	Your car will be ready 60 mins after your appointment.<BR>
	This service doesn't accept cancellations.  <br>

     <p class='callout'>REMEMBER: <br> We are not responsible for personal items left in your vehicle. Please double-check to see that you have your wallet, purse, camera, or any other personal items.<BR>
	 We don't accept CASH. Our policy of insurance is not responsible for any cash transactions; We ONLY accept credit cards payments.<BR>

	</p> <a href='tel:+19367038748'class='btn'>Call Us: +1 (936)703-8748</a>
    <br/> <br/>
	<!-- social & contact -->
						<table class='social' width='100%'>
							<tr>
								<td>

									<!--- column 1 -->
									<table align='left' class='column'>
										<tr>
											<td>

												<h5 class=''>Connect with Us:</h5>
												<p class=''>
												<a href='https://www.facebook.com/ezcwash/' class='soc-btn fb'>Facebook</a>
												<a href='https://twitter.com/@ezcwash' class='soc-btn tw'>Twitter</a>
												<a href='https://www.instagram.com/ezcwash/' class='soc-btn gp'>Instagram</a></p>


											</td>
										</tr>
									</table><!-- /column 1 -->

									<!--- column 2 -->
									<table align='left' class='column'>
										<tr>
											<td>

												<h5 class=''>EZ Carwash</h5>
												<p>Phone: <strong>+1 (936)703-8748</strong><br/>
												<p>Email: <strong><a href='emailto:ezcs@ezcwash.com'>ezcs@ezcwash.com</a></strong></p>
												<p>Website: <a href='http://ezcwash.com/'>www.ezcwash.com </a></p>

											</td>
										</tr>
									</table><!-- /column 2 -->

									<span class='clear'></span>

								</td>
							</tr>
						</table><!-- /social & contact -->

    </td> </tr> </table> </div>
    </td> <td></td> </tr> </table><!-- /BODY -->

    <!-- FOOTER --> <table class='footer-wrap'> <tr> <td></td> <td class='container'>
    <!-- content --> <div class='content'> <table> <tr> <td align='center'> <p> <a href='http://ezcwash.com/faqs.html'>Terms</a> | <a href='http://ezcwash.com/faqs.html'>Privacy</a> |
    </p> </td> </tr> </table> </div><!-- /content -->
    </td> <td></td> </tr> </table><!-- /FOOTER -->
    </body> </html>";

    $to = $EMAIL;

    $subject = 'Rescheduled';

    $headers = "From: ezcs@ezcwash.com\r\n";
    $headers .= "Reply-To: ezcs@ezcwash.com\r\n";
    $headers .= "CC: ezcs@ezcwash.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    // use wordwrap() if lines are longer than 70 characters
    // send email
    mail($to, $subject, $msg, $headers);
    $db=null;
    return $to;

  } catch (PDOException $e) {
    return $e->getMessage();
  }
  return "hola";
}
function sendAdminData($RESERVATION_ID){
$sql="SELECT RES.RESERVATION_ID,US.USER_NAME AS RETAILER,PE4.EMAIL AS RETAILER_EMAIL,LO.ZIP,LO.LOCATION_NAME AS LOCATION,CONCAT(PE3.NAME,' ',PE3.LAST_NAME) AS CLIENT_NAME,PE3.EMAIL AS CLIENT_EMAIL,PE3.PHONE,SER.SERVICE_NAME,DATE_FORMAT(RES.DATE,'%m-%d-%Y') AS DATE,SCHE.HOUR ,CONCAT(CARME.MAKE_NAME,' ',CARM.MODEL_NAME,' ',VEH.YEAR) ";
$sql.=" AS VEHICLE,VEH.PLATE,VEH.COLOR, RES.TOTAL,RES.TAX,RES.TRANSACCION  FROM RESERVATION RES INNER JOIN VEHICLE_DETAIL VEH ON VEH.VHE_DETAIL=RES.VEHICLE_DETAIL INNER JOIN CAR_MAKE CARME ON CARME.MAKE_ID=VEH.MAKE INNER JOIN CAR_MODEL CARM ON CARM.MODEL_ID=VEH.MODEL INNER JOIN LOCATION LO ON ";
$sql.=" LO.LOCATION_ID=RES.LOCATION_ID INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID INNER JOIN PERSON PE4 ON PE4.PERSON_ID=US.PERSON_ID INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID ";
$sql.=" INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID WHERE RESERVATION_ID=".$RESERVATION_ID;

  try {

    $db=new db();
    $db=$db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();
    $resultado2 = $gsent->fetchAll();
    $CLIENT_NAME="";
    $DATE="";
    $TIME="";
    $SERVICE="";
    $CLIENT_PHONE="";
    $LOCATION="";
    $CLIENT_EMAIL="";
    $VEHICLE="";
    $COLOR="";
    $PLATE="";
    foreach ($resultado2 as &$valor) {
        $CLIENT_NAME=$valor[5];
        $DATE=$valor[9];
        $TIME=$valor[10];
        $SERVICE=$valor[8];
        $CLIENT_PHONE=$valor[7];
        $LOCATION=$valor[4];
        $CLIENT_EMAIL=$valor[6];
        $VEHICLE=$valor[11];
        $COLOR=$valor[13];
        $PLATE=$valor[12];
        $RETAILER_EMAIL=$valor[2];
    }
    $sql="SELECT ADDITIONAL_NAME,AD.PRICE FROM RESERVATION_ADDITIONAL_SERVICE RAS INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=RAS.ADDITIONAL_ID WHERE RESERVATION_ID=".$RESERVATION_ID;
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $resultado2 = $gsent->fetchAll();
    $additionals="";
    $sum=0.0;
    foreach ($resultado2 as &$valor2) {
        $additionals.=$valor2[0].", ";
        $sum+=(float)$valor2[1];
    }
    $msg = "<html>";
    $msg .="<head>
    <style>
    /* ------------------------------------- GLOBAL ------------------------------------- */ * { margin:0; padding:0; } * { font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; }
    img { max-width: 100%; } .collapse { margin:0; padding:0; } body { -webkit-font-smoothing:antialiased; -webkit-text-size-adjust:none; width: 100%!important; height: 100%; }
    /* ------------------------------------- ELEMENTS ------------------------------------- */ a { color: #2BA6CB;}
    .btn { text-decoration:none; color: #FFF; background-color: #666; padding:10px 16px; font-weight:bold; margin-right:10px; text-align:center; cursor:pointer; display: inline-block; }
    p.callout { padding:15px; background-color:#ECF8FF; margin-bottom: 15px; } .callout a { font-weight:bold; color: #2BA6CB; }
    table.social { /* 	padding:15px; */ background-color: #ebebeb;
    } .social .soc-btn { padding: 3px 7px; font-size:12px; margin-bottom:10px; text-decoration:none; color: #FFF;font-weight:bold; display:block; text-align:center; } a.fb { background-color: #3B5998!important; } a.tw { background-color: #1daced!important; } a.gp { background-color: #DB4A39!important; } a.ms { background-color: #000!important; }
    .sidebar .soc-btn { display:block; width:100%; }
    /* ------------------------------------- HEADER ------------------------------------- */ table.head-wrap { width: 100%;}
    .header.container table td.logo { padding: 15px; } .header.container table td.label { padding: 15px; padding-left:0px;}
    /* ------------------------------------- BODY ------------------------------------- */ table.body-wrap { width: 100%;}
    /* ------------------------------------- FOOTER ------------------------------------- */ table.footer-wrap { width: 100%;	clear:both!important; } .footer-wrap .container td.content  p { border-top: 1px solid rgb(215,215,215); padding-top:15px;} .footer-wrap .container td.content p { font-size:10px; font-weight: bold;
    }
    /* ------------------------------------- TYPOGRAPHY ------------------------------------- */ h1,h2,h3,h4,h5,h6 { font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif; line-height: 1.1; margin-bottom:15px; color:#000; } h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }
    h1 { font-weight:200; font-size: 44px;} h2 { font-weight:200; font-size: 37px;} h3 { font-weight:500; font-size: 27px;} h4 { font-weight:500; font-size: 23px;} h5 { font-weight:900; font-size: 17px;} h6 { font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}
    .collapse { margin:0!important;}
    p, ul { margin-bottom: 10px; font-weight: normal; font-size:14px; line-height:1.6; } p.lead { font-size:17px; } p.last { margin-bottom:0px;}
    ul li { margin-left:5px; list-style-position: inside; }
    /* ------------------------------------- SIDEBAR ------------------------------------- */ ul.sidebar { background:#ebebeb; display:block; list-style-type: none; } ul.sidebar li { display: block; margin:0;} ul.sidebar li a { text-decoration:none; color: #666; padding:10px 16px; /* 	font-weight:bold; */ margin-right:10px; /* 	text-align:center; */ cursor:pointer; border-bottom: 1px solid #777777; border-top: 1px solid #FFFFFF; display:block; margin:0; } ul.sidebar li a.last { border-bottom-width:0px;} ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p { margin-bottom:0!important;}
    /* --------------------------------------------------- RESPONSIVENESS Nuke it from orbit. It's the only way to be sure. ------------------------------------------------------ */
    /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */ .container { display:block!important; max-width:600px!important; margin:0 auto!important; /* makes it centered */ clear:both!important; }
    /* This should also be a block element, so that it will fill 100% of the .container */ .content { padding:15px; max-width:600px; margin:0 auto; display:block; }
    /* Let's make sure tables in the content area are 100% wide */ .content table { width: 100%; }
    /* Odds and ends */ .column { width: 300px; float:left; } .column tr td { padding: 15px; } .column-wrap { padding:0!important; margin:0 auto; max-width:600px!important; } .column table { width:100%;} .social .column { width: 280px; min-width: 279px; float:left; }
    /* Be sure to place a .clear element after each set of columns, just to be safe */ .clear { display: block; clear: both; }
    /* ------------------------------------------- PHONE For clients that support media queries. Nothing fancy. -------------------------------------------- */ @media only screen and (max-width: 600px) {
    a[class='btn'] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}
    div[class='column'] { width: auto!important; float:none!important;}
    table.social div[class='column'] { width:auto!important; }
    }
    </style>
    </head><body bgcolor='#FFFFFF'>
    <table class='head-wrap' bgcolor='#fff'>
    <tr><td></td>
    <td class='headercontainer'>
    <div class='content'>
    <table bgcolor='#fff'><tr>
    <td><a href='http://ezcwash.com/'><img src='http://ezcwash.com/email/ezcwash_logo.png'/></a></td>
    </tr></table></div></td><td></td></tr></table><table class='body-wrap'>
    <tr><td></td><td class='container'bgcolor='#FFFFFF'><div class='content'>
    <table><tr><td><h3>You have a new booking. ".$LOCATION."</h3><p class='lead'>Service:".$SERVICE."</p>
    <p><img src='http://ezcwash.com/email/ezcwash_image.png'/></p>
    <!--CalloutPanel-->
    <p class='callout'>
    <b>DATE:</b> ".$DATE."<br>
    <b>TIME:</b> ".$TIME."<br><BR>
    <b>CLIENT NAME:</b> ".$CLIENT_NAME."<br>
    <b>CLIENT PHONE:</b> ".$CLIENT_PHONE."<br>
    <b>CLIENT EMAIL:</b> ".$CLIENT_EMAIL."<br><BR>
    </p><!--/CalloutPanel-->
    <p class='callout'><b>VEHICLE INFORMATION:</b><br>
    <b>VEHICLE:</b> ".$VEHICLE."<br>
    <b>COLOR:</b> ".$COLOR."<br>
    <b>PLATE:</b> ".$PLATE."<br>
    </p><br/><br/>
    <table class='social'width='100%'>
    <tr><td>
    <table align='left'class='column'>
    <tr></tr></table>
    <table align='left'class='column'>
    <tr></tr></table>
    <span class='clear'></span>
    </td></tr></table></td></tr>
    </table></div></td><td></td></tr></table>
    <table class='footer-wrap'>
    <tr><td></td><td class='container'>
    <div class='content'><tr><table><td align='center'><p>
    <a href='http://ezcwash.com/faqs.html'>Terms</a>|
    <a href='http://ezcwash.com/faqs.html'>Privacy</a>|
    </p></td></tr></table></div>
    </td><td></td></tr></table></body></html>";

    $to = $RETAILER_EMAIL;

    $subject = 'New Wash Info';

    $headers = "From: ezcs@ezcwash.com\r\n";
    $headers .= "Reply-To: ezcs@ezcwash.com\r\n";
    $headers .= "CC: ezcs@ezcwash.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    // use wordwrap() if lines are longer than 70 characters
    // send email
    mail($to, $subject, $msg, $headers);
    $db=null;
    return $to;

  } catch (PDOException $e) {
    return $e->getMessage();
  }
  return "hola";
}
function sendCompleted($RESERVATION_ID){

$sql="SELECT RES.RESERVATION_ID,US.USER_NAME AS RETAILER,LO.ZIP,LO.LOCATION_NAME AS LOCATION,US2.USER_NAME AS EMPLOYEE,";
$sql.=" CONCAT(PE3.NAME,' ',PE3.LAST_NAME) AS CLIENT_NAME,PE3.EMAIL AS CLIENT_EMAIL,SER.SERVICE_NAME,DATE_FORMAT(RES.DATE,'%m-%d-%Y') AS DATE,SCHE.HOUR,LS.PRICE AS IMPORT,LO.TAX,RES.TOTAL,CONCAT(PER5.NAME,' ',PER5.LAST_NAME) AS EMPLOYEE_NAME  FROM RESERVATION RES INNER JOIN USER US5 ON US5.USER_ID=RES.EMPLOYEE INNER JOIN PERSON PER5 ON PER5.PERSON_ID=US5.PERSON_ID INNER JOIN ";
$sql.=" LOCATION LO ON LO.LOCATION_ID=RES.LOCATION_ID INNER JOIN USER US ON US.USER_ID=LO.RETAILER_ID INNER JOIN USER US2 ON RES.EMPLOYEE=US2.USER_ID ";
$sql.=" INNER JOIN USER US3 ON RES.CLIENT_ID=US3.USER_ID INNER JOIN PERSON PE3 ON PE3.PERSON_ID=US3.PERSON_ID INNER JOIN LOCATION_SERVICE LS ON LS.LS_ID=RES.LS_ID ";
$sql.=" INNER JOIN SERVICE SER ON SER.SERVICE_ID=LS.SERVICE_ID INNER JOIN SCHEDULE_LOCATION SCHLO ON SCHLO.ID=RES.SCHEDULE_LOCATION INNER JOIN SCHEDULE SCHE ON SCHE.SCHEDULE_ID=SCHLO.SCHEDULE_ID";
$sql.=" WHERE RES.RESERVATION_ID=".$RESERVATION_ID;

  try {

    $db=new db();
    $db=$db->connect();

    $gsent = $db->prepare($sql);
    $gsent->execute();
    $resultado2 = $gsent->fetchAll();
    $CLIENT_NAME="";
    $DATE="";
    $TIME="";
    $SERVICE="";
    $LOCATION="";
    $CLIENT_EMAIL="";
    $SERVICE_PRICE="";
    $TAX="";
    $TOTAL="";
    foreach ($resultado2 as &$valor) {
        $CLIENT_NAME=$valor[5];
        $DATE=$valor[8];
        $TIME=$valor[9];
        $SERVICE=$valor[7];
        $LOCATION=$valor[3];
        $CLIENT_EMAIL=$valor[6];
        $SERVICE_PRICE=$valor[10];
        $TAX=$valor[11];
        $TOTAL=$valor[12];
        $EMPLOYE_NAME=$valor[13];
    }
    $sql="SELECT ADDITIONAL_NAME,AD.PRICE FROM RESERVATION_ADDITIONAL_SERVICE RAS INNER JOIN ADDITIONAL AD ON AD.ADDITIONAL_ID=RAS.ADDITIONAL_ID WHERE RESERVATION_ID=".$RESERVATION_ID;
    $gsent = $db->prepare($sql);
    $gsent->execute();
    $resultado2 = $gsent->fetchAll();
    $additionals="";
    $sum=0.0;
    foreach ($resultado2 as &$valor2) {
        $additionals.=$valor2[0].", ";
        $sum+=(float)$valor2[1];
    }

    $msg = "<html>";
    $msg .="<head>
    <style>
    /* ------------------------------------- GLOBAL ------------------------------------- */ * { margin:0; padding:0; } * { font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; }
    img { max-width: 100%; } .collapse { margin:0; padding:0; } body { -webkit-font-smoothing:antialiased; -webkit-text-size-adjust:none; width: 100%!important; height: 100%; }
    /* ------------------------------------- ELEMENTS ------------------------------------- */ a { color: #2BA6CB;}
    .btn { text-decoration:none; color: #FFF; background-color: #666; padding:10px 16px; font-weight:bold; margin-right:10px; text-align:center; cursor:pointer; display: inline-block; }
    p.callout { padding:15px; background-color:#ECF8FF; margin-bottom: 15px; } .callout a { font-weight:bold; color: #2BA6CB; }
    table.social { /* 	padding:15px; */ background-color: #ebebeb;
    } .social .soc-btn { padding: 3px 7px; font-size:12px; margin-bottom:10px; text-decoration:none; color: #FFF;font-weight:bold; display:block; text-align:center; } a.fb { background-color: #3B5998!important; } a.tw { background-color: #1daced!important; } a.gp { background-color: #DB4A39!important; } a.ms { background-color: #000!important; }
    .sidebar .soc-btn { display:block; width:100%; }
    /* ------------------------------------- HEADER ------------------------------------- */ table.head-wrap { width: 100%;}
    .header.container table td.logo { padding: 15px; } .header.container table td.label { padding: 15px; padding-left:0px;}
    /* ------------------------------------- BODY ------------------------------------- */ table.body-wrap { width: 100%;}
    /* ------------------------------------- FOOTER ------------------------------------- */ table.footer-wrap { width: 100%;	clear:both!important; } .footer-wrap .container td.content  p { border-top: 1px solid rgb(215,215,215); padding-top:15px;} .footer-wrap .container td.content p { font-size:10px; font-weight: bold;
    }
    /* ------------------------------------- TYPOGRAPHY ------------------------------------- */ h1,h2,h3,h4,h5,h6 { font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif; line-height: 1.1; margin-bottom:15px; color:#000; } h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }
    h1 { font-weight:200; font-size: 44px;} h2 { font-weight:200; font-size: 37px;} h3 { font-weight:500; font-size: 27px;} h4 { font-weight:500; font-size: 23px;} h5 { font-weight:900; font-size: 17px;} h6 { font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}
    .collapse { margin:0!important;}
    p, ul { margin-bottom: 10px; font-weight: normal; font-size:14px; line-height:1.6; } p.lead { font-size:17px; } p.last { margin-bottom:0px;}
    ul li { margin-left:5px; list-style-position: inside; }
    /* ------------------------------------- SIDEBAR ------------------------------------- */ ul.sidebar { background:#ebebeb; display:block; list-style-type: none; } ul.sidebar li { display: block; margin:0;} ul.sidebar li a { text-decoration:none; color: #666; padding:10px 16px; /* 	font-weight:bold; */ margin-right:10px; /* 	text-align:center; */ cursor:pointer; border-bottom: 1px solid #777777; border-top: 1px solid #FFFFFF; display:block; margin:0; } ul.sidebar li a.last { border-bottom-width:0px;} ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p { margin-bottom:0!important;}
    /* --------------------------------------------------- RESPONSIVENESS Nuke it from orbit. It's the only way to be sure. ------------------------------------------------------ */
    /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */ .container { display:block!important; max-width:600px!important; margin:0 auto!important; /* makes it centered */ clear:both!important; }
    /* This should also be a block element, so that it will fill 100% of the .container */ .content { padding:15px; max-width:600px; margin:0 auto; display:block; }
    /* Let's make sure tables in the content area are 100% wide */ .content table { width: 100%; }
    /* Odds and ends */ .column { width: 300px; float:left; } .column tr td { padding: 15px; } .column-wrap { padding:0!important; margin:0 auto; max-width:600px!important; } .column table { width:100%;} .social .column { width: 280px; min-width: 279px; float:left; }
    /* Be sure to place a .clear element after each set of columns, just to be safe */ .clear { display: block; clear: both; }
    /* ------------------------------------------- PHONE For clients that support media queries. Nothing fancy. -------------------------------------------- */ @media only screen and (max-width: 600px) {
    a[class='btn'] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}
    div[class='column'] { width: auto!important; float:none!important;}
    table.social div[class='column'] { width:auto!important; }
    }
    </style>
    </head>
    <body bgcolor='#FFFFFF'>
    <table class='head-wrap' bgcolor='#fff'>
    <tr><td></td><td class='header container'><div class='content'>
    <table bgcolor='#fff'><tr>
    <td><a href='http://ezcwash.com/'><img src='http://ezcwash.com/email/ezcwash_logo.png' /></a></td>
    </tr></table></div></td><td></td></tr>
    </table><table class='body-wrap'><tr><td></td>
    <td class='container' bgcolor='#FFFFFF'>
    <div class='content'>
    <table><tr><td>
    <h3>".$CLIENT_NAME." thank you for choosing EZ CarWash </h3>
    <p class='lead'>DATE: ".$DATE." ".$TIME." /EZ CarWash</p>
    <p><img src='http://ezcwash.com/email/thankyou.jpg' /></p><!-- /hero -->
    <p class='callout'>
    EZ T-Member: ".$EMPLOYE_NAME."<br>
    Location: ".$LOCATION." <br>
    Service: ".$SERVICE."<br><br>
    Fare does not include fees that may be charged by your bank. Please contact your bank directly for inquiries.<br>
    </p>

    <p class='callout'>YOUR FARE: <br>
    Service Fare		$".$SERVICE_PRICE."<br>
    ADDS: ".$additionals."			$".$sum."<br>
    Tax			 		$".round(($TAX/100)*($SERVICE_PRICE+$sum),2)."	<br><br>
    <H3>CHARGED        		$".$TOTAL."	</H3><br>
    </p><a href='tel:+19367038748'class='btn'>For any questions, Call Us: +1 (936)703-8748</a>

	<br/><br/>

	<!-- social & contact -->
						<table class='social' width='100%'>
							<tr>
								<td>

									<!--- column 1 -->
									<table align='left' class='column'>
										<tr>
											<td>

												<h5 class=''>Connect with Us:</h5>
												<p class=''>
												<a href='https://www.facebook.com/ezcwash/' class='soc-btn fb'>Facebook</a>
												<a href='https://twitter.com/@ezcwash' class='soc-btn tw'>Twitter</a>
												<a href='https://www.instagram.com/ezcwash/' class='soc-btn gp'>Instagram</a></p>


											</td>
										</tr>
									</table><!-- /column 1 -->

									<!--- column 2 -->
									<table align='left' class='column'>
										<tr>
											<td>

												<h5 class=''>EZ Carwash</h5>
												<p>Phone: <strong>+1 (936)703-8748</strong><br/>
												<p>Email: <strong><a href='emailto:ezcs@ezcwash.com'>ezcs@ezcwash.com</a></strong></p>
												<p>Website: <a href='http://ezcwash.com/'>www.ezcwash.com </a></p>

											</td>
										</tr>
									</table><!-- /column 2 -->

									<span class='clear'></span>

								</td>
							</tr>
						</table><!-- /social & contact -->

    </td> </tr> </table> </div>
    </td> <td></td> </tr> </table><!-- /BODY -->

    <!-- FOOTER --> <table class='footer-wrap'> <tr> <td></td> <td class='container'>
    <!-- content --> <div class='content'> <table> <tr> <td align='center'> <p> <a href='http://ezcwash.com/faqs.html'>Terms</a> | <a href='http://ezcwash.com/faqs.html'>Privacy</a> |
    </p> </td> </tr> </table> </div><!-- /content -->
    </td> <td></td> </tr> </table><!-- /FOOTER -->

	</body>
    </html>";

    $to = $CLIENT_EMAIL;

    $subject = 'EZ CarWash service is done';

    $headers = "From: ezcs@ezcwash.com\r\n";
    $headers .= "Reply-To: ezcs@ezcwash.com\r\n";
    $headers .= "CC: ezcs@ezcwash.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    // use wordwrap() if lines are longer than 70 characters
    // send email
    mail($to, $subject, $msg, $headers);
    $db=null;
    return $to;

  } catch (PDOException $e) {
    return $e->getMessage();
  }
  return "hola";
}
//mail("javilkira@gmail.com","My subject",$msg,$headers);
?>
