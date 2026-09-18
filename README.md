# SAAS - Controle de Frotas

## Sumário

- [Sobre o projeto](#sobre-o-projeto)
- [Arquitetura](#arquitetura)
- [Funcionalidades implementadas](#funcionalidades-implementadas)
- [Padrão de resposta da API](#padrão-de-resposta-da-api)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Documentação da API](#documentação-da-api)
- [Testes](#testes)
- [Deploy (produção)](#deploy-produção)
- [Licença](#licença)
- [Contribuidores](#contribuidores)

## Sobre o projeto

Sistema de gestão e monitoramento eletrônico de frota (veículos, máquinas e equipamentos), com backend em **Laravel** e frontend separado em **Vite**. O backend expõe uma API consumida pelo frontend, com gerenciamento de perfis, secretarias, usuários e permissões (RBAC).

O sistema contempla, entre outras funcionalidades previstas para o domínio de frotas:

- Cadastro e gerenciamento de veículos, máquinas, equipamentos, implementos e motoristas
- Controle de abastecimentos, consumo e estoque de combustíveis
- Compra de autopeças e controle de aquisição/aplicação de peças e materiais de manutenção
- Manutenções preventivas e corretivas, com abertura, acompanhamento e encerramento de ordens de serviço
- Gestão de oficinas, borracharias e fornecedores de serviços (lava-jatos, autopeças, etc.)
- Controle de lavagens
- Controle de pneus, baterias, lubrificantes e demais componentes (vida útil, histórico, substituição)
- Cadastro e gerenciamento de secretarias, unidades administrativas e centros de custo
- Organização da frota e utilização compartilhada de veículos entre secretarias (requisição, cessão, transferência)
- Controle de diárias de motoristas (solicitação, autorização, registro e prestação de contas)
- Controle de solicitações, reservas e autorizações de saída/retorno de veículos
- Emissão de autorizações e identificação de veículos por **QR code** (abastecimento, lavagem, etc.)
- Controle de multas de trânsito e indicação de condutores
- Controle de custos operacionais, quilometragem, horímetro e médias de consumo por veículo/secretaria/período
- Registro de avarias e ocorrências pelos motoristas via aplicativo móvel, com notificação automática aos gestores
- Emissão de alerta automáticos (vencimento de CNH, licenciamento, documentação, seguros, manutenções, revisões, pneus)
- Aplicativo móvel para gestores, motoristas e fornecedores
- Painel gerencial com indicadores de custo, consumo, disponibilidade e desempenho da frota (dashboards)
- Emissão de relatórios gerenciais (PDF, Excel, CSV) com assinatura digital
- Integração/exportação de dados compatível com o Sistema de Informações Municipais (SIM) do TCE-CE
- Registro de logs de auditoria e histórico completo das operações

## Arquitetura

- **Backend:** Laravel (via Sail em desenvolvimento), servindo APIs REST
- **Frontend:** repositório separado — [controle-de-frotas-fe](https://github.com/jlsoftware-tech/controle-de-frotas-fe), rodando com Vite/Docker (porta `5173`)
- **Banco de dados:** PostgreSQL
- **Cache/Filas:** Redis
- **E-mail (dev):** Mailpit
- **Produção:** Nginx + PHP-FPM, com queue worker e scheduler (`schedule:work`) rodando junto

## Funcionalidades implementadas

Com base no estado atual do repositório da API ([controle-de-frotas](https://github.com/jlsoftware-tech/controle-de-frotas)), o projeto encontra-se na camada de fundação (autenticação, usuários e controle de acesso), ainda sem os módulos específicos de frota (veículos, abastecimentos, manutenções, etc. — ver seção "Sobre o projeto" para o escopo previsto):

- **Autenticação via JWT** (`AuthController`, `JwtMiddleware`), incluindo fluxo de "esqueci minha senha" e redefinição de senha (`ResetPasswordApiNotification`)
- **Gerenciamento de usuários** (`UserController`), com requests dedicados de listagem, criação e atualização
- **Gerenciamento de perfis de acesso** (`ProfileController`)
- **Gerenciamento de permissões** (`PermissionController`), com relação many-to-many entre Perfil e Permissão via tabela pivô `profile_permission`
- **Gerenciamento de secretarias** (`SecretariatController`)
- **Autorização por Policies** (`UserPolicy`, `ProfilePolicy`, `SecretariatPolicy`)
- **Respostas e paginação padronizadas** via `ApiResponder` (classe com métodos estáticos `ApiResponder::success()` e `ApiResponder::error()`) e `PaginatedCollection`/`UserCollection`/`UserResource`/`ProfileResource`
- Seeders para perfis e secretarias

## Padrão de resposta da API

Todas as respostas (incluindo erros de validação) seguem um formato padronizado, gerado pela classe `ApiResponder`, com métodos estáticos `ApiResponder::success()` e `ApiResponder::error()` reutilizados nos controllers/handlers:

```json
{
  "success": true,
  "status_code": 200,
  "message": "string",
  "data": {}
}
```

## Requisitos

- Docker e Docker Compose
- Laravel Sail (backend)
- Node.js (frontend)

## Instalação

### Backend

```bash
# clonar o repositório
git clone https://github.com/jlsoftware-tech/controle-de-frotas.git
cd controle-de-frotas

# subir os containers via Sail
./vendor/bin/sail up -d

# instalar dependências (se necessário)
./vendor/bin/sail composer install

# rodar migrations e seeders
./vendor/bin/sail artisan migrate:fresh --seed
```

### Frontend

```bash
git clone https://github.com/jlsoftware-tech/controle-de-frotas-fe.git
cd controle-de-frotas-fe
docker compose up -d
# aplicação disponível em http://localhost:5173
```

## Documentação da API

A documentação da API é gerada via **Scribe** (pacote `knuckleswtf/scribe` para Laravel).

> 📌 Após subir o backend, gere/atualize a documentação com `./vendor/bin/sail artisan scribe:generate`. Por padrão, ela fica disponível em `/docs`.

## Testes

Os testes são escritos com **Pest**, organizados por funcionalidade/endpoint:

```bash
./vendor/bin/sail artisan test
```

Exemplo de estrutura:
```
tests/Feature/Auth/LoginTest.php
```

## Deploy (produção)

- Dockerfile próprio para produção (Nginx + PHP-FPM)
- Queue worker e scheduler rodando como processos junto ao container

## Licença

Este é um software proprietário. Todos os direitos reservados — uso, cópia, modificação e distribuição não autorizados são proibidos.

## Contribuidores

**Back-end**
- Rener Pontes
- Gabriel Santos
- Yago Elias

**Front-end**
- Débora Veras
