using System.Collections.Generic;
using GLOW.Core.Modules.TimeScaleController;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.InGame.Domain.Models.InGameUnitDetail;
using GLOW.Scenes.InGame.Domain.UseCases;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail;
using GLOW.Scenes.InGame.Presentation.Views.InGameUnitDetail;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Presenters
{
    public class InGameUnitDetailPresenter : IInGameUnitDetailViewDelegate
    {
        [Inject] InGameUnitDetailViewController ViewController { get; }
        [Inject] InGameUnitDetailViewController.Argument Argument { get; }
        [Inject] InGameUnitDetailUseCase UseCase { get; }
        [Inject] ITimeScaleController TimeScaleController { get; }
        [Inject]  BattleStateEffectViewManager BattleStateEffectViewManager { get; }

        ITimeScaleControlHandler _timeScaleControlHandler;

        void IInGameUnitDetailViewDelegate.OnViewDidLoad()
        {
            var model = UseCase.GetInGameUnitDetail(Argument.UserUnitId);
            var viewModel = TranslateDetailViewModel(model);
            ViewController.Setup(viewModel, BattleStateEffectViewManager);
        }

        void IInGameUnitDetailViewDelegate.ViewDidAppear()
        {
            // チュートリアル中は速度変更しない
            if(ViewController.IsPlayingTutorial) return;
            _timeScaleControlHandler = TimeScaleController.ChangeTimeScale(
                0.1f,
                TimeScaleType.Multiply,
                TimeScalePriorityDefinitions.UnitDetail);
        }

        void IInGameUnitDetailViewDelegate.OnClosed()
        {
            _timeScaleControlHandler?.Dispose();
            _timeScaleControlHandler = null;
        }

        InGameUnitDetailViewModel TranslateDetailViewModel(InGameUnitDetailModel model)
        {
            return new InGameUnitDetailViewModel(
                TranslateInfoViewModel(model.Info),
                TranslateSpecialAttackViewModel(model.SpecialAttack),
                TranslateAbilityViewModel(model.AbilityList),
                TranslateStatusViewModel(model.Status));
        }

        InGameUnitDetailInfoViewModel TranslateInfoViewModel(InGameUnitDetailInfoModel model)
        {
            return new InGameUnitDetailInfoViewModel(
                CharacterIconViewModelTranslator.Translate(model.IconModel),
                model.Name,
                model.StateEffectTypeList,
                model.BonusPercentage);
        }

        InGameUnitDetailSpecialAttackViewModel TranslateSpecialAttackViewModel(InGameUnitDetailSpecialAttackModel model)
        {
            return new InGameUnitDetailSpecialAttackViewModel(
                model.Name,
                model.Description,
                model.CoolTime);
        }

        IReadOnlyList<UnitEnhanceAbilityViewModel> TranslateAbilityViewModel(IReadOnlyList<UnitEnhanceAbilityModel> modelList)
        {
            var viewModelList = new List<UnitEnhanceAbilityViewModel>();
            foreach (var model in modelList)
            {
                var ability = new UnitEnhanceAbilityViewModel(
                    model.Ability,
                    model.UnlockUnitLevel,
                    model.IsLock);
                viewModelList.Add(ability);
            }
            return viewModelList;
        }

        InGameUnitDetailStatusViewModel TranslateStatusViewModel(InGameUnitDetailStatusModel model)
        {
            return new InGameUnitDetailStatusViewModel(
                model.RoleType,
                model.Hp,
                model.CurrentHp,
                model.DefaultHp,
                model.AttackPower,
                model.DefaultAttackPower,
                model.AttackRange,
                model.MoveSpeed,
                model.DefaultMoveSpeed,
                model.InGameUnitDetailBalloonMessageList,
                model.IsTutorialIntroductionUnit);
        }
    }
}
