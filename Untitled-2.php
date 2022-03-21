<?php require (__DIR__ . '/../wp-load.php');
clearstatcache();

$user = wp_get_current_user();
if ($user->exists())
{

    $_TYPE_EMBALLAGE = array(
        'affiche-pliee'  => 350, //ENVELOPPE si Pathé poids = 500g
        'affiche-roulee' => 10,//TUBE
        'affiche-carton' => 300 //CARTON
    );

    $emballage = array(
        'affiche-pliee' => 10,
        'affiche-roulee' => 350,
        'goodies' => 1
    );

    $type_conditionnements = array(
        'petite' => 'P',
        'moyen' => "M",
        'grand' => 'G'
    );

    $tailles = array(
        'affiche-pliee' => 'P',
        'affiche-roulee' => "R",
        'goodies' => 'G'
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



    $from_date = (isset($_GET['date_debut'])) ? strtotime(date($_GET['date_debut'] . ' 00:00:00')) : strtotime(date('Y-m-d 00:00:00', strtotime("-30 day")));
    $to_date = (isset($_GET['date_fin'])) ? strtotime(date($_GET['date_fin'] . ' 23:59:59')) : strtotime(date('Y-m-d 23:59:59', strtotime("+0 day")));

   // $from_date = strtotime(date('Y-m-d 00:00:00', strtotime("-30 day")));
   // $to_date =  strtotime(date('Y-m-d 23:59:59', strtotime("+0 day")));
         
        $args = array(
            'date_created' => $from_date . '...' . $to_date,
            'limit' => 1000000
        );

        // Get orders from people named John that were paid in the year 2016.
        $orders = wc_get_orders($args);
        $item = $orders[ 0 ];
        $id = $item->get_id();
        $taille = wc_get_order_item_meta($id, 'pa_taille');
        $odr = wc_get_order( $id );
        get_the_terms($item_data['product_id'], 'product_tag');
        var_dump( $item_data, $taille, $odr );

        /*if (count($orders)>0) {
            
            foreach ($orders as $key_order => $order) {
                get_order_details($order->get_id() , $output, $key_order, $_EXAPAQ);
            }

            if ($_FILE_HAS_ACCORD == true) { 
                
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
        }*/
    
}
else
{
    $loggin = false;
    $message = "Vous n'avez pas la permission d'accéder";
    include ('notfound.php');
    die();
} ?>
