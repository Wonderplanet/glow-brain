using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Home.Presentation.Components;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Home.Presentation.Views.HomeStageInfoView
{
    public class HomeStageInfoView : UIView
    {
        [Header("コンテンツRoot")]
        [SerializeField] UIObject _scrollRoot;
        [SerializeField] ScrollRect _scrollRootComponent;
        [SerializeField] VerticalLayoutGroup _infoRoot;
        [Header("敵ステータス情報")]
        [SerializeField] LayoutElement _enemyInfoTitle;
        [SerializeField] UIObject _enemyIconListRoot;
        [Header("原画のかけら情報")]
        [SerializeField] LayoutElement _artworkFragmentInfoTitle;
        [SerializeField] UIObject _artworkFragmentIconListRoot;
        [SerializeField] LayoutElement _artworkNoDataGameObject;
        [Header("クリア時間情報")]
        [SerializeField] LayoutElement _clearTimeInfo;
        [SerializeField] UIText _clearTimeText;
        [Header("報酬情報")]
        [SerializeField] LayoutElement _rewardInfoTitle;
        [SerializeField] UIObject _rewardIconListRoot;
        [SerializeField] LayoutElement _rewardNoDataGameObject;
        [SerializeField] UIObject _speedAttackRewardListRoot;

        [Header("Prefab")]
        [SerializeField] HomeStageInfoEnemyThumbnail _stageInfoEnemyThumbnailPrefab;
        [SerializeField] PlayerResourceIconButtonComponent _playerResourceIconButtonComponentPrefab;
        [SerializeField] HomeStageInfoSpeedAttackReward _speedAttackRewardPrefab;

        [Header("特別ルール")]
        [SerializeField] InGameSpecialRuleComponent _inGameSpecialRuleComponent;

        [Header("ステージ情報")]
        [SerializeField] GameObject _stageInfoTitleComponent;
        [SerializeField] GameObject _stageInfoArea;
        [SerializeField] UIText _stageInfoText;

        public ScrollRect ScrollRootComponent => _scrollRootComponent;

        public HomeStageInfoEnemyThumbnail InstantiateEnemyThumbnail()
        {
            return Instantiate(_stageInfoEnemyThumbnailPrefab, _enemyIconListRoot.RectTransform);
        }

        public PlayerResourceIconButtonComponent InstantiateArtworkFragmentPlayerResourceIconButtonComponent()
        {
            return Instantiate(_playerResourceIconButtonComponentPrefab, _artworkFragmentIconListRoot.RectTransform);
        }

        public PlayerResourceIconButtonComponent InstantiateRewardPlayerResourceIconButtonComponent()
        {
            return Instantiate(_playerResourceIconButtonComponentPrefab, _rewardIconListRoot.RectTransform);
        }

        public HomeStageInfoSpeedAttackReward InstantiateSpeedAttackReward()
        {
            return Instantiate(_speedAttackRewardPrefab, _speedAttackRewardListRoot.RectTransform);
        }

        public void SettingDialogViewSize(
            int enemyCount,
            int artworkFragmentCount,
            int rewardCount,
            bool isSpeedAttack)
        {
            var infoRootPaddingTop = _infoRoot.padding.top;

            var enemyInfoTitleHeight = _enemyInfoTitle.minHeight;
            var artworkFragmentInfoTitleHeight = _artworkFragmentInfoTitle.minHeight;
            var artworkNoDataHeight = _artworkNoDataGameObject.minHeight;
            var rewardInfoTitleHeight = _rewardInfoTitle.minHeight;
            var rewardNoDataHeight = _rewardNoDataGameObject.minHeight;

            // 敵情報
            var enemyIconListRootGridLayoutGroup = _enemyIconListRoot.GetComponent<GridLayoutGroup>();
            var enemyCalcWidthSize = enemyIconListRootGridLayoutGroup.cellSize.x;
            int enemyHorizontalCellCount = 0;
            while (enemyCalcWidthSize < _enemyIconListRoot.RectTransform.sizeDelta.x)
            {
                enemyHorizontalCellCount++;
                enemyCalcWidthSize += enemyIconListRootGridLayoutGroup.cellSize.x + enemyIconListRootGridLayoutGroup.spacing.x;
            }

            var enemyVerticalCellCount = Mathf.Floor((float)enemyCount / enemyHorizontalCellCount) + 1;
            if ((float)enemyCount % enemyHorizontalCellCount == 0)
                enemyVerticalCellCount--;

            var enemyTotalHeight = (enemyIconListRootGridLayoutGroup.cellSize.y + enemyIconListRootGridLayoutGroup.spacing.y) * enemyVerticalCellCount
                + enemyIconListRootGridLayoutGroup.padding.top + enemyIconListRootGridLayoutGroup.padding.bottom - enemyIconListRootGridLayoutGroup.spacing.y + enemyInfoTitleHeight;

            // 原画のかけら情報
            _artworkNoDataGameObject.gameObject.SetActive(artworkFragmentCount <= 0);
            var artworkFragmentIconListRootGridLayoutGroup = _artworkFragmentIconListRoot.GetComponent<GridLayoutGroup>();
            var artworkFragmentCalcWidthSize = artworkFragmentIconListRootGridLayoutGroup.cellSize.x;
            int artworkFragmentHorizontalCellCount = 0;
            while (artworkFragmentCalcWidthSize < _artworkFragmentIconListRoot.RectTransform.sizeDelta.x)
            {
                artworkFragmentHorizontalCellCount++;
                artworkFragmentCalcWidthSize += artworkFragmentIconListRootGridLayoutGroup.cellSize.x + artworkFragmentIconListRootGridLayoutGroup.spacing.x;
            }

            var artworkFragmentVerticalCellCount = Mathf.Floor((float)artworkFragmentCount / artworkFragmentHorizontalCellCount) + 1;
            if ((float)artworkFragmentCount % artworkFragmentHorizontalCellCount == 0)
                artworkFragmentVerticalCellCount--;

            var artworkFragmentTotalHeight = (artworkFragmentIconListRootGridLayoutGroup.cellSize.y + artworkFragmentIconListRootGridLayoutGroup.spacing.y) * artworkFragmentVerticalCellCount
                + artworkFragmentIconListRootGridLayoutGroup.padding.top + artworkFragmentIconListRootGridLayoutGroup.padding.bottom - artworkFragmentIconListRootGridLayoutGroup.spacing.y + artworkFragmentInfoTitleHeight;
            if (artworkFragmentCount <= 0) artworkFragmentTotalHeight += artworkNoDataHeight;

            // スピードアタック情報
            _clearTimeInfo.gameObject.SetActive(isSpeedAttack);
            _speedAttackRewardListRoot.Hidden = !isSpeedAttack;
            var clearTimeInfoHeight = isSpeedAttack ? _clearTimeInfo.minHeight : 0;

            // 報酬情報
            _rewardNoDataGameObject.gameObject.SetActive(rewardCount <= 0);
            var rewardIconListRootGridLayoutGroup = _rewardIconListRoot.GetComponent<GridLayoutGroup>();
            var rewardCalcWidthSize = rewardIconListRootGridLayoutGroup.cellSize.x;
            if (rewardCount <= 0) rewardCalcWidthSize += _rewardNoDataGameObject.minHeight;
            int rewardHorizontalCellCount = 0;
            while (rewardCalcWidthSize < _rewardIconListRoot.RectTransform.sizeDelta.x)
            {
                rewardHorizontalCellCount++;
                rewardCalcWidthSize += rewardIconListRootGridLayoutGroup.cellSize.x + rewardIconListRootGridLayoutGroup.spacing.x;
            }

            var rewardVerticalCellCount = Mathf.Floor((float)rewardCount / rewardHorizontalCellCount) + 1;
            if ((float)rewardCount % rewardHorizontalCellCount == 0)
                rewardVerticalCellCount--;

            var rewardTotalHeight = (rewardIconListRootGridLayoutGroup.cellSize.y + rewardIconListRootGridLayoutGroup.spacing.y) * rewardVerticalCellCount
                + rewardIconListRootGridLayoutGroup.padding.top + rewardIconListRootGridLayoutGroup.padding.bottom - rewardIconListRootGridLayoutGroup.spacing.y + rewardInfoTitleHeight;
            if (rewardCount <= 0) rewardTotalHeight += rewardNoDataHeight;

            // トータルの高さ計算
            var totalHeight = enemyTotalHeight
                              + artworkFragmentTotalHeight
                              + rewardTotalHeight
                              + infoRootPaddingTop
                              + clearTimeInfoHeight;
            if (_scrollRoot.RectTransform.sizeDelta.y > totalHeight)
                _scrollRoot.RectTransform.sizeDelta = new Vector2(_scrollRoot.RectTransform.sizeDelta.x, totalHeight);
        }

        public void SetupClearTime(EventClearTimeMs clearTime)
        {
            _clearTimeText.SetText(clearTime.ToString());
        }

        public void ShowInGameSpecialRule()
        {
            _inGameSpecialRuleComponent.gameObject.SetActive(true);
        }

        public void HideInGameSpecialRule()
        {
            _inGameSpecialRuleComponent.gameObject.SetActive(false);
        }

        public void InitializeSpecialRule()
        {
            _inGameSpecialRuleComponent.SetupEmpty();
        }

        public void SetupEmpty()
        {
            _inGameSpecialRuleComponent.SetupEmpty();
        }

        public void SetupSeriesLogos(List<SeriesLogoImagePath> seriesLogoImagePathList)
        {
            _inGameSpecialRuleComponent.SetupSeriesLogos(seriesLogoImagePathList);
        }

        public void SetupUnitRarity(List<Rarity> rarities)
        {
            _inGameSpecialRuleComponent.SetupUnitRarity(rarities);
        }

        public void SetupUnitRoleType(List<CharacterUnitRoleType> roleTypes)
        {
            _inGameSpecialRuleComponent.SetupUnitRoleType(roleTypes);
        }

        public void SetupUnitCount(InGameSpecialRuleUnitAmount unitAmount)
        {
            _inGameSpecialRuleComponent.SetupUnitCount(unitAmount);
        }

        public void SetupAttackRange(List<CharacterAttackRangeType> unitAttackRangeTypes)
        {
            _inGameSpecialRuleComponent.SetupAttackRange(unitAttackRangeTypes);
        }

        public void SetupSpeedAttack(InGameSpecialRuleSpeedAttackFlag isSpeedAttack)
        {
            _inGameSpecialRuleComponent.SetupSpeedAttack(isSpeedAttack);
        }

        public void SetupTimeLimit(InGameSpecialRuleTimeLimit timeLimit)
        {
            _inGameSpecialRuleComponent.SetupTimeLimit(timeLimit);
        }

        public void SetupDefenseTarget(InGameSpecialRuleDefenseTargetFlag isDefenseTarget)
        {
            _inGameSpecialRuleComponent.SetupDefenseTarget(isDefenseTarget);
        }

        public void SetupEnemyDestruction(
            DefeatEnemyCount count,
            InGameSpecialRuleEnemyDestructionFlag isEnemyDestruction)
        {
            _inGameSpecialRuleComponent.SetupEnemyDestruction(count, isEnemyDestruction);
        }

        public void SetupSpecificEnemyDestruction(
            CharacterName enemyName,
            DefeatEnemyCount count,
            InGameSpecialRuleSpecificEnemyDestructionFlag isSpecificEnemyDestruction)
        {
            _inGameSpecialRuleComponent.SetupSpecificEnemyDestruction(
                enemyName,
                count,
                isSpecificEnemyDestruction);
        }

        public void SetupStartOutpost(InGameSpecialRuleStartOutpostHp startOutpostHp, InGameSpecialRuleStartOutpostHpFlag isStartOutpostHp)
        {
            _inGameSpecialRuleComponent.SetupStartOutpost(startOutpostHp, isStartOutpostHp);
        }

        public void SetupEnemyOutpostDamageInvalidation(InGameSpecialRuleEnemyOutpostDamageInvalidationFlag isEnemyOutpostDamageInvalidation)
        {
            _inGameSpecialRuleComponent.SetupEnemyOutpostDamageInvalidation(isEnemyOutpostDamageInvalidation);
        }

        public void SetupNoContinue(InGameSpecialRuleNoContinueFlag isNoContinue)
        {
            _inGameSpecialRuleComponent.SetupNoContinue(isNoContinue);
        }

        public void SetupHeaderComment(InGameSpecialRuleFromUnitSelectFlag isFromUnitSelect, InGameSpecialRuleExistFormationRuleFlag existsFormationRule)
        {
            _inGameSpecialRuleComponent.SetupHeaderComment(isFromUnitSelect, existsFormationRule);
        }

        public void SetUpSpecialRuleHeader(
            InGameSpecialRuleExistFormationRuleFlag existsFormationRule,
            InGameSpecialRuleExistOtherRuleFlag existsOtherRule)
        {
            _inGameSpecialRuleComponent.SetUpSpecialRuleHeader(existsFormationRule, existsOtherRule);
        }

        public void SetInGameDescription(InGameDescription inGameDescription)
        {
            _stageInfoTitleComponent.SetActive(!inGameDescription.IsEmpty());
            _stageInfoArea.SetActive(!inGameDescription.IsEmpty());
            _stageInfoText.gameObject.SetActive(!inGameDescription.IsEmpty());
            _stageInfoText.SetText(inGameDescription.Value);
        }
    }
}
