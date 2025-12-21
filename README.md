# CodePitch

Plataforma web para divulgação de projetos de desenvolvimento de software, conexão entre desenvolvedores e apresentação de portfólios técnicos.

> Este repositório é voltado à **apresentação do sistema** (TCC / portfólio acadêmico).  
> O código-fonte da aplicação não é disponibilizado publicamente.

---

## 📌 Visão Geral

O **CodePitch** foi desenvolvido com o objetivo de criar um ambiente centralizado onde desenvolvedores possam:

- Publicar seus projetos de software de forma organizada
- Apresentar imagens das soluções desenvolvidas
- Interagir com outros usuários por meio de comentários
- Divulgar contatos e redes profissionais
- Consolidar um portfólio técnico em um único local

A aplicação simula o funcionamento de uma mini plataforma de comunidade técnica, com autenticação, perfis, posts e área administrativa.

---

## 🧭 Objetivos do Projeto

### Objetivo Geral

Desenvolver uma plataforma web que facilite a exposição de projetos de desenvolvimento de software e promova a interação entre desenvolvedores.

### Objetivos Específicos

- Permitir o **cadastro e autenticação** de usuários
- Disponibilizar uma interface para **publicação, edição e exclusão de projetos**
- Exibir **detalhes de cada projeto**, com imagens e descrições
- Criar um **perfil público de usuário**, com bio e redes sociais
- Implementar uma **camada administrativa** para moderação de conteúdo
- Garantir **usabilidade** em dispositivos desktop e mobile

---

## 🎯 Problema que o Sistema Resolve

Muitos desenvolvedores iniciantes ou em formação têm dificuldade em:

- Organizar seus projetos em um formato atrativo para recrutadores
- Demonstrar experiência prática além do currículo tradicional
- Criar um portfólio centralizado com contatos profissionais
- Receber feedback sobre seus projetos

O **CodePitch** surge como uma solução para:

- Centralizar projetos em um ambiente único
- Facilitar a apresentação visual das soluções (prints/imagens)
- Criar um perfil profissional simples, mas objetivo
- Estimular a troca de conhecimento via comentários

---

## 👥 Público‑Alvo

- Estudantes de cursos técnicos e superiores em TI
- Desenvolvedores iniciantes em busca do primeiro emprego
- Profissionais que desejam organizar um portfólio de projetos pessoais
- Comunidades de estudo e grupos de desenvolvimento

---

## 🧩 Funcionalidades Principais

### 1. Autenticação de Usuário

- Cadastro com:
  - Nome
  - Email
  - Senha
- Login com email e senha
- Logout seguro
- Tratamento de estados:
  - Conta ativa/inativa
  - Permissão de admin

### 2. Publicação de Projetos

Cada projeto contém:

- **Título** do projeto
- **Localização** (cidade/estado) – útil para contexto profissional
- **Descrição detalhada** do que foi desenvolvido
- **Tags** (ex.: _React, Node.js, API REST_)
- **Galeria de imagens** (até 5 imagens por projeto)
  - Imagens podem ser enviadas via upload
  - Também é possível informar uma URL direta de imagem

Recursos adicionais:

- Listagem de projetos na página inicial (destaques)
- Página “Explorar Projetos” com todos os posts
- Página de detalhes com foco em um projeto específico

### 3. Comentários em Projetos

- Usuários autenticados podem comentar em qualquer projeto
- Exibição de:
  - Nome do autor do comentário (com link para o perfil)
  - Data/hora
  - Conteúdo do comentário
- Permissões:
  - Autor do comentário pode excluir o próprio comentário
  - Admin pode excluir qualquer comentário

### 4. Perfil de Usuário

Cada usuário possui uma página de perfil com:

- Nome e email
- Bio (breve resumo pessoal/profissional)
- Redes sociais (URLs configuráveis):
  - GitHub
  - LinkedIn
  - Twitter/X
  - Website / Portfólio pessoal
- Lista de projetos publicados pelo usuário

O próprio usuário (e o admin) pode editar:

- Nome
- Bio
- Links de redes sociais

### 5. Área Administrativa (Conta Admin)

Usuários marcados como **admin** possuem:

- Acesso para:
  - Editar e excluir **qualquer** projeto
  - Excluir **qualquer** comentário
  - Editar o perfil de qualquer usuário (por exemplo, ajustar bio ou links)
- Visão ampliada de moderação:
  - Útil para simular o papel de um moderador/gestor da comunidade

> A distinção entre usuário comum e admin é tratada por uma flag específica no cadastro (`is_admin`), permitindo cenários de autorização mais complexos.

### 6. Interface e Experiência de Uso

- Tema **escuro** (dark) com foco em legibilidade
- Navbar fixa com acesso rápido a:
  - Home
  - Explorar projetos
  - Login / Criar Post / Meu Perfil
- Modais para:
  - Login / Cadastro (com transição animada entre telas)
  - Criação de projeto
- **Inputs com labels flutuantes animados**:
  - Nome do campo “sobe” ao focar/digitar
  - Facilita entendimento dos campos e mantém visual limpo
- Carrossel informativo na página inicial
- Layout responsivo para diferentes tamanhos de tela

---

## 🧱 Arquitetura (Visão de Alto Nível)

- **Camada de Apresentação (Frontend):**
  - Páginas PHP com HTML5
  - CSS personalizadas para tema dark e animações
  - JavaScript para:
    - Carrossel
    - Abertura/fechamento de modais
    - Dropdown do usuário
    - Labels flutuantes
    - Feedback de upload de imagens

- **Camada de Lógica de Negócio (Backend):**
  - PHP procedimental/estruturado
  - Organização por responsabilidades:
    - `create_project.php`, `edit_project.php`, `delete_project.php`
    - `add_comment.php`, `delete_comment.php`
    - `login.php`, `register.php`, `logout.php`
    - `profile.php`, `edit_profile.php`
  - Controle de sessão e autenticação via `auth.php`

- **Camada de Dados:**
  - Banco de dados relacional MySQL
  - Tabelas principais:
    - `users` (usuários e perfis)
    - `projects` (projetos)
    - `comments` (comentários)
  - Campos de status para soft delete / ativação

---

## ✅ Vantagens de Utilizar o CodePitch

### Para o Usuário (Dev/Estudante)

- **Portfólio organizado**: todos os projetos em um lugar só
- **Apresentação profissional**: bio + redes sociais integradas
- **Fácil compartilhamento**: basta enviar o link do perfil para recrutadores
- **Feedback da comunidade**: comentários em cada projeto
- **Controle total** sobre seus posts:
  - Criar, editar, remover
  - Atualizar informações a qualquer momento

### Para quem avalia (professores, recrutadores, banca de TCC)

- Visualização rápida dos projetos de um aluno/candidato
- Entendimento do contexto de cada solução:
  - Descrição
  - Tecnologias (tags)
  - Evidências visuais (imagens)
- Validação de boas práticas:
  - Organização do portfólio
  - Clareza na documentação dos projetos
  - Uso de autenticação, controle de acesso, CRUDs etc.

### Para o contexto acadêmico (TCC / Trabalho Final)

- Demonstra:
  - Implementação de autenticação e autorização
  - Modelagem de dados relacional (users, projects, comments)
  - Upload e gerenciamento de arquivos
  - Separação de responsabilidades (várias páginas de backend)
  - Integração entre frontend e backend
- Pode ser facilmente estendido para:
  - Métricas de uso
  - Filtros avançados
  - Dashboard administrativo

---

## 🔄 Possíveis Evoluções

- Sistema de likes/favoritos em projetos
- Filtros avançados por tecnologia, nível de complexidade, área (web, mobile, dados)
- Mensagens privadas entre usuários
- Upload de avatar personalizado para o perfil
- Paginação e ordenação em “Explorar Projetos”
- Dashboard para admin com gráficos (nº de usuários, posts, comentários)

---

## 🧾 Observação sobre o Código-Fonte

> Este repositório tem foco na **apresentação conceitual e funcional** do sistema CodePitch,  
> sendo destinado a fins acadêmicos e de portfólio.  
> O código-fonte completo da aplicação **não é disponibilizado publicamente**.

Caso haja interesse em demonstrar partes específicas da implementação (para banca ou avaliação técnica), recomenda-se:

- Apresentar **trechos selecionados** em slides (ex.: regras de negócio, modelos de dados)
- Discutir as decisões de projeto (segurança, validação, estrutura de tabelas)
- Focar na arquitetura e nas funcionalidades que o sistema entrega

---

## 📌 Resumo Final

O **CodePitch** é um sistema web que integra:

- Cadastro e autenticação de usuários
- Publicação e gerenciamento de projetos
- Comentários e interação social
- Perfis personalizados com redes profissionais
- Moderação via conta admin

Sendo assim, atende plenamente ao objetivo de servir como **plataforma de portfólio e networking técnico**, e ao mesmo tempo como **projeto completo para apresentação em TCC**, contemplando:

- Backend
- Frontend
- Banco de dados
- UX
- Segurança básica
- Regras de negócio e controle de acesso.