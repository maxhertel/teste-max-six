# Desafio Técnico – Desenvolvedor Backend Pleno – Grupo Six

## Contexto
O Grupo Six atua com diversos produtos físicos, utilizando o gateway de pagamento internacional Cartpanda para processar pagamentos. Nosso time de desenvolvimento é responsável por manter e evoluir o CRM interno, desenvolvido em PHP (Laravel), que integra dados de múltiplas fontes.

Como desenvolvedor(a) backend, seu papel será criar, tratar, organizar e disponibilizar dados de APIs externas de forma estruturada e eficiente para consumo interno, que será utilizado por diferentes áreas da empresa (Financeiro, Customer Service, Logística, Tráfego etc.).

# Guia de Métricas para Dashboard - Teste Prático Laravel

## Sobre Este Documento
Este guia apresenta as métricas sugeridas para o dashboard do teste prático, organizadas por nível de complexidade. Use como referência para decidir o que implementar e priorizar seu tempo.

**Objetivo:** Criar um dashboard funcional que consuma dados da API e apresente análises relevantes sobre pedidos, produtos e clientes.

## Como Usar
- Comece pelo básico: implemente todas as métricas básicas (obrigatório)
- Avance gradualmente: adicione métricas intermediárias conforme o tempo
- Destaque-se: métricas avançadas são diferenciais competitivos
- Priorize qualidade: melhor poucas métricas bem feitas do que muitas mal feitas

## Endpoint da API
https://dev-crm.ogruposix.com/candidato-teste-pratico-backend-dashboard/test-orders


---

# Métricas Básicas (Obrigatório)

## 1. Total de Pedidos
**Complexidade:** Básico  
Motivo: Métrica fundamental que mostra o volume de operações.  
Mostrar: Número total de pedidos  
Campos: `id` (contagem)  
Dica: Use `count()` na collection de pedidos.  
Visualização: Card numérico grande.

## 2. Receita Total
Complexidade: Básico  
Motivo: Indica volume financeiro do negócio.  
Mostrar:
- Valor total em USD e BRL formatado
- Comparação com período anterior (opcional)
Campos: `local_currency_amount`  
Dica: Somar todos os valores, removendo vírgulas antes de somar.  
Visualização: Card com destaque em verde.

## 3. Pedidos Entregues
Complexidade: Básico  
Motivo: Mede eficiência operacional de entrega.  
Mostrar:
- Quantidade de pedidos entregues  
- Taxa percentual de entrega  
Campos: `fulfillment_status` (filtrar por "Fully Fulfilled")  
Visualização: Card com indicador percentual.

## 4. Clientes Únicos
Complexidade: Básico  
Motivo: Diferencia clientes de pedidos.  
Mostrar:
- Número de clientes únicos  
- Média de pedidos por cliente (opcional)  
Campos: `customer.id` (contar únicos)  
Dica: Use `unique()` no `customer_id`.  
Visualização: Card simples.

## 5. Resumo Financeiro (Faturamento x Reembolso x Receita Líquida)
Complexidade: Básico  
Motivo: Mostra saúde financeira real do negócio.  
Mostrar:
- Faturamento bruto  
- Total de reembolsos  
- Receita líquida (faturamento - reembolsos)  
Campos:
- `local_currency_amount` (faturamento)
- `refunds[].total_amount` (reembolsos)  
Visualização: Três cards lado a lado.

## 6. Taxa de Reembolso
Complexidade: Básico  
Motivo: KPI crítico de qualidade e satisfação.  
Mostrar:
- Percentual de pedidos reembolsados  
- Indicador visual (verde, amarelo, vermelho)  
Campos:
- `refunds[]`
- `line_items[].is_refunded`  
Visualização: Card com indicador de cor.

## 7. Produto Mais Vendido
Complexidade: Básico  
Motivo: Identifica o produto estrela da empresa.  
Mostrar:
- Nome do produto  
- Quantidade vendida  
- Receita gerada  
Campos:
- `line_items[].name`
- `line_items[].quantity`
- `line_items[].local_currency_item_total_price`  
Visualização: Card destacado.

## 8. Tabela de Pedidos
Complexidade: Básico  
Motivo: Interface principal para visualizar pedidos individuais.  
Mostrar:
- ID do pedido  
- Nome e email do cliente  
- Status de pagamento e entrega  
- Valor e data  
Campos:  
`id, customer.first_name, customer.last_name, contact_email, financial_status, fulfillment_status, local_currency_amount, created_at`  
Visualização: Tabela responsiva com paginação e busca.

---

# Métricas Intermediárias (Desejável)

9. Top 5 Produtos por Receita  
10. Análise de Upsell  
11. Faturamento por Variante  
12. Top 10 Cidades em Vendas  
13. Pedidos Entregues Depois Reembolsados  
14. Ticket Médio  
15. Taxa de Conversão por Forma de Pagamento  

---

# Métricas Avançadas (Diferencial)

16. Análise Temporal de Vendas  
17. Produtos com Alta Taxa de Reembolso  
18. Análise de Motivos de Reembolso  
19. Mapa de Reembolsos por Estado  
20. Distribuição Geográfica de Vendas  

---

# Guia de Priorização

Para 1-2 dias de trabalho:
1. Todas as 8 métricas básicas  
2. 3 a 4 métricas intermediárias  
3. 1 a 2 métricas avançadas  

Sugestões:
- Intermediárias: Top 5 Produtos, Análise de Upsell, Top 10 Cidades  
- Avançadas: Vendas ao Longo do Tempo, Top 10 Clientes VIP  

---

# Tecnologias Sugeridas

## Backend (Laravel):
- Laravel 10+

## Frontend:
- Blade  
- Chart.js  
- Tailwind CSS ou Bootstrap  

---

# Checklist de Implementação

## Setup:
- Criar projeto Laravel  
- Testar conexão com API  
- Configurar cache  

## Básico (Obrigatório):
- Total de pedidos  
- Receita total  
- Pedidos entregues  
- Clientes únicos  
- Resumo financeiro  
- Taxa de reembolso  
- Produto mais vendido  
- Tabela de pedidos  

## Intermediário (Escolha 3-4):
- Top 5 produtos  
- Análise de upsell  
- Faturamento por variante  
- Top 10 cidades  
- Pedidos Entregues x Reembolsados  
- Ticket médio  
- Conversão por forma de pagamento  

## Avançado (Escolha 1-2):
- Vendas ao longo do tempo  
- Produtos com alta taxa de reembolso  
- Motivo Reembolso  
- Mapa de Reembolso  
- Distribuição Geográfica de Vendas  

---

# UI/UX:
- Design responsivo  
- Cards estilizados  
- Gráficos implementados  
- Busca e filtros  

---

# Critérios de Avaliação

## Funcionalidade (40%)
- Todas as métricas básicas funcionando  
- Precisão dos cálculos  

## Qualidade do Código (25%)
- Organização e clareza  
- Boas práticas Laravel  

## UI/UX (10%)
- Design profissional e responsivo  

## Performance (20%)
- Cache implementado  
- Tempo de carregamento  

## Documentação (5%)
- README claro  
- Instruções de instalação  

---

# Dicas Finais
1. Comece simples e evolua depois.  
2. Use cache para otimizar desempenho.  
3. Priorize qualidade sobre quantidade.  
4. Capriche na aparência do dashboard.  
5. Teste tudo antes de entregar.  
6. Faça commits organizados.  
7. Entregue dentro do prazo.
