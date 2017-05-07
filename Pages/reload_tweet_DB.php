<?php 
include "Lib/function_pre_processing.php";
require_once "Lib/kint/Kint.class.php";

$tweets_keluhan = [];
$query_tweets = mysql_query("select a.*, c.label as label_manual from tweets a, clean_tweet b, tweet_nbc c where a.id = b.id and b.data_status = 'testing' and a.id = c.id"); 
$query_tweets_keluhan = mysql_query("SELECT a.id,a.label, b.label as label_rocchio FROM tweet_nbc a, tweet_rocchio b WHERE a.label='2' AND a.label_system != '0' AND b.id = a.id");
while($data = mysql_fetch_object($query_tweets_keluhan)):
    $tweets_keluhan[$data->id] = ['label_manual'=>$data->label.'.'.$data->label_rocchio];
endwhile;

// load semua kata unik
$sql_kataunik = mysql_query("select * from nbc_kataunik");
$kataunik = array();
while($d_unik = mysql_fetch_array($sql_kataunik)){
    $kataunik[] = $d_unik;
}

//load kelas prior
// 1->pujian, 2->keluhan, 3->follow, 4->unknown
$label_kelas = [1=>'pujian',2=>'keluhan','2.1'=>'keluhan umum', '2.2'=>'keluhan sopir',3=>'follow',4=>'unknown'];
$sql_prior = mysql_query("select * from nbc_priorclass");
$prior = array();
while($dprior = mysql_fetch_array($sql_prior)){
    $prior[$dprior['class']] = $dprior['prior'];
}

// ambil data TF, IDF, TFIDF, dan semua_kata
$sqllatih = mysql_query("select tb.id id_tweet_bersih, tb.tweet, tr.label, tn.id id_tweet_nbc
                        from clean_tweet tb left join tweet_nbc tn on tb.id=tn.id
                        join tweet_rocchio tr on tn.id=tr.id
                        where tb.data_status='training' and tn.label='2'"
                        );           
$N = mysql_num_rows($sqllatih);

$sql_tfidf = mysql_query("select a.id_tfidf, a.class as kelas, a.d, a.tf, a.idf, a.tf_idf, b.word from rocchio_tfidf a, rocchio_word b WHERE a.word = b.id");
$TF = array();
$IDF = array();
$TFIDF = array();
$semua_kata = array();
while($tfidf_data = mysql_fetch_array($sql_tfidf)){
    $TF[$tfidf_data['word']][$tfidf_data['kelas']][$tfidf_data['d']]  = $tfidf_data['tf'];
    $IDF[$tfidf_data['word']]= $tfidf_data['idf'];
    $TFIDF[$tfidf_data['word']][$tfidf_data['kelas']][$tfidf_data['d']] 
        = $tfidf_data['tf_idf']; 

    if(!array_key_exists($tfidf_data['word'], $semua_kata)) $semua_kata[$tfidf_data['word']] = array();
}

$i = 0;
$processed_tweets = [];
$id_tweets_keluhan = [];
$jmlumum = 0;
// looping tiap tweet
while($tweet = mysql_fetch_object($query_tweets)):

    $processed_tweets[$i]['tweet'] = $tweet->tweet;
    $processed_tweets[$i]['id'] = $tweet->id;
    // if($tweet->label_manual == 2) $jmlumum++;
    if($tweet->label_manual != 2):
        $processed_tweets[$i]['label_manual'] = $tweet->label_manual;
    else:
        $processed_tweets[$i]['label_manual'] = $tweets_keluhan[$tweet->id]['label_manual'];
    endif;
    // pre process tweet
    $tweet_bersih=pre_processing( ['tweet'=>$tweet->tweet] ); //preprocessing tweet
    
    // tokenize hasil tweet bersih
    $tokenize = explode(" ", $tweet_bersih);

    // hitung score probabilitas posterior
    $score = $prior;
    foreach($tokenize as $token) {
        foreach ($kataunik as $key=>$d) {
            if(isset($d['word'])){
                if($d['word']==$token){
                    $score[1] = $score[1] * $d['pPujian'];
                    $score[2] = $score[2] * $d['pKeluhan'];
                    $score[3] = $score[3] * $d['pFollow'];
                    $score[4] = $score[4] * $d['pUnknown'];
                }
            }
        }
    }

    // cari nilai maksimum dan kelas tweet
    $max = 0;
    $kelas = 0;
    foreach($score as $key=>$value):
        if($value > $max) {
            $max   = $value;
            $kelas = $key;
        }
    endforeach;

    // tentukan respon
    //1 pujian, 2 keluhan, 3 follow, 4 un
    $id_tweet_respon = 0;
    $processed_tweets[$i]['label_sistem'] = $kelas;
    if ($kelas == 1) {
        $id_tweet_respon = 1; //pujian
    } elseif ($kelas == 3) {
        $id_tweet_respon = 2; //follow
    } elseif ($kelas==2) {
        ######rocchio######
        $kata_d['word'] = explode(' ', strtolower($tweet_bersih));
        $cek = klasifikasi_rocchio($kata_d, $semua_kata, $N, $TF, $IDF, $TFIDF);
        if($cek == 1){ //umum
            $id_tweet_respon = 4;
        }else{ // sopir
            $id_tweet_respon = 3;
        }
        $processed_tweets[$i]['label_sistem'] = '2.'.$cek;
        ######rocchio###### 
    } else {
        // klo kelas 4 (unknown)
    }
    
    // update label kelas
    // mysql_query("update tweets set label='$kelas' where id='$id_tweet' ");
    $processed_tweets[$i]['id_response'] = $id_tweet_respon;    
    $i++;

endwhile;

function klasifikasi_rocchio($kata_d=array(), $semua_kata, $N, $TF, $IDF, $TFIDF){ 
    foreach ($kata_d['word'] as $v1) {
        if(!array_key_exists($v1, $semua_kata)) $semua_kata[$v1] = array();
    }

    // Hitung TF
    foreach($semua_kata as $key=>$val){
        //load semua dokumen
        $i=$N+1; 
        $TF[$key]['kelas_uji'][$i]=0;
        foreach($kata_d['word'] as $k){
            if($key==$k){
                $TF[$key]['kelas_uji'][$i] ++; 
            }
        }  
    }

    foreach($TF as $index1=>$tf){
        $df=0;
        foreach($tf as $idx=>$ft1){
            if($idx != 'kelas_uji'){
                foreach($ft1 as $ft2){
                    if($ft2 > 0 ) $df++;
                }
            }
        }
        $IDF[$index1] = log10(@($N/$df)) ; 
    }

    // $TFIDF = array();
    $Panjang_Vektor = array();

    // yang baru
    foreach($semua_kata as $key=>$val){
        $i=$N + 1; 
        $TFIDF[$key]['kelas_uji'][$i]=$TF[$key]['kelas_uji'][$i] * $IDF[$key];
        if(isset($Panjang_Vektor[$i])){
        $Panjang_Vektor[$i] += ($TFIDF[$key]['kelas_uji'][$i]*$TFIDF[$key]['kelas_uji'][$i]);
        }else{
        $Panjang_Vektor[$i] = ($TFIDF[$key]['kelas_uji'][$i]*$TFIDF[$key]['kelas_uji'][$i]);
        } 
    }

    foreach ($Panjang_Vektor as $key1=>$pv) {
        $Panjang_Vektor[$key1] = sqrt($pv);
    }

    $Normalisasi_TFIDF = array();

    // yang baru
    foreach($semua_kata as $key=>$val){
        $i=$N+1;  
        $Normalisasi_TFIDF[$key]['kelas_uji'][$i] 
        = $TFIDF[$key]['kelas_uji'][$i] / $Panjang_Vektor[$i]; 
    }

    // $sql_centroid = mysql_query("select * from rocchio_centroid");
    $sql_centroid = mysql_query("select a.cUmum, a.cSopir, b.word from rocchio_centroid a, rocchio_word b WHERE a.word = b.id");
    $CUuji = array(1=>0,0);
    while($c=mysql_fetch_array($sql_centroid)){
        //ngecek klo kata uji sudah ada di data training
        if(array_key_exists($c['word'], $Normalisasi_TFIDF )){
            $CUuji[1] += pow($c['cUmum'] - $Normalisasi_TFIDF[$c['word']]['kelas_uji'][$i]  , 2);
            $CUuji[2] += pow($c['cSopir'] - $Normalisasi_TFIDF[$c['word']]['kelas_uji'][$i] , 2);
        }
    }

    if($CUuji[1] < $CUuji[2]){
        return 1;
    }
    return 2;
}

// kalau kelas 2
$benar = 0;
$no = 0;

// ambil response
$response = [];
$query_response = mysql_query("SELECT id, tweetresponse FROM tweet_response");
while($data = mysql_fetch_object($query_response)):
    $response[$data->id] = $data->tweetresponse;
endwhile;

// ini untuk hitung rekap kelas
$statistics = [
    "1"  =>["jumlah"=>0,"1"=>0,"2.1"=>0,"2.2"=>0,"3"=>0,"4"=>0],
    "2.1"=>["jumlah"=>0,"1"=>0,"2.1"=>0,"2.2"=>0,"3"=>0,"4"=>0],
    "2.2"=>["jumlah"=>0,"1"=>0,"2.1"=>0,"2.2"=>0,"3"=>0,"4"=>0],
    "3"  =>["jumlah"=>0,"1"=>0,"2.1"=>0,"2.2"=>0,"3"=>0,"4"=>0],
    "4"  =>["jumlah"=>0,"1"=>0,"2.1"=>0,"2.2"=>0,"3"=>0,"4"=>0]
];
?>

<div class="row">
    <div class="col-sm-12">
        <div class="showback">
            <h4><i class="fa fa-angle-right"></i> Testing Naive Bayes Classifier</h4>
            <table id="tabel1" class="display" cellspacing="0" width="100%" >
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tweet</th>
                        <th>Label Manual</th>
                        <th>Label System</th>
                        <th>Respons</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($processed_tweets as $tweet): ?>
                    <?php $label_manual = ''; $label_system = ''; ?>
                    <tr>
                        <td><?php echo ++$no; ?></td>
                        <td><?php echo $tweet['tweet']; ?></td>
                        <td><?php 
                            echo $label_manual = $label_kelas[$tweet['label_manual']];
                            // kode untuk update statistic
                            $statistics[$tweet['label_manual']]['jumlah'] += 1;
                            $statistics[$tweet['label_manual']][$tweet['label_sistem']] += 1;
                            ?>
                        </td>
                        <td><?php echo $label_system = $label_kelas[$tweet['label_sistem']]; ?></td>
                        <td><?php echo ($tweet['id_response'] != 0) ? $response[$tweet['id_response']] : '-'; ?></td>
                    </tr>
                    <?php if($label_manual == $label_system) $benar++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            akurasi: <?php echo ($benar / $no) * 100; ?>%
        </div><!--/showback -->
        <?php
        $kelas = ['1'=>'pujian','2.1'=>'keluhan umum', '2.2'=>'keluhan sopir','3'=>'follow','4'=>'unknown'];
        ?>
        <h3>Hasil Pengujian</h3>
        <table class="display2" width="100%" border="1">
            <thead>
                <tr>
                    <th rowspan="2">Actual Class</th>
                    <th rowspan="2">Jumlah Data</th>
                    <th colspan="5" align="center">Kelas Prediksi</th>
                </tr>
                <tr>
                    <?php foreach($kelas as $k=>$v): ?>
                    <th><?php echo $v; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($kelas as $k=>$v): ?>
                <tr>
                    <td><?php echo $v; ?></td>
                    <td><?php echo $statistics[$k]['jumlah']; ?></td>
                    <?php foreach($kelas as $k2=>$v2): ?>
                        <td><?php echo $statistics[$k][$k2]; ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3>Evaluasi Pengujian</h3>
        <table class="display2">
            <thead>
                <tr>
                    <th>Kelas</th>
                    <th>Precision</th>
                    <th>Recall</th>
                    <th>F-Measure</th>
                </tr>
                <?php foreach($kelas as $key=>$value): ?>
                <tr>
                    <td><?php echo $value; ?></td>
                    <td>
                        <?php
                        $precision = $statistics[$key][$key] / ($statistics[1][$key] + $statistics['2.1'][$key] + $statistics['2.2'][$key] + $statistics[3][$key] + $statistics[4][$key]) * 100;
                        echo number_format($precision, 2, ",", ".");
                        ?>%
                    </td>
                    <td>
                        <?php
                        $recall = $statistics[$key][$key] / ($statistics[$key][1] + $statistics[$key]['2.1'] + $statistics[$key]['2.2'] + $statistics[$key][3] + $statistics[$key][4]) * 100;
                        echo number_format($recall, 2, ",", ".");
                        ?>%
                    </td>
                    <td>
                        <?php $fmeasure = 2 * $precision * $recall / ($precision + $recall); echo number_format($fmeasure, 2, ",", "."); ?>%
                    </td>
                </tr>
                <?php endforeach; ?>
            </thead>
        </table>
    </div><!-- /col-sm-12 -->
</div><!-- /row -->