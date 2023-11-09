<?php

function showRating($rating) {
    $ra = floatval($rating /2);
    for($c=1;$c<6;$c++)
     {
        echo $c<= $ra ? '<i class="material-icons rating-star">star</i>' : ((($c - $ra) == 0.5) ? '<i class="material-icons rating-star">star_half</i>' : '<i class="material-icons rating-star">star_border</i>') ;
     }
     echo "<span style='display:inline-block'><h4>$ra</h4></span>";
}

?>
