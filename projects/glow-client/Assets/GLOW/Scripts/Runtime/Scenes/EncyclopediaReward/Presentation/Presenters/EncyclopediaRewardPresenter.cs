using System.Collections.Generic;
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ValueObject;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.EncyclopediaEffectDialog.Presentation.Views;
using GLOW.Scenes.EncyclopediaReward.Domain.Models;
using GLOW.Scenes.EncyclopediaReward.Domain.UseCases;
using GLOW.Scenes.EncyclopediaReward.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaReward.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaReward.Presentation.Presenters
{
    /// <summary>
    /// 91_図鑑
    /// 　91-1_図鑑
    /// 　　91-1-3_キャラ図鑑ランク
    /// </summary>
    public class EncyclopediaRewardPresenter : IEncyclopediaRewardViewDelegate
    {
        [Inject] EncyclopediaRewardViewController ViewController { get; }
        [Inject] GetEncyclopediaRewardUseCase GetEncyclopediaRewardUseCase { get; }
        [Inject] ReceiveUnitEncyclopediaRewardUseCase ReceiveUnitEncyclopediaRewardUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }

        void IEncyclopediaRewardViewDelegate.OnViewWillAppear()
        {
            UpdateRewardList();
        }

        void IEncyclopediaRewardViewDelegate.OnSelectLockReward(EncyclopediaRewardListCellViewModel cellViewModel)
        {
            var text = ZString.Format("キャラの累計グレード数{0}で開放", cellViewModel.RequireGrade.Value);
            CommonToastWireFrame.ShowScreenCenterToast(text);
        }

        void IEncyclopediaRewardViewDelegate.OnSelectReward(EncyclopediaRewardListCellViewModel cellViewModel)
        {
            ReceiveReward(new List<EncyclopediaRewardListCellViewModel>() {cellViewModel});
        }

        void IEncyclopediaRewardViewDelegate.OnShowEncyclopediaEffectButtonTapped()
        {
            var vc = ViewFactory.Create<EncyclopediaEffectDialogViewController>();
            ViewController.PresentModally(vc);
        }

        void IEncyclopediaRewardViewDelegate.OnReceiveAllRewardButtonTapped(IReadOnlyList<EncyclopediaRewardListCellViewModel> cellViewModels)
        {
            ReceiveReward(cellViewModels);
        }

        void IEncyclopediaRewardViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IEncyclopediaRewardViewDelegate.OnBackToHomeButtonTapped()
        {
            HomeFooterDelegate.BackToHome();
        }

        void UpdateRewardList()
        {
            var model = GetEncyclopediaRewardUseCase.GetRewardList();
            var viewModel = Translate(model);
            ViewController.Setup(viewModel);
        }

        void ReceiveReward(IReadOnlyList<EncyclopediaRewardListCellViewModel> cellViewModels)
        {
            DoAsync.Invoke(ViewController.View, async ct =>
            {
                IReadOnlyList<CommonReceiveResourceModel> results;
                using (ScreenInteractionControl.Lock())
                {
                    var ids = cellViewModels
                        .Select(cell => cell.MstEncyclopediaRewardId)
                        .ToList();

                    results = await ReceiveUnitEncyclopediaRewardUseCase.ReceiveRewards(ct, ids);
                }

                var viewModel = results
                    .Select(r =>
                        CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(r))
                    .ToList();

                await CommonReceiveWireFrame.ShowAsync(
                    ct,
                    viewModel,
                    RewardTitle.Default,
                    ReceivedRewardDescription.Empty);

                HomeHeaderDelegate.UpdateStatus();
                await HomeHeaderDelegate.PlayExpGaugeAnimationAsync(ct);

                UpdateRewardList();
            });
        }

        EncyclopediaRewardViewModel Translate(EncyclopediaRewardModel model)
        {
            var releasedCells = model.ReleasedCells.Select(TranslateRewardCell).ToList();
            var lockedCells = model.LockedCells.Select(TranslateRewardCell).ToList();
            return new EncyclopediaRewardViewModel(
                model.CurrentGrade,
                releasedCells,
                lockedCells
            );
        }

        EncyclopediaRewardListCellViewModel TranslateRewardCell(EncyclopediaRewardListCellModel model)
        {
            return new EncyclopediaRewardListCellViewModel(
                model.MstEncyclopediaRewardId,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.RewardItem),
                model.RequireGrade,
                model.EffectType,
                model.EffectValue,
                model.Badge,
                model.IsReceived
            );
        }
    }
}
