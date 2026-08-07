{{-- Partial form de pagamentos --}}
<div class="grid-2">
    <div class="form-group">
        <label class="form-label" for="cliente_id">Cliente *</label>
        <select id="cliente_id" name="cliente_id" class="form-control" required>
            <option value="">Selecione...</option>
            @foreach($clientes as $c)
                <option value="{{ $c->id }}" {{ old('cliente_id', $pagamento->cliente_id ?? '') == $c->id ? 'selected':'' }}>{{ $c->nome }}</option>
            @endforeach
        </select>
        @error('cliente_id') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="site_id">Site (opcional)</label>
        <select id="site_id" name="site_id" class="form-control">
            <option value="">Sem site vinculado</option>
            @foreach($sites as $s)
                <option value="{{ $s->id }}" {{ old('site_id', $pagamento->site_id ?? '') == $s->id ? 'selected':'' }}>{{ $s->nome_site }}</option>
            @endforeach
        </select>
        @error('site_id') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="tipo">Tipo *</label>
        <select id="tipo" name="tipo" class="form-control" required>
            <option value="site"        {{ old('tipo', $pagamento->tipo ?? '') === 'site'        ? 'selected':'' }}>Site</option>
            <option value="mensalidade" {{ old('tipo', $pagamento->tipo ?? '') === 'mensalidade' ? 'selected':'' }}>Mensalidade</option>
            <option value="anuidade"    {{ old('tipo', $pagamento->tipo ?? '') === 'anuidade'    ? 'selected':'' }}>Anuidade</option>
        </select>
        @error('tipo') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="valor">Valor (R$) *</label>
        <input id="valor" type="number" step="0.01" name="valor" class="form-control" value="{{ old('valor', $pagamento->valor ?? '') }}" required placeholder="0.00">
        @error('valor') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="status">Status *</label>
        <select id="status" name="status" class="form-control">
            <option value="pendente" {{ old('status', $pagamento->status ?? '') === 'pendente' ? 'selected':'' }}>Pendente</option>
            <option value="pago"     {{ old('status', $pagamento->status ?? '') === 'pago'     ? 'selected':'' }}>Pago</option>
            <option value="atrasado" {{ old('status', $pagamento->status ?? '') === 'atrasado' ? 'selected':'' }}>Atrasado</option>
        </select>
        @error('status') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="metodo_pagamento">Método de Pagamento</label>
        <select id="metodo_pagamento" name="metodo_pagamento" class="form-control">
            <option value="">Selecione...</option>
            <option value="pix"         {{ old('metodo_pagamento', $pagamento->metodo_pagamento ?? '') === 'pix'         ? 'selected':'' }}>PIX</option>
            <option value="boleto"      {{ old('metodo_pagamento', $pagamento->metodo_pagamento ?? '') === 'boleto'      ? 'selected':'' }}>Boleto</option>
            <option value="cartao"      {{ old('metodo_pagamento', $pagamento->metodo_pagamento ?? '') === 'cartao'      ? 'selected':'' }}>Cartão</option>
            <option value="transferencia" {{ old('metodo_pagamento', $pagamento->metodo_pagamento ?? '') === 'transferencia' ? 'selected':'' }}>Transferência</option>
        </select>
        @error('metodo_pagamento') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="data_vencimento">Data de Vencimento *</label>
        <input id="data_vencimento" type="date" name="data_vencimento" class="form-control" value="{{ old('data_vencimento', isset($pagamento->data_vencimento) ? $pagamento->data_vencimento->format('Y-m-d') : '') }}" required>
        @error('data_vencimento') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="data_pagamento">Data do Pagamento</label>
        <input id="data_pagamento" type="date" name="data_pagamento" class="form-control" value="{{ old('data_pagamento', isset($pagamento->data_pagamento) ? $pagamento->data_pagamento->format('Y-m-d') : '') }}">
        @error('data_pagamento') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>
