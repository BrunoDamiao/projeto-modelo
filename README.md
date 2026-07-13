# BaseFWK-PHP

Um framework PHP leve, simples e prático desenvolvido para acelerar o desenvolvimento de aplicações web.

---

## Sobre o Projeto

**BaseFWK-PHP** ou **fwBD** é um framework MVC minimalista em PHP criado por **Bruno Damião**. Seu objetivo é fornecer uma base sólida, organizada e fácil de usar para projetos PHP, sem a complexidade de frameworks maiores como Laravel.

Ideal para:
- Sistemas administrativos (CMS)
- Aplicações web médias
- Projetos que precisam de estrutura rápida e controle total

## Recursos

- **Router** simples e poderoso com suporte a grupos e middlewares
- **MVC** completo (Model, View, Controller)
- Injeção de Dependências (DI)
- Sistema de Autenticação pronto
- CRUDs administrativos de exemplo (Usuários, Categorias, Níveis)
- Validação, Filtros, Sessão, Upload de Imagens
- Envio de e-mails (PHPMailer)
- Conexão com banco de dados
- Sistema de Templates/Views
- Estrutura organizada e escalável

## Estrutura de Pastas

```bash
appName/
├── app/                  # Controllers, Models, Views, Filters, Routers
├── bootstrap/            # Inicialização da aplicação
├── config/               # Configurações (Banco, Sessão, Mail, etc)
├── public_html/          # Entrada pública (index.php)
├── src/fwBD/             # Núcleo do framework
│   ├── Router/
│   ├── Controller/
│   ├── Model/
│   ├── View/
│   ├── DBConect/
│   ├── DI/
│   ├── Validate/
│   ├── Filter/
│   └── ... (mais componentes)
├── storage/              # Arquivos gerados, logs, uploads
└── vendor/               # Dependências via Composer
```

## Requisitos

- PHP 7.4 ou superior
- Extensão PDO (para banco de dados)
- Composer

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/BrunoDamiao/BaseFWK-PHP.git
```

2. Entre na pasta do projeto:
```bash
cd BaseFWK-PHP/appName
```

3. Instale as dependências:
```bash
composer install
```

4. Configure o ambiente:
   - Edite os arquivos em `config/` (banco de dados, chaves, etc)
   - Crie o banco de dados e importe as tabelas (se houver)

5. Configure seu servidor web para apontar a raiz para a pasta `public_html`

## Como Usar

Após a instalação, acesse `http://seusite.com` e o framework já carregará a rota inicial.

### Criando uma nova rota

```php
// app/Routers.php
Router::get('minha-rota', 'MeuController@index');
Router::post('salvar', 'MeuController@store');
```

### Exemplo de Controller

```php
namespace App\Controllers;

use FwBD\Controller\Controller;

class MeuController extends Controller
{
    public function index()
    {
        $this->view('home', ['title' => 'Minha Página']);
    }
}
```

## Tecnologias Utilizadas

- PHP
- Composer
- PHPMailer
- HTML5, CSS3, JavaScript

## Autor

**Bruno Damião**  
Desenvolvedor Web  
[GitHub](https://github.com/BrunoDamiao) | [E-mail](mailto:dev@brunodamiao.com.br)

## Licença

Este projeto está sob a licença [MIT](LICENSE).

---

**Feito com ❤️ para facilitar minha vida com os meus projetos desenvolvidos em PHP.**
