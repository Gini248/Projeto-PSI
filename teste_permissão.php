<?php
$pasta = 'assets/uploads/perfis/';

echo "Testando pasta: " . $pasta . "<br>";

// Verificar se pasta existe
if (is_dir($pasta)) {
    echo "✓ Pasta existe<br>";
    
    // Verificar permissões
    echo "Permissões: " . substr(sprintf('%o', fileperms($pasta)), -4) . "<br>";
    
    // Tentar escrever um arquivo de teste
    $arquivo_teste = $pasta . 'teste.txt';
    if (file_put_contents($arquivo_teste, 'teste')) {
        echo "✓ Permissão de escrita OK<br>";
        unlink($arquivo_teste); // Remover arquivo de teste
    } else {
        echo "✗ Erro: Não conseguiu escrever na pasta<br>";
    }
    
    // Verificar usuário/grupo
    $stat = stat($pasta);
    echo "UID: " . $stat['uid'] . "<br>";
    echo "GID: " . $stat['gid'] . "<br>";
    
} else {
    echo "✗ Pasta não existe<br>";
    
    // Tentar criar
    if (mkdir($pasta, 0755, true)) {
        echo "✓ Pasta criada com sucesso<br>";
    } else {
        echo "✗ Não conseguiu criar pasta<br>";
    }
}

// Mostrar informações do servidor
echo "<hr>";
echo "Usuário do PHP: " . get_current_user() . "<br>";
echo "Usuário efetivo: " . posix_geteuid() . "<br>";
echo "Grupo efetivo: " . posix_getegid() . "<br>";
echo "Apache user (se aplicável): " . exec('whoami') . "<br>";
?>