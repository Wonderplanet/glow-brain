using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.InGameSpecialRule.Presentation.ViewModels;
using UIKit;
using Zenject;

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
    public class AdventBattleInfoViewController : UIViewController<AdventBattleInfoView>
    {
        public record Argument(MasterDataId MstAdventBattleId);

        [Inject] IAdventBattleInfoDelegate ViewDelegate { get; }

        public Action ReopenStageInfoAction { get; set; }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            ViewDelegate.OnViewWillAppear();
        }

        public void SetupAdventBattleInfoView(
            IReadOnlyList<HomeStageInfoEnemyCharacterViewModel> enemyViewModels,
            IReadOnlyList<PlayerResourceIconViewModel> rewardViewModels,
            InGameDescription inGameDescription)
        {
            foreach (var adventBattleInfoEnemyViewModel in enemyViewModels)
            {
                AddEnemyThumbnail(adventBattleInfoEnemyViewModel);
            }

            // 一番上に固定
            ActualView.ScrollRootComponent.verticalNormalizedPosition = 1;

            ActualView.SetupReward(rewardViewModels, OnTappedPlayerResourceIcon);

            ActualView.SetInGameDescription(inGameDescription);
        }

        void AddEnemyThumbnail(HomeStageInfoEnemyCharacterViewModel viewModel)
        {
            ActualView.InstantiateEnemyThumbnail(viewModel);
        }

        public void SetupInGameSpecialRule(InGameSpecialRuleViewModel viewModel)
        {
            var isEmptyViewModel = viewModel.IsEmpty();

            if (isEmptyViewModel)
            {
                ActualView.HideInGameSpecialRule();
                ActualView.SetupInGameSpecialRuleEmpty();
                ActualView.SetScrollRootComponentHeight();
            }
            else
            {
                ActualView.InitializeSpecialRule();
                ActualView.ShowInGameSpecialRule();
                ActualView.SetupSeriesLogos(viewModel.SeriesLogoImagePathList);
                ActualView.SetupUnitRarity(viewModel.UnitRarities);
                ActualView.SetupUnitRoleType(viewModel.UnitRoleTypes);
                ActualView.SetupUnitCount(viewModel.UnitAmount);
                ActualView.SetupAttackRange(viewModel.UnitAttackRangeTypes);
                ActualView.SetupSpeedAttack(viewModel.IsSpeedAttack);
                ActualView.SetupTimeLimit(viewModel.TimeLimit);
                ActualView.SetupDefenseTarget(viewModel.IsDefenseTarget);
                ActualView.SetupEnemyDestruction(viewModel.EnemyDestructionCount, viewModel.IsEnemyDestruction);
                ActualView.SetupSpecificEnemyDestruction(
                    viewModel.SpecificEnemyDestructionTargetName,
                    viewModel.SpecificEnemyDestructionCount,
                    viewModel.IsSpecificEnemyDestruction);
                ActualView.SetupStartOutpost(viewModel.StartOutpostHp, viewModel.IsStartOutpostHp);
                ActualView.SetupEnemyOutpostDamageInvalidation(viewModel.IsEnemyOutpostDamageInvalidation);
                ActualView.SetupNoContinue(viewModel.IsNoContinue);
                ActualView.SetupHeaderComment(viewModel.IsFromUnitSelect, viewModel.ExistsFormationRule);
                ActualView.SetScrollRootComponentHeightWithSpecialRule();
                ActualView.SetUpSpecialRuleHeader(viewModel.ExistsFormationRule, viewModel.ExistsOtherRule);
            }
        }

        void OnTappedPlayerResourceIcon(PlayerResourceIconViewModel viewModel)
        {
            ViewDelegate.OnTappedPlayerResourceIcon(viewModel);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
    }
}
