{{-- Partial form compartilhado para sites --}}
<div class="grid-2">
    <div class="form-group">
        <label class="form-label" for="cliente_id">Cliente *</label>
        <select id="cliente_id" name="cliente_id" class="form-control" required>
            <option value="">Selecione...</option>
            @foreach($clientes as $c)
                <option value="{{ $c->id }}" {{ old('cliente_id', $site->cliente_id ?? '') == $c->id ? 'selected':'' }}>{{ $c->nome }}</option>
            @endforeach
        </select>
        @error('cliente_id') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="plano_id">Plano</label>
        <select id="plano_id" name="plano_id" class="form-control">
            <option value="">Sem plano</option>
            @foreach($planos as $p)
                <option value="{{ $p->id }}" {{ old('plano_id', $site->plano_id ?? '') == $p->id ? 'selected':'' }}>{{ $p->nome }}</option>
            @endforeach
        </select>
        @error('plano_id') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="dominio">Domínio *</label>
        <input id="dominio" type="text" name="dominio" class="form-control" value="{{ old('dominio', $site->dominio ?? '') }}" placeholder="exemplo.com.br" required>
        @error('dominio') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="nome_site">Nome do Site *</label>
        <input id="nome_site" type="text" name="nome_site" class="form-control" value="{{ old('nome_site', $site->nome_site ?? '') }}" required>
        @error('nome_site') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="status">Status *</label>
        <select id="status" name="status" class="form-control">
            <option value="em_construcao" {{ old('status', $site->status ?? '') === 'em_construcao' ? 'selected':'' }}>Em construção</option>
            <option value="ativo"         {{ old('status', $site->status ?? '') === 'ativo'         ? 'selected':'' }}>Ativo</option>
            <option value="suspenso"      {{ old('status', $site->status ?? '') === 'suspenso'      ? 'selected':'' }}>Suspenso</option>
        </select>
        @error('status') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="url_vercel">URL Vercel</label>
        <input id="url_vercel" type="url" name="url_vercel" class="form-control" value="{{ old('url_vercel', $site->url_vercel ?? '') }}" placeholder="https://...vercel.app">
        @error('url_vercel') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="data_publicacao">Data de Publicação</label>
        <input id="data_publicacao" type="date" name="data_publicacao" class="form-control" value="{{ old('data_publicacao', isset($site->data_publicacao) ? $site->data_publicacao->format('Y-m-d') : '') }}">
        @error('data_publicacao') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="data_renovacao">Data de Renovação</label>
        <input id="data_renovacao" type="date" name="data_renovacao" class="form-control" value="{{ old('data_renovacao', isset($site->data_renovacao) ? $site->data_renovacao->format('Y-m-d') : '') }}">
        @error('data_renovacao') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>
<div class="form-group">
    <label class="form-label" for="observacoes">Observações</label>
    <textarea id="observacoes" name="observacoes" class="form-control" rows="3">{{ old('observacoes', $site->observacoes ?? '') }}</textarea>
    @error('observacoes') <div class="form-error">{{ $message }}</div> @enderror
</div>
