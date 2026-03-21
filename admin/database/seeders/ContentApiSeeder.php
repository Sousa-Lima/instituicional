<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ContentApiSeeder extends Seeder
{
    public function run(): void
    {
        Service::query()->updateOrCreate(
            ['slug' => 'consultoria-processos'],
            [
                'title' => 'Consultoria de processos',
                'short_description' => 'Mapeamento e otimização de processos com foco em resultado mensurável.',
                'content_html' => '<p>Conteúdo editorial da página de serviço (placeholder).</p>',
                'icon_name' => 'workflow',
                'category' => 'consultoria',
                'seo' => [
                    'meta_title' => 'Consultoria de processos | Sousa Lima Consultoria',
                    'meta_description' => 'Consultoria de processos para eficiência operacional.',
                    'og_image' => 'https://api.sousalimaconsultoria.com.br/storage/services/og-consultoria.png',
                ],
                'order' => 1,
                'status' => 'published',
            ]
        );

        CaseStudy::query()->updateOrCreate(
            ['slug' => 'modernizacao-plataforma-logistica-ai'],
            [
                'status' => 'published',
                'featured' => true,
                'title' => 'Modernização de ecossistema logístico com arquitetura de micro-serviços',
                'customer_name' => 'LogTech Brasil S.A.',
                'sector' => 'Logística e transportes',
                'short_summary' => 'Redução drástica de downtime e escalabilidade para suportar +200% de volume de pedidos em datas sazonais.',
                'content_html' => '<section><h2>O desafio</h2><p>A LogTech enfrentava gargalos críticos durante a Black Friday.</p></section><section><h2>Nossa solução</h2><p>Implementamos uma nova camada de backend em <strong>Laravel</strong> com frontend <strong>Astro</strong> (SSG).</p></section><section><h2>O resultado</h2><p>99,9% de uptime no período de maior carga.</p></section>',
                'metrics' => [
                    ['label' => 'Redução de latência', 'value' => '85%'],
                    ['label' => 'Aumento de conversão', 'value' => '12%'],
                    ['label' => 'Tempo de entrega (MVP)', 'value' => '75 dias'],
                ],
                'main_image' => [
                    'url' => 'https://api.sousalimaconsultoria.com.br/storage/cases/logtech-hero.png',
                    'alt' => 'Dashboard de monitoramento logístico em tempo real',
                    'width' => 1200,
                    'height' => 630,
                ],
                'seo' => [
                    'meta_title' => 'Case LogTech: modernização de software | Sousa Lima Consultoria',
                    'meta_description' => 'Veja como a SLC ajudou a LogTech a reduzir o tempo de resposta em 85%.',
                    'og_image' => 'https://api.sousalimaconsultoria.com.br/storage/cases/og-logtech.png',
                    'keywords' => [
                        'Consultoria de software',
                        'Laravel',
                        'Astro SSG',
                        'Docker Swarm',
                        'Logística',
                    ],
                ],
            ]
        );
    }
}
