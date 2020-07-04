<?php
// Protege o acesso a todos os posts de um site
function area_de_membros_posts() {
	global $post;
	
	// Verifica se é um post
	if ($post->post_type != 'post') {
		return;
	}
	
	// Página de redirecionamento
    // Substitua o conteúdo desta variável pelo endereço da sua página de login.
	$redirect_page = 'https://www.seudominio.com/login/';
	
	// Se o usuário acessar um post e não estiver logado, redireciona para a página de login
	if ( $post->post_type == 'post' and !is_user_logged_in() ) {
		wp_redirect( $redirect_page );
		exit;
  }
}
add_action('template_redirect', 'area_de_membros_posts');
?>
