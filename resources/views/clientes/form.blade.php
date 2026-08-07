@extends('layouts.app')

@section('titulo', (isset($modo) && $modo == 'editar' ? 'Editar Cliente: ' . ($cliente->nome ?? '') : 'Novo Cliente'))

@section('conteudo')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
    <form method="POST" action="{{ isset($modo) && $modo == 'editar' ? route('clientes.update', $cliente->id) : route('clientes.store') }}" class="space-y-6">
        @csrf
        @if(isset($modo) && $modo == 'editar')
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nome -->
            <div>
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                <input type="text" 
                       name="nome" 
                       id="nome" 
                       value="{{ old('nome', $cliente->nome ?? '') }}" 
                       required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors @error('nome') border-red-500 @enderror">
                @error('nome')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email', $cliente->email ?? '') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Telefone -->
            <div>
                <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input type="text" 
                       name="telefone" 
                       id="telefone" 
                       value="{{ old('telefone', $cliente->telefone ?? '') }}" 
                       placeholder="(00) 00000-0000"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors @error('telefone') border-red-500 @enderror">
                @error('telefone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Empresa -->
            <div>
                <label for="empresa" class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                <input type="text" 
                       name="empresa" 
                       id="empresa" 
                       value="{{ old('empresa', $cliente->empresa ?? '') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors @error('empresa') border-red-500 @enderror">
                @error('empresa')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- CNPJ/CPF -->
            <div>
                <label for="cnpj_cpf" class="block text-sm font-medium text-gray-700 mb-1">CNPJ / CPF</label>
                <input type="text" 
                       name="cnpj_cpf" 
                       id="cnpj_cpf" 
                       value="{{ old('cnpj_cpf', $cliente->cnpj_cpf ?? '') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors @error('cnpj_cpf') border-red-500 @enderror">
                @error('cnpj_cpf')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select name="status" 
                        id="status" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors @error('status') border-red-500 @enderror">
                    <option value="prospect" {{ old('status', $cliente->status ?? '') == 'prospect' ? 'selected' : '' }}>Prospect</option>
                    <option value="ativo" {{ old('status', $cliente->status ?? '') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="inativo" {{ old('status', $cliente->status ?? '') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Origem Lead -->
            <div>
                <label for="origem_lead" class="block text-sm font-medium text-gray-700 mb-1">Origem do Lead</label>
                <input type="text" 
                       name="origem_lead" 
                       id="origem_lead" 
                       value="{{ old('origem_lead', $cliente->origem_lead ?? '') }}" 
                       placeholder="Ex: Google, Indicação, Instagram" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors @error('origem_lead') border-red-500 @enderror">
                @error('origem_lead')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Endereço -->
            <div class="md:col-span-2">
                <label for="endereco" class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                <textarea name="endereco" 
                          id="endereco" 
                          rows="2" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors @error('endereco') border-red-500 @enderror">{{ old('endereco', $cliente->endereco ?? '') }}</textarea>
                @error('endereco')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('clientes.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                {{ isset($modo) && $modo == 'editar' ? 'Atualizar Cliente' : 'Salvar Cliente' }}
            </button>
        </div>
    </form>
</div>
@endsection
