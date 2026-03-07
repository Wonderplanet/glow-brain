using System;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCase;
using GLOW.Scenes.ArtworkEnhance.Presentation.Translator;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Presenter
{
    public class ArtworkGradeUpAnimPresenter : IArtworkGradeUpAnimDelegate
    {
        [Inject] ArtworkGradeUpAnimViewController.Argument Argument { get; }
        [Inject] ArtworkGradeUpAnimViewController ViewController { get; }
        [Inject] ArtworkGradeUpAnimUseCase ArtworkGradeUpAnimUseCase { get; }

        void IArtworkGradeUpAnimDelegate.OnViewDidLoad()
        {
            var useCaseModel = ArtworkGradeUpAnimUseCase.CreateArtworkGradeUpAnim(Argument.MstArtworkId);
            var viewModel = ArtworkGradeUpAnimViewModelTranslator.Translate(useCaseModel);

            DoAsync.Invoke(ViewController.View, async ct =>
            {
                ViewController.Setup(viewModel);

                await UniTask.Delay(TimeSpan.FromSeconds(1.0f), cancellationToken: ct);

                ViewController.AnimationEnded();
            });
        }

        void IArtworkGradeUpAnimDelegate.OnBackButtonTapped()
        {
            ViewController.Dismiss();
        }
    }
}
