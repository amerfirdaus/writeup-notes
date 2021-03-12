<?php


// config db
  // include("config.php");



  if (isset($_POST["coupon_code"])) {



    $coupon_code = $_POST["coupon_code"];


                                                                          // note
    $query = "SELECT coupon,id,expire_date FROM valid_coupons WHERE coupon like '".$_POST["coupon_code"]."%'";



    $result = $db->query($query);



    if (!$result) {

      $error = $db->error;
      echo '<div align="center">' .$error.'</div>';





    } else {

       $count = $result->num_rows;

                         if ($count != 0) {


        while($row = mysqli_fetch_array($result)){



          $valid = "<tr><td style=\"width:200px\">Coupon Code : </td>".$row["coupon"]. " With ID : ".$row["id"]. " And With Expire Date Of : " .$row["expire_date"] . " Is Valid!";

          echo '<div align="center">' .$valid.'</div>';

        }

      }  else {

         echo '<div align="center">Coupon Code Does Not Exist!</div>';



      }

 }
}


?>