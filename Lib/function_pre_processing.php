<?php
  function pre_processing($tweet=array()){
    // include composer autoloader
    require_once __DIR__ . '/../vendor/autoload.php'; // create stemmer
    // cukup dijalankan sekali saja, biasanya didaftarkan di service container
    $stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
    $stemmer  = $stemmerFactory->createStemmer();
      $text_Concat = array();
      if(substr($tweet['tweet'], 0, 3) != 'RT '){
        $text = trim($tweet['tweet']);

        //1. Buang link
        $regex = "@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@";
        $text = preg_replace($regex, '', $text);

        //2. Buang @username
        $regex = '/(\s+|^)@\S+/';
        $text = preg_replace($regex, '', $text);

        //3. Buang tanda baca (. , ! " ? #)
        $text = omitCharacters($text, null);

        //5. Casefolding (transformasi teks menjadi lowercase atau huruf kecil)
        $text = strtolower($text);

        // Replacement kata baku
        $text = explode(" ", $text); //token
        $text = array_filter($text); //hapus array yg bernilai kosong
        $text = wordReplacement($text);

        //6. Stemming 
        $text = implode(" ", $text); //kalimaat
        $text = $stemmer->stem($text);
        
        // Stop Word Removal
        //$text = implode(" ", $text); //kalimaat
        $text = stopWordRemoval($text);
        $text = trim($text);
         
        // Tokenize
        $text = explode(" ", $text);
        //$result = array_filter( array( "0",0,1,2,3,'text' ) , 'is_numeric' );
        $text = array_filter($text,'is_string');
        $text = array_filter($text); //hapus array yg bernilai kosong

        //$text = array_filter($text, 'is_string') + array_values($text);
        $text = filter_angka($text);//hapus kata yg mengandung angka
        //hapus kata yg jml hurufnya 1 atau 2
        $text = hapus_huruf($text);
        
        //penggabungan tidak
        $text = clusterTidak($text);  
        
        $text = implode(" ",$text);

        return $text;
 
      } 
  }

    /*
        $text = array('ujian','pra','tesis','bulan','juli','aamiin')
    */
  function filter_angka($text=array()){
     $ar_baru=array(); //untuk menmpung kata2 yg tidak mengandung angka
     foreach ($text as $key => $kata){ //lakukan pemanggilan semua kata
       //variabel $kata pada perulangan ke 1, bernilai ujian
        if(!empty($kata)){ //kata yang tidak kosong akan diproses
           //melakukan pengecekatan huruf/karakter pada kata tersebut, apakah mengandung angka
           $mixnumeric = false;
            for($i=0;$i<=strlen($kata)-1;$i++){
              if(is_numeric($kata[$i]) || empty($kata[$i])) $mixnumeric = true;
            }
            //kalau kata tersebut tidak mengandung angka, maka akan disimpan ke array baru,
            //$ar_baru =$kata
            if($mixnumeric==false) $ar_baru[] = $kata;
        }
     }
     return $ar_baru;
  }

  //hapus huruf 1 atau 2
  function hapus_huruf($text=array()){
     $ar_baru=array(); 
     $whitelist = array("dm","ac","hp");
     foreach ($text as $key => $kata){
         if(trim($kata)!="")
            if(strlen(trim($kata))>2 || in_array(trim($kata), $whitelist)) $ar_baru[] = $kata;
     }
     return $ar_baru;
  }

  function clusterTidak($text=array()){
     $cluster=array();
     for($i=0;$i<=count($text)-1;$i++){
        if($text[$i]=="tidak"){
          if(isset($text[$i]) && isset($text[$i+1]))
            $cluster[]=$text[$i]."".$text[$i+1];
          else $cluster[] = $text[$i];
          $i++;
        }else $cluster[] = $text[$i]; 
     }
     return $cluster;
  }

  function omitCharacters($text="", $replaceArray=array()) {
    $replacelist = array(
      '.',',',"\'",':',';','=','*','%',"\"",'?','!','#','-','_','+','=','$',"\/","\\",'(',')'
    );
    if ( $replaceArray ) {
      $replacelist = $replaceArray;
    }
    foreach($replacelist as $list){
      $text = str_replace($list, ' ', $text);
    }
    return $text;
  }

  function stopWordRemoval($text=""){
    $sql = mysql_query("select id,stopword from stopwords");
    $kata = "";
    while($stopword=mysql_fetch_array($sql)){
      $kata .= $stopword['stopword']."|"; //semua stopword ditampung dalam $kata dan dipisahkan dengan |, sehingga menjadi -> mampu|semua|am|antara ... 
    }

    $regex = "/\b(".$kata.")\b/";
    return preg_replace($regex, '', $text);
  }

  function wordReplacement($text=array()){
    foreach ($text as $k=>$v) {
      $v =  mysql_escape_string($v); //omitCharacters($v, null);
      $sql = mysql_query("select rw.word, rw.vocab_id, v.word katadasar
                          from replace_words rw join vocabs v on v.id = rw.vocab_id  
                          where rw.word='$v'"
                        );
      $d = mysql_fetch_array($sql); 
      if(mysql_num_rows($sql)>0) $text[$k] = $d['katadasar'];
    }
    return $text;
  }

?>
