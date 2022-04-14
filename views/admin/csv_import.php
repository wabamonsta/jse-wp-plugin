
<h1>Historical CSV Data Import</h1>
<?php 
    if(isset($msg)){
        echo $msg;
    }
?>
<form action="" method="post" enctype="multipart/form-data" class="qiwi_import_form">
<label>Import your csv file below:</label>
<div class="row">
    <input type="file" name="csv_import" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
</div>
<div>
<input type="submit" name='csvupload' value="Upload CSV">
</div>
</form>
<style>
.success{
    background: #89D2AB;
    width:30%; 
    font-weight:bold;
    font-size:30px;
    display:block; padding:20px;
    border-radius:10px;
}
.qiwi_import_form{
    margin-top:40px;
    padding:20px; 
    width:30%; 
    background:#fff; 
}
.qiwi_import_form .row{ 
    padding:10px; display:block;
}
.qiwi_import_form label{
    padding:10px; font-size:20px;
}
.qiwi_import_form input[type='submit']{
background:#102F57; color:#fff; 
padding:10px 20px 10px 20px; font-size:25px; border:1px solid #ccc;
}
.qiwi_import_form input[type='text']{
    border:1px #ccc solid; padding:10px; font-size:20px; color:#102F57; font-weight:bolder;
    width:40%;
}
</style>