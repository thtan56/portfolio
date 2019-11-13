<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<div class="container">
  <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label for="file">Select file:</label>
      <input type="file" name="file" id="file" />
    </div>
    <div class="form-group">            
      <button class="btn btn-info" id="update" type="submit" name="convert"><i class="icon-hand-right"></i> &nbsp 1) Submit(csv->mysql)</button>
      <button class="btn btn-info" id="update" type="submit" name="updates"><i class="icon-hand-right"></i> &nbsp 2) Update(store_proc)</button>      
      <button class="btn btn-info" id="show"   type="submit" name="show"><i class="icon-hand-right"></i> &nbsp Contract Query</button>
      <button class="btn btn-info" id="calMeans"   type="submit" name="calMeans"><i class="icon-hand-right"></i> &nbsp 3)Calculate Stock Means</button>
      <button class="btn btn-info" id="calReg"   type="submit" name="calReg"><i class="icon-hand-right"></i> &nbsp 4)Calculate Stock Regression</button>
      <a href="http://localhost:8080" class="button">Home</a>
      5) Compute ES - R-Studio
    </div>
  </form>
  <!-- Progress bar holder (1) -->
  <div id="progress" style="width:500px;border:1px solid #ccc;"></div>
  <!-- Progress information -->
  <div id="information" style="width"></div>

</div>
<?php
include('./php_modules/db_utilities.php');
include('./php_modules/utilities.php');
ini_set('max_execution_time', 0); // to get unlimited php script execution time
//-=============================================================
if        ( isset($_POST["show"]) )     { showContracts();
} else if ( isset($_POST["calMeans"]) ) { calculate_StockMeans();
} else if ( isset($_POST["calReg"]) )   { update_StockRegression();  
} else if ( isset($_POST["updates"]) )  { execute_sp_update_all();
} else if ( isset($_POST["convert"]) )  {
  if ( isset($_FILES["file"])) {
    $filename="download/".$_FILES['file']['name'];
    $csv=readCsv($filename);
    updateContract_Stock_DailyPrices($csv);
    echo "Updated 1";
  }
}  
?>
