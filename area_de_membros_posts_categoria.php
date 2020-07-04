<?php
// Protege o acesso de posts de uma determinada categoria para usar na área de membros
function area_de_membros_posts_categoria() {
	global $post;
	
	// Verifica se é um post
	if ($post->post_type != 'post') {
		return;
	}
	
	// Slug da categoria de posts da área de membros
    // Substitua o conteúdo desta varável pelo slug da categoria que deseja proteger
	$membros_category_slug = 'membros';
	
	// Página de redirecionamento
    // Substitua o conteúdo desta variável pelo endereço da sua página de login.
	$redirect_page = 'https://www.meudominio.com/login/';
	
	// Pega a lista de categorias
	$categories = get_the_category($post->ID);
	
	// Verifica se o post tem a categoria de membros
	$BoAreaDeMembros = false;
	foreach($categories as $cat) {
		if ($cat->slug == $membros_category_slug) {
			$BoAreaDeMembros = true;
		}
	}
	
	// Se o usuário acessar um post da área de membros e não estiver logado, redireciona para a página de login
	if ( $BoAreaDeMembros and !is_user_logged_in() ) {
		wp_redirect( $redirect_page );
		exit;
  }
}
add_action('template_redirect', 'area_de_membros_posts_categoria');
?>