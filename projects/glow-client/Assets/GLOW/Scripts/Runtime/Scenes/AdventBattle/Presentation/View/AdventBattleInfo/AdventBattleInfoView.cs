using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Components;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.AdventBattle.Presentation.View.AdventBattleInfo
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-2_降臨バトル詳細情報表示
    ///
    /// 44_降臨バトル
    /// 　44-6_特別ルール
    /// 　　44-6-2-1_特別ルール専用ダイアログ（リミテッドバトルを参考）
    /// </summary>
    public class AdventBattleInfoView : UIView
    {
        [Header("コンテンツRoot")]
        [SerializeField] ScrollRect _scrollRootComponent;
        [SerializeField] RectTransform _scrollRectTransform;
        [SerializeField] float _scrollRootComponentHeight = 500f;
        [SerializeField] float _scrollRootComponentHeightWithSpecialRule = 807f;

        [Header("Prefab")]
        [SerializeField] HomeStageInfoEnemyThumbnail _enemyThumbnail;

        [Header("特別ルール")]
        [SerializeField] InGameSpecialRuleComponent _inGameSpecialRuleComponent;

        [Header("報酬情報")]
        [SerializeField] UIObject _rewardIconListRoot;
        [SerializeField] GameObject _rewardNoDataGameObject;
        [SerializeField] PlayerResourceIconButtonComponent _playerResourceIconButtonComponentPrefab;

        [Header("ステージ情報")]
        [SerializeField] GameObject _stageInfoTitleComponent;
        [SerializeField] GameObject _stageInfoArea;
        [SerializeField] UIText _stageInfoText;

        public ScrollRect ScrollRootComponent => _scrollRootComponent;

        public void InstantiateEnemyThumbnail(HomeStageInfoEnemyCharacterViewModel viewModel)
        {
            var enemyThumbnail = Instantiate(_enemyThumbnail, _scrollRectTransform);
            enemyThumbnail.Setup(
                viewModel.CharacterColor,
                viewModel.CharacterUnitKind,
                viewModel.EnemyCharacterIconAssetPath,
                viewModel.CharacterName,
                viewModel.CharacterUnitRoleType);
        }

        public void SetScrollRootComponentHeight()
        {
            SetScrollRootComponentHeight(_scrollRootComponentHeight);
        }

        public void SetScrollRootComponentHeightWithSpecialRule()
        {
            SetScrollRootComponentHeight(_scrollRootComponentHeightWithSpecialRule);
        }

        void SetScrollRootComponentHeight(float height)
        {
            var scrollRootComponent = _scrollRootComponent.gameObject;
            var sizeDelta = ((RectTransform)scrollRootComponent.transform).sizeDelta;
            sizeDelta.y = height;
            ((RectTransform)scrollRootComponent.transform).sizeDelta = sizeDelta;
        }

        public void SetInGameDescription(InGameDescription inGameDescription)
        {
            _stageInfoTitleComponent.SetActive(!inGameDescription.IsEmpty());
            _stageInfoArea.SetActive(!inGameDescription.IsEmpty());
            _stageInfoText.gameObject.SetActive(!inGameDescription.IsEmpty());
            _stageInfoText.SetText(inGameDescription.Value);
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

        public void SetupInGameSpecialRuleEmpty()
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

        public void SetupUnitColor(List<CharacterColor> characterColors)
        {
            _inGameSpecialRuleComponent.SetupUnitColor(characterColors);
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

        public void SetupHeaderComment(
            InGameSpecialRuleFromUnitSelectFlag isFromUnitSelect,
            InGameSpecialRuleExistFormationRuleFlag existsFormationRule)
        {
            _inGameSpecialRuleComponent.SetupHeaderComment(isFromUnitSelect, existsFormationRule);
        }

        public void SetUpSpecialRuleHeader(
            InGameSpecialRuleExistFormationRuleFlag existsFormationRule,
            InGameSpecialRuleExistOtherRuleFlag existsOtherRule)
        {
            _inGameSpecialRuleComponent.SetUpSpecialRuleHeader(existsFormationRule, existsOtherRule);
        }

        public void SetupReward(IReadOnlyList<PlayerResourceIconViewModel> resourceIconViewModelList, Action<PlayerResourceIconViewModel> onItemIconTapped)
        {
            _rewardNoDataGameObject.SetActive(resourceIconViewModelList.Count <= 0);
            _rewardIconListRoot.gameObject.SetActive(resourceIconViewModelList.Count > 0);

            foreach (var viewModel in resourceIconViewModelList)
            {
                var rewardItemIcon = Instantiate(_playerResourceIconButtonComponentPrefab, _rewardIconListRoot.RectTransform);
                rewardItemIcon.Setup(viewModel, () => onItemIconTapped(viewModel));
            }
        }
    }
}
