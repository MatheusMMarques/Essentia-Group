# Essentia_StorePickup

Módulo para Magento Open Source que adiciona a opção **Retirado na Loja** aos métodos de entrega do checkout.

A retirada não possui custo e não depende de serviços externos, CEP, endereço, peso ou cálculo de frete.

## Compatibilidade

- Magento Open Source 2.4.8-p5
- PHP 8.3
- Módulo: `Essentia_StorePickup`
- Carrier: `storepickup`
- Método: `storepickup`
- Código persistido no pedido: `storepickup_storepickup`

## Estrutura

```text
Essentia/StorePickup/
├── Model/
│   └── Carrier/
│       └── StorePickup.php
├── etc/
│   ├── adminhtml/
│   │   └── system.xml
│   ├── config.xml
│   └── module.xml
├── view/
│   └── adminhtml/
│       └── ui_component/
│           └── sales_order_grid.xml
├── composer.json
├── registration.php
└── README.md
```

## Instalação

Copie o diretório `Essentia/StorePickup` para:

```text
app/code/Essentia/StorePickup
```

Na raiz da instalação Magento, execute:

```bash
php bin/magento module:enable Essentia_StorePickup
php bin/magento setup:upgrade
php bin/magento cache:flush
```

Para confirmar que o módulo foi habilitado:

```bash
php bin/magento module:status Essentia_StorePickup
```

Em ambientes de produção, execute também os procedimentos de compilação e deploy de conteúdo estático adotados pela loja, quando necessários.

## Configuração

No Admin do Magento, acesse:

**Stores > Configuration > Sales > Delivery Methods > Retirado na Loja**

Configure:

1. **Habilitado:** Sim
2. **Título:** nome apresentado como carrier
3. **Nome do método:** nome apresentado para a opção de retirada
4. **Ordem:** posição do método entre as opções disponíveis

Por padrão, o módulo utiliza:

- Título: **Retirado na Loja**
- Nome do método: **Retirada na loja**
- Preço: **0**
- Custo: **0**
- Status inicial: **desabilitado**

As configurações podem ser definidas nos escopos Default, Website e Store View.

## Funcionamento

Quando habilitado, o módulo registra um carrier no mecanismo nativo de entrega do Magento.

O método disponibilizado é:

```text
storepickup_storepickup
```

A opção aparece no checkout para pedidos que exigem entrega física e sempre retorna preço e custo iguais a zero.

Nenhuma API externa ou serviço de cotação é utilizado.

Ao finalizar o checkout, o próprio Magento persiste o método selecionado no pedido e mantém essas informações disponíveis nas telas administrativas.

## Pedidos no Admin

O Magento armazena nativamente:

```text
shipping_method
shipping_description
```

Para pedidos feitos com retirada na loja, o `shipping_method` recebe:

```text
storepickup_storepickup
```

A descrição do método é exibida normalmente na visualização do pedido, sem necessidade de criar campos adicionais.

No grid **Sales > Orders**, o módulo utiliza a coluna nativa `shipping_information`, exibindo-a com o nome **Método de entrega**.

A coluna possui filtro textual, permitindo localizar os pedidos pesquisando, por exemplo:

```text
Retirado na Loja
```

Outros métodos de entrega continuam sendo apresentados normalmente na mesma coluna.

## Validação

Para validar o módulo:

1. Adicione ao carrinho um produto físico.
2. Avance até a etapa de entrega do checkout.
3. Confirme que **Retirado na Loja** aparece entre os métodos disponíveis.
4. Confirme que o valor do frete é zero.
5. Selecione o método e finalize o pedido.
6. Acesse **Sales > Orders** no Admin.
7. Confirme o método na coluna **Método de entrega**.
8. Utilize o filtro da coluna para pesquisar por `Retirado na Loja`.
9. Abra o pedido e confirme o método nas informações de envio.

Caso a loja utilize atualização assíncrona do grid de pedidos, pode ser necessário aguardar o processamento correspondente antes que o pedido apareça no grid.

## Validação realizada

O módulo foi testado em uma instalação limpa do **Magento Open Source 2.4.8-p5 com PHP 8.3**.

Foram validados:

- instalação e habilitação do módulo;
- compilação de Dependency Injection;
- configuração do carrier pelo Magento;
- exibição do método no checkout;
- frete com valor zero;
- seleção do método e finalização do pedido;
- persistência de `storepickup_storepickup` em `shipping_method`;
- exibição do método na visualização do pedido no Admin;
- exibição da coluna **Método de entrega** no grid de pedidos;
- filtro textual por `Retirado na Loja`;
- coexistência com outro método de entrega;
- comportamento de pedidos com produtos virtuais.

Nenhuma alteração no core do Magento foi necessária.

## Decisões técnicas

### Carrier nativo

O método foi implementado utilizando a estrutura nativa de carriers do Magento.

`StorePickup` estende `AbstractCarrier` e implementa `CarrierInterface`.

O método `collectRates()` retorna uma única opção de entrega com preço e custo iguais a zero.

Como a retirada acontece na própria loja, o cálculo não depende de destino, CEP, peso ou conteúdo do carrinho.

### Persistência do método

Não foi criada persistência adicional para armazenar a retirada.

O Magento já mantém o método selecionado em `shipping_method` e sua descrição em `shipping_description`. Esses mesmos dados são utilizados pela visualização administrativa do pedido.

Dessa forma, o módulo utiliza o fluxo padrão da plataforma em vez de duplicar informações.

### Grid de pedidos

O grid nativo de pedidos já possui o campo `shipping_information`, preenchido a partir das informações de entrega do pedido.

O módulo reutiliza esse campo para:

- exibir a coluna **Método de entrega**;
- permitir filtro textual;
- manter compatibilidade com outros carriers.

Não foi necessário criar tabelas, colunas, joins, plugins ou observers para essa funcionalidade.

### Escopo

Configurações como restrição por país, taxa de manuseio ou cálculo dinâmico de preço não foram adicionadas porque não fazem parte do comportamento esperado para uma retirada gratuita na loja.

## Desabilitação

O módulo não cria tabelas ou colunas próprias.

Para desabilitá-lo:

```bash
php bin/magento module:disable Essentia_StorePickup
php bin/magento cache:flush
```

Após ser desabilitado, o método deixa de ser oferecido em novos checkouts.

Pedidos já realizados continuam mantendo normalmente o método e a descrição de entrega armazenados pelo Magento.