<?php require (__DIR__ . '/../wp-load.php');
clearstatcache();

$user = wp_get_current_user();
if ($user->exists())
{
    
    $output = fopen("php://output", "w");
    $delimiter = ";";
    $enclosure = '"';
    $statistique ="";
    $_FACTAGE = 0;
    $_FILE_HAS_ACCORD = false;
    /*** #################  START FUNCTION ################ **/

    $_TYPE_EMBALLAGE = array(
                'affiche-pliee'  => 0, //ENVELOPPE
                'affiche-roulee' => 0 ,//TUBE
                'affiche-carton' => 0 //CARTON
            );


     $_EXAPAQ = array(
                    "EXAPAQ 0,5 à 1 KG 7,00" => array ( 
                        "qte" => 0,
                        "prix" => 7.00,
                        "Total" => 0
                    ),
                    "EXAPAQ 1 à 2 KG 7,32" => array ( 
                        "qte" => 0,
                        "prix" => 7.32,
                        "Total" => 0
                    ),
                    "EXAPAQ 2 à 3 KG 8,10" => array ( 
                        "qte" => 0,
                        "prix" => 8.00,
                        "Total" => 0
                    ),
                    "EXAPAQ 4 à 5 KG 10,20" => array ( 
                        "qte" => 0,
                        "prix" => 10.20,
                        "Total" => 0
                    ),
                    "EXAPAQ 6 à 7 KG 11,42" => array ( 
                        "qte" => 0,
                        "prix" => 11.42,
                        "Total" => 0
                    )
            );


                $type_conditionnements = array(
                    'affiche-pliee' => 'P',
                    'affiche-roulee' => "R",
                    'goodies' => 'G'
                );

    $array_type_affiche = array(
        'affiche-700x1000' => array(
            'affiche' => 'M', // Moyenne
            'gramme' => 150
        ) ,
        'affiche-120x160' => array(
            'affiche' => 'G', // Grand 120x160
            'gramme' => 200
        ) ,
        'affiche-160x120' => array(
            'affiche' => 'G', // Grand 160x120
            'gramme' => 200
        ) ,
        'affiche-700x1000-beige' => array(
            'affiche' => 'M beige', // Moyenne beige
            'gramme' => 150
        ) ,
        'affiche-700x1000-rouge' => array(
            'affiche' => 'M rouge', // Moyenne rouge
            'gramme' => 150
        ) ,
        'affiche-700x1000-vert' => array(
            'affiche' => 'M vert', // Moyenne vert
            'gramme' => 150
        ) ,
        'affiche-700x1000-violet' => array(
            'affiche' => 'M violet', // Moyenne violet
            'gramme' => 150
        ) ,
        'affichette-40x60' => array(
            'affiche' => 'P', // petit 40x60
            'gramme' => 100
        ) ,
        'affichette-60x40' => array(
            'affiche' => 'P', //petit 60x40
            'gramme' => 100
        ) ,
        'flyer-pedagogique' => array(
            'affiche' => 'F-P', 
            'gramme' => 100
        ) ,
        'boules-vertes' => array(
            'affiche' => 'B-V',
            'gramme' => 100
        ),
        'boules-rouges' => array(
            'affiche' => 'B-R',
            'gramme' => 100
        ),
    );


            $emballage = array(
                'affiche-pliee' => 10,
                'affiche-roulee' => 350,
                'goodies' => 1
            );

                
//$_EXAPAQ = array();

    function get_order_details($order_id, $output, $key_order, $_EXAPAQ)
    {
        global $_EXAPAQ;
        global $_TYPE_EMBALLAGE;
        global $_FACTAGE;
        global $_FILE_HAS_ACCORD;
        global $array_type_affiche;
        global $type_conditionnements;
        global $emballage;

        $order = wc_get_order($order_id);
        $categories = array($_GET['distributeur'] );
       //  echo "<br><br>" . $order->get_order_number();
        //if ( sizeof( $order->get_items() ) > 0 ) {

        $details = "";
        $poids = 0;
        $texts = "";
        $poidsType = 0;
        $pa_conditionnement_for_poids = array();
        $not_has_term =  true;
             foreach( $order->get_items() as $item_id => $item_data){ // les produits commandée
                // Just for a defined product category     

                $_pa_taille = wc_get_order_item_meta($item_id, 'pa_taille');
                $pa_conditionnement = wc_get_order_item_meta($item_id, 'pa_conditionnement');
               

                //$list_distributeur = array();

                      
                $cellue1 = utf8_decode($order->get_item_meta($item_id, '_qty', true) .  $array_type_affiche[$_pa_taille]['affiche'] . ' ' . $type_conditionnements[$pa_conditionnement] ) ;


                $tags = get_the_terms($item_data['product_id'], 'product_tag'); // Get tags of product
                $parent_produit = wc_get_product($_GET['affiches']);
                $product_synoname = (isset($tags[0])) ? $tags[0]->name : $parent_produit->get_name(); // check if product has a tag name

                $bon_livraison = array();
                $not_has_term = true;
                //echo "<br> has_term : ".has_term( $categories, 'product_cat', $item_data->get_product_id() ) ;
                if( has_term( $categories, 'product_cat', $item_data->get_product_id() ) ) {// has same distributeur selected
                    if ($_GET['affiches'] == $item_data->get_product_id() ) { // eqauls the same affiche name
                        //Get an instance of the WC_Product Object
                        //$_product = $item_data->get_product();


                        if ($_FILE_HAS_ACCORD == false) { // enter in this condition one time whene this variable is false
                            $csvExportFile = 'facture-'. $_GET['distributeur'] . '__' . date($_GET['date_debut'] . ' 00:00:00') .'__'. date($_GET['date_fin'] . ' 23:59:59') .'.csv';
                            header("Content-type: text/csv");
                            header("Content-Disposition: attachment; filename=$csvExportFile");
                        }

                        $_FILE_HAS_ACCORD = true;

                        $calculs = $order->get_item_meta($item_id, '_qty', true) * $array_type_affiche[$_pa_taille]['gramme'];


                       /* if (strpos($_pa_taille, "120") !== false)
                        { // qte * 200g
                            $calculs = $order->get_item_meta($item_id, '_qty', true) * 200;
                        }
                        else
                        { //qte * 100g
                            $calculs = $order->get_item_meta($item_id, '_qty', true) * 100;
                        }*/


                        $bon_livraison =array(
                            $order->get_order_number()  ,
                            date('Y-m-d H:i:s', strtotime(get_post($order->get_id())->post_date)),
                            utf8_decode($order->get_shipping_company())  ,
                            "CINEMA"  ,
                            utf8_decode($order->get_shipping_company())  ,
                            utf8_decode($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name())  ,
                            utf8_decode($order->get_shipping_address_1())  ,
                            utf8_decode($order->get_shipping_address_2())  ,
                            $order->get_shipping_postcode()  ,
                            utf8_decode($order->get_shipping_city())
                        );

                        
                        $typeDistributeur = ($term->name == "Pathé Distribution") ? "pathe" : "autres";
                        if ($typeDistributeur == 'pathe') {
                            $results[$_GET['distributeur']][$order->get_order_number()][$parent_produit->get_name()]['type-affiche'][$pa_conditionnement]['emballage'] = 500;
                        }else{
                            $results[$_GET['distributeur']][$order->get_order_number()][$parent_produit->get_name()]['type-affiche'][$pa_conditionnement]['emballage'] = $emballage[$pa_conditionnement];
                        }


                        $results[$_GET['distributeur']][$order->get_order_number()][$parent_produit->get_name()]['details'] = 
                                $results[$_GET['distributeur']][$order->get_order_number()][$parent_produit->get_name()]['details'] 
                                . "+" . 
                                $cellue1;   

                        $results[$_GET['distributeur']][$order->get_order_number()][$parent_produit->get_name()]['g'] = $results[$_GET['distributeur']][$order->get_order_number()][$parent_produit->get_name()]['g'] + $calculs;
                         $results[$_GET['distributeur']][$order->get_order_number()][$parent_produit->get_name()]['order_details'] = $bon_livraison;      
                       

                        $details .=  "+" . $cellue1;  
                        $poids  = $poids + $calculs ;

                        
                        if (!in_array($pa_conditionnement, $pa_conditionnement_for_poids)) {
                           $poidsType = $poidsType + $emballage[$pa_conditionnement] ; 
                        }
                        $pa_conditionnement_for_poids = array($pa_conditionnement);

                        $affichename = $parent_produit->get_name();
                        $distributeurname = $_GET['distributeur'] ;

                        
                         $texts = date('Y-m-d H:i:s', strtotime(get_post($order->get_id())->post_date)).';'.
                            utf8_decode($order->get_shipping_company())  .';'.
                            "CINEMA"  .';'.
                            utf8_decode($order->get_shipping_company())  .';'.
                            //utf8_decode($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name())  .';'.
                            utf8_decode($order->get_shipping_address_1())  .';'.
                            utf8_decode($order->get_shipping_address_2())  .';'.
                            $order->get_shipping_postcode()  .';'.
                            utf8_decode($order->get_shipping_city()).';'.
                            "'250095316669314203" ;


                        $_TYPE_EMBALLAGE[$pa_conditionnement] = $_TYPE_EMBALLAGE[$pa_conditionnement] + 1;
                        
                       // echo 'true <br>';
                    }
                    //else{
                    //    echo 'false <br>';
                    //    $not_has_term = false ;
                    //}
                 }//else{
                  //      echo 'false <br>';
                  //   $not_has_term = false ; 
                 //} 
            }// fin foreach list produits commandées

            if ($texts != "" && $_FILE_HAS_ACCORD == true ) {
                $_FACTAGE++; 
                
                    $texts =  $texts .';' . ltrim($details, "+") .';' .($poids + $poidsType) / 1000 . ';' . $affichename . ';' . $_GET['distributeur'];
                   fputcsv($output, explode(';', $texts) , ";");   

                   $total_poids = ($poids + $poidsType) / 1000 ; 
                    if ( $total_poids <= 1 ) {
                        $_EXAPAQ["EXAPAQ 0,5 à 1 KG 7,00"]["qte"]   =  $_EXAPAQ["EXAPAQ 0,5 à 1 KG 7,00"]["qte"] + 1;
                       // $_EXAPAQ["EXAPAQ 0,5 à 1 KG 7,00"]["prix"]   = 7.00 ;
                        $_EXAPAQ["EXAPAQ 0,5 à 1 KG 7,00"]["Total"] =   ($_EXAPAQ["EXAPAQ 0,5 à 1 KG 7,00"]["qte"] * $_EXAPAQ["EXAPAQ 0,5 à 1 KG 7,00"]["prix"] ) ;
                    }else if ( $total_poids > 1 && $total_poids <= 2 ) {
                        $_EXAPAQ["EXAPAQ 1 à 2 KG 7,32"]["qte"]   =  $_EXAPAQ["EXAPAQ 1 à 2 KG 7,32"]["qte"] + 1;
                        $_EXAPAQ["EXAPAQ 1 à 2 KG 7,32"]["Total"] =  $_EXAPAQ["EXAPAQ 1 à 2 KG 7,32"]["qte"] * $_EXAPAQ["EXAPAQ 1 à 2 KG 7,32"]["prix"]  ;

                    }else if ( $total_poids > 2 && $total_poids <= 3 ) {
                        $_EXAPAQ["EXAPAQ 2 à 3 KG 8,10"]["qte"]   =  $_EXAPAQ["EXAPAQ 2 à 3 KG 8,10"]["qte"] + 1;
                        $_EXAPAQ["EXAPAQ 2 à 3 KG 8,10"]["Total"] =  $_EXAPAQ["EXAPAQ 2 à 3 KG 8,10"]["qte"] * $_EXAPAQ["EXAPAQ 2 à 3 KG 8,10"]["prix"]  ;

                    }else if ( $total_poids > 4 && $total_poids <= 5 ) {
                        $_EXAPAQ["EXAPAQ 4 à 5 KG 10,20"]["qte"]   =  $_EXAPAQ["EXAPAQ 4 à 5 KG 10,20"]["qte"] + 1;
                        $_EXAPAQ["EXAPAQ 4 à 5 KG 10,20"]["Total"] =  $_EXAPAQ["EXAPAQ 4 à 5 KG 10,20"]["qte"] * $_EXAPAQ["EXAPAQ 4 à 5 KG 10,20"]["prix"]  ;

                    }else if ( $total_poids > 6 && $total_poids <= 7 ) {
                        $_EXAPAQ["EXAPAQ 6 à 7 KG 11,42"]["qte"]   =  $_EXAPAQ["EXAPAQ 6 à 7 KG 11,42"]["qte"] + 1;
                        $_EXAPAQ["EXAPAQ 6 à 7 KG 11,42"]["Total"] =  $_EXAPAQ["EXAPAQ 6 à 7 KG 11,42"]["qte"] * $_EXAPAQ["EXAPAQ 6 à 7 KG 11,42"]["prix"]  ;
                    }        
            } 
        
                       
    } // end function
    /*** #################  END FUNCTION ################ **/


    $from_date = (isset($_GET['date_debut'])) ? strtotime(date($_GET['date_debut'] . ' 00:00:00')) : strtotime(date('Y-m-d 00:00:00', strtotime("-30 day")));
    $to_date = (isset($_GET['date_fin'])) ? strtotime(date($_GET['date_fin'] . ' 23:59:59')) : strtotime(date('Y-m-d 23:59:59', strtotime("+0 day")));

   // $from_date = strtotime(date('Y-m-d 00:00:00', strtotime("-30 day")));
   // $to_date =  strtotime(date('Y-m-d 23:59:59', strtotime("+0 day")));
         
        $args = array(
           
           // 'status' => isset($_GET['status']) ? $_GET['status'] : array(
           //     'wc-processing'
           // ) ,
            'date_created' => $from_date . '...' . $to_date,
            'limit' => 1000000
        );

        // Get orders from people named John that were paid in the year 2016.
        $orders = wc_get_orders($args);

        if (count($orders)>0) {
            
            foreach ($orders as $key_order => $order) {
                get_order_details($order->get_id() , $output, $key_order, $_EXAPAQ);
            }

            if ($_FILE_HAS_ACCORD == true) { 
                $SURTAXE_GASOIL = 0 ;
                $_TOTAL = 0;

                $statistique = '';
                fputcsv($output, explode(';',  utf8_decode($statistique) ) , ";");

                $statistique = ';'. 'RECAPITULATIF GENERALE  MALIGNANT ';
                fputcsv($output, explode(';',  utf8_decode($statistique) ) , ";");

                $statistique = ';'. 'FACTAGE' .';'. $_FACTAGE .';'. 2.80 .';'. $_FACTAGE * 2.80;
                fputcsv($output, explode(';',  utf8_decode($statistique)) , ";");
                $statistique = ';'. 'ENVELOPPE' .';'. $_TYPE_EMBALLAGE['affiche-pliee'] .';'. 0.82 .';'.  $_TYPE_EMBALLAGE['affiche-pliee'] * 0.82;
                fputcsv($output, explode(';',  utf8_decode($statistique) ) , ";");
                $statistique = ';'. 'TUBE' .';'. $_TYPE_EMBALLAGE['affiche-roulee'] .';'. 1.90 .';'. $_TYPE_EMBALLAGE['affiche-roulee'] * 1.90 ;
                fputcsv($output, explode(';',  utf8_decode($statistique) ) , ";");
                $statistique = ';'. 'CARTON' .';'. $_TYPE_EMBALLAGE['affiche-carton'] .';'. 1.65 .';'. $_TYPE_EMBALLAGE['affiche-carton'] * 1.65; 
                fputcsv($output, explode(';',  utf8_decode($statistique) ) , ";");

                foreach($_EXAPAQ as $key => $item){
                    $statistique = ';'. $key .';'. $item['qte'] .';'. $item['prix'] .';'. $item['Total'];
                    $SURTAXE_GASOIL +=  $item['Total'] ;
                    $_TOTAL += $item['Total'] ;
                    fputcsv($output, explode(';',  utf8_decode($statistique) ) , ";");
                }

                $statistique = ';'. 'FORFAIT' .';'. 0 .';'. 0 .';'. 0;
                fputcsv($output, explode(';',  utf8_decode($statistique) ) , ";");
               
                $statistique = ';'. 'SURTAXE GASOIL 13.02%' .';'. $SURTAXE_GASOIL .';'. '13.02%' .';'. $SURTAXE_GASOIL  * 0.1302;
                fputcsv($output, explode(';',  utf8_decode($statistique) ) , ";");
                $statistique = ';'. 'TAXE SURETE' .';'. $_FACTAGE .';'. 0.32 .';'.   $_FACTAGE * 0.32;
                fputcsv($output, explode(';',  utf8_decode($statistique) ) , ";");

                $_TOTAL += ($_FACTAGE * 2.80) + ($_TYPE_EMBALLAGE['affiche-pliee'] * 0.82) + ($_TYPE_EMBALLAGE['affiche-roulee'] * 1.90) + ($_TYPE_EMBALLAGE['affiche-carton'] * 1.65) + ($SURTAXE_GASOIL  * 0.1302) + ($_FACTAGE * 0.32) ;

                $statistique = ';'. 'TOTAL' .';'. '' .';'. '' .';'. $_TOTAL ;
                fputcsv($output, explode(';',  utf8_decode($statistique) ) , ";");


                //print_r($_TYPE_EMBALLAGE);
                fclose($output);
            }else{

                $produit = wc_get_product($_GET['affiches']);
                $loggin = false; 
                $message = "Il n'y a pas de commande d'affiche << <b>" . $produit->get_name() ." >> </b> à la date sélectionnée : " . "<br><b>" . date($_GET['date_debut'] . ' 00:00:00')  . '</b> à <b>' . date($_GET['date_fin'] . ' 23:59:59')."</b>" ;
                include ('layouts/menu.php');
                include ('notfound.php');
                include ('layouts/footer.php');
            }

        }else{
            $loggin = false; 
            $message = "Il n'y a pas de commande à la date sélectionnée : " . "<br><b>" . date($_GET['date_debut'] . ' 00:00:00')  . '</b> à <b>' . date($_GET['date_fin'] . ' 23:59:59')."</b>" ;
            include ('layouts/menu.php');
            include ('notfound.php');
            include ('layouts/footer.php');
        }
    
}
else
{
    $loggin = false;
    $message = "Vous n'avez pas la permission d'accéder";
    include ('notfound.php');
    die();
} ?>
