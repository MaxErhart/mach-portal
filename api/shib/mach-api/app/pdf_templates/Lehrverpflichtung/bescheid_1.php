<?php
namespace Lehrverpflichtung\Bescheid_1;


use App\Classes\fpdf\fpdf;
use App\Classes\fpdi\fpdi;
use App\Classes\Pdf2text\Pdf2text;


function create_row($y_old, $pdf, $text_array) {
  $pdf->SetFontSize('6');
  $pdf->SetFont('Arial');
  $cell_height = 3;
  $y = 0;

  $pdf->SetXY(20, $y_old);
  $pdf->MultiCell(11, $cell_height, utf8_decode($text_array[0]), 0, "C", 0);

  $new_y = $pdf->GetY();
  if($new_y>$y) {
    $y=$new_y;
  }

  $pdf->SetXY(31, $y_old);
  if($text_array[1]==NULL) {
    $pdf->MultiCell(9, $cell_height, '', 0, "C", 0);
  } else {
    $pdf->MultiCell(9, $cell_height, utf8_decode($text_array[1]), 0, "C", 0);
  }
  $new_y = $pdf->GetY();
  if($new_y>$y) {
    $y=$new_y;
  }

  $pdf->SetXY(41, $y_old);
  if($text_array[2]==NULL) {
    $pdf->MultiCell(9, $cell_height, '', 0, "C", 0);
  } else {
    $pdf->MultiCell(48, $cell_height, utf8_decode($text_array[2]).' - '.utf8_decode($text_array[3]), 0, "L", 0);
  }
  $new_y = $pdf->GetY();
  if($new_y>$y) {
    $y=$new_y;
  }

  $pdf->SetXY(90, $y_old);
  $pdf->MultiCell(34, $cell_height, utf8_decode($text_array[4]), 0, "L", 0);
  $new_y = $pdf->GetY();
  if($new_y>$y) {
    $y=$new_y;
  }

  $pdf->SetXY(124, $y_old);
  $pdf->MultiCell(11, $cell_height, utf8_decode($text_array[5]), 0, "C", 0);
  $new_y = $pdf->GetY();
  if($new_y>$y) {
    $y=$new_y;
  }

  $pdf->SetXY(135, $y_old);
  $pdf->MultiCell(11, $cell_height, utf8_decode($text_array[6]), 0, "C", 0);
  $new_y = $pdf->GetY();
  if($new_y>$y) {
    $y=$new_y;
  }

  $pdf->SetXY(146, $y_old);
  $pdf->MultiCell(11, $cell_height, utf8_decode($text_array[7]), 0, "C", 0);
  $new_y = $pdf->GetY();
  if($new_y>$y) {
    $y=$new_y;
  }


  $insgesamt = floatval($text_array[5])*floatval($text_array[6])*floatval($text_array[7]);

  $pdf->SetXY(157, $y_old);
  $pdf->MultiCell(11, $cell_height, utf8_decode($insgesamt), 0, "C", 0);
  $new_y = $pdf->GetY();
  if($new_y>$y) {
    $y=$new_y;
  }

  $prof = $insgesamt * floatval($text_array[8]) / 100;

  $pdf->SetXY(168, $y_old);
  $pdf->MultiCell(11, $cell_height, utf8_decode($prof), 0, "C", 0);
  $new_y = $pdf->GetY();
  if($new_y>$y) {
    $y=$new_y;
  }

  $mitarbeiter = $insgesamt - $prof;

  $pdf->SetXY(179, $y_old);
  $pdf->MultiCell(11, $cell_height, utf8_decode($mitarbeiter), 0, "C", 0);
  $new_y = $pdf->GetY();
  if($new_y>$y) {
    $y=$new_y;
  }
  return $y;

}

function create($submissions,$elements,$ref_subs,$output_file,$prof,$institut,$semester) {

  $pdf = new Fpdi('P','mm','A4');
  $pdf->AddPage();
  $pdf->SetFont('Arial');
  $pdf->SetFillColor(255, 255, 255);

  $page_padding_x = 20;
  $page_padding_y = 20;
  $page_width = 210-2*$page_padding_x;
  $page_height = 297-2*$page_padding_y;
  $text_padding = 1;
  
  $pdf->setDocumentFormattingSize(12);

  $pdf->SetFontSize('16');
  $pdf->bold();
  $pdf->SetXY($page_padding_x,$page_padding_y);
  $pdf->MultiCell($page_width-30, $pdf->fontHeight(), utf8_decode($institut), 0,"L",0);


  $pdf->SetXY($page_padding_x,$page_padding_y);
  $pdf->MultiCell($page_width, $pdf->fontHeight(), utf8_decode($semester), 0,"R",0);

  $y = $pdf->padding(8);
  $pdf->SetFontSize('14');
  $pdf->SetXY($page_padding_x, $y);
  $pdf->MultiCell($page_width, $pdf->fontHeight(14), utf8_decode("Erfüllung der Lehrverpflichtungen für {$prof}"), 0,"L",0);
  $a = $pdf->GetY();

  $y = $pdf->padding(10);
  $pdf->SetFontSize('12');
  $pdf->SetXY($page_padding_x,$y);
  $pdf->MultiCell($page_width, $pdf->fontHeight(), utf8_decode('A. Lehrveranstaltungen'), 0,"L",0);

  $pdf->bold(false);
  $y = $pdf->padding(4);
  $pdf->SetXY($page_padding_x,$y);
  $pdf->MultiCell($page_width, $pdf->fontHeight(), utf8_decode('Das wissenschaftliche Personal des Instituts hat im oben angegebenen Semester die folgenden Lehrveranstaltungen (Status: P=Pflicht-, WP=Wahlpflicht-, W=Wahlveranstaltung) abgehalten:'), 0,"L",0);

  $table_start_y = $pdf->padding(4);
  $col_width = [12,9,49,34,11,11,11,11,11,11];

  $footer_height = $pdf->fontHeight()*3+2*$text_padding;
  $pages = 1;
  $make_table = true;
  $table_submissions = $submissions;

  $id_order = [
    "810",
    "857",
    "826",
    "809",
    "814",
    "829",
    "860",
    "861",
    "815",
  ];
  $insg_total = 0;
  $prof_total = 0;
  $mitarb_total = 0;  
  foreach($submissions as $submission) {
      $insg = round(floatval($submission->_data[$id_order[5]])*floatval($submission->_data[$id_order[6]])*floatval($submission->_data[$id_order[7]]), 0);
      $prof = round($insg*floatval($submission->_data[$id_order[8]])/100, 2);
      $mitarb = $insg-$prof;
      $insg_total += $insg;
      $prof_total += $prof;
      $mitarb_total += $mitarb;
  }

  while($make_table) {
    $make_table_return_data = makeTable($pdf,$table_submissions,$page_width,$table_start_y,$text_padding,$page_padding_x,$col_width,$page_height,$footer_height,$elements,$insg_total,$prof_total,$mitarb_total,$pages,$ref_subs);
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

  makePageNumbers($pdf,$pages,$page_padding_x,$page_padding_y);

  $pdf->Output('F', $output_file);
  
  return $output_file;
}

function makePageNumbers($pdf,$pages,$page_padding_x,$page_padding_y) {
  for($page=1; $page<=$pages;$page++) {
    $pdf->setPage($page);
    $pdf->SetXY($page_padding_x/2,297-$page_padding_y/2);
    $pdf->Cell(210-$page_padding_x,$pdf->fontHeight(),"{$page}/{$pages}",0,0,"R");
  }
}

function makeTable($pdf,$submissions,$page_width,$table_start_y,$text_padding,$page_padding_x,$col_width,$page_height,$footer_height,$elements,$insg_total,$prof_total,$mitarb_total,$pages,$ref_subs) {
  $new_page=false;
  $index=0;
  $table_header_end = makeTableHeader($pdf,$table_start_y,$text_padding,$page_padding_x,$col_width);

  $table_fill_return_data = fillTable($pdf,$submissions,$table_header_end,$text_padding,$page_padding_x,$col_width,$page_height,$footer_height,$elements,$ref_subs);
  if($table_fill_return_data["new_page"]) {
    $new_page=true;
    $index=$table_fill_return_data["index"];
  }
  $table_end_y = $table_fill_return_data["table_end_y"];

  $table_end_y = makeTableBody($pdf,$table_header_end,$table_end_y,$col_width,$page_padding_x);
  makeTableFooter($pdf,$col_width,$page_padding_x,$table_end_y,$text_padding,$page_width,$footer_height,$insg_total,$prof_total,$mitarb_total,$new_page,$pages);
  return ["new_page"=>$new_page,"index"=>$index];
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
function parseSubmission($submission, $submissions=null,$elements=null) {

  $data = [];
  foreach($submission->_data as $el_id=>$value) {
    $element = $elements[$el_id];
    $rec_val = getValRecursively($element,$value,$elements,$submissions);
    $val = implode(' ', $rec_val);
    $data[$el_id] = $val;
  }
  return $data;
}
function fillTable($pdf, $submissions,$table_header_end,$text_padding,$page_padding_x,$col_width,$page_height,$footer_height,$elements,$ref_subs,$index=0) {
  $pdf->SetY($table_header_end);
  $id_order = [
    "810",
    "857",
    "826",
    "809",
    "814",
    "829",
    "860",
    "861",
    "815",
  ];
  $y = $pdf->GetY();
  foreach($submissions as $index=>$submission) {
    $submission = parseSubmission($submission,$ref_subs,$elements);
    $insg = round(floatval($submission[$id_order[5]])*floatval($submission[$id_order[6]])*floatval($submission[$id_order[7]]),2);
    $prof = round($insg*(floatval($submission[$id_order[8]]))/100,2);
    $row_data = [
      $submission[$id_order[0]],
      $submission[$id_order[1]],
      $submission[$id_order[2]].' - '.$submission[$id_order[3]],
      $submission[$id_order[4]],
      $submission[$id_order[5]],
      $submission[$id_order[6]],
      $submission[$id_order[7]],
      $insg,
      $prof,
      $insg - $prof,
    ];
    
    $x = 0;
    $_y = $y;
    if($y>$page_height-$footer_height-2) {
      return ["table_end_y"=>$y, "new_page"=>true,"index"=>$index];
    }
    foreach($col_width as $index=>$width) {
      if($index!=2 && $index!=3) {
        $pdf->SetXY($page_padding_x+$x+$text_padding, $y);
        $pdf->MultiCell($width-2*$text_padding, $pdf->fontHeight(), utf8_decode($row_data[$index]), 0,'C',0);
      } else {
        $pdf->SetXY($page_padding_x+$x+$text_padding, $y);
        $pdf->MultiCell($width-2*$text_padding, $pdf->fontHeight(), utf8_decode($row_data[$index]), 0,'L',0);
      }
      $new_y = $pdf->GetY();
      if($new_y>$_y) {
        $_y=$new_y;
      }
      $x +=$width;
    }
    $y = $_y;
  }
  $table_end_y = $pdf->GetY();
  return ["table_end_y"=>$table_end_y, "new_page"=>false,"index"=>$index];
}

function makeTableFooter($pdf,$col_width,$page_padding_x,$footer_start_y,$text_padding,$page_width,$footer_height,$insg_total,$prof_total,$mitarb_total,$pages,$new_page=false) {
  $footer_height=0;
  if($footer_height==0) {
    $footer_height = $pdf->fontHeight()*3+2*$text_padding;
  }
  $fotter_end_y = $footer_height+$footer_start_y;
  $pdf->SetFontSize('10');

  $pdf->SetXY($page_padding_x+$text_padding, $footer_start_y);
  if($new_page || $pages>1) {
    $pdf->Cell($page_width-$col_width[7]-$col_width[8]-$col_width[9]-2*$text_padding, $footer_height, utf8_decode('Übertrag / Summe für alle Lehrveranstaltungen (alle Seiten)'), 0,0,'R');
  } else {
    $pdf->Cell($page_width-$col_width[7]-$col_width[8]-$col_width[9]-2*$text_padding, $footer_height, utf8_decode('Übertrag / Summe für alle Lehrveranstaltungen'), 0,0,'R');
  }

  $pdf->SetXY($page_padding_x+$page_width-$col_width[7]-$col_width[8]-$col_width[9], $footer_start_y);
  $pdf->Cell($col_width[7], $footer_height, utf8_decode($insg_total), "RBL",0,'C');

  $pdf->SetXY($page_padding_x+$page_width-$col_width[8]-$col_width[9], $footer_start_y);
  $pdf->Cell($col_width[8], $footer_height, utf8_decode($prof_total), "BR",0,'C');

  $pdf->SetXY($page_padding_x+$page_width-$col_width[9], $footer_start_y);
  $pdf->Cell(+$col_width[9], $footer_height, utf8_decode($mitarb_total), 'BR',0,'C');
}


function makeTableHeader($pdf,$table_start_y,$text_padding,$page_padding_x,$col_width) {

  $header_height = $pdf->fontHeight()*3+2*$text_padding;
  $pdf->SetXY($page_padding_x, $table_start_y);
  foreach($col_width as $index=>$width) {
    if($index==0) {
      $pdf->Cell($width, $header_height, '', 1);
    } else if($index==1 || $index==7 || $index==8) {
      $pdf->Cell($width, $header_height, '', 'TB');
    } else {
      $pdf->Cell($width, $header_height, '', 'RTB');
    }
  }

  $pdf->SetFontSize('7');
  $x = 0;
  $header_text_row_1 = ['LV-Nr.','Status, Art und Bezeichnung der Lehrveranstaltung','','Namen','SWh','Multipli-','Anrechn-','Angerechnete SWh'];
  $header_text_row_2 = ['kator','faktor','insges.','Prof','Mitarb.'];
  $header_text_row_3 = ['1','2a','2b','3','4','5','6','7','8','9'];
  foreach($col_width as $index=>$width) {
    if($index!=1 && $index!=2 && $index<7) {
      $pdf->SetXY($page_padding_x+$x+$text_padding, $table_start_y+$text_padding);
      $pdf->Cell($width-2*$text_padding, $pdf->fontHeight(), $header_text_row_1[$index], 0,0,'C');
    } else if($index==1) {
      $pdf->SetXY($page_padding_x+$x+$text_padding, $table_start_y+$text_padding);
      $pdf->Cell($width+$col_width[$index+1]-2*$text_padding, $pdf->fontHeight(), $header_text_row_1[$index], 0,0,'C');
    } else if($index==7) {
      $pdf->SetXY($page_padding_x+$x+$text_padding, $table_start_y+$text_padding);
      $pdf->Cell($width+$col_width[$index+1]+$col_width[$index+2]-2*$text_padding, $pdf->fontHeight(), $header_text_row_1[$index], 0,0,'C');
    }

    if($index>=5) {
      $pdf->SetXY($page_padding_x+$x+$text_padding, $table_start_y+$text_padding+$pdf->fontHeight());
      $pdf->Cell($width-2*$text_padding, $pdf->fontHeight(), $header_text_row_2[$index-5], 0,0,'C');
    }


    $pdf->SetXY($page_padding_x+$x+$text_padding, $table_start_y+$text_padding+2*$pdf->fontHeight());
    $pdf->Cell($width-2*$text_padding, $pdf->fontHeight(), $header_text_row_3[$index], 0,0,'C');
    $x +=$width;
  }

  return $header_height+$table_start_y;
}

function makeTableBody($pdf,$table_start_y,$table_end_y,$col_width,$page_padding_x) {
  $x = 0;
  $height = $table_end_y-$table_start_y;
  if($height<297-$table_start_y-50); {
    $height = 297-$table_start_y-50;
  }
  foreach($col_width as $index=>$width) {
    if($index==0) {
      $pdf->SetXY($page_padding_x+$x, $table_start_y);
      $pdf->Cell($width, $height, '', 'BLR',0,'C');
    } else {
      $pdf->SetXY($page_padding_x+$x, $table_start_y);
      $pdf->Cell($width, $height, '', 'BR',0,'C');
    }
    $x +=$width;
  }
  return $table_start_y+$height;
}

?>