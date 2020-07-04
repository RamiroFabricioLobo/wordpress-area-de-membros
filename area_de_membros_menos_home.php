// Obriga a estar logado ou na página home para acessar o site
// Adicione este código no arquivo functions.php do seu tema. Utilize preferencialmente um tema filho.
function area_de_membros_menos_home() {
	// Página de redirecionamento
	// Substitua o conteúdo desta variável pelo endereço da sua página de login.
	$redirect_page = 'https://www.seudominio.com/login/';
	
	// Se o usuário não estiver na página home, também não estiver na página de login e não estiver logado, redireciona para a página de login
	if ( !is_front_page() and get_permalink() != $redirect_page and !is_user_logged_in() ) {
		wp_redirect( $redirect_page );
		exit;
  }
}
add_action('template_redirect', 'area_de_membros_menos_home');
