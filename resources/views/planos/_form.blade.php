{{-- Partial compartilhado por create/edit de planos --}}
<div class="grid-2">
    <div class="form-group">
        <label class="form-label" for="nome">Nome do Plano *</label>
        <input id="nome" type="text" name="nome" class="form-control" value="{{ old('nome', $plano->nome ?? '') }}" required placeholder="Ex: Básico, Pro, Enterprise">
        @error('nome') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="preco_mensal">Preço Mensal (R$) *</label>
        <input id="preco_mensal" type="number" step="0.01" name="preco_mensal" class="form-control" value="{{ old('preco_mensal', $plano->preco_mensal ?? '') }}" required placeholder="0.00">
        @error('preco_mensal') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="preco_anual">Preço Anual (R$)</label>
        <input id="preco_anual" type="number" step="0.01" name="preco_anual" class="form-control" value="{{ old('preco_anual', $plano->preco_anual ?? '') }}" placeholder="0.00 (opcional)">
        @error('preco_anual') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>
<div class="form-group">
    <label class="form-label" for="descricao">Descrição</label>
    <textarea id="descricao" name="descricao" class="form-control" rows="2" placeholder="Breve descrição do plano...">{{ old('descricao', $plano->descricao ?? '') }}</textarea>
    @error('descricao') <div class="form-error">{{ $message }}</div> @enderror
</div>
<div class="form-group">
    <label class="form-label" for="recursos">Recursos inclusos (um por linha)</label>
    <textarea id="recursos" name="recursos" class="form-control" rows="5" placeholder="SSL grátis&#10;Backup semanal&#10;Suporte por e-mail&#10;1 domínio">{{ old('recursos', isset($plano->recursos) ? implode("\n", $plano->recursos) : '') }}</textarea>
    @error('recursos') <div class="form-error">{{ $message }}</div> @enderror
</div>
