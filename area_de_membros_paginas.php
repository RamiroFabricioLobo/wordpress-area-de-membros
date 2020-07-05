<?php
// Página de redirecionamento
function area_de_membros_redirect() {
	
	// Substitua o conteúdo desta variável pelo endereço da sua página de login.
	$redirect_page = 'https://www.modeloshostnet.com/lvt/meus-cadastro/';
	
	return $redirect_page;
}

// Regras de páginas restritas para mebros cadastrados
function area_de_membros_regras() {
	
	// Configure aqui as suas regras de páginas para membros
	$pages_members[] = array(
		'pages' => array( // Não utilize http:// ou https://
			'www.modeloshostnet.com/lvt/teste/teste-1/',
			'www.modeloshostnet.com/lvt/contact-us/'

		),
		'parentpages' => array(
			'teste',
			'teste1'
		)
	);
	
	return $pages_members;
}

// Protege o acesso da páginas da área de membros
function area_de_membros_paginas() {
	global $post;
	
	// Verifica se é um página
	if ($post->post_type != 'page') {
		return;
	}

	// Variável que define se será necessário redirecionar para a página de login
	$redirect = false;
	
	// Página de redirecionamento
	$redirect_page = area_de_membros_redirect();

	// Regras contendo as páginas protegidas para acesso exclusivo para membros
	$pages_members = area_de_membros_regras();
   
	// Processa as regras de acesso
	foreach ($pages_members as $pages_rules) {
		
		// Verifica se tem regra para páginas específicas
		if (!empty($pages_rules['pages'])) {
			
			// URL completa da página acessada sem HTTP ou HTTPS
			$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				
			// Verifica se a página atual está nas regras de produtos
			foreach ($pages_rules['pages'] as $page) {
				if ($url == $page) {
					$redirect = true;
					break;
				}
			}
			
		}
		
		// Sai do loop se já identificou que é uma página de membros
		if ($redirect) {
			break;
		}
		
		// Verifica se tem regra para páginas com ancestrais
		if (!empty($pages_rules['parentpages'])) {
				
			// Pega os dados da página pai
			$post_parent = get_post($post->post_parent);
	
			// Pega o slug da página pai
			$parent_slug = $post_parent->post_name;
				
			// Verifica se a página atual tem um ancestral nas regras de produtos
			foreach ($pages_rules['parentpages'] as $parent) {
				if ($parent_slug == $parent) {
					$redirect = true;
					break;
				}
			}
			
		}
	}
	
	// Caso a página esteja entre as páginas para membros
	// e o usuário não estiver logado redireciona para a página de login
	if ($redirect and !is_user_logged_in()) {
		wp_redirect( $redirect_page );
		exit;
	}   
}
add_action('template_redirect', 'area_de_membros_paginas');
?>
