using System;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.InGameSpecialRule.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Views.HomeStageInfoView
{
    public class HomeStageInfoViewController : UIViewController<HomeStageInfoView>, IEscapeResponder
    {
        public record Argument(HomeStageInfoViewModel ViewModel)
        {
            public HomeStageInfoViewModel ViewModel { get; } = ViewModel;
        }

        [Inject] IHomeStageInfoViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ITutorialBackKeyViewDelegate TutorialBackKeyViewDelegate { get; }

        public Action ReopenStageInfoAction { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad(Args.ViewModel);
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            EscapeResponderRegistry.Unregister(this);
            ViewDelegate.OnViewDidUnload();
        }

        public void Initialize(HomeStageInfoViewModel homeStageInfoViewModel)
        {
            // SpecialRule設定
            SetupInGameSpecialRule(homeStageInfoViewModel.InGameSpecialRuleViewModel);

            // SpecialRule設定後のComponentの大きさ計算
            ActualView.SettingDialogViewSize(homeStageInfoViewModel.EnemyCharacters.Count,
                homeStageInfoViewModel.ArtworkFragmentResource.Count,
                homeStageInfoViewModel.PlayerResources.Count + homeStageInfoViewModel.SpeedAttackViewModel.ClearTimeRewards.Count,
                !homeStageInfoViewModel.SpeedAttackViewModel.IsEmpty());

            foreach (var stageInfoEnemyModel in homeStageInfoViewModel.EnemyCharacters)
            {
                SetupStageInfoEnemyThumbnail(stageInfoEnemyModel);
            }

            foreach (var stageInfoTreasureModel in homeStageInfoViewModel.ArtworkFragmentResource)
            {
                SetupStageInfoArtworkFragmentIcon(stageInfoTreasureModel);
            }

            foreach(var speedAttackReward in homeStageInfoViewModel.SpeedAttackViewModel.ClearTimeRewards)
            {
                SetupStageInfoSpeedAttackRewardIcon(speedAttackReward);
            }

            foreach(var stageInfoItemModel in homeStageInfoViewModel.PlayerResources)
            {
                SetupStageInfoRewardItemIcon(stageInfoItemModel);
            }

            ActualView.SetupClearTime(homeStageInfoViewModel.SpeedAttackViewModel.ClearTime);

            ActualView.ScrollRootComponent.verticalNormalizedPosition = 1;

            ActualView.SetInGameDescription(homeStageInfoViewModel.InGameDescription);
        }

        void SetupInGameSpecialRule(InGameSpecialRuleViewModel viewModel)
        {
            var isEmptyViewModel = viewModel.IsEmpty();

            if (isEmptyViewModel)
            {
                ActualView.HideInGameSpecialRule();
                ActualView.SetupEmpty();
            }
            else
            {
                ActualView.InitializeSpecialRule();
                ActualView.ShowInGameSpecialRule();
                ActualView.SetupSeriesLogos(viewModel.SeriesLogoImagePathList);
                ActualView.SetupUnitRarity(viewModel.UnitRarities);
                ActualView.SetupUnitRoleType(viewModel.UnitRoleTypes);
                ActualView.SetupUnitColor(viewModel.CharacterColors);
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
                ActualView.SetUpSpecialRuleHeader(viewModel.ExistsFormationRule, viewModel.ExistsOtherRule);
            }
        }

        void SetupStageInfoEnemyThumbnail(HomeStageInfoEnemyCharacterViewModel viewModel)
        {
            var enemyThumbnail = ActualView.InstantiateEnemyThumbnail();
            enemyThumbnail.Setup(
                viewModel.CharacterColor,
                viewModel.CharacterUnitKind,
                viewModel.EnemyCharacterIconAssetPath,
                viewModel.CharacterName,
                viewModel.CharacterUnitRoleType);
        }

        void SetupStageInfoArtworkFragmentIcon(PlayerResourceIconViewModel viewModel)
        {
            var rewardItemIcon = ActualView.InstantiateArtworkFragmentPlayerResourceIconButtonComponent();
            rewardItemIcon.Setup(viewModel, () =>
            {
                OnTappedPlayerResourceIcon(viewModel);
            });
        }

        void SetupStageInfoRewardItemIcon(PlayerResourceIconViewModel viewModel)
        {
            var rewardItemIcon = ActualView.InstantiateRewardPlayerResourceIconButtonComponent();
            rewardItemIcon.Setup(viewModel, () =>
            {
                OnTappedPlayerResourceIcon(viewModel);
            });
        }

        void SetupStageInfoSpeedAttackRewardIcon(PlayerResourceIconViewModel viewModel)
        {
            var speedAttackRewardIcon = ActualView.InstantiateSpeedAttackReward();
            speedAttackRewardIcon.Setup(viewModel);
        }

        void OnTappedPlayerResourceIcon(PlayerResourceIconViewModel viewModel)
        {
            ViewDelegate.OnTappedPlayerResourceIcon(viewModel);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (TutorialBackKeyViewDelegate.IsPlayingTutorial())
            {
                // トーストでバックキーが無効であると表示する
                CommonToastWireFrame.ShowInvalidOperationMessage();

                return true;
            }

            return false;
        }

        [UIAction]
        void OnClose()
        {
            ViewDelegate.OnClose();
        }
    }
}
