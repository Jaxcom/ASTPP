
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

			<title>
	<?php
$this->db->where('domain', $_SERVER['HTTP_HOST']);
$this->db->select('*');
$this->db->order_by('accountid', 'desc');
$this->db->limit(1);
$invoiceconf = $this->db->get('invoice_conf');
$invoiceconf = (array) $invoiceconf->first_row();
if (isset($invoiceconf['website_title']) && $invoiceconf['website_title'] != '') {
    ?>
	Signup | <?php echo $invoiceconf['website_title']; ?>
	<?php
} else {
    ?>
	Signup | ASTPP - Open Source Voip Billing Solution
	<?php
}
?>
</title>
<?php

{
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
        $domain = "https://" . $_SERVER["HTTP_HOST"] . "/";
    } else {
        $domain = "http://" . $_SERVER["HTTP_HOST"] . "/";
    }
    $http_host = $_SERVER["HTTP_HOST"];
    $this->db->select('favicon');
    $this->db->where("domain LIKE '%$domain%'");
    $this->db->or_where("domain LIKE '%$http_host%'");
    $user_favicon = (array) $this->db->get_where("invoice_conf")->first_row();
}
?>
<?php if(!empty($user_favicon['favicon'])) {  ?>
        <link rel="icon"
				href="<? echo base_url(); ?>upload/<? echo $user_favicon['favicon'] ?>" />
<?php } else { ?>
    <link rel="icon"
				href="<? echo base_url(); ?>assets/images/favicon.ico" />
<?php } ?>
<link href="<?php echo base_url(); ?>/assets/css/bootstrap.min.css"
				rel="stylesheet">
				<link
					href="<?= base_url() ?>assets/fonts/font-awesome-4.7.0/css/font-awesome.css"
					rel="stylesheet">
					<link rel="stylesheet"
						href="<?php echo base_url(); ?>assets/css/bootstrap-select.css" />
					<link href="<?php echo base_url(); ?>assets/css/global-style.css"
						rel="stylesheet" type="text/css">
						<link href="<?php echo base_url(); ?>assets/css/style.css"
							rel="stylesheet" type="text/css">
							<link
								href="<?php echo base_url(); ?>assets/css/custome_index.css"
								rel="stylesheet" type="text/css">

								<link
									href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i"
									rel="stylesheet">

									<script
										src="<?php echo base_url(); ?>assets/js/jquery-1.12.4.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/bootstrap.bundle.min.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/status/dist/js/bootstrap-select.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/facebox.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/module_js/generate_grid.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/fileinput.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/tabcontent.js"></script>
									<!-- IE -->
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/respond.js"></script>
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/respond.src.js"></script>
									<!-- -->
									<script type="text/javascript"
										src="<?php echo base_url(); ?>assets/js/custome_index.js"></script>
									<style>
html, body {
	height: 100%;
}

body {
	display: -ms-flexbox;
	display: flex;
	-ms-flex-align: center;
	align-items: center;
	padding-top: 40px;
	padding-bottom: 40px;
	background-color: #f5f5f5;
}

select {
	-webkit-appearance: none;
	-moz-appearance: none;
	text-indent: 1px;
	text-overflow: '';
}
</style>
									<script>
$(document).ready(function() {
    
	$('input[type="text"]').keyup(function() {
			if($(this).val() != '') {
				$('#signup_submit').prop('disabled', false);
			}
		});
 });
</script>
<script>
      $(document).ready(function () {
		$("#countrycode").attr('disabled',true);
		$("#country_id").change(function () {		
	 		var country = $('#country_id').val();
			$("#countrycode").attr('disabled',true);	
			$("#countrycode").val(country);
		});
	});
</script>

<script type="text/javascript">
         $('document').ready(function() {
			$("#signup_submit").click(function(){
	 			$('#signup_submit').prop('disabled', true);
				 if($('#customer_form').valid()){
					$('#customer_form').submit();
					return true;
				 }else{
					return false;
				 }
     		});
			$("#customer_form").validate({
                 rules: {
		     userCaptcha: "required",
                     first_name: {
                         required: true,
                     },
					 telephone: {
						 required: true,
				     },
	                 email: {
							required: true,
							email: true
					 },
                      
                 },
                 messages: {
		     userCaptcha: '<span class="text-danger">Captcha is required</span>',
                     first_name: { 
                         required: '<span class="text-danger">First Name is Required</span>',
                     },
				     telephone: {
						 required: '<span class="text-danger">Telephone is Required</span>',
				     },
                     email: {
						required: '<span class="text-danger">Email is Required</span>',
						email:'<span class="text-danger">Please enter a valid email address</span>',
					 },
					 
                 },
                  errorClass: "error_label",
                 submitHandler: function(form) {
					
                     form.submit();
                 }
             });
	  
         });
function isNumberKey(evt){
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
      	    return true;
    	}
	
 </script>

									<noscript>
										<div id="noscript-warning">ASTPP work best with JavaScript
											enabled</div>
									</noscript>

									</script>
									<script src='https://www.google.com/recaptcha/api.js'></script>

									<script>
            function onSubmit(token) {
				
                $("#customer_form").submit();
                return true;
            }
			 
        </script>

</head>
<? extend('master.php') ?>
<? startblock('extra_head') ?>
<body style="background: url(<?= base_url() ?>assets/images/login_bg.jpg);background-size: cover;background-attachment: fixed;">
					<?php if (isset($astpp_notification)){ ?>
					<div class="col-md-12">
		<div
			class="container alert alert-danger alert-dismissible mt-5 fade show">
			<strong><?php echo gettext('Login unsuccessful.')?></strong> <?php echo gettext('Please make sure you entered the correct username and password, and that your account is active.')?>
							<button type="button" class="close" data-dismiss="alert"
				aria-label="Close" data-ripple=" ">
				<span aria-hidden="true">×</span>
			</button>
		</div>
	</div>
					<?php

} else {
        echo "&nbsp;";
    }
    ?>
            	<div class="col-md-12 m-auto form_card">
		<form class="card p-4 col-12 mb-5"
			action="<?php echo base_url(); ?>signup/" method="post"
			accept-charset="utf-8" id="customer_form" name="customer_form"
			style="background: rgba(255, 255, 255, 0.8);">
			<h2 class="text-center">
								 <?php
        if (isset($this->session->userdata['user_logo']) && $this->session->userdata['user_logo'] != "") {
            $logo = $this->session->userdata['user_logo'];
        } else {
            $logo = 'logo.png';
        }
        if ($this->session->userdata('userlevel_logintype') != '0') {
            ?>
										<a class="logo_title" href="<?php echo base_url(); ?>"><img
					class="mb-2 img-fluid" id="logo" alt="Signup"
					src="<?php echo base_url(); ?>upload/<?php echo $logo;?>"></a>
									<? } else {?> 
										<a class="logo_title" href="<?php echo base_url(); ?>"><img
					class="mb-2 img-fluid" id="logo"
					title='ASTPP - Open Source Voip Billing Solution'
					alt='ASTPP - Open Source Voip Billing Solution'
					src="<?php echo base_url(); ?>upload/<?php echo $logo;?>"></a>
									<? }?>
							</h2>
			<div class="card pt-5 px-4 pb-4">
				<input type="hidden" name="key_unique"
					value="<?php if (isset($key_unique)) {echo $key_unique;} else {'';}?>"
					id="key_unique" size="15" maxlength="250" class="form-control" />
				<div class="form-group pb-2">
					<div id="floating-label">
													<?
            $js = 'id="country_id"';
            $country = form_dropdown(array(
                'id' => 'country_id',
                'name' => 'country_id',
                'class' => 'country_id'
            ), $this->db_model->build_dropdown("id,country", "countrycode", "", ""), isset($country_id) ? $country_id : $country_id);
            echo $country;
            ?>
												</div>
					<label class="control-label"><?php echo gettext('Country')?></label>
				</div>
				<div class="form-group">
					<input type="text" id="company_name" name="company_name"
						value="<?php if (isset($company_name)) {echo $company_name;} else {'';}?>"
						maxlength="40" size="15" class="form-control" /> <label
						for="company_name" class="control-label"><?php echo gettext('Company Name')?></label>
				</div>
				<div class="form-group">
					<select id="countrycode"
						class="col-3 float-left form-control select2" style="width: 100%;">
									    <?

foreach ($countrycode_array as $key => $countrycode_info) :
                $selected_value = '';
                if ($country_id == $key) {
                    $selected_value = "selected=selected";
                }
                ?>
									     <option <?php echo $selected_value ?>
							class="parent-element" value="<?php print_r($key); ?>"><?php print_r('+ '.$countrycode_info); ?></option>
									     <?endforeach;?> 
									    </select> <input type="text" id="telephone"
						name="telephone"
						value="<?php if (isset($telephone)) {echo $telephone;} else {'';}?>"
						size="15" maxlength="20" class="col-9 float-left form-control"
						onkeypress="return isNumberKey(event)" />
		<?php
if (isset($error['account_number']) && $error['account_number']) {
    ?>
				<label class="error_label">
					<?php echo $error['account_number'];?>
				</label>
		<?php }?>
													<label for="telephone" class="control-label"><?php echo gettext('Telephone *')?></label>

				</div>
				<div class="form-group">
					<input type="text" name="email" id="email"
						value="<?php if (isset($email)) {echo $email;} else {'';}?>"
						size="50" maxlength="80" class="form-control error_login" />
		<?php
if (isset($error['account_email']) && $error['account_email']) {
    ?>
				<label class="error_label">
					<?php echo $error['account_email'];?>
				</label>
		<?php }?>
													<label for="email" class="control-label"><?php echo gettext('Email *')?></label>

				</div>
				<div class="col-md-12">
					<div class="row">
						<div class="form-group col-6 pl-0">
							<input type="text" name="first_name"
								value="<?php if (isset($first_name)) {echo $first_name;} else {'';}?>"
								id="first_name" maxlength="40" class="form-control" /> <label
								for="first_name" class="control-label"><?php echo gettext('First Name *')?></label>
						</div>
						<div class="form-group col-6 p-0">
							<input type="text" name="last_name" id="last_name"
								value="<?php if (isset($last_name)) {echo $last_name;} else {'';}?>"
								maxlength="40" class="form-control" /> <label for="last_name"
								class="control-label"><?php echo gettext('Last Name')?></label>
						</div>
					</div>
				</div>
				<div class="col-md-12 mt-2">
					<div class="row">
						<div class="form-group col-6 pl-0">
							<label class="control-label"><?php echo gettext('Currency')?></label>
							<div id="floating-label">
															<?
            $currency = form_dropdown(array(
                'id' => 'currency_id',
                'name' => 'currency_id',
                'class' => 'currency_id'
            ), $this->db_model->build_dropdown("id,currencyname", "currency", "", ""), isset($currency_id) ? $currency_id : $currency_id);
            echo $currency;
            ?>
														</div>
						</div>
						<div class="form-group col-6 p-0">
							<label for="Timezone" class="control-label add_settings"><?php echo gettext('Timezone')?></label>
							<div id="floating-label">
															<?
            $timezone = form_dropdown(array(
                'id' => 'timezone_id',
                'name' => 'timezone_id',
                'class' => 'timezone_id selectpicker form-control '
            ), $this->db_model->build_dropdown("id,gmtzone", "timezone", "", ""), isset($timezone_id) ? $timezone_id : $timezone_id);
            echo $timezone;
            ?>
														</div>
						</div>
					</div>
				</div>

				<div class="col-md-12 form-group p-0">
				<?php echo $captcha['image']; ?>				
			</div>
				<div class="form-group">
					<input
						class='form-control form-control-lg col-md-12 posttoexternal m-0'
						id="userCaptcha" name="userCaptcha" type="text" autocomplete="off" />
		<?php
if (isset($error['captcha_err']) && $error['captcha_err']) {
    ?>
				<label class="error_label">
					<?php print_r($error['captcha_err']);?>
				</label>
		<?php }?>

												<label for="userCaptcha" class="control-label"><?php echo gettext('Enter above Captcha *')?></label>


				</div>
																									   			<?php
                            if (isset($error['account_deleted']) && $error['account_deleted']) {
                                ?>
				<label class="error_label">
					<?php echo $error['account_deleted'];?>
				</label>
		<?php }?>
										<div class="col-md-12 p-0 mt-4">
					<button name="action" type="submit" value="Signup"
						id="signup_submit" class="btn btn-block text-uppercase border_box"><?php echo gettext('Sign up')?></button>
				</div>
			</div>
			<div class="col-12 mt-4">
				<p class="text-center px-4 m-0">
					<a class="btn btn-outline-primary btn-block btn_index"
						href="<?php echo base_url(); ?>"><?php echo gettext('Already Registered !')?></a>
				</p>
			</div>
		</form>
						<?php
    if (isset($validation_errors) && $validation_errors != '') {
        ?>
						<script>
							var ERR_STR = '<?php echo $validation_errors; ?>';
							print_error(ERR_STR);
						</script>
					<? } ?>	
				</div>

	<script type="text/javascript">
		$("#country_id").val(<?= $country_id ?>);
		$("#timezone_id").val(<?= $timezone_id ?>);
		$("#currency_id").val(<?= $currency_id ?>);
</script>

	<script type="text/javascript">
	var country = $("#country_id").val();
	var timezone = $("#timezone_id").val();
	var currency = $("#currency_id").val();
	$("#reset").click(function () {
		$("#first_name").val("");
		$("#last_name").val("");
		$("#company_name").val("");
		$("#telephone_1").val("");
		$("#email").val("");
		$("#country_id").val(country);
		$("#timezone_id").val(timezone);
		$("#currency_id").val(currency);
		$("#address_1").val("");
		$("#userCaptcha").val("");
		$("#email_error").hide("");
		$("#capcha_error").hide("");
		$("#first_name_error").hide("");
		$("#telephone_1_error").hide("");
	});
</script>
	<script>
      $(document).ready(function () {
		$("select").closest('.form-group').addClass('control-select');
	});
</script>

