<?php 
require (__DIR__ . '/../wp-load.php');
include_once( "./PHP_XLSX_CSV_WRITER/csv/csv.writer.php" );
include_once( "./PHP_XLSX_CSV_WRITER/headers.writer.php" );
clearstatcache();

$user = wp_get_current_user();
if ($user->exists())
{

    $_TYPE_EMBALLAGE = array(
        'affiche-pliee'  => 0, //ENVELOPPE
        'affiche-roulee' => 0 ,//TUBE
        'affiche-carton' => 0 //CARTON
    );


    $_EXAPAQ = array(
        "EXAPAQ 0,5 à 1 KG 7,00" => array ( 
            "prix" => 7.00,
            "key" => 'exa1'
        ),
        "EXAPAQ 1 à 2 KG 7,32" => array ( 
            "prix" => 7.32,
            "key" => 'exa2'
        ),
        "EXAPAQ 2 à 3 KG 8,10" => array ( 
            "prix" => 8.10,
            "key" => 'exa3'
        ),
        "EXAPAQ 4 à 5 KG 10,20" => array ( 
            "prix" => 10.20,
            "key" => 'exa4'
        ),
        "EXAPAQ 6 à 7 KG 11,42" => array ( 
            "prix" => 11.42,
            "key" => 'exa5'
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

    $final_result = [];
    $colis_list = [];
    $summary_list = [];

    function get_order_meta_data( $size, $qty, $cond, $company ) {
        //code = $size . '-' . $cond
        global $type_conditionnements;
        global $array_type_affiche;

        $cond_key = $type_conditionnements[ $cond ];
        $size_data = $array_type_affiche[ $size ];
        $size_code = $size_data[ 'affiche' ];
        $weight = ( $cond === 'affiche-roulee' && $company === 'Pathé Distribution' ? 500 : $size_data[ 'gramme' ] ) * $qty;
        $code = $size_code . '-' . $cond_key;

        return [
            'weight' => $weight,
            'code' => $code,
            'size' => $size,
            'cond' => $cond,
            'qty' => $qty
        ];
    };

    function place_data_in_result( $data ) {
        global $final_result;

        if ( count( $final_result ) ) {
            foreach( $final_result as $index => $item ) {
                if ( $item[ 'cinema' ] === $data[ 'data' ][ 'cinema' ] ) {
                    if ( $item[ 'company' ] === $data[ 'data' ][ 'company' ] ) {
                        if ( $item[ 'cond' ] === $data[ 'data' ][ 'cond' ] ) {
                            foreach( $item[ 'metas' ] as $meta_id => $meta ) {
                                if ( $meta[ 'code' ] === $data[ 'meta' ][ 'code' ] ) {
                                    if ( $meta[ 'film' ] === $data[ 'data' ][ 'film' ] ) {
                                            $final_result[ $index ][ 'metas' ][ $meta_id ][ 'weight' ] += $data[ 'meta' ][ 'weight' ];
                                            $final_result[ $index ][ 'metas' ][ $meta_id ][ 'qty' ] += $data[ 'meta' ][ 'qty' ];
                                        return;
                                    }
                                }
                            }
                            array_push( $final_result[ $index ][ 'metas' ], [
                                'film' => $data[ 'data' ][ 'film' ],
                                'weight' => $data[ 'meta' ][ 'weight' ],
                                'code' => $data[ 'meta' ][ 'code' ],
                                'size' => $data[ 'meta' ][ 'size' ],
                                'cond' => $data[ 'meta' ][ 'cond' ],
                                'qty' => $data[ 'meta' ][ 'qty' ]
                            ] );
                            return;
                        }
                    }
                }
            }
        }

        array_push( $final_result, [
            'cond' => $data[ 'data' ][ 'cond' ],
            'cinema' => $data[ 'data' ][ 'cinema' ],
            'company' => $data[ 'data' ][ 'company' ],
            'id' => $data[ 'data' ][ 'id' ],
            'postcode' => $data[ 'data' ][ 'postcode' ],
            'address1' => $data[ 'data' ][ 'address1' ], 
            'address2' => $data[ 'data' ][ 'address2' ],
            'city' => $data[ 'data' ][ 'city' ],
            'date' => $data[ 'data' ][ 'date' ],
            'metas' => [ [
                'film' => $data[ 'data' ][ 'film' ],
                'weight' => $data[ 'meta' ][ 'weight' ],
                'code' => $data[ 'meta' ][ 'code' ],
                'size' => $data[ 'meta' ][ 'size' ],
                'cond' => $data[ 'meta' ][ 'cond' ],
                'qty' => $data[ 'meta' ][ 'qty' ]
            ] ]
        ] );
    }

    function get_summary_data( $cond, $qty, $weight ) {
        $kilo = $weight / 1000;
        $expaq = '';
            if ( $kilo >= 0.5 AND $kilo <= 1 ) {
                $expaq = 'exa1';
            } elseif ( $kilo >= 1 AND $kilo <= 2 ) {
                $expaq = 'exa2';
            } elseif ( $kilo > 2 AND $kilo <= 3 ) {
                $expaq = 'exa3';
            } elseif ( $kilo > 4 AND $kilo <= 5 ) {
                $expaq = 'exa4';
            } elseif ( $kilo >= 6 AND $kilo <= 7 ) {
                $expaq = 'exa5';
            }
        return [
            'enveloppe' => $cond === 'affiche-pliee' && $qty<= 10 ? 1 : 0,
            'tube' => $cond === 'affiche-roulee' && $qty<= 10 ? 1 : 0,
            'carton' => $qty > 10 ? 1 : 0,
            'exapaq' => $expaq
        ];
    };

    function get_colis_summary_data( $colis ) {
        $data = get_summary_data( $colis[ 'cond' ], $colis[ 'qty' ], $colis[ 'weight' ] );
        return [
            'fact' => 1,
            'enveloppe' => $data[ 'enveloppe' ],
            'tupe' => $data[ 'tupe' ],
            'carton' => $data[ 'carton' ],
            'exapaq' => $data[ 'exapaq' ]
        ];
    }

    function create_all_summaries() {
        global $summary_list;
        global $colis_list;

        $summary = [];

            foreach ( $colis_list as $colis ) {
                if ( !array_key_exists( $colis[ 'film' ], $summary ) ) {
                    $data = get_colis_summary_data( $colis );
                    $summary[ $colis[ 'film' ] ] = [
                        'fact' => $data[ 'fact' ],
                        'enveloppe' => $data[ 'enveloppe' ],
                        'tupe' => $data[ 'tupe' ],
                        'carton' => $data[ 'carton' ],
                        "{$data[ 'exapaq' ]}" => 1
                    ];
                } else {
                    $data = get_colis_summary_data( $colis );
                    $summary[ $colis[ 'film' ] ][ 'fact' ]++;
                    $summary[ $colis[ 'film' ] ][ 'enveloppe' ] += $data[ 'enveloppe' ];
                    $summary[ $colis[ 'film' ] ][ 'tube' ] += $data[ 'tube' ];
                    $summary[ $colis[ 'film' ] ][ 'carton' ] += $data[ 'carton' ];

                    if ( array_key_exists( $data[ 'exapaq' ], $summary[ $colis[ 'film' ] ] ) ) {
                        $summary[ $colis[ 'film' ] ][ $data[ 'exapaq' ] ]++;
                    } else {
                        $summary[ $colis[ 'film' ] ][ $data[ 'exapaq' ] ] = 1;
                    }
                }
            }

        foreach ( $summary as $film => $item ) {
            $total = 0;
            $exapaq = 0;
            $summary_item = [
                'film' => $film,
                'fact' => $item[ 'fact' ],
                'enveloppe' => $item[ 'enveloppe' ],
                'tube' => $item[ 'tube' ],
                'carton' => $item[ 'carton' ],
                'exa1' => 0, 
                'exa2' => 0, 
                'exa3' => 0, 
                'exa4' => 0, 
                'exa5' => 0,
                'taxe' => 2 * $item[ 'fact' ],
                'surtaxe' => 0,
                'total' => 0
            ];

            foreach( [ 'exa1', 'exa2', 'exa3', 'exa4', 'exa5' ] as $exa ) {
                if ( array_key_exists( $exa, $item ) ) {
                    $summary_item[ $exa ] = $item[ $exa ];
                }
            }

            $total += ( $item[ 'fact' ] * 2.8 );
            $total += ( $item[ 'carton' ] * 1.65 );
            $total += ( $item[ 'enveloppe' ] * 0.82 );
            $total += ( $item[ 'tube' ] * 1.9 );

            $exapaq += ( $summary_item[ 'exa1' ] * 7 );
            $exapaq += ( $summary_item[ 'exa2' ] * 7.32 );
            $exapaq += ( $summary_item[ 'exa3' ] * 8.10 );
            $exapaq += ( $summary_item[ 'exa4' ] * 10.20 );
            $exapaq += ( $summary_item[ 'exa5' ] * 11.42 );

            $summary_item[ 'surtaxe' ] = $exapaq;

            $total += ( $summary_item[ 'surtaxe' ] * ( 13.02 / 100 ) );
            $total += ( $summary_item[ 'taxe' ] * 0.32 );
            $total += $exapaq;

            $summary_item[ 'total' ] = $total;
            array_push( $summary_list, $summary_item );
        }
    }

    function create_all_colis() {
        global $final_result;
        global $colis_list;

        foreach( $final_result as $item ) {
            $colis = [
                'cond' => $item[ 'cond' ],
                'cinema' => $item[ 'cinema' ],
                'company' => $item[ 'company' ],
                'id' => $item[ 'id' ],
                'postcode' => $item[ 'postcode' ],
                'address1' => $item[ 'address1' ], 
                'address2' => $item[ 'address2' ],
                'city' => $item[ 'city' ],
                'date' => $item[ 'date' ],
                'code' => '',
                'weight' => 0,
                'film' => '',
                'qty' => 0
            ];

            $films = [];
            $codes = [];
            foreach( $item[ 'metas' ]  as $meta ) {
                if ( array_key_exists( $meta[ 'film' ], $films ) ) {
                    $films[ $meta[ 'film' ] ] += $meta[ 'qty' ];
                } else {
                    $films[ $meta[ 'film' ] ] = $meta[ 'qty' ];
                }

                if ( array_key_exists( $meta[ 'code' ], $codes ) ) {
                    $codes[ $meta[ 'code' ] ][ 'qty' ] += $meta[ 'qty' ];
                    $codes[ $meta[ 'code' ] ][ 'weight' ] += $meta[ 'weight' ];
                } else {
                    $codes[ $meta[ 'code' ] ] = [ ];
                    $codes[ $meta[ 'code' ] ][ 'qty' ] = $meta[ 'qty' ];
                    $codes[ $meta[ 'code' ] ][ 'weight' ] = $meta[ 'weight' ];
                }
            }

            foreach( $codes as $code => $data ) {
                if ( !$colis[ 'code' ] ) {
                    $colis[ 'code' ] = "{$data[ 'qty' ]} $code";
                } else {
                    $colis[ 'code' ] .= " + {$data[ 'qty' ]} $code";
                }

                $colis[ 'weight' ] += $data[ 'weight' ];
                $colis[ 'qty' ] += $data[ 'qty' ];
            }

            $film = '';
            $qty = 0;

            foreach ( $films as $film_item => $qty_item ) {
                if ( $qty_item > $qty ) {
                    $film = $film_item;
                }
            }

            $colis[ 'film' ] = $film;
            array_push( $colis_list, $colis );
        }

        create_all_summaries();
    }

    function analyse_data( $data ) {
        $meta = get_order_meta_data(
            $data[ 'size' ],
            $data[ 'qty' ],
            $data[ 'cond' ],
            $data[ 'company' ]
        );

        place_data_in_result( [
            'data' =>  $data,
            'meta' =>  $meta
        ] );
    }

    function get_order_data( $order_id ) {
        $order = wc_get_order( $order_id );
        $categories = array( $_GET['distributeur'] );
        //$parent_produit = wc_get_product($_GET['affiches']);

        foreach( $order->get_items() as $item_id => $item_data ) {
            /** 
                *
                * comme le nom des films ressemble à ceci "La Revanche des Crevettes Pailletés - Affiche 120x160, Affiche pliée"
                * en général, nous voulons le pretourner 
            */
            $film_infos = explode( "-", $item_data->get_name() );
            $film_infos = array_splice( $film_infos, 0, count( $film_infos ) - 1 );
            $film = implode( '-', $film_infos );
            if ( has_term( $categories, 'product_cat', $item_data->get_product_id() ) ) {
                analyse_data( [
                    'qty' => intval( $order->get_item_meta( $item_id, '_qty', true ) ), //quantite
                    'cond' => wc_get_order_item_meta($item_id, 'pa_conditionnement'), //conditionnement
                    'cinema' => trim( $order->get_shipping_company() ), //cinema
                    'size' => wc_get_order_item_meta($item_id, 'pa_taille'), //taille de l'affiche
                    'company' => trim( $order->get_shipping_first_name(). ' ' .$order->get_shipping_last_name() ), //vendeur
                    'id' => intval( $order->get_order_number() ), // identifiant
                    'postcode' => intval( $order->get_shipping_postcode() ), //code postal du cinema
                    'film' => trim( $film ), //film
                    'address1' => $order->get_shipping_address_1(), // adresse du cinema
                    'address2' => $order->get_shipping_address_2(), // adresse du cinema
                    'city' => $order->get_shipping_city(), //ville
                    'date' => date('Y-m-d H:i:s', strtotime(get_post($order->get_id())->post_date)) //date de commander
                ] );
            }
        }
    }

    function create_models() {
        global $colis_list;
        global $summary_list;

        function get_summary_header( $film ) {
            return array(
                "", "", "RECAPITULATIF DU FILM (" . strtoupper( $film ) . "): ", "", "", "", "", "", "", ""
            );
        };

        function add_resume_row( &$rows, $summary, $key, $name, $unit ) {
            array_push( $rows, [
                "", "", $name, $summary[ $key ], $unit, $unit * $summary[ $key ], "", "", "", ""
            ] );
        };

        HeadersWriter::CSVHeaders( 'facture-'. $_GET[ 'distributeur' ] . '__' . date($_GET['date_debut'] . ' 00:00:00') .'__'. date($_GET['date_fin'] . ' 23:59:59') );
        $writter = new CSV_Writer();
        $colis_headers = array(
            'DATE DE COMMANDE', 'NOM DU FILM', 'NOM DU CINEMA', 'ADRESSE N॰ 1', 'ADRESSE॰ 2', 'CODE POSTAL', 'VILLE', 'NUMERO DE COMMANDE', 'DESIGNATION', 'POIDS'
        );

        $rows = [ $colis_headers, [ ] ];

            foreach ( $colis_list as $colis ) {
                array_push( $rows, [
                    $colis[ 'date' ],
                    $colis[ 'film' ],
                    $colis[ 'cinema' ],
                    $colis[ 'address1' ],
                    $colis[ 'address2' ] ? $colis[ 'address2' ] : " ",
                    $colis[ 'postcode' ],
                    $colis[ 'city' ],
                    $colis[ 'id' ],
                    $colis[ 'code' ],
                    $colis[ 'weight' ]
                ] );
            }

            foreach ( $summary_list as $summary ) {
                array_push( $rows, [], [] );
                array_push( $rows, get_summary_header( $summary[ 'film' ] ) );
                add_resume_row( $rows, $summary, 'fact', 'FACTAGE', 2.8 );
                add_resume_row( $rows, $summary, 'enveloppe', 'ENVELOPPE', 0.82 );
                add_resume_row( $rows, $summary, 'tube', 'TUBE', 1.9 );
                add_resume_row( $rows, $summary, 'carton', 'CARTON', 1.65 );
                add_resume_row( $rows, $summary, 'exa1', 'EXAPAQ 0,5 à 1 KG 7,00', 7.1 );
                add_resume_row( $rows, $summary, 'exa2', 'EXAPAQ 1 à 2 KG 7,32', 7.32 );
                add_resume_row( $rows, $summary, 'exa3', 'EXAPAQ 2 à 3 KG 8,10', 8.10 );
                add_resume_row( $rows, $summary, 'exa4', 'EXAPAQ 4 à 5 KG 10,20', 10.20 );
                add_resume_row( $rows, $summary, 'exa5', 'EXAPAQ 6 à 7 KG 11,42', 11.42 );
                array_push( $rows, [
                    "", "", "FORFAIT", 0, 0, 0, "", "", "", ""
                ] );
                add_resume_row( $rows, $summary, 'surtaxe', 'SURTAXE GASOIL 13.02%', ( 13.02 / 100 ) );
                add_resume_row( $rows, $summary, 'taxe', 'TAXE SURETE', 0.32 );
                array_push( $rows, [
                    "", "", "TOTAL", "", "", $summary[ 'total' ], "", "", "", ""
                ] );
            }
        
        $writter->addLines( $rows );
        $writter->writeToStdOut();
    };


    $from_date = (isset($_GET['date_debut'])) ? strtotime(date($_GET['date_debut'] . ' 00:00:00')) : strtotime(date('Y-m-d 00:00:00', strtotime("-30 day")));
    $to_date = (isset($_GET['date_fin'])) ? strtotime(date($_GET['date_fin'] . ' 23:59:59')) : strtotime(date('Y-m-d 23:59:59', strtotime("+0 day")));
         
        $args = array(
            'date_created' => $from_date . '...' . $to_date,
            'limit' => 1000000
        );

        $orders = wc_get_orders( $args );

        if ( count( $orders ) > 0 ) {
            
            foreach ( $orders as $key_order => $order ) 
                get_order_data( $order->get_id(), $key_order );
            create_all_colis();
            create_models();
            exit( 0 );
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
