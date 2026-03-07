using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using GLOW.Scenes.ArtworkExpandDialog.Domain.Evaluator;
using GLOW.Scenes.ArtworkExpandDialog.Domain.UseCases;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.ValueObject;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkExpandDialog.Presentation.Presenters
{
    /// <summary>
    /// 91_図鑑
    /// 　91-4_作品別原画表示
    /// 　　91-4-1_原画拡大ダイアログ
    /// </summary>
    public class ArtworkExpandDialogPresenter : IArtworkExpandDialogViewDelegate
    {
        [Inject] ArtworkExpandDialogViewController ViewController { get; }
        [Inject] ArtworkExpandDialogViewController.Argument Argument { get; }
        [Inject] GetArtworkExpandUseCase GetArtworkExpandUseCase { get; }
        [Inject] HasArtworkEvaluator HasArtworkEvaluator { get; }
        [Inject] ReceiveEncyclopediaFirstCollectionRewardUseCase ReceiveEncyclopediaFirstCollectionRewardUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IViewFactory ViewFactory { get; }

        void IArtworkExpandDialogViewDelegate.OnViewDidLoad()
        {
            var model = GetArtworkExpandUseCase.GetArtwork(Argument.MstArtworkId);
            var viewModel = ArtworkExpandDialogTranslator.Translate(model);

            bool isLock = !HasArtworkEvaluator.HasArtwork(Argument.MstArtworkId);

            switch (Argument.ArtworkDetailDisplayType)
            {
                case ArtworkDetailDisplayType.Normal:
                    ReceiveFirstCollectionReward(Argument.MstArtworkId);
                    ViewController.SetUpFromEncyclopedia(viewModel, isLock);
                    break;
                case ArtworkDetailDisplayType.GrayOut:
                    ViewController.SetUpFromExchangeShop(viewModel, isLock);
                    break;
            }
        }

        void ReceiveFirstCollectionReward(MasterDataId mstArtworkId)
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                var result = await ReceiveEncyclopediaFirstCollectionRewardUseCase.ReceiveReward(
                    cancellationToken,
                    mstArtworkId,
                    EncyclopediaType.Artwork
                );

                if(result.IsEmpty()) return;

                var rewards = result
                    .Select(r => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(r))
                    .ToList();
                CommonReceiveWireFrame.Show(rewards);
                HomeHeaderDelegate.UpdateStatus();
            });
        }

        void IArtworkExpandDialogViewDelegate.OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }

        void IArtworkExpandDialogViewDelegate.OnInfoButtonTapped()
        {
            var argument = new ArtworkAcquisitionRouteViewController.Argument(Argument.MstArtworkId);
            var controller = ViewFactory.Create<ArtworkAcquisitionRouteViewController,
                ArtworkAcquisitionRouteViewController.Argument>(argument);
            controller.OnTransitionAction = () =>
            {
                ViewController.Dismiss();
            };

            ViewController.PresentModally(controller);
        }
    }
}
