<?php
/**
 * Meta tags y JSON-LD para artículos del blog (SEO + compartir).
 */

function blog_seo_canonical_url(string $slug, string $base_url = ''): string
{
    $base = rtrim($base_url, '/');
    $path = 'blog.php?slug=' . rawurlencode($slug);
    if ($base === '') {
        return $path;
    }
    return $base . '/' . ltrim($path, '/');
}

function blog_seo_absolute_url(string $pathOrUrl, string $base_url = ''): string
{
    if (preg_match('~^https?://~i', $pathOrUrl)) {
        return $pathOrUrl;
    }
    $base = rtrim($base_url, '/');
    $path = ltrim($pathOrUrl, '/');
    if ($base === '') {
        return $path;
    }
    return $base . '/' . $path;
}

/** @return array<string, mixed> */
function blog_seo_article_json_ld(array $articulo, string $base_url = ''): array
{
    $img = blog_img_url($articulo['portada'] ?? '', $base_url);
    $published = date('c', strtotime($articulo['fecha'] ?? 'now'));

    return [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $articulo['titulo'] ?? '',
        'description' => $articulo['resumen'] ?? '',
        'image' => [blog_seo_absolute_url($img, $base_url)],
        'datePublished' => $published,
        'dateModified' => $published,
        'author' => [
            '@type' => 'Organization',
            'name' => 'IMPROGYP',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'IMPROGYP',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => blog_seo_absolute_url('logo-oscuro.png', $base_url),
            ],
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => blog_seo_canonical_url($articulo['slug'] ?? '', $base_url),
        ],
    ];
}

function blog_seo_render_article_head(array $articulo, string $base_url = ''): void
{
    $title = htmlspecialchars($articulo['titulo'] ?? 'Blog IMPROGYP', ENT_QUOTES, 'UTF-8');
    $desc = htmlspecialchars(mb_substr(trim($articulo['resumen'] ?? ''), 0, 160), ENT_QUOTES, 'UTF-8');
    $canonical = htmlspecialchars(blog_seo_canonical_url($articulo['slug'] ?? '', $base_url), ENT_QUOTES, 'UTF-8');
    $ogImage = htmlspecialchars(blog_seo_absolute_url(blog_img_url($articulo['portada'] ?? '', $base_url), $base_url), ENT_QUOTES, 'UTF-8');
    $jsonLd = json_encode(blog_seo_article_json_ld($articulo, $base_url), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ?>
    <title><?= $title ?> | Blog IMPROGYP</title>
    <meta name="description" content="<?= $desc ?>">
    <link rel="canonical" href="<?= $canonical ?>">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= $title ?>">
    <meta property="og:description" content="<?= $desc ?>">
    <meta property="og:url" content="<?= $canonical ?>">
    <meta property="og:image" content="<?= $ogImage ?>">
    <meta property="og:site_name" content="IMPROGYP">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?>">
    <meta name="twitter:description" content="<?= $desc ?>">
    <meta name="twitter:image" content="<?= $ogImage ?>">
    <script type="application/ld+json"><?= $jsonLd ?></script>
    <?php
}
