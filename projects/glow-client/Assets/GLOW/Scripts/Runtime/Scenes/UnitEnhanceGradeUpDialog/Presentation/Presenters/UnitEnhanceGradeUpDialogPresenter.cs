using System;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.EncyclopediaReward.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Domain.UseCases;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.Presenters
{
    public class UnitEnhanceGradeUpDialogPresenter : IUnitEnhanceGradeUpDialogViewDelegate
    {
        [Inject] UnitEnhanceGradeUpDialogViewController ViewController { get; }
        [Inject] UnitEnhanceGradeUpDialogViewController.Argument Argument { get; }
        [Inject] UnitEnhanceGradeUpDialogUseCase UseCase { get; }
        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        EncyclopediaRewardConditionAchievedFlag _isEncyclopediaRewardConditionAchieved;

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(UnitEnhanceGradeUpDialogPresenter), nameof(OnViewDidLoad));

            var model = UseCase.GetUnitEnhanceGradeUpDialogModel(Argument.UserUnitId, Argument.BeforeGrade, Argument.AfterGrade);
            _isEncyclopediaRewardConditionAchieved = model.IsEncyclopediaRewardConditionAchieved;

            var viewModel = new UnitEnhanceGradeUpDialogViewModel(
                model.RoleType,
                CharacterStandImageAssetPath.FromAssetKey(model.AssetKey),
                Argument.BeforeGrade,
                Argument.AfterGrade,
                model.BeforeHP,
                model.AfterHP,
                model.BeforeAttackPower,
                model.AfterAttackPower,
                model.SpecialAttackName,
                model.Description);

            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_031_004);
                ViewController.Setup(viewModel);

                await UniTask.Delay(TimeSpan.FromSeconds(1.0f), cancellationToken: cancellationToken);

                ViewController.AnimationEnded();
            });
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(UnitEnhanceGradeUpDialogPresenter), nameof(OnViewDidUnload));
        }

        public void OnClose()
        {
            if (!_isEncyclopediaRewardConditionAchieved)
            {
                CloseDialog();
                return;
            }

            MessageViewUtil.ShowMessageWith2Buttons(
                "確認",
                "キャラ図鑑ランクが開放されました。\nキャラ図鑑ランクのページに遷移しますか？",
                null,
                "OK",
                "閉じる",
                () =>
                {
                    CloseDialog();
                    ShowEncyclopediaReward();
                },
                CloseDialog,
                CloseDialog);
        }

        void CloseDialog()
        {
            ViewController.Dismiss();
        }

        void ShowEncyclopediaReward()
        {
            var vc = ViewFactory.Create<EncyclopediaRewardViewController>();
            HomeViewNavigation.TryPush(vc, HomeContentDisplayType.BottomOverlap);
        }
    }
}
