<?php
// Página de redirecionamento
function area_de_membros_redirect() {
	
	// Substitua o conteúdo desta variável pelo endereço da sua página de venda de assinaturas.
	$redirect_page = 'https://www.meusite.com/assinaturas';
	
	return $redirect_page;
}

// Regras de produtos de assinaturas de membros
function area_de_membros_produtos_regras() {
	
	// Configure aqui as suas regras de produtos para acesso a páginas e posts
	$products_members[] = array(
		'pages' => array(
			'productid'	=> 0,
			'days' 		=> 0,
			'pages' 	=> array( // Não utilize http:// ou https://
				'www.meusite.com/pagina1',
				'www.meusite.com/pagina2'
			),

		),
		
		'parentpages' => array(
			'productid'	=> 0,
			'days' 		=> 0,
			'parents'	=> array(
				'parent1',
				'parent2'
			)
		),
		
		'postcategories' => array(
			'productid'		=> 0,
			'days' 			=> 0,
			'categories'	=> array(
				'categoria1',
				'categoria2'
			)
		)

	);
	
	return $products_members;
}

// Verifica se o usuário comprou o produto que permite acesso a esta página ou post
function area_de_membros_produtos() {
	global $post;

	// Variável que define se será necessário redirecionar para a página de vendas
	$redirect = false;
	
	// Página de redirecionamento
	$redirect_page = area_de_membros_redirect();

	// Regras de produtos de assinaturas de membros
	$products_members = area_de_membros_produtos_regras();
   
	// Processa as regras de acesso
	$necessary_products = array();
	$expiration = array();
	foreach ($products_members as $product_rule) {
		
		// Verifica se tem regra de produtos para páginas específicas
		if (!empty($product_rule['pages'])) {
			if ($product_rule['pages']['productid'] > 0 
				and !empty($product_rule['pages']['pages']) ) {
				
				// URL completa da página acessada sem HTTP ou HTTPS
				$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				
				// Verifica se a página atual está nas regras de produtos
				foreach ($product_rule['pages']['pages'] as $page) {
					if ($url == $page) {
						$necessary_products[] = $product_rule['pages']['productid'];
						$expiration[ $product_rule['pages']['productid'] ] = $product_rule['pages']['days'];
					}
				}
			}
		}
		
		// Verifica se tem regra de produtos para páginas com ancestrais
		if (!empty($product_rule['parentpages']) and $post->post_type == 'page') {
			if ($product_rule['parentpages']['productid'] > 0 
				and !empty($product_rule['parentpages']['parents']) ) {
				
				// Pega os dados da página pai
				$post_parent = get_post($post->post_parent);
	
				// Pega o slug da página pai
				$parent_slug = $post_parent->post_name;
				
				// Verifica se a página atual tem um ancestral nas regras de produtos
				foreach ($product_rule['parentpages']['parents'] as $parent) {
					if ($parent_slug == $parent) {
						$necessary_products[] = $product_rule['parentpages']['productid'];
						$expiration[ $product_rule['parentpages']['productid'] ] = $product_rule['parentpages']['days'];
					}
				}
			}
		}
		
		// Verifica se tem regra de produtos para categorias de post
		if (!empty($product_rule['postcategories']) and $post->post_type == 'post') {
			if ($product_rule['postcategories']['productid'] > 0 
				and !empty($product_rule['postcategories']['categories']) ) {
				
				// Pega a lista de categorias do post
				$categories = get_the_category($post->ID);
				$postcategories = array();
				foreach($categories as $cat) {
					$postcategories[] = $cat->slug;
				}
				
				// Verifica se post atual tem uma categoria nas regras de produtos
				foreach ($product_rule['postcategories']['categories'] as $category) {
					if (array_search($category, $postcategories) !== false) {
						$necessary_products[] = $product_rule['postcategories']['productid'];
						$expiration[ $product_rule['postcategories']['productid'] ] = $product_rule['postcategories']['days'];
					}
				}
			}
		}
	}
	
	// Se a página ou post tiver um produto associado, verifica se o usuário comprou o produto
	if (!empty($necessary_products)) {
		
		// Força o redirecionamento
		$redirect = true;
		
		// Pega os produtos comprados pelo usuários
		$product_ids_by_curr_user = products_bought_by_curr_user();
		
		if (!empty($product_ids_by_curr_user)) {
			foreach ($product_ids_by_curr_user as $product_array) {
				$productid 		= $product_array['ID'];
				$StDataCompra 	= $product_array['data'];
				
				// Se o usuário comprou um dos produtos necessários para acessar
				// a página ou post, desativa o redirecionamento e sai do loop
				if (array_search($productid, $necessary_products) !== false) {
					
					// Verifica se a assiratura expirou
					$days = $expiration[ $productid ];
				
					// Data da expiração
					$expiration_date = 
						date( 
							'Y-m-d', 
								mktime(
									0, 
									0, 
									0, 
									substr($StDataCompra, 5, 2), 
									substr($StDataCompra, 8, 2) + $days,  
									substr($StDataCompra, 0, 4) 
								)
						);

					// Se dias para expiração for zero ou se a assinatura NÃO estiver expirada
					if ($days == 0 or date('Y-m-d') < substr($expiration_date, 0, 10) ) {
						$redirect = false;
						break;
					}
				}
			}
		}
	}
	
	// Caso a página ou post tenha regras para produtos e o usuário
	// não tenha comprado um desses produtos (ou o produto expirou), redireciona para a página de vendas
	if ($redirect) {
		wp_redirect( $redirect_page );
		exit;
	}   
}
add_action('template_redirect', 'area_de_membros_produtos');

// Coletas os produtos comprados pelo usuário
function products_bought_by_curr_user() {
	
	// Se o usuário não estiver logado
	if (!is_user_logged_in()) {
		return array();
	}
	
	// Dados do usuário
    $current_user = wp_get_current_user();
    if ( 0 == $current_user->ID ) return array();
   
    // Pedidos do usuário (Concluído + Processando)
	$order_statuses = array('wc-processing', 'wc-completed');
    $customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $current_user->ID,
        'post_type'   => wc_get_order_types(),
		'post_status' => $order_statuses,
    ) );

    // Loop dos pedidos para verifica se o usuário comprou o produto
    if ( ! $customer_orders ) return;
    $product_ids = array();
    foreach ( $customer_orders as $customer_order ) {
		
		// Data do pedido
		$StDataCompra = $customer_order->post_date;
		
		// Data da última alteração
		//$StDataCompra = $customer_order->post_modified;
		
        $order = wc_get_order( $customer_order->ID );
        $items = $order->get_items();
        foreach ( $items as $item ) {
            $product_id 	= $item->get_product_id();
            $product_ids[] = array('ID' => $product_id, 'data' => $StDataCompra);
        }
    }
    return array_unique($product_ids);
}

// Shortcode que exibe o conteúdo passado caso o usuário tenha
// comprado os produtos do parâmetro
//
// Exemplo:
// [areademembros products="1,2"]<p>Seu conteúdo</p>[/areademembros]
function area_de_membros_conteudo( $atts = array(), $content = null ) {
	
	// Estraindo parâmetros do shortcode
    extract(shortcode_atts(array(
		'products'			=> '',
		'mensagem_erro'	=> ''
    ), $atts));

	// Extrai os IDs dos produtos
	$product_ids = explode( ",", $products );
	
	// Variável que retorna o conteúdo
	$show = false;
		
	if (!empty($product_ids)) {
		// Pega os produtos comprados pelo usuários
		$product_ids_by_curr_user = products_bought_by_curr_user();
		if (!empty($product_ids_by_curr_user)) {
			foreach ($product_ids_by_curr_user as $product_array) {
				$productid 		= $product_array['ID'];
				
				// Se o usuário comprou um dos produtos necessários para acessar
				// a página ou post, ativa a exibição de conteúdo e sai do loop
				if (array_search($productid, $product_ids) !== false) {
					$show = true;
					break;
				}
			}
		}
	}
	if ($show) {
		return $content;
	} else {
		return $mensagem_erro;
	}	
}
add_shortcode( 'areademembros', 'area_de_membros_conteudo' );
?>
