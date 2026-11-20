<?php

defined('CASTOR_USE_CHDIR') || define('CASTOR_USE_CHDIR', true);

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Helper\ProgressIndicator;

use function Castor\fs;
use function Castor\io;
use function Castor\run;

const REGISTRY = 'aamant';
const ARCHIVE_MARKER = '.archived';

/**
 * @return array<string, bool> nom de l'image => archivée
 */
function images(): array
{
    $images = [];
    foreach (glob(__DIR__ . '/*/Dockerfile') as $dockerfile) {
        $dir = dirname($dockerfile);
        $images[basename($dir)] = file_exists($dir . '/' . ARCHIVE_MARKER);
    }
    ksort($images, SORT_NATURAL);

    return $images;
}

/** @return list<string> */
function active_images(): array
{
    return array_keys(array_filter(images(), fn (bool $archived) => !$archived));
}

/** @return list<string> */
function archived_images(): array
{
    return array_keys(array_filter(images()));
}

function complete_images(CompletionInput $input): array
{
    return array_keys(images());
}

function complete_active_images(CompletionInput $input): array
{
    return active_images();
}

function complete_archived_images(CompletionInput $input): array
{
    return archived_images();
}

function assert_image(string $image): void
{
    if (!array_key_exists($image, images())) {
        throw new InvalidArgumentException(sprintf('Image inconnue : %s', $image));
    }
}

#[AsTask(namespace: 'image', name: 'list', description: 'Liste les images et leur état')]
function list_images(): void
{
    $rows = [];
    foreach (images() as $image => $archived) {
        $rows[] = [$image, $archived ? '<comment>archivée</comment>' : '<info>active</info>'];
    }
    io()->table(['Image', 'État'], $rows);
}

#[AsTask(namespace: 'image', description: 'Build les images données, ou toutes les images non archivées')]
function build(
    #[AsArgument(description: 'Images à builder (toutes les actives par défaut)', autocomplete: 'complete_images')]
    array $images = [],
    #[AsOption(description: 'Pousse les images sur Docker Hub après le build')]
    bool $push = false,
    #[AsOption(description: 'Récupère la dernière version de l\'image de base')]
    bool $pull = false,
    #[AsOption(description: 'Build sans cache')]
    bool $noCache = false,
    #[AsOption(description: 'Affiche la sortie de docker au lieu de la barre de progression')]
    bool $logs = false,
): void {
    foreach ($images as $image) {
        assert_image($image);
    }
    $images = $images ?: active_images();

    $context = \Castor\context()->withAllowFailure()->withQuiet(!$logs);
    $logDir = sys_get_temp_dir() . '/docker-php-dev-build';
    $total = count($images);
    $failedLogs = [];
    $rows = [];

    foreach (array_values($images) as $i => $image) {
        $tag = REGISTRY . '/' . $image;
        $prefix = sprintf('[%d/%d] %s', $i + 1, $total, $tag);

        $command = ['docker', 'build', '--compress', '-t', $tag];
        if ($pull) {
            $command[] = '--pull';
        }
        if ($noCache) {
            $command[] = '--no-cache';
        }
        $command[] = __DIR__ . '/' . $image;

        $steps = [['build', $command]];
        if ($push) {
            $steps[] = ['push', ['docker', 'push', $tag]];
        }

        // L'indicateur n'avance qu'à chaque sortie de docker : il tourne tant que le build produit des logs.
        $indicator = $logs ? null : new ProgressIndicator(io(), 'verbose', 100, null, '<info>✔</info>');
        $indicator?->start($prefix);
        $start = microtime(true);
        $failedStep = null;

        foreach ($steps as [$action, $stepCommand]) {
            $logs ? io()->section(sprintf('%s %s', ucfirst($action), $prefix)) : $indicator->setMessage(sprintf('%s : %s…', $prefix, $action));
            $process = run($stepCommand, context: $context, callback: $indicator ? fn () => $indicator->advance() : null);
            if (!$process->isSuccessful()) {
                $failedStep = $action;
                if (!$logs) {
                    fs()->mkdir($logDir);
                    $failedLogs[$image] = sprintf('%s/%s-%s.log', $logDir, $image, $action);
                    file_put_contents($failedLogs[$image], $process->getOutput() . $process->getErrorOutput());
                } else {
                    $failedLogs[$image] = null;
                }
                break;
            }
        }

        $duration = sprintf('%ds', microtime(true) - $start);
        $status = $failedStep ? sprintf('<error>échec %s</error>', $failedStep) : '<info>OK</info>';
        $rows[] = [$image, $status, $duration];
        $indicator?->finish(sprintf('%s : %s', $prefix, $status), $failedStep ? '<error>✘</error>' : null);
    }

    if ($logs) {
        io()->table(['Image', 'Résultat', 'Durée'], $rows);
    }

    if ($failedLogs) {
        io()->error(sprintf('%d/%d image(s) en échec', count($failedLogs), $total));
        if (array_filter($failedLogs)) {
            io()->listing(array_values(array_filter($failedLogs)));
        }
        exit(1);
    }
    io()->success(sprintf('%d image(s) traitée(s)', $total));
}

#[AsTask(namespace: 'image', description: 'Archive une image : elle est exclue du build global')]
function archive(
    #[AsArgument(description: 'Image à archiver', autocomplete: 'complete_active_images')]
    string $image,
): void {
    assert_image($image);
    fs()->touch(__DIR__ . '/' . $image . '/' . ARCHIVE_MARKER);
    io()->success(sprintf('%s archivée', $image));
}

#[AsTask(namespace: 'image', description: 'Désarchive une image')]
function unarchive(
    #[AsArgument(description: 'Image à désarchiver', autocomplete: 'complete_archived_images')]
    string $image,
): void {
    assert_image($image);
    fs()->remove(__DIR__ . '/' . $image . '/' . ARCHIVE_MARKER);
    io()->success(sprintf('%s désarchivée', $image));
}
