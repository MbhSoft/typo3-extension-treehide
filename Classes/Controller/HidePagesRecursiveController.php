<?php

declare(strict_types=1);

namespace MbhSoftware\Treehide\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * class HidePagesRecursiveController
 */
class HidePagesRecursiveController
{
    protected DataHandler $dataHandler;

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    }

    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];
        $skippedRecordsDueToPermissions = 0;
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();
        $pageUid = (int)($parsedBody['id'] ?? $queryParams['id'] ?? 0);
        $mode = (int)($parsedBody['mode'] ?? $queryParams['mode'] ?? 0);
        $message = $this->getLanguageService()->sL('LLL:EXT:treehide/Resources/Private/Language/locallang.xlf:treehide.message.error');
        $success = false;
        if ($pageUid !== 0) {
            $fieldName = $GLOBALS['TCA']['pages']['ctrl']['enablecolumns']['disabled'];
            if ($this->isPageEditable($pageUid)) {
                $data['pages'][$pageUid][$fieldName] = $mode;
            } else {
                $skippedRecordsDueToPermissions++;
            }
            $page = $this->getPageInfo($pageUid);
            $subPages = [];
            $sysLanguage = 0;

            if ($page['sys_language_uid'] > 0) {
                $sysLanguage = $page['sys_language_uid'];
                $pageUid = $page['l10n_parent'];
            }
            $this->getPageTreeInfo($pageUid, 99, $subPages, $sysLanguage);
            foreach ($subPages as $subPage) {
                if ($this->isPageEditable((int)$subPage)) {
                    $data['pages'][$subPage][$fieldName] = $mode;
                } else {
                    $skippedRecordsDueToPermissions++;
                }
            }
            if ($data) {
                $this->dataHandler->start($data, []);
                $this->dataHandler->process_datamap();
            }
            if ($skippedRecordsDueToPermissions > 0) {
                $message = $this->getLanguageService()->sL('LLL:EXT:treehide/Resources/Private/Language/locallang.xlf:treehide.message.someSkippedDueToPermissions');
            } else {
                $message = $this->getLanguageService()->sL('LLL:EXT:treehide/Resources/Private/Language/locallang.xlf:treehide.message.success');
            }
            $success = true;

        }
        return new JsonResponse([
            'success' => $success,
            'title' => $this->getLanguageService()->sL('LLL:EXT:treehide/Resources/Private/Language/locallang.xlf:treehide.title'),
            'message' => $message,
        ]);
    }

    protected function isPageEditable(int $pageId): bool
    {
        if ($this->getBackendUserAuthentication()->isAdmin()) {
            return true;
        }
        $result = BackendUtility::readPageAccess($pageId, $this->getBackendUserAuthentication()->getPagePermsClause(Permission::PAGE_EDIT));
        return $result !== false;
    }

    protected function getPageTreeInfo(int $pid, int $levels = 99, array &$CPtable = [], $sysLanguage = 0): array
    {
        if ($levels > 0) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
            $restrictions = $queryBuilder->getRestrictions()->removeAll();
            $restrictions->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $queryBuilder
                ->select('uid', 'l10n_parent')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pid, Connection::PARAM_INT)),
                    $queryBuilder->expr()->eq('sys_language_uid', $sysLanguage),
                );
            $result = $queryBuilder->executeQuery();

            $pages = [];
            while ($row = $result->fetchAssociative()) {
                $pages[$row['uid']] = $row;
            }

            foreach ($pages as $page) {
                $CPtable[] = $page['uid'];
                if ($levels - 1) {
                    $CPtable = $this->getPageTreeInfo($sysLanguage > 0 ? $page['l10n_parent'] : $page['uid'], $levels - 1, $CPtable, $sysLanguage);
                }
            }
        }
        return $CPtable;
    }

    protected function getPageInfo(int $uid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $restrictions = $queryBuilder->getRestrictions()->removeAll();
        $restrictions->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT))
            );
        $result = $queryBuilder->executeQuery();

        $row = $result->fetchAssociative();
        if (!is_array($row)) {
            $row = null;
        }

        return $row;
    }

    protected function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
