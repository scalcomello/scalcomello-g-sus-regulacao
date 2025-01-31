# Documentação do Módulo: Nova Solicitação

## Descrição Geral
Este módulo faz parte de um sistema de gerenciamento de solicitações. Ele permite a busca de cidadãos com base em informações como nome, CNS, CPF ou nome da mãe. A página inclui uma barra de progresso, um formulário de busca com funcionalidade de auto-completar e exibição de resultados de forma dinâmica usando AJAX.

## Tecnologias Utilizadas
- **PHP**: Para a lógica de backend e inclusão de arquivos.
- **HTML5**: Estrutura da página.
- **CSS3**: Estilos customizados.
- **JavaScript/jQuery**: Para manipulação de eventos e requisições AJAX.
- **Bootstrap 5**: Para estilos e responsividade.
- **Font Awesome**: Para ícones.

## Estrutura do Código
### Arquivos Incluídos
- **`dbconnect.php`**: Conexão com o banco de dados.
- **`header.php`** e **`footer.php`**: Estrutura de cabeçalho e rodapé da página.
- **`sidebar.php`**: Barra lateral de navegação.
- **`progress_bar.php`**: Componente que exibe a barra de progresso da página.

### Funcionalidade Principal
A página possui três passos, com o primeiro (`Buscar Cidadão`) sendo ativo por padrão. Há um formulário de busca que permite ao usuário inserir informações para procurar registros de cidadãos. A busca é otimizada por uma função `debounce` para evitar requisições desnecessárias ao servidor.

### Componentes da Interface
1. **Breadcrumb**: Facilita a navegação dentro do sistema.
2. **Formulário de Busca**: Permite a entrada de dados para consulta.
3. **Indicador de Carregamento**: Exibido durante a busca de dados.
4. **Resultados da Busca**: Exibidos em cartões estilizados.

### Detalhes do CSS Personalizado
- Estilização dos cartões de resultados para melhor apresentação.
- Botões personalizados com cores específicas.
- Indicador de carregamento estilizado com fundo translúcido e bordas arredondadas.

### JavaScript e jQuery
- **Função de busca com debounce**: Limita a frequência de execuções da função de busca, melhorando a performance.
- **Requisição AJAX**: Realiza a busca em tempo real enviando os dados de entrada para o script `busca_cidadao_ajax.php` e exibe os resultados dinamicamente.
- **Reset de Filtros**: Botão para limpar a busca e redefinir o estado da página.

### Estrutura do JavaScript
```javascript
$(document).ready(function() {
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    $('#search').on('input', debounce(function() {
        let searchQuery = $(this).val();
        $('#results-container').empty();
        $('#results-count').addClass('d-none');
        $('#loading').removeClass('d-none');

        $.ajax({
            url: 'busca_cidadao_ajax.php',
            method: 'GET',
            data: { search: searchQuery },
            success: function(response) {
                $('#results-container').html(response);
                $('#loading').addClass('d-none');

                let resultCount = $('#results-container .result-card').length;
                $('#results-count p').text(resultCount > 0 ? `${resultCount} cidadão(ãos) encontrado(s)` : 'Nenhum resultado encontrado.');
                $('#results-count').removeClass('d-none');
            },
            error: function() {
                $('#loading').addClass('d-none');
                $('#results-container').html('<div class="alert alert-danger">Erro ao buscar dados. Tente novamente.</div>');
            }
        });
    }, 500));

    $('#reset-filters').click(function() {
        $('#search').val('');
        $('#results-container').empty();
        $('#results-count').addClass('d-none');
    });
});
