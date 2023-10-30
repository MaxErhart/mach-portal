<?php
namespace Lehrverpflichtung\Bescheid_2;


use App\Classes\fpdf\fpdf;
use App\Classes\fpdi\fpdi;
use App\Classes\Pdf2text\Pdf2text;


// function create($submissions_group, $file,$bescheid_settings,$elements,$submissions,$professor) {
function create($submissions_group,$file,$elements,$submissions,$inst,$prof,$semester) {
  // A4 
  //  210mm X 297mm
  //  20mm padding => x_min = 20mm , x_max = 190mm , width_max = 170mm, y_min=20mm, y_max = 277mm height_max = 257mm
  // 8 pt = 2.8222222222 mm
  // 9 pt = 3.175 mm
  // 10 pt = 3.5277777778 mm
  // 11 pt = 3.8805555556 mm
  // 12 pt = 4.2333333333 mm
  // 13 pt = 4.5861111111 mm
  // 14 pt = 4.9388888889 mm
  // 15 pt = 5.2916666667 mm
  // 16 pt = 5.6444444444 mm

  $pdf = new Fpdi('P', 'mm', 'A4');
  $pdf->SetFont('Arial');
  $pdf->AddPage();
  $pdf->setDocumentFormattingSize(12);

  $settings = [
    "padding_x"=>20,
    "padding_y"=>20,
    "width"=>170,
    "height"=>257,
    "text_padding"=>1,
  ];

  $pdf->setDocumentSettings($settings);

  $pdf->SetFontSize(12);
  $pdf->SetFontSize('16');
  $pdf->bold();

  $pdf->SetXY($settings["padding_x"],$settings["padding_y"]);
  $pdf->MultiCell($settings["width"]-30, $pdf->fontHeight(), utf8_decode($inst),0,"L",0);


  $pdf->SetXY($settings["padding_x"],$settings["padding_y"]);
  $pdf->MultiCell($settings["width"], $pdf->fontHeight(), utf8_decode($semester), 0,"R",0);

  $y = $pdf->padding(8);
  $pdf->SetFontSize('14');
  $pdf->SetXY($settings["padding_x"], $y);
  $pdf->MultiCell($settings["width"], $pdf->fontHeight(14), utf8_decode("{$prof}"), 0,"L",0);

  $y = $pdf->padding(10);
  $pdf->SetFontSize('12');
  $pdf->SetXY($settings["padding_x"],$y);
  $pdf->MultiCell($settings["width"], $pdf->fontHeight(), utf8_decode('B. Lehrverpflichtungen des Instituts'), 0,"L",0);

  $pdf->bold(false);
  $y = $pdf->padding(4);
  $pdf->SetXY($settings["padding_x"],$y);
  $pdf->MultiCell($settings["width"], $pdf->fontHeight(), utf8_decode('Für das wissenschaftliche Personal des Instituts bestehen gemäß LVVO bzw. Funktionsbeschreibungen / Dienstverträgen die folgenden Lehrverpflichtungen in SWh:'), 0,"L",0);

  $table_start_y = $pdf->padding(4);
  $cols = [74,74,22];
  
  $make_table = true;
  
  $table_submissions = [];
  $sws_total_prof = 0;
  $sws_total_mitarb = 0;
  $defined_el_ids = [
    1333,1334,1335,1336,1365,1337,1343
  ];
  foreach($submissions_group as $submission) {
    

    $_submission = parseSubmission($submission,$submissions,$elements);

    foreach($defined_el_ids as $id) {
      if(!array_key_exists($id, $_submission)) {
        $_submission[$id] = "None";
      }
    }

    $table_submissions[] = [
      utf8_decode(implode(' ',[$_submission[1333],$_submission[1334],$_submission[1336],$_submission[1335]])),
      utf8_decode($_submission[1365]),
      utf8_decode($_submission[1337]),
    ];
    if($submission->_data[1343]==7) {
      $sws_total_prof += floatval($_submission[1337]);
    } else {
      $sws_total_mitarb += floatval($_submission[1337]);
    }
  }

  $pages = 1;
  while($make_table) {
    $make_table_return_data = makeTable($pdf,$table_submissions,$cols,$table_start_y,$pages,$sws_total_prof,$sws_total_mitarb);
    $make_table = $make_table_return_data["new_page"];
    if($make_table) {
      $index = $make_table_return_data["index"];
      $table_submissions = array_slice($table_submissions,$index);
      $pdf->AddPage();
      $pdf->SetFont('Arial');
      $pdf->SetFillColor(255, 255, 255);
      $pages ++;
      $table_start_y = 20;
    }
  }
  makePageNumbers($pdf,$pages);

  $pdf->Output('F', $file);
  
  return $file;
}

function makePageNumbers($pdf,$pages) {
  for($page=1; $page<=$pages;$page++) {
    $pdf->setPage($page);
    $pdf->SetXY($pdf->padX()/2,297-$pdf->padY()/2);
    $pdf->Cell(210-$pdf->padX(),$pdf->fontHeight(),"{$page}/{$pages}",0,0,"R");
  }
}

function makeTable($pdf,$submissions,$cols,$table_start_y,$pages,$sws_total_prof,$sws_total_mitarb) {
  $header_end_y = makeTableHeader($pdf,$cols,$table_start_y);
  $footer_height = 2*$pdf->fontHeight()+$pdf->padT()*3;
  $summ_1 = 'a';
  $summ_2 = 'b';
  $index=0;
  $new_page = false;
  $table_fill_return_data = fillTable($pdf,$submissions,$cols,$header_end_y,$footer_height);
  if($table_fill_return_data["new_page"]) {
    $new_page=true;
    $index=$table_fill_return_data["index"];
  }
  $table_end_y = $table_fill_return_data["table_end_y"];

  $table_end_y = makeTableBody($pdf,$cols,$header_end_y,$table_end_y);
  makeTableFooter($pdf,$cols,$table_end_y,$footer_height,$sws_total_prof,$sws_total_mitarb,$new_page,$pages);
  return ["new_page"=>$new_page,"index"=>$index];
}

function makeTableFooter($pdf,$cols,$footer_start_y,$footer_height,$summ_1,$summ_2,$new_page,$pages) {
  if($new_page || $pages>1) {
    $pdf->SetXY($pdf->padX()+4,$footer_start_y+$pdf->padT());
    $pdf->MultiCell($pdf->docW()-$cols[2]-$cols[1]+6,$pdf->fontHeight()+$pdf->padT(),utf8_decode("Summe der Lehrveranstaltungen in SWh (alle Seiten)"),0,"L");

  } else {
    $pdf->SetXY($pdf->padX(),$footer_start_y);
    $pdf->Cell($pdf->docW()-$cols[2]-$cols[1]+6,$footer_height,utf8_decode("Summe der Lehrveranstaltungen in SWh"),0,0,"R");
  }




  $pdf->SetXY($pdf->padX()+$cols[0]+12,$footer_start_y+$pdf->padT());
  $pdf->Cell($cols[1]-12,$pdf->fontHeight(),utf8_decode("a) der Professoren"),0,0,"L");

  $pdf->SetXY($pdf->padX()+$cols[0]+12,$footer_start_y+$pdf->fontHeight()+2*$pdf->padT());
  $pdf->Cell($cols[1]-12,$pdf->fontHeight(),utf8_decode("b) der weiteren Lehrpersonen"),0,0,"L");


  $pdf->SetXY($pdf->docW()+$pdf->padX()-$cols[2],$footer_start_y);
  $pdf->Cell($cols[2],$footer_height,'',"RBL",0,"C");

  $pdf->SetXY($pdf->docW()+$pdf->padX()-$cols[2],$footer_start_y+$pdf->padT());
  $pdf->Cell($cols[2],$pdf->fontHeight(),utf8_decode($summ_1),0,0,"C");

  $pdf->SetXY($pdf->docW()+$pdf->padX()-$cols[2],$footer_start_y+$pdf->fontHeight()+2*$pdf->padT());
  $pdf->Cell($cols[2],$pdf->fontHeight(),utf8_decode($summ_2),0,0,"C");

}

function makeTableBody($pdf,$cols,$table_start_y,$table_end_y) {
  $x = 0;
  $height = $table_end_y-$table_start_y;
  if($height<297-$table_start_y-50); {
    $height = 297-$table_start_y-50;
  }
  foreach($cols as $index=>$width) {
    $border = "TRB";
    if($index==0) {
      $border = 1;
    }
    $pdf->SetXY($pdf->padX()+$x,$table_start_y);
    $pdf->Cell($width,$height,'',$border,0,"C");
    $x += $width;
  }
  return $table_start_y+$height;
}

function fillTable($pdf,$submissions,$cols,$start_y,$footer_height) {
  $y = $start_y+$pdf->padT();
  $y_next = $y;
  $sub_index = 0;
  foreach($submissions as $sub_index=>$submission) {


    $x = 0;
    $y_curr = $y_next;

    if($y_curr>$pdf->docH()-$footer_height-2) {
      return ["table_end_y"=>$y_curr, "new_page"=>true,"index"=>$sub_index];
    }

    foreach($cols as $index=>$width) {
      $center = "L";
      if($index==2) {
        $center = "C";
      }

      $pdf->SetXY($pdf->padX()+$x+$pdf->padT(),$y_curr);
      $pdf->MultiCell($width-2*$pdf->padT(),$pdf->fontHeight(),$submission[$index],0,$center); 
      $x += $width;
      $y = $pdf->GetY();
      if($y>$y_next) {
        $y_next = $y;
      } 
    }
  }
  $y_curr = $pdf->GetY();
  return ["table_end_y"=>$y_curr, "new_page"=>false,"index"=>$sub_index];

}

function makeTableHeader($pdf,$cols,$header_start_y) {
  $header_height = 2*$pdf->fontHeight()+$pdf->padT()*3;
  $x = 0;
  $header_text = ["Name", "Funktion", "SWh"];
  foreach($cols as $index=>$width) {
    $border = "TBR";
    if($index==0) {
      $border = 1;
    }
    $text_w = $width/3.5;
    if($index==2) {
      $text_w = $width;
    }
    $pdf->SetXY($pdf->padX()+$x,$header_start_y);
    $pdf->Cell($width,$header_height,'',$border,0,"C");

    $pdf->SetXY($pdf->padX()+$x,$header_start_y+$pdf->padT());
    $pdf->Cell($text_w,($header_height-3*$pdf->padT())/2,$header_text[$index],0,0,"C");
    
    $pdf->SetXY($pdf->padX()+$x,$header_start_y+2*$pdf->padT()+$pdf->fontHeight());
    $pdf->Cell($text_w,($header_height-3*$pdf->padT())/2,$index+1,0,0,"C");

    $x += $width;
  }
  return $header_start_y+$header_height;
}
function parseSubmission($submission, $submissions=null,$elements=null) {

  $data = [];
  foreach($submission->_data as $el_id=>$value) {
    $element = $elements[$el_id];
    $val = implode(' ', getValRecursively($element,$value,$elements,$submissions));
    $data[$el_id] = $val;
  }
  return $data;
}
function getValRecursively($element, $value, $elements,$submissions) {
  if($value===null) {
    return [];
  }
  if($element->component=='SelectReferenceElement') {
    $data = [];
    $ref_formId = $element->data['formId'];
    $ref_formElementIds = $element->data['formElementIds'];
    $ref_sub = $submissions[$ref_formId]->first(function ($submission) use($value) {
      return $submission->id==$value;
    });
    if($ref_sub===null) {
      return $data;
    }
    foreach($ref_formElementIds as $ref_el_id) {
      $ref_el = $elements[$ref_el_id];
      $ref_val = $ref_sub->_data[$ref_el_id];

      $data += getValRecursively($ref_el,$ref_val, $elements,$submissions);
    }
    return $data;
  } else if($element->component=='SelectElement') {
    foreach($element->data['data'] as $option) {
      if($option["id"]==$value) {
        return [$option["name"]];
      }
    }
    return [];
  } else {
    return [$value];
  }
}
?>