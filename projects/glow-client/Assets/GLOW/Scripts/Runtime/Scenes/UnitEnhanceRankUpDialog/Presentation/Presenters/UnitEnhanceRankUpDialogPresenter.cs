using System;
using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Domain.UseCases;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.Presenters
{
    public class UnitEnhanceRankUpDialogPresenter : IUnitEnhanceRankUpDialogViewDelegate
    {
        [Inject] UnitEnhanceRankUpDialogViewController ViewController { get; }
        [Inject] UnitEnhanceRankUpDialogViewController.Argument Argument { get; }
        [Inject] UnitEnhanceRankUpDialogUseCase UseCase { get; }
        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(UnitEnhanceRankUpDialogPresenter), nameof(OnViewDidLoad));

            var model = UseCase.GetUnitEnhanceRankUpDialogModel(Argument.UserUnitId, Argument.BeforeRank, Argument.AfterRank);
            var newlyAbilityViewModels = model.NewlyAbilityModels
                .Select(abilityModel => new UnitEnhanceAbilityViewModel(
                    abilityModel.Ability,
                    abilityModel.UnlockUnitLevel,
                    abilityModel.IsLock))
                .ToList();

            var viewModel = new UnitEnhanceRankUpDialogViewModel(
                CharacterStandImageAssetPath.FromAssetKey(model.AssetKey),
                model.RoleType,
                model.BeforeLimitLevel,
                model.AfterLimitLevel,
                model.BeforeHP,
                model.AfterHP,
                model.BeforeAttackPower,
                model.AfterAttackPower,
                newlyAbilityViewModels);

            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                ViewController.Setup(viewModel);
                SoundEffectPlayer.Play(SoundEffectId.SSE_031_005);

                await UniTask.Delay(TimeSpan.FromSeconds(1.0f), cancellationToken: cancellationToken);

                ViewController.AnimationEnded();
            });
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(UnitEnhanceRankUpDialogPresenter), nameof(OnViewDidUnload));
        }

        public void OnClose()
        {
            ViewController.Dismiss();
        }
    }
}
