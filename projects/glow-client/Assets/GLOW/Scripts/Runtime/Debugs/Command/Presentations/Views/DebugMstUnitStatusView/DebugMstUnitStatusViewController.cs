using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Debugs.Command.Domains.UseCase;
using UIKit;

namespace GLOW.Debugs.Command.Presentations.Views.DebugMstUnitStatusView
{
    public enum DebugMstUnitStatusType
    {
        AttackStatus,
        LevelStatus,
        SpecialUnitSpecialAttackStatus,
    }

    public class DebugMstUnitStatusViewController : UIViewController<DebugMstUnitStatusView>
    {
        // 本当はアーキテクチャ的にViewModelに一度変換するのが必要だが、実装時間が惜しいのでUseCaseModelのまま使う
        // 後で困ったらちゃんとViewModel作る
        public void SetUp(DebugMstUnitStatusUseCaseModel model)
        {
            SetUpTopArea(model.AttackStatus);
            ActualView.ShowTextArea(DebugMstUnitStatusType.AttackStatus);
            ActualView.SetUpAttackStatusText(model.AttackStatus);
            ActualView.SetUpLevelStatusText(model.LevelStatuses);
            ActualView.SetUpSpecialUnitSpecialAttackStatusText(model.SpecialUnitSpecialParam);
            ActualView.SetUpSpecialUnitFilter(model.SpecialUnitSpecialParam);
        }

        void SetUpTopArea(DebugMstUnitAttackStatusUseCaseModel model)
        {
            var vm = new CharacterIconViewModel(
                model.AtBase.CharacterIconAssetPath,
                model.AtBattleBase.CharacterUnitRoleType,
                model.AtBase.CharacterColor,
                model.AtBase.Rarity,
                UnitLevel.Empty,
                model.AtBattleBase.SummonCost,
                UnitGrade.Minimum,
                model.AtBattleBase.MinHp,
                model.AtBattleBase.MinAttackPower,
                model.AtBattleBase.CharacterAttackRangeType,
                model.AtBattleBase.UnitMoveSpeed);

            ActualView.SetUpTopArea(
                model.AtPiece.ItemIconAssetPath,
                vm,
                model.AtBase.CharacterName,
                model.AtBase.SeriesName);
        }

        [UIAction]
        void OnClose()
        {
            Dismiss();
        }

        [UIAction]
        void OnAttackStatus()
        {
            ActualView.ShowTextArea(DebugMstUnitStatusType.AttackStatus);
        }
        [UIAction]
        void OnLevelStatus()
        {
            ActualView.ShowTextArea(DebugMstUnitStatusType.LevelStatus);
        }
        [UIAction]
        void OnSpecialUnitSpecialAttackStatus()
        {
            ActualView.ShowTextArea(DebugMstUnitStatusType.SpecialUnitSpecialAttackStatus);
        }

    }
}
