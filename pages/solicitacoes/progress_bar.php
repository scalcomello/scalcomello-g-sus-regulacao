<div class="progress-steps-container mb-4">
    <!-- Barra de Progresso Customizada com Bolinhas e Ícones -->
    <div class="progress-steps d-flex align-items-center">
        <!-- Passo 1: Buscar Cidadão -->
        <div class="step <?php echo isset($step1_active) && $step1_active ? 'active' : ''; ?>">
            <div class="circle"><i class="fas fa-search"></i></div>
            <div class="label">Buscar Cidadão</div>
        </div>

        <!-- Linha 1 entre os passos 1 e 2 -->
        <div class="step-line line-1 <?php echo isset($step2_active) && $step2_active ? 'active-line' : ''; ?>"></div>

        <!-- Passo 2: Informar Detalhes -->
        <div class="step <?php echo isset($step2_active) && $step2_active ? 'active' : ''; ?>">
            <div class="circle"><i class="fas fa-info-circle"></i></div>
            <div class="label">Informar Detalhes</div>
        </div>

        <!-- Linha 2 entre os passos 2 e 3 -->
        <div class="step-line line-2 <?php echo isset($step3_active) && $step3_active ? 'active-line' : ''; ?>"></div>

        <!-- Passo 3: Finalizar -->
        <div class="step <?php echo isset($step3_active) && $step3_active ? 'active' : ''; ?>">
            <div class="circle"><i class="fas fa-check-circle"></i></div>
            <div class="label">Finalizar</div>
        </div>
    </div>
</div>


<style>
    /* Variáveis de cores */
    :root {
        --progress-bg: #e0e0e0;
        --progress-active: #007bff; /* Azul padrão */
        --progress-shadow: rgba(0, 123, 255, 0.6);
        --circle-size: 50px;
    }

    /* Barra de Progresso */
    .progress-steps-container {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .progress-steps {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        position: relative;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
    }

    /* Estilo da bolinha (círculo) */
    .circle {
        width: var(--circle-size);
        height: var(--circle-size);
        background-color: var(--progress-bg);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #fff;
        transition: background-color 0.3s, transform 0.3s;
        z-index: 1; /* Garante que o círculo esteja acima da linha */
    }

    /* Estado ativo do passo */
    .step.active .circle {
        background-color: var(--progress-active);
        transform: scale(1.1);
        box-shadow: 0 0 10px var(--progress-shadow);
    }

    .label {
        margin-top: 10px;
        font-size: 16px;
    }

    /* Linhas entre os passos */
    .step-line {
        position: absolute;
        top: 35%; /* Centraliza a linha verticalmente em relação aos círculos */
        height: 4px;
        background-color: var(--progress-bg);
        z-index: 0; /* Coloca atrás dos círculos */
        transition: background-color 0.3s;
    }

    /* Linha ativa (Azul) */
    .step-line.active-line {
        background-color: var(--progress-active);
    }

    /* Linha 1 (Entre Passo 1 e Passo 2) */
    .line-1 {
        left: calc(var(--circle-size) + 10px); /* Posiciona à direita do círculo 1 */
        width: calc(50% - var(--circle-size)); /* Largura da linha */
    }

    /* Linha 2 (Entre Passo 2 e Passo 3) */
    .line-2 {
        left: calc(56% - var(--circle-size)); /* Posiciona à direita do círculo 2 */
        width: calc(50% - var(--circle-size)); /* Largura da linha */
    }

</style>
