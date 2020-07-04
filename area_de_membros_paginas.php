<?php
// Protege o acesso da páginas da área de membros
function area_de_membros_paginas() {
	global $post;
	
	// Verifica se é um post
	if ($post->post_type != 'page') {
		return;
	}
	
	// Slug da páginas da área de membros
	// Substitua o conteúdo desta variável pelo slug da página pai (ancestral)
	$membros_slug = 'membros';
	
	// Página de redirecionamento
    // Substitua o conteúdo desta variável pelo endereço da sua página de login.
	$redirect_page = 'https://www.seudominio.com/login/';
	
	// Pega os dados da página pai
	$post_parent = get_post($post->post_parent);
	
	// Pega o slug da página pai
	$parent_slug = $post_parent->post_name;
	
    // Se o usuário acessar uma página de membros e não estiver logado, redireciona para a página de login
	if ( $parent_slug == $membros_slug and !is_user_logged_in() ) {
		wp_redirect( $redirect_page );
		exit;
  }
}
add_action('template_redirect', 'area_de_membros_paginas');
?>
