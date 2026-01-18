using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PartyFormation.Domain.Models;
using GLOW.Scenes.PartyFormation.Domain.UseCases;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using GLOW.Scenes.PartyFormation.Presentation.Views;
using GLOW.Scenes.UnitList.Domain.Constants;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Presentation.Presenters
{
    public class PartyFormationPartyPresenter : IPartyFormationPartyViewDelegate
    {
        [Inject] GetPartyFormationPartyUseCase UseCase { get; }
        [Inject] IPartyFormationPartyViewController ViewController { get; }
        [Inject] PartyFormationPartyViewController.Argument Args { get; }

        void IPartyFormationPartyViewDelegate.ViewWillAppear()
        {
            var model = UseCase.GetModel(
                Args.PartyNo,
                Args.SpecialRuleTargetMstId,
                Args.SpecialRuleContentType,
                Args.UnitSortFilterCacheType,
                Args.EventBonusGroupId);
            var viewModel = TranslateToPartyViewModel(model);

            ViewController.Setup(viewModel);
        }

        void IPartyFormationPartyViewDelegate.UpdateView()
        {
            var model = UseCase.GetModel(
                Args.PartyNo,
                Args.SpecialRuleTargetMstId,
                Args.SpecialRuleContentType,
                Args.UnitSortFilterCacheType,
                Args.EventBonusGroupId);
            var viewModel = TranslateToPartyViewModel(model);
            ViewController.Setup(viewModel);
        }

        PartyFormationPartyViewModel TranslateToPartyViewModel(PartyFormationPartyModel model)
        {
            var members = model.Members
                .Select(TranslateToPartyMemberViewModel)
                .ToList();
            return new PartyFormationPartyViewModel(
                model.PartyNo,
                model.Name,
                model.TotalPartyStatus,
                model.TotalPartyStatusUpperArrowFlag,
                model.SlotCount,
                model.SpecialRulePartyUnitNum,
                members);
        }

        PartyFormationPartyMemberViewModel TranslateToPartyMemberViewModel(PartyFormationPartyMemberModel model)
        {
            return new PartyFormationPartyMemberViewModel(
                model.UserUnitId,
                model.ImageAssetPath,
                model.Color,
                model.Rarity,
                model.Level,
                model.Cost,
                model.Grade,
                model.HP,
                model.AttackPower,
                model.Role,
                model.AttackRangeType,
                model.MoveSpeed,
                model.SortType,
                model.EventBonus,
                model.SpecialRuleItemModel.IsAchievedSpecialRule,
                model.InGameSpecialRuleUnitStatusTargetFlag
            );
        }
    }
}
