<?php

use nulastudio\Document\CSV\CSVHelper;

$CSVHelper = new CSVHelper('C:\Users\LiesAuer\Desktop\test.csv');
for ($i=0; $i < 10; $i++) { 
    $CSVHelper->writeRow(['ac'=>2,'ba'=>1.0,'cc'=>'ctest32432','da'=>false,'ed'=>null]);
}
