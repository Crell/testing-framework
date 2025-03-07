<?php
declare(strict_types=1);
namespace TYPO3\TestingFramework\Composer;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Composer\Script\Event;
use Composer\Util\Filesystem;
use TYPO3\CMS\Composer\Plugin\Config;

/**
 * If a TYPO3 extension should be tested, the extension needs to be embedded in
 * a TYPO3 instance. The composer.json file of the extension then acts as a
 * root composer.json file that creates a TYPO3 project around the extension code
 * in a build folder like "./.Build". The to-test extension then needs to reside
 * in ./.Build/Web/typo3conf/ext. This composer script takes care of this operation
 * and links the current root directory as "./<web-dir>/typo3conf/ext/<extension-key>".
 *
 * This class is added as composer "script" in TYPO3 extensions:
 *
 *   "scripts": {
 *     "post-autoload-dump": [
 *       "@prepare-extension-test-environment"
 *     ],
 *     "prepare-extension-test-structure": [
 *       "TYPO3\TestingFramework\Composer\ExtensionTestEnvironment::prepare"
 *     ]
 *   },
 *
 */
final class ExtensionTestEnvironment
{
    /**
     * Link directory that contains the composer.json file as
     * ./<web-dir>/typo3conf/ext/<extension-key>.
     *
     * @param Event $event
     */
    public static function prepare(Event $event): void
    {
        $composer = $event->getComposer();
        $rootPackage = $composer->getPackage();
        if ($rootPackage->getType() !== 'typo3-cms-extension') {
            throw new \RuntimeException(
                'This script can only be used for TYPO3 extensions',
                1630244768
            );
        }
        $typo3ExtensionInstallPath = $composer->getInstallationManager()->getInstaller('typo3-cms-extension')->getInstallPath($rootPackage);
        $pluginConfig = Config::load($composer);
        $extensionPath = $pluginConfig->get('base-dir');
        $filesystem = new Filesystem();
        $filesystem->ensureDirectoryExists(dirname($typo3ExtensionInstallPath));
        if (!$filesystem->isSymlinkedDirectory($typo3ExtensionInstallPath)) {
            $filesystem->relativeSymlink($extensionPath, $typo3ExtensionInstallPath);
        }
    }
}
