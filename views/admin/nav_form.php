<?php echo (isset($msg)? $msg:'');?>
<form method="post" action="" class="nav_form">
<div class="row">
    <label>NAV</label>
<input type='text' name='NAV' placeholder='Enter NAV value here' value="<?php  echo trim($NAV);?>">
</div>
<div>
<input type="submit" value="Submit" name="submit">
</div>

</form>
<style>
.success{
    background: #89D2AB;
    width:50%; 
    font-weight:bold;
    font-size:20px;
    display:block; padding:20px;
    border-radius:10px;
}
.nav_form{
    margin-top:40px;
    padding:20px; 
    width:30%; 
    background:#fff; 
}
.nav_form .row{ 
    padding:10px; display:block;
}
.nav_form label{
    padding:10px; font-size:20px;
}
.nav_form input[type='submit']{
background:#102F57; color:#fff; 
padding:10px 20px 10px 20px; font-size:25px; border:1px solid #ccc;
}
.nav_form input[type='text']{
    border:1px #ccc solid; padding:10px; font-size:20px; color:#102F57; font-weight:bolder;
    width:40%;
}
</style>