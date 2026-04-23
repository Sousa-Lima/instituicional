<?php
\$file = '/var/www/html/app/Http/Resources/BlogPostResource.php';
\$content = file_get_contents(\$file);
\$badLine = '            \'/Por\s+que\s+o\s+Traefik\s+se\s+tornou\s+meu\s+["\\\' . "\'" . \']?braço\s+direito["\\\' . "\'" . \']?\s+na\s+orquestração\s+com\s+Docker\s+Swarm\?\s*[\p{So}\p{Sk}\x{1F300}-\x{1FAFF}]*/iu\',';
\$goodLine = '            \'/Por\s+que\s+o\s+Traefik\s+se\s+tornou\s+meu\s+["\\\']*braço\s+direito["\\\']*\s+na\s+orquestração\s+com\s+Docker\s+Swarm\?\s*[\p{So}\p{Sk}\x{1F300}-\x{1FAFF}]*/iu\',';
\$newContent = str_replace(\$badLine, \$goodLine, \$content);
file_put_contents(\$file, \$newContent);
