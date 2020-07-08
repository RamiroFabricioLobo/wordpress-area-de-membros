<?php
// Página de redirecionamento
function area_de_membros_redirect() {
	
	// Substitua o conteúdo desta variável pelo endereço da sua página de login.
	$redirect_page = 'https://www.meudominio.com/login/';
	
	return $redirect_page;
}

// Regras de posts restritos para membros cadastrados
function area_de_membros_posts_regras() {
	
	// Configure aqui as suas regras de categorias de posts para membros.
	// Preecha com o slug de cada categoria.
	$categories_members = array(
		'teste',
		'teste1'
	);
	
	return $categories_members;
}

// Protege o acesso de posts de categorias determinadas 
// para usar na área de membros
function area_de_membros_posts_categorias() {
	global $post;

	// Variável que define se será necessário redirecionar para a página de login
	$redirect = false;
	
	// Página de redirecionamento
	$redirect_page = area_de_membros_redirect();

	// Array com as regras de posts restritos para membros cadastrados
	$categories_members = area_de_membros_posts_regras();
   
	// Verifica se tem regras de posts restritos para membros cadastrados
	// e se o usuário está acessando um post
	if (!empty($categories_members) and $post->post_type == 'post') {

		// Pega a lista de categorias do post
		$categories = get_the_category($post->ID);
		$postcategories = array();
		foreach($categories as $cat) {
			$postcategories[] = $cat->slug;
		}
				
		// Verifica se o post atual está em uma das categorias de membros das regras
		foreach ($categories_members as $category) {
			if (array_search($category, $postcategories) !== false) {
				$redirect = true;
				break;
			}
		}
	}
	
	// Se o usuário acessar um post de uma categoria de área de membros
	// e não estiver logado, redireciona para a página de login
	if ( $redirect and !is_user_logged_in() ) {
		wp_redirect( $redirect_page );
		exit;
	}  
}
add_action('template_redirect', 'area_de_membros_posts_categorias');
?>
