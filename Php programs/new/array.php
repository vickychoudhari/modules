
<?php
$movies = array(
  array(
    "title" => "Rear Window",
    "director" => "Alfred Hitchcock",
    "year" => 1954
  ),
  array(
    "title" => "Full Metal Jacket",
    "director" => "Stanley Kubrick",
    "year" => 1987
  ),
  array(
    "title" => "Mean Streets",
    "director" => "Martin Scorsese",
    "year" => 1973
  ),
  array(
  	"tittle" =>"vishal",
  	"director" => "frontend",
    "year" => 1919
),
array(
  	"tittle" =>"sumit",
  	"director" => "backEnd",
    "year" => 1999
),
array(
  	"tittle" =>"sachin",
  	"director" => "database",
    "year" => 1998
)
);


foreach ( $movies as $movie ) {

  echo '<dl>';

  foreach ( $movie as $key => $value ) {
    echo "<dt>$key</dt><dd>$value</dd>";
  }

  echo '</dl>';
}
?>