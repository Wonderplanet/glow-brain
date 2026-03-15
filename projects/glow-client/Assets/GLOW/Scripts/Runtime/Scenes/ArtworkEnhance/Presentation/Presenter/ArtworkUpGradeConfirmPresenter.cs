using System.Collections.Generic;
using System.Threading;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Constants;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCase;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Presentation.Translator;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Presenter
{
    public class ArtworkUpGradeConfirmPresenter : IArtworkUpGradeConfirmDelegate
    {
        [Inject] ArtworkGradeUpConfirmViewController ViewController { get; }
        [Inject] ArtworkGradeUpConfirmViewController.Argument Argument { get; }
        [Inject] ArtworkUpGradeConfirmUseCase ArtworkUpGradeConfirmUseCase { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] ArtworkGradeUpUseCase ArtworkGradeUpUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        CancellationToken _cancellationToken;

        void IArtworkUpGradeConfirmDelegate.OnViewDidLoad()
        {
            var useCaseModel = ArtworkUpGradeConfirmUseCase.GetArtworkEnhanceConfirmUseCaseModel(Argument.MstArtworkId);
            var viewModel = ArtworkUpGradeConfirmViewModelTranslator.Translate(useCaseModel);

            ViewController.SetUpView(viewModel);
        }

        void IArtworkUpGradeConfirmDelegate.OnItemIconTapped(PlayerResourceIconViewModel iconViewModel)
        {
            ItemDetailWireFrame.ShowItemDetailView(iconViewModel, ViewController);
        }

        void IArtworkUpGradeConfirmDelegate.OnInfoButtonTapped()
        {
            var argument = new ArtworkGradeContentsViewController.Argument(Argument.MstArtworkId);
            var controller = ViewFactory.Create<ArtworkGradeContentsViewController,
                ArtworkGradeContentsViewController.Argument>(argument);

            ViewController.PresentModally(controller);
        }

        void IArtworkUpGradeConfirmDelegate.OnConfirmButtonTapped()
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async ct =>
            {
                await ArtworkGradeUpUseCase.UpdateAndArtworkGradeUp(_cancellationToken, Argument.MstArtworkId);
                ViewController.OnClose();

                SoundEffectPlayer.Play(SoundEffectId.SSE_031_004);

                var argument = new ArtworkGradeUpAnimViewController.Argument(Argument.MstArtworkId);
                var controller = ViewFactory.Create<ArtworkGradeUpAnimViewController,
                    ArtworkGradeUpAnimViewController.Argument>(argument);

                ViewController.PresentModally(controller);

                Argument.OnConfirm?.Invoke();
            });
        }

        void IArtworkUpGradeConfirmDelegate.OnBackButtonTapped()
        {
            ViewController.OnClose();
        }
    }
}
