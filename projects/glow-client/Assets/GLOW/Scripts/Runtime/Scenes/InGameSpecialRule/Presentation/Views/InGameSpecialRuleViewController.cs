using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.InGameSpecialRule.Presentation.Views
{
    public class InGameSpecialRuleViewController : UIViewController<InGameSpecialRuleView>
    {
        [Inject] IInGameSpecialRuleViewDelegate ViewDelegate { get; }

        public record Argument(
            MasterDataId SpecialRuleTargetMstId,
            InGameContentType SpecialRuleContentType,
            InGameSpecialRuleFromUnitSelectFlag IsFromUnitSelect);

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public void SetViewModel(InGameSpecialRuleViewModel viewModel)
        {
            var isEmptyViewModel = viewModel.IsEmpty();

            if (isEmptyViewModel)
            {
                ActualView.SetupEmpty();
            }
            else
            {
                ActualView.InitializeSpecialRule();
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
                ActualView.SetUpSpecialRuleHeader(viewModel.ExistsFormationRule, viewModel.ExistsOtherRule);
            }
        }

        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
