{{-- Partial compartilhado por create.blade.php e edit.blade.php --}}
<div class="grid-2">
    <div class="form-group">
        <label class="form-label" for="nome">Nome *</label>
        <input id="nome" type="text" name="nome" class="form-control" value="{{ old('nome', $cliente->nome ?? '') }}" required>
        @error('nome') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="email">E-mail</label>
        <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $cliente->email ?? '') }}" placeholder="opcional">
        @error('email') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="telefone">Telefone</label>
        <input id="telefone" type="text" name="telefone" class="form-control" value="{{ old('telefone', $cliente->telefone ?? '') }}" placeholder="(11) 99999-9999">
        @error('telefone') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="empresa">Empresa</label>
        <input id="empresa" type="text" name="empresa" class="form-control" value="{{ old('empresa', $cliente->empresa ?? '') }}">
        @error('empresa') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="cnpj_cpf">CPF / CNPJ</label>
        <input id="cnpj_cpf" type="text" name="cnpj_cpf" class="form-control" value="{{ old('cnpj_cpf', $cliente->cnpj_cpf ?? '') }}" placeholder="000.000.000-00">
        @error('cnpj_cpf') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="status">Status *</label>
        <select id="status" name="status" class="form-control">
            <option value="prospect" {{ old('status', $cliente->status ?? '') === 'prospect' ? 'selected' : '' }}>Prospect</option>
            <option value="ativo"    {{ old('status', $cliente->status ?? '') === 'ativo'    ? 'selected' : '' }}>Ativo</option>
            <option value="inativo"  {{ old('status', $cliente->status ?? '') === 'inativo'  ? 'selected' : '' }}>Inativo</option>
        </select>
        @error('status') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="origem_lead">Origem / Lead</label>
        <input id="origem_lead" type="text" name="origem_lead" class="form-control" value="{{ old('origem_lead', $cliente->origem_lead ?? '') }}" placeholder="Instagram, indicação, Google...">
        @error('origem_lead') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>
<div class="form-group">
    <label class="form-label" for="endereco">Endereço</label>
    <textarea id="endereco" name="endereco" class="form-control" rows="2" placeholder="Rua, número, cidade...">{{ old('endereco', $cliente->endereco ?? '') }}</textarea>
    @error('endereco') <div class="form-error">{{ $message }}</div> @enderror
</div>
